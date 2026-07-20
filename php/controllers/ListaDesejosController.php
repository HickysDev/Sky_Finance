<?php

require_once __DIR__ . '/../../conn/config.php';
require_once __DIR__ . '/../../conn/conn.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../models/ListaDesejosModel.php';

header('Content-Type: application/json; charset=utf-8');

$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

// Pasta das imagens enviadas. Fora não: fica dentro de src/img para ser servida.
const DESEJOS_DIR_REL = 'desejos';
$desejosDir = __DIR__ . '/../../src/img/' . DESEJOS_DIR_REL . '/';

/**
 * Busca a imagem de prévia (og:image) de uma página de produto.
 * Retorna a URL absoluta da imagem, ou null se não achar / URL inválida.
 */
function buscarImagemDoLink(string $url): ?string
{
    $url = trim($url);
    if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) return null;

    $parts = parse_url($url);
    $scheme = strtolower($parts['scheme'] ?? '');
    if (!in_array($scheme, ['http', 'https'], true)) return null;

    // Guarda anti-SSRF: não deixa o servidor buscar endereços internos/loopback.
    $host = $parts['host'] ?? '';
    $ip   = filter_var($host, FILTER_VALIDATE_IP) ? $host : @gethostbyname($host);
    if ($ip && !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        return null;
    }

    $html = false;
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 4,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; SkyFinance/1.0)',
            CURLOPT_RANGE          => '0-262143', // até 256 KB: o <head> basta para as metatags
        ]);
        $html = curl_exec($ch);
        curl_close($ch);
    }
    if ($html === false || $html === '') {
        $ctx  = stream_context_create(['http' => ['timeout' => 8, 'user_agent' => 'Mozilla/5.0 (compatible; SkyFinance/1.0)']]);
        $html = @file_get_contents($url, false, $ctx, 0, 262144);
    }
    if (!$html) return null;

    // og:image e twitter:image cobrem a maioria das lojas (é a imagem do preview de link).
    $padroes = [
        '/<meta[^>]+property=["\']og:image(?::secure_url)?["\'][^>]+content=["\']([^"\']+)["\']/i',
        '/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:image["\']/i',
        '/<meta[^>]+name=["\']twitter:image["\'][^>]+content=["\']([^"\']+)["\']/i',
    ];
    foreach ($padroes as $re) {
        if (preg_match($re, $html, $m)) {
            $img = html_entity_decode(trim($m[1]), ENT_QUOTES);
            // Resolve caminho relativo contra o domínio da página.
            if (strpos($img, '//') === 0)       $img = $scheme . ':' . $img;
            elseif (strpos($img, '/') === 0)    $img = $scheme . '://' . $host . $img;
            if (filter_var($img, FILTER_VALIDATE_URL)) return $img;
        }
    }
    return null;
}

/**
 * Salva um arquivo de imagem enviado. Retorna "desejos/<arquivo>" ou null.
 * Mesma validação de MIME por conteúdo usada no upload de avatar.
 */
function salvarImagemEnviada(array $upload, int $itemId, string $dir): ?string
{
    if ($upload['error'] !== UPLOAD_ERR_OK) return null;

    $tipos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $mime  = mime_content_type($upload['tmp_name']);
    if (!isset($tipos[$mime]))                 return null;
    if ($upload['size'] > 5 * 1024 * 1024)     return null; // 5 MB

    if (!is_dir($dir) && !@mkdir($dir, 0755, true)) return null;

    $filename = 'd' . $itemId . '_' . time() . '.' . $tipos[$mime];
    if (!move_uploaded_file($upload['tmp_name'], $dir . $filename)) return null;

    return DESEJOS_DIR_REL . '/' . $filename;
}

switch ($acao) {

    case 'listar':
        echo json_encode([
            'itens'  => ListaDesejosModel::listar(),
            'resumo' => ListaDesejosModel::resumo(),
        ]);
        break;

    case 'salvar':
        $id = (int) ($_POST['id'] ?? 0);

        // Resolve a imagem: URL colada / og:image tem prioridade no campo hidden;
        // arquivo enviado, se houver, sobrescreve depois de salvar (precisa do id).
        $imagemUrl = trim($_POST['imagem_url'] ?? '');
        if ($imagemUrl === '' && trim($_POST['link'] ?? '') !== '' && empty($_FILES['imagem_arquivo']['name'])) {
            // Sem imagem informada mas com link: tenta pegar a og:image automaticamente.
            $auto = buscarImagemDoLink($_POST['link']);
            if ($auto) $imagemUrl = $auto;
        }

        $dados = [
            'nome'       => $_POST['nome']       ?? '',
            'link'       => $_POST['link']       ?? '',
            'valor'      => $_POST['valor']      ?? '0',
            'prioridade' => $_POST['prioridade'] ?? 'media',
            'imagem'     => $imagemUrl !== '' ? $imagemUrl : null,
        ];

        $itemId = ListaDesejosModel::salvar($dados, $id);
        if (!$itemId) {
            http_response_code(400);
            echo json_encode(['erro' => 'Nome do produto é obrigatório.']);
            break;
        }

        // Arquivo enviado sobrescreve a URL (imagem local é mais confiável).
        if (!empty($_FILES['imagem_arquivo']['name'])) {
            $rel = salvarImagemEnviada($_FILES['imagem_arquivo'], $itemId, $desejosDir);
            if ($rel) {
                ListaDesejosModel::definirImagem($itemId, $rel);
            } else {
                echo json_encode(['ok' => true, 'id' => $itemId, 'aviso' => 'Item salvo, mas a imagem enviada foi recusada (use JPG, PNG, WEBP ou GIF até 5 MB).']);
                break;
            }
        }

        echo json_encode(['ok' => true, 'id' => $itemId]);
        break;

    case 'remover':
        $id = (int) ($_POST['id'] ?? 0);
        if (!$id) { http_response_code(400); echo json_encode(['erro' => 'ID inválido']); break; }
        // Apaga a imagem local (se houver) antes de remover a linha.
        $imgLocal = ListaDesejosModel::imagemLocalDe($id);
        $ok = ListaDesejosModel::remover($id);
        if ($ok && $imgLocal) {
            $caminho = __DIR__ . '/../../src/img/' . $imgLocal;
            if (is_file($caminho)) @unlink($caminho);
        }
        echo json_encode(['ok' => $ok]);
        break;

    case 'comprado':
        $id = (int) ($_POST['id'] ?? 0);
        if (!$id) { http_response_code(400); echo json_encode(['erro' => 'ID inválido']); break; }
        echo json_encode(['ok' => ListaDesejosModel::toggleComprado($id)]);
        break;

    case 'buscarImagem':
        // Prévia ao vivo no modal: usuário cola o link e clica em "buscar imagem".
        $img = buscarImagemDoLink($_POST['link'] ?? $_GET['link'] ?? '');
        echo json_encode(['imagem' => $img]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['erro' => 'Ação inválida']);
}
