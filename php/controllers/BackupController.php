<?php
require_once __DIR__ . '/../../conn/config.php';
require_once __DIR__ . '/../../conn/conn.php';
// O backup exporta o banco inteiro (todos os usuários, incluindo senha_hash).
// Sem esta proteção, qualquer visitante baixaria a base completa.
require_once __DIR__ . '/../middleware/auth.php';

// Backup e restauração são operações sobre a base inteira, não sobre os dados de
// um usuário — por isso ficam restritas ao administrador (id 1), a mesma regra que
// UsuariosController aplica a listar/adicionar/remover/reset_dados.
// Sem isto, qualquer usuário comum baixaria os dados e os hashes de senha dos demais.
if ((int) ($_SESSION['usuario_id'] ?? 0) !== 1) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['erro' => 'Apenas o administrador pode exportar ou restaurar backups.']);
    exit;
}

$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

// ── EXPORTAR ────────────────────────────────────────────────────────────────
if ($acao === 'exportar') {

    // Backup COMPLETO: todas as tabelas, todos os usuários, sem filtro.
    // A restauração faz DELETE + INSERT por tabela, então exportar tudo é o que
    // mantém o arquivo consistente — um export parcial apagaria os demais usuários.
    // Ordem: pais antes dos filhos, para a restauração respeitar as FKs.
    $tabelas = [
        'usuarios',
        'login_tentativas',
        'categorias',
        'cartoes_credito',
        'responsaveis',
        'gastos',
        'parcelas',
        'gastos_recorrentes',
        'gastos_recorrentes_lancamentos',
        'renda_mensal',
        'contas_pessoa',
        'contas_fixas',
        'contas_fixas_pagamentos',
        'faturas_pagas',
        'cofrinhos',
        'cofrinho_aportes',
        'orcamentos',
        'lista_desejos',
    ];

    $conn  = Database::getConnection();
    $stamp = date('d/m/Y H:i');
    $fname = 'skyfinance_' . date('Y-m-d_H-i') . '.sql';

    $sql  = "-- Sky Finance Backup COMPLETO | Gerado em: {$stamp}\n";
    $sql .= "-- Contém todas as tabelas e todos os usuários.\n";
    $sql .= "-- Importe via a página de Backup do sistema (substitui a base inteira).\n\n";
    $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n";
    $sql .= "SET NAMES utf8mb4;\n\n";

    foreach ($tabelas as $t) {
        $sql .= dumpTabela($conn, $t);
    }

    $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $fname . '"');
    header('Content-Length: ' . strlen($sql));
    header('Pragma: no-cache');
    echo $sql;
    exit;
}

// ── ESTRUTURA (setup completo) ───────────────────────────────────────────────
if ($acao === 'estrutura') {
    $arquivo = realpath(__DIR__ . '/../../sql/setup_completo.sql');
    if (!$arquivo || !is_readable($arquivo)) {
        http_response_code(500);
        echo 'Arquivo setup_completo.sql não encontrado.';
        exit;
    }
    $fname = 'skyfinance_setup_' . date('Y-m-d') . '.sql';
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $fname . '"');
    header('Content-Length: ' . filesize($arquivo));
    header('Pragma: no-cache');
    readfile($arquivo);
    exit;
}

// ── IMPORTAR ────────────────────────────────────────────────────────────────
if ($acao === 'importar') {
    header('Content-Type: application/json; charset=utf-8');

    if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['ok' => false, 'msg' => 'Nenhum arquivo recebido.']);
        exit;
    }

    $ext = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));
    if ($ext !== 'sql') {
        echo json_encode(['ok' => false, 'msg' => 'Apenas arquivos .sql são aceitos.']);
        exit;
    }

    $conteudo = file_get_contents($_FILES['arquivo']['tmp_name']);
    if (!$conteudo) {
        echo json_encode(['ok' => false, 'msg' => 'Arquivo vazio.']);
        exit;
    }

    // Snapshot do estado atual ANTES de restaurar. A restauração apaga tudo e
    // reinsere; se o arquivo importado estiver errado ou incompleto, este é o
    // único caminho de volta.
    $antes = BackupAutomatico::gerar('pre-restauracao');

    // Normaliza quebras de linha e remove BOM
    $conteudo = preg_replace('/^\xEF\xBB\xBF/', '', $conteudo);
    $conteudo = str_replace("\r\n", "\n", $conteudo);

    // Divide nos delimitadores de instrução
    $stmts = preg_split('/;\s*\n/', $conteudo);

    $conn = Database::getConnection();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $ok    = 0;
    $erros = [];

    // Garante FK desligadas durante toda a importação (independe do conteúdo do arquivo)
    try { $conn->exec("SET FOREIGN_KEY_CHECKS = 0"); } catch (Exception $e) {}

    foreach ($stmts as $raw) {
        // Remove linhas de comentário (-- ...) antes de decidir se executa
        $stmt = trim(preg_replace('/^--[^\n]*(\n|$)/m', '', $raw));
        if ($stmt === '') continue;
        // Backups antigos usavam TRUNCATE, que falha em tabelas com FK → converte para DELETE
        $stmt = preg_replace('/^TRUNCATE\s+TABLE\s+/i', 'DELETE FROM ', $stmt);
        try {
            $conn->exec($stmt);
            $ok++;
        } catch (PDOException $e) {
            $trecho  = mb_substr($stmt, 0, 80);
            $erros[] = $trecho . ' → ' . $e->getMessage();
        }
    }

    try { $conn->exec("SET FOREIGN_KEY_CHECKS = 1"); } catch (Exception $e) {}

    if (empty($erros)) {
        $aviso = $antes
            ? " O estado anterior foi salvo em " . basename($antes) . "."
            : " (atenção: não foi possível salvar o estado anterior)";
        echo json_encode(['ok' => true, 'msg' => "{$ok} instruções executadas com sucesso.{$aviso}"]);
    } else {
        $primeiros = implode('<br>', array_map('htmlspecialchars', array_slice($erros, 0, 3)));
        $extra     = count($erros) > 3 ? ' (e mais ' . (count($erros) - 3) . ')' : '';
        echo json_encode(['ok' => false, 'msg' => count($erros) . " erro(s){$extra}:<br>{$primeiros}"]);
    }
    exit;
}

// ── HELPERS ─────────────────────────────────────────────────────────────────
function dumpTabela(PDO $conn, string $tabela, ?string $where = null): string
{
    $query = "SELECT * FROM `{$tabela}`" . ($where ? " WHERE {$where}" : '');
    try {
        $rows = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return "-- Tabela `{$tabela}` não encontrada, ignorada.\n\n";
    }

    // DELETE FROM (e não TRUNCATE) — TRUNCATE falha em tabelas referenciadas por FK,
    // mesmo com FOREIGN_KEY_CHECKS=0. DELETE com as checagens desligadas funciona.
    $sql  = "-- [{$tabela}]\n";
    $sql .= "DELETE FROM `{$tabela}`;\n";

    if (empty($rows)) {
        return $sql . "\n";
    }

    $cols   = '`' . implode('`, `', array_keys($rows[0])) . '`';
    $values = [];

    foreach ($rows as $row) {
        $vals = array_map(function ($v) use ($conn) {
            return $v === null ? 'NULL' : $conn->quote((string) $v);
        }, array_values($row));
        $values[] = '(' . implode(', ', $vals) . ')';
    }

    foreach (array_chunk($values, 200) as $chunk) {
        $sql .= "INSERT INTO `{$tabela}` ({$cols}) VALUES\n";
        $sql .= implode(",\n", $chunk) . ";\n";
    }

    return $sql . "\n";
}
