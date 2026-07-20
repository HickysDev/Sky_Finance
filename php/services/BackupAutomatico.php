<?php

/**
 * Backup automático disparado por alteração de dados.
 *
 * Por que por alteração e não por horário: este sistema roda local, com o MySQL
 * subido à mão pelo painel do XAMPP. Uma tarefa agendada dispara em horários em
 * que o banco costuma estar desligado e grava arquivo vazio. Disparar na escrita
 * garante que o backup só acontece quando o banco está de pé — que é exatamente
 * quando existe dado novo a proteger.
 *
 * Regra de ouro deste arquivo: um backup ruim NUNCA pode apagar um backup bom.
 * Todo dump é validado antes de virar arquivo definitivo, e a rotação só conta
 * arquivos válidos. Foi assim que o backup anterior destruiu o histórico: gravava
 * arquivos de 0 byte e a rotação empurrava os bons para fora.
 */
class BackupAutomatico
{
    /** Pasta onde os snapshots ficam. Fora do htdocs para não ser servida via HTTP. */
    const DIR = 'C:/SkyFinanceBackups/auto';

    /** Só gera um novo snapshot se o último tiver mais que isto (segundos). */
    const INTERVALO = 300; // 5 minutos

    /** Quantos snapshots recentes manter. */
    const MANTER_RECENTES = 60;

    /** Por quantos dias manter também um snapshot "do dia" (o mais antigo de cada dia). */
    const MANTER_DIARIOS = 30;

    /** Menor tamanho plausível para um dump deste banco. Abaixo disto, é lixo. */
    const TAMANHO_MINIMO = 2048;

    private static $pendente = false;
    private static $registrado = false;

    /**
     * True se o SQL altera dados. Usado para não disparar backup em SELECT
     * nem no "SET @uid" que a conexão executa a cada request.
     */
    public static function ehEscrita(string $sql): bool
    {
        return (bool) preg_match(
            '/^\s*(INSERT|UPDATE|DELETE|REPLACE|TRUNCATE|ALTER|DROP|CREATE)\b/i',
            ltrim($sql, " \t\n\r(")
        );
    }

    /**
     * Marca que esta requisição alterou o banco. O dump em si só acontece no fim
     * da requisição, uma única vez, mesmo que a tela faça 20 alterações seguidas.
     */
    public static function marcarAlteracao(): void
    {
        self::$pendente = true;

        if (!self::$registrado) {
            self::$registrado = true;
            register_shutdown_function([self::class, 'aoFinalizar']);
        }
    }

    /** Chamado automaticamente no fim de toda requisição que alterou dados. */
    public static function aoFinalizar(): void
    {
        if (!self::$pendente) return;
        self::$pendente = false;

        try {
            if (self::precisaGerar()) {
                self::gerar('alteracao');
            }
        } catch (Throwable $e) {
            self::log('ERRO inesperado: ' . $e->getMessage());
        }
    }

    /** True se já passou tempo suficiente desde o último snapshot válido. */
    private static function precisaGerar(): bool
    {
        $ultimo = self::ultimoSnapshot();
        if (!$ultimo) return true;
        return (time() - filemtime($ultimo)) >= self::INTERVALO;
    }

    private static function ultimoSnapshot(): ?string
    {
        $arquivos = self::snapshotsValidos();
        return $arquivos ? end($arquivos) : null;
    }

    /**
     * Gera um snapshot. Retorna o caminho do arquivo, ou null se falhou.
     * $motivo entra no nome do arquivo (ex: 'alteracao', 'pre-restauracao').
     */
    public static function gerar(string $motivo = 'manual'): ?string
    {
        if (!is_dir(self::DIR) && !@mkdir(self::DIR, 0755, true)) {
            self::log('ERRO: não foi possível criar ' . self::DIR);
            return null;
        }

        $dump = self::caminhoMysqldump();
        if (!$dump) {
            self::log('ERRO: mysqldump não encontrado');
            return null;
        }

        $motivo = preg_replace('/[^a-z0-9\-]/i', '', $motivo) ?: 'manual';
        $stamp  = date('Y-m-d_H-i-s');
        $final  = self::DIR . "/skyfinance_{$stamp}_{$motivo}.sql";
        // Grava em .tmp e só promove depois de validar: se o dump sair quebrado,
        // o arquivo definitivo nunca chega a existir e a rotação não é acionada.
        $temp   = $final . '.tmp';

        // Sem aspas envolvendo o comando inteiro: o cmd do Windows interpreta
        // `""C:\...` como nome de programa e falha. Aspas só nos caminhos.
        $senha = DB_PASS !== '' ? ' -p' . DB_PASS : '';
        $cmd = sprintf(
            '"%s" -u %s%s --databases %s --single-transaction --routines --events '
            . '--default-character-set=utf8mb4 --result-file="%s" 2>&1',
            $dump,
            DB_USER,
            $senha,
            DB_NAME,
            $temp
        );

        $saida = [];
        $code  = 0;
        @exec($cmd, $saida, $code);

        if (!self::dumpValido($temp)) {
            @unlink($temp);
            self::log("ERRO: dump inválido (exit={$code}) " . trim(implode(' | ', $saida)));
            return null; // histórico anterior fica intacto
        }

        if (!@rename($temp, $final)) {
            @unlink($temp);
            self::log('ERRO: falha ao renomear o dump');
            return null;
        }

        self::log('OK: ' . basename($final) . ' (' . filesize($final) . ' bytes)');
        self::rotacionar();

        return $final;
    }

    /**
     * Um dump só é aceito se realmente contiver o banco: tamanho plausível,
     * estrutura, dados e a linha final que o mysqldump escreve quando termina
     * sem erro. Arquivo truncado por falta de disco ou queda do MySQL é rejeitado.
     */
    private static function dumpValido(string $arquivo): bool
    {
        if (!is_file($arquivo) || filesize($arquivo) < self::TAMANHO_MINIMO) return false;

        $conteudo = @file_get_contents($arquivo);
        if ($conteudo === false) return false;

        return strpos($conteudo, 'CREATE TABLE') !== false
            && strpos($conteudo, 'INSERT INTO') !== false
            && strpos($conteudo, 'Dump completed') !== false;
    }

    /** Lista snapshots válidos, do mais antigo para o mais recente. */
    private static function snapshotsValidos(): array
    {
        if (!is_dir(self::DIR)) return [];
        $arquivos = glob(self::DIR . '/skyfinance_*.sql') ?: [];
        $arquivos = array_values(array_filter($arquivos, function ($a) {
            return filesize($a) >= self::TAMANHO_MINIMO;
        }));
        sort($arquivos);
        return $arquivos;
    }

    /**
     * Rotação conservadora. Mantém os N mais recentes E o primeiro snapshot de
     * cada dia dos últimos MANTER_DIARIOS dias — assim, uma corrupção percebida
     * só depois de semanas ainda encontra uma cópia antiga sadia. Nunca apaga
     * arquivo se sobrar menos de um snapshot válido.
     */
    private static function rotacionar(): void
    {
        $arquivos = self::snapshotsValidos();
        if (count($arquivos) <= self::MANTER_RECENTES) return;

        $recentes = array_slice($arquivos, -self::MANTER_RECENTES);
        $manter   = array_flip($recentes);

        // Guarda o primeiro snapshot de cada dia (profundidade histórica)
        $limiteDiario = strtotime('-' . self::MANTER_DIARIOS . ' days');
        $vistos = [];
        foreach ($arquivos as $a) {
            $mt = filemtime($a);
            if ($mt < $limiteDiario) continue;
            $dia = date('Y-m-d', $mt);
            if (!isset($vistos[$dia])) {
                $vistos[$dia] = true;
                $manter[$a] = true;
            }
        }

        foreach ($arquivos as $a) {
            if (isset($manter[$a])) continue;
            @unlink($a);
        }
    }

    private static function caminhoMysqldump(): ?string
    {
        $candidatos = [
            'C:/xampp/mysql/bin/mysqldump.exe',
            'C:/wamp64/bin/mysql/mysql8.0.31/bin/mysqldump.exe',
            '/usr/bin/mysqldump',
        ];
        if (defined('MYSQLDUMP_PATH') && is_file(MYSQLDUMP_PATH)) return MYSQLDUMP_PATH;
        foreach ($candidatos as $c) {
            if (is_file($c)) return $c;
        }
        return null;
    }

    private static function log(string $msg): void
    {
        if (!is_dir(self::DIR)) return;
        @file_put_contents(
            self::DIR . '/backup.log',
            '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL,
            FILE_APPEND
        );
    }

    /** Info para a tela de Backup: quantos snapshots existem e qual o último. */
    public static function status(): array
    {
        $arquivos = self::snapshotsValidos();
        $ultimo   = $arquivos ? end($arquivos) : null;
        return [
            'total'      => count($arquivos),
            'ultimo'     => $ultimo ? basename($ultimo) : null,
            'ultimo_em'  => $ultimo ? date('d/m/Y H:i:s', filemtime($ultimo)) : null,
            'tamanho_mb' => round(array_sum(array_map('filesize', $arquivos)) / 1048576, 2),
            'pasta'      => self::DIR,
        ];
    }
}
