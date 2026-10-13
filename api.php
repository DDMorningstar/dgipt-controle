<?php
/**
 * API centrale � Notes de Perception
 *
 * GET  ?action=ping
 * GET  ?action=setup          cr�e la base et les tables
 * GET  ?action=pull           exporte toutes les notes (JSON)
 * POST ?action=push           upsert des notes depuis l'application
 *
 * En-t�te optionnel : X-API-Key
 */
declare(strict_types=1);
@set_time_limit(180);
@ini_set('memory_limit', '256M');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '') {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
    header('Access-Control-Allow-Headers: Content-Type, X-API-Key');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
}
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$configFile = __DIR__ . '/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Fichier config.php introuvable.']);
    exit;
}
$config = require $configFile;
require_once __DIR__ . '/npc-lib.php';

function json_out(array $data, int $code = 200): void {
    http_response_code($code);
    echo npc_json_encode($data);
    exit;
}

function read_body(): array {
    $raw = file_get_contents('php://input') ?: '';
    if ($raw === '') return $_POST ?: [];
    $json = json_decode($raw, true);
    return is_array($json) ? $json : [];
}

function require_key(array $config): void {
    $expected = (string)($config['api_key'] ?? '');
    if ($expected === '') return;
    $got = (string)($_SERVER['HTTP_X_API_KEY'] ?? $_GET['key'] ?? '');
    if (!hash_equals($expected, $got)) {
        json_out(['ok' => false, 'error' => 'Cle API invalide.'], 401);
    }
}

function db_connect(array $config, bool $withDb = true): PDO {
    return npc_db($config, $withDb);
}
function ensure_schema(PDO $db): void { npc_ensure_schema($db); }
function compact_ref(string $s): string { return npc_compact_ref($s); }
function fetch_note_records(PDO $db): array { return npc_fetch_notes($db); }
function fetch_inspectors(PDO $db): array { return npc_fetch_inspectors($db); }

$action = (string)($_GET['action'] ?? $_POST['action'] ?? 'ping');
require_key($config);

try {
    if ($action === 'ping') {
        json_out(['ok' => true, 'app' => 'npc', 'time' => time()]);
    }

    if ($action === 'setup') {
        npc_setup($config);
        json_out(['ok' => true, 'message' => 'Base et tables pretes.']);
    }

    $db = db_connect($config, true);
    ensure_schema($db);

    if ($action === 'pull' || $action === 'sync') {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 0;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        $withFields = isset($_GET['fields']) && $_GET['fields'] === '1';
        if ($limit > 0) {
            $page = npc_fetch_notes_page($db, $offset, $limit, $withFields);
            $got = count($page['records']);
            $out = [
                'ok' => true,
                'count' => $got,
                'total' => $page['total'],
                'offset' => $offset,
                'limit' => $page['limit'],
                'done' => ($offset + $got) >= $page['total'] || $got === 0,
                'records' => $page['records'],
            ];
            if ($offset === 0) $out['inspectors'] = fetch_inspectors($db);
            json_out($out);
        }
        $records = npc_fetch_notes($db, ['fields' => $withFields]);
        $inspectors = fetch_inspectors($db);
        json_out([
            'ok' => true,
            'count' => count($records),
            'total' => count($records),
            'done' => true,
            'records' => $records,
            'inspectors' => $inspectors,
        ]);
    }

    if ($action === 'push') {
        $body  = read_body();
        $notes = $body['notes'] ?? [];
        if (!is_array($notes)) json_out(['ok' => false, 'error' => 'Format invalide.'], 400);
        $source = (string)($body['source'] ?? 'push application');
        $r = npc_upsert_notes($db, $notes, $source);
        json_out(['ok' => true, 'added' => $r['added'], 'updated' => $r['updated'], 'total' => $r['total']]);
    }

    if ($action === 'push_inspectors') {
        $body = read_body();
        $people = $body['inspectors'] ?? [];
        if (!is_array($people)) json_out(['ok' => false, 'error' => 'Format invalide.'], 400);
        $n = npc_upsert_inspectors($db, $people);
        json_out(['ok' => true, 'count' => $n]);
    }

    json_out(['ok' => false, 'error' => 'Action inconnue : ' . $action], 400);
} catch (Throwable $e) {
    json_out(['ok' => false, 'error' => $e->getMessage()], 500);
}
