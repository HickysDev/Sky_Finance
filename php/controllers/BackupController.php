<?php
require_once __DIR__ . '/../../conn/conn.php';

$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

// ── EXPORTAR ────────────────────────────────────────────────────────────────
if ($acao === 'exportar') {

    // [tabela, WHERE clause para filtrar usuario_id = @uid]
    // null = sem filtro (tabela compartilhada)
    // Subqueries para tabelas-filho sem coluna usuario_id
    $tabelas = [
        ['usuarios',                        'id = @uid'],
        ['categorias',                      'usuario_id = @uid'],
        ['cartoes_credito',                 'usuario_id = @uid'],
        ['responsaveis',                    'usuario_id = @uid'],
        ['gastos',                          'usuario_id = @uid'],
        ['parcelas',                        'gasto_id IN (SELECT id FROM gastos WHERE usuario_id = @uid)'],
        ['gastos_recorrentes',              'usuario_id = @uid'],
        ['gastos_recorrentes_lancamentos',  'usuario_id = @uid'],
        ['renda_mensal',                    'usuario_id = @uid'],
        ['contas_pessoa',                   'usuario_id = @uid'],
        ['contas_fixas',                    'usuario_id = @uid'],
        ['contas_fixas_pagamentos',         'usuario_id = @uid'],
        ['faturas_pagas',                   'usuario_id = @uid'],
        ['cofrinhos',                       'usuario_id = @uid'],
        ['cofrinho_aportes',                'cofrinho_id IN (SELECT id FROM cofrinhos WHERE usuario_id = @uid)'],
        ['orcamentos',                      'usuario_id = @uid'],
    ];

    $conn  = Database::getConnection();
    $stamp = date('d/m/Y H:i');
    $fname = 'skyfinance_' . date('Y-m-d_H-i') . '.sql';

    $sql  = "-- Sky Finance Backup | Gerado em: {$stamp} | usuario_id = @uid\n";
    $sql .= "-- Importe via a página de Backup do sistema.\n\n";
    $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n";
    $sql .= "SET NAMES utf8mb4;\n\n";

    foreach ($tabelas as [$t, $where]) {
        $sql .= dumpTabela($conn, $t, $where);
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

    // Normaliza quebras de linha e remove BOM
    $conteudo = preg_replace('/^\xEF\xBB\xBF/', '', $conteudo);
    $conteudo = str_replace("\r\n", "\n", $conteudo);

    // Divide nos delimitadores de instrução
    $stmts = preg_split('/;\s*\n/', $conteudo);

    $conn = Database::getConnection();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $ok    = 0;
    $erros = [];

    foreach ($stmts as $raw) {
        // Remove linhas de comentário (-- ...) antes de decidir se executa
        $stmt = trim(preg_replace('/^--[^\n]*(\n|$)/m', '', $raw));
        if ($stmt === '') continue;
        try {
            $conn->exec($stmt);
            $ok++;
        } catch (PDOException $e) {
            $trecho  = mb_substr($stmt, 0, 80);
            $erros[] = $trecho . ' → ' . $e->getMessage();
        }
    }

    if (empty($erros)) {
        echo json_encode(['ok' => true, 'msg' => "{$ok} instruções executadas com sucesso."]);
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

    $sql  = "-- [{$tabela}]\n";
    $sql .= "TRUNCATE TABLE `{$tabela}`;\n";

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
