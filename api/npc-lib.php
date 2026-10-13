<?php
declare(strict_types=1);

function npc_config(): array {
    $file = __DIR__ . '/config.php';
    if (!is_file($file)) {
        throw new RuntimeException('Fichier config.php introuvable.');
    }
    return require $file;
}

function npc_driver(array $config): string {
    $d = strtolower((string)($config['db_driver'] ?? 'mysql'));
    if (in_array($d, ['pgsql', 'postgres', 'postgresql'], true)) return 'pgsql';
    if (in_array($d, ['sqlite', 'sqlite3'], true)) return 'sqlite';
    return 'mysql';
}

function npc_sqlite_file(array $config): string {
    $src = (string)($config['sqlite_path'] ?? (__DIR__ . '/data/npc.sqlite'));
    if (!is_file($src)) {
        throw new RuntimeException('Fichier de notes introuvable sur le serveur.');
    }
    $onVercel = (string)getenv('VERCEL') !== '';
    if (!$onVercel) {
        return $src;
    }
    $dst = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'npc.sqlite';
    $needCopy = !is_file($dst)
        || filesize($dst) !== filesize($src)
        || filemtime($src) > filemtime($dst);
    if ($needCopy && !@copy($src, $dst)) {
        return $src;
    }
    return is_file($dst) ? $dst : $src;
}

function npc_db(array $config, bool $withDb = true): PDO {
    $driver = npc_driver($config);
    $host = (string)$config['db_host'];
    $user = (string)$config['db_user'];
    $pass = (string)$config['db_pass'];
    $name = (string)$config['db_name'];
    $opts = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    if ($driver === 'sqlite') {
        $path = npc_sqlite_file($config);
        try {
            $pdo = new PDO('sqlite:' . $path, null, null, $opts);
            $pdo->exec('PRAGMA busy_timeout=5000');
            return $pdo;
        } catch (PDOException $e) {
            throw new RuntimeException('Connexion SQLite impossible : ' . $e->getMessage());
        }
    }
    if ($driver === 'pgsql') {
        $port = (int)($config['db_port'] ?? 5432);
        $dsn = "pgsql:host={$host};port={$port};dbname={$name}";
        if (!empty($config['db_ssl'])) {
            $dsn .= ';sslmode=require';
        }
        try {
            return new PDO($dsn, $user, $pass, $opts);
        } catch (PDOException $e) {
            throw new RuntimeException('Connexion Postgres impossible : ' . $e->getMessage());
        }
    }
    $port = (int)($config['db_port'] ?? 3306);
    $charset = (string)($config['db_charset'] ?? 'utf8mb4');
    $dsn = "mysql:host={$host};port={$port};charset={$charset}";
    if ($withDb) {
        $dsn .= ';dbname=' . $name;
    }
    if (!empty($config['db_ssl']) && defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
        $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    try {
        return new PDO($dsn, $user, $pass, $opts);
    } catch (PDOException $e) {
        throw new RuntimeException('Connexion MySQL impossible : ' . $e->getMessage());
    }
}

function npc_ensure_schema(PDO $db): void {
    $driver = (string)$db->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'sqlite') {
        $db->exec('CREATE TABLE IF NOT EXISTS notes (
            ref TEXT PRIMARY KEY,
            ref_raw TEXT NOT NULL,
            payload TEXT NOT NULL,
            updated_at INTEGER NOT NULL
        )');
        $db->exec('CREATE TABLE IF NOT EXISTS imports (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            source TEXT DEFAULT NULL,
            added INTEGER NOT NULL DEFAULT 0,
            updated INTEGER NOT NULL DEFAULT 0,
            total INTEGER NOT NULL DEFAULT 0,
            imported_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )');
        $db->exec('CREATE TABLE IF NOT EXISTS inspectors (
            id TEXT PRIMARY KEY,
            payload TEXT NOT NULL,
            updated_at INTEGER NOT NULL
        )');
        return;
    }
    if ($driver === 'pgsql') {
        $db->exec('CREATE TABLE IF NOT EXISTS notes (
            ref VARCHAR(191) PRIMARY KEY,
            ref_raw VARCHAR(255) NOT NULL,
            payload JSONB NOT NULL,
            updated_at BIGINT NOT NULL
        )');
        $db->exec('CREATE TABLE IF NOT EXISTS imports (
            id SERIAL PRIMARY KEY,
            source VARCHAR(255) DEFAULT NULL,
            added INT NOT NULL DEFAULT 0,
            updated INT NOT NULL DEFAULT 0,
            total INT NOT NULL DEFAULT 0,
            imported_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
        )');
        $db->exec('CREATE TABLE IF NOT EXISTS inspectors (
            id VARCHAR(64) PRIMARY KEY,
            payload JSONB NOT NULL,
            updated_at BIGINT NOT NULL
        )');
        return;
    }
    $db->exec("CREATE TABLE IF NOT EXISTS notes (
        ref         VARCHAR(191)  NOT NULL,
        ref_raw     VARCHAR(255)  NOT NULL,
        payload     JSON          NOT NULL,
        updated_at  BIGINT        NOT NULL,
        PRIMARY KEY (ref)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $db->exec("CREATE TABLE IF NOT EXISTS imports (
        id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
        source      VARCHAR(255)  DEFAULT NULL,
        added       INT           NOT NULL DEFAULT 0,
        updated     INT           NOT NULL DEFAULT 0,
        total       INT           NOT NULL DEFAULT 0,
        imported_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $db->exec("CREATE TABLE IF NOT EXISTS inspectors (
        id          VARCHAR(64)   NOT NULL,
        payload     JSON          NOT NULL,
        updated_at  BIGINT        NOT NULL,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

function npc_setup(array $config): void {
    $driver = npc_driver($config);
    if ($driver === 'pgsql' || $driver === 'sqlite') {
        $db = npc_db($config, true);
        try {
            npc_ensure_schema($db);
        } catch (Throwable $e) {
            if ($driver !== 'sqlite') throw $e;
        }
        return;
    }
    try {
        $root = npc_db($config, false);
        $name = str_replace(['`', '"', "'"], '', (string)$config['db_name']);
        $root->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $root->exec("USE `$name`");
        npc_ensure_schema($root);
    } catch (Throwable $e) {
        $db = npc_db($config, true);
        npc_ensure_schema($db);
    }
}

function npc_decode_payload($raw): array {
    if (is_array($raw)) return $raw;
    if (is_string($raw) && $raw !== '') {
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }
    return [];
}

function npc_blob_token(): string {
    return trim((string)(getenv('BLOB_READ_WRITE_TOKEN') ?: ''));
}

function npc_blob_store_id(): string {
    $id = trim((string)(getenv('BLOB_STORE_ID') ?: 'xB9telflK7GXHksL'));
    return str_starts_with($id, 'store_') ? substr($id, 6) : $id;
}

function npc_blob_enabled(): bool {
    return npc_blob_token() !== '';
}

function npc_http_blob(string $method, string $url, array $headers, ?string $body = null): array {
    $ch = curl_init($url);
    if ($ch === false) {
        throw new RuntimeException('Impossible d’appeler le stockage distant.');
    }
    $hdrs = [];
    foreach ($headers as $k => $v) {
        $hdrs[] = $k . ': ' . $v;
    }
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $hdrs,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 45,
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
    if ((string)getenv('VERCEL') === '') {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }
    $raw = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    if ($raw === false) {
        throw new RuntimeException('Stockage distant inaccessible : ' . $err);
    }
    return ['code' => $code, 'body' => (string)$raw];
}

function npc_delta_load(): array {
    if (isset($GLOBALS['npc_delta_mem']) && is_array($GLOBALS['npc_delta_mem'])) {
        return $GLOBALS['npc_delta_mem'];
    }
    $empty = ['notes' => [], 'inspectors' => []];
    if (!npc_blob_enabled()) {
        $GLOBALS['npc_delta_mem'] = $empty;
        return $empty;
    }
    $url = 'https://' . npc_blob_store_id() . '.private.blob.vercel-storage.com/npc-delta.json?cache=0';
    try {
        $res = npc_http_blob('GET', $url, [
            'Authorization' => 'Bearer ' . npc_blob_token(),
        ]);
    } catch (Throwable $e) {
        $GLOBALS['npc_delta_mem'] = $empty;
        return $empty;
    }
    if ($res['code'] === 404 || $res['code'] < 200 || $res['code'] >= 300) {
        $GLOBALS['npc_delta_mem'] = $empty;
        return $empty;
    }
    $data = json_decode($res['body'], true);
    if (!is_array($data)) $data = $empty;
    if (!isset($data['notes']) || !is_array($data['notes'])) $data['notes'] = [];
    if (!isset($data['inspectors']) || !is_array($data['inspectors'])) $data['inspectors'] = [];
    $GLOBALS['npc_delta_mem'] = $data;
    return $data;
}

function npc_delta_save(array $delta): void {
    if (!npc_blob_enabled()) return;
    $body = npc_json_encode([
        'notes' => array_values($delta['notes'] ?? []),
        'inspectors' => array_values($delta['inspectors'] ?? []),
        'updatedAt' => (int) round(microtime(true) * 1000),
    ]);
    $url = 'https://vercel.com/api/blob?pathname=' . rawurlencode('npc-delta.json');
    $res = npc_http_blob('PUT', $url, [
        'Authorization' => 'Bearer ' . npc_blob_token(),
        'x-api-version' => '12',
        'x-vercel-blob-store-id' => npc_blob_store_id(),
        'x-vercel-blob-access' => 'private',
        'x-allow-overwrite' => '1',
        'x-add-random-suffix' => '0',
        'x-content-type' => 'application/json',
        'x-cache-control-max-age' => '60',
    ], $body);
    if ($res['code'] < 200 || $res['code'] >= 300) {
        throw new RuntimeException('Enregistrement distant impossible (HTTP ' . $res['code'] . ').');
    }
    $GLOBALS['npc_delta_mem'] = [
        'notes' => array_values($delta['notes'] ?? []),
        'inspectors' => array_values($delta['inspectors'] ?? []),
    ];
}

function npc_note_item(array $note, bool $withFields, bool $withStatus = false): ?array {
    $ref = npc_compact_ref((string)($note['ref'] ?? ''));
    if ($ref === '') return null;
    $std = is_array($note['std'] ?? null) ? $note['std'] : [];
    $fields = is_array($note['fields'] ?? null) ? $note['fields'] : [];
    $std = npc_hydrate_std($std, $fields);
    $item = [
        'ref' => $ref,
        'refRaw' => (string)($note['refRaw'] ?? $note['ref'] ?? $ref),
        'std' => $std,
        'updatedAt' => (int)($note['updatedAt'] ?? 0),
    ];
    if ($withFields) $item['fields'] = $fields;
    if ($withStatus) $item['status'] = npc_classify_status($std);
    return $item;
}

function npc_delta_note_records(bool $withFields, bool $withStatus = false): array {
    $out = [];
    foreach (npc_delta_load()['notes'] as $n) {
        if (!is_array($n)) continue;
        $item = npc_note_item($n, $withFields, $withStatus);
        if ($item) $out[] = $item;
    }
    return $out;
}

function npc_compact_ref(string $s): string {
    return strtoupper((string)preg_replace('/[^A-Za-z0-9]/', '', $s));
}

function npc_norm(string $s): string {
    $s = str_replace(["\xC5\x93", "\xC5\x92"], ['oe', 'OE'], $s);
    if (class_exists('Normalizer')) {
        $s = Normalizer::normalize($s, Normalizer::FORM_D) ?: $s;
    }
    $s = preg_replace('/\p{Mn}+/u', '', $s) ?? $s;
    $s = mb_strtolower($s, 'UTF-8');
    return trim((string)preg_replace('/[^a-z0-9]+/', ' ', $s));
}

function npc_num($v): ?float {
    if ($v === null || $v === '') return null;
    if (is_int($v) || is_float($v)) {
        $n = (float)$v;
        return (!is_nan($n) && !is_infinite($n)) ? $n : null;
    }
    $s = str_replace(["\xC2\xA0", ' '], '', (string)$v);
    $s = str_replace(',', '.', $s);
    $s = preg_replace('/[^0-9.+-eE]/', '', $s) ?? $s;
    if ($s === '' || $s === '-' || $s === '+' || preg_match('/^(nan|inf|-inf|infinity)$/i', $s)) return null;
    if (!is_numeric($s)) return null;
    $n = (float)$s;
    return (!is_nan($n) && !is_infinite($n)) ? $n : null;
}

function npc_json_encode($data): string {
    $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
    if (defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
        $flags |= JSON_INVALID_UTF8_SUBSTITUTE;
    }
    if (defined('JSON_PARTIAL_OUTPUT_ON_ERROR')) {
        $flags |= JSON_PARTIAL_OUTPUT_ON_ERROR;
    }
    $json = json_encode($data, $flags);
    if ($json === false) {
        return json_encode([
            'ok' => false,
            'error' => 'Encodage JSON : ' . json_last_error_msg(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{"ok":false,"error":"JSON"}';
    }
    return $json;
}

function npc_field_lookup(array $fields): array {
    $map = [];
    foreach ($fields as $k => $v) {
        $map[npc_norm((string)$k)] = $v;
    }
    return $map;
}

function npc_pick($std, array $fmap, array $names) {
    if (!is_array($std)) $std = [];
    foreach ($names as $name) {
        if (array_key_exists($name, $std) && $std[$name] !== null && $std[$name] !== '') {
            return $std[$name];
        }
        $nk = npc_norm($name);
        if (array_key_exists($nk, $fmap) && $fmap[$nk] !== null && $fmap[$nk] !== '') {
            return $fmap[$nk];
        }
    }
    return null;
}

function npc_hydrate_std(array $std, array $fields): array {
    $fmap = npc_field_lookup($fields);

    $statut = npc_pick($std, $fmap, ['statut', 'status', 'etat', 'situation']);
    if ($statut !== null) $std['statut'] = is_string($statut) ? trim($statut) : $statut;

    $etape = npc_pick($std, $fmap, ['etape', 'tax step', 'etape fiscale', 'step']);
    if ($etape !== null) $std['etape'] = is_string($etape) ? trim($etape) : $etape;

    $nom = npc_pick($std, $fmap, ['nom', 'responsible to pay', 'taxpayer', 'contribuable']);
    if (($std['nom'] ?? '') === '' && $nom !== null) $std['nom'] = trim((string)$nom);

    $tel = npc_pick($std, $fmap, ['tel', 'phone number', 'telephone', 'phone']);
    if (($std['tel'] ?? '') === '' && $tel !== null) $std['tel'] = trim((string)$tel);

    $dev = $std['devise'] ?? null;
    $curName = npc_pick([], $fmap, ['currency name', 'devise', 'monnaie']);
    if ($curName !== null && !is_numeric($curName)) {
        $std['devise'] = trim((string)$curName);
    } elseif ($dev !== null && is_numeric($dev) && (float)$dev > 20) {
        $std['devise'] = $curName ? trim((string)$curName) : 'CDF';
    }

    $isUsd = npc_norm((string)($std['devise'] ?? '')) === 'usd';
    $paidC = npc_num(npc_pick($std, $fmap, ['montantPaye', 'paid cdf', 'montant paye']));
    $paidU = npc_num(npc_pick([], $fmap, ['paid usd']));
    $declC = npc_num(npc_pick($std, $fmap, ['montantDeclare', 'declared cdf', 'montant declare']));
    $declU = npc_num(npc_pick([], $fmap, ['declared usd']));
    $unpC  = npc_num(npc_pick($std, $fmap, ['reste', 'unpaid cdf', 'reste a payer']));
    $unpU  = npc_num(npc_pick([], $fmap, ['unpaid usd']));

    if (!isset($std['montantPaye']) || $std['montantPaye'] === '' || $std['montantPaye'] === null) {
        $std['montantPaye'] = $isUsd ? ($paidU ?? $paidC) : ($paidC ?? $paidU);
    } else {
        $std['montantPaye'] = npc_num($std['montantPaye']);
    }
    if (!isset($std['montantDeclare']) || $std['montantDeclare'] === '' || $std['montantDeclare'] === null) {
        $std['montantDeclare'] = $isUsd ? ($declU ?? $declC) : ($declC ?? $declU);
    } else {
        $std['montantDeclare'] = npc_num($std['montantDeclare']);
    }
    if (!isset($std['reste']) || $std['reste'] === '' || $std['reste'] === null) {
        $std['reste'] = $isUsd ? ($unpU ?? $unpC) : ($unpC ?? $unpU);
    } else {
        $std['reste'] = npc_num($std['reste']);
    }
    if (!isset($std['montantAttendu']) || $std['montantAttendu'] === '' || $std['montantAttendu'] === null) {
        $attendu = npc_num($std['montantDeclare'] ?? null);
        $reste = npc_num($std['reste'] ?? null);
        $paye = npc_num($std['montantPaye'] ?? null);
        if ($attendu !== null) $std['montantAttendu'] = $attendu;
        elseif ($reste !== null && $paye !== null) $std['montantAttendu'] = $reste + $paye;
    } else {
        $std['montantAttendu'] = npc_num($std['montantAttendu']);
    }

    $emi = npc_pick($std, $fmap, ['dateEmission', 'issue date', 'date emission']);
    if (($std['dateEmission'] ?? '') === '' && $emi) $std['dateEmission'] = $emi;
    $payd = npc_pick($std, $fmap, ['datePaiement', 'paid date', 'payment date', 'date paiement']);
    if (($std['datePaiement'] ?? '') === '' && $payd) $std['datePaiement'] = $payd;

    return $std;
}

function npc_status_label_fr(string $raw, string $key, string $fallback): string {
    $n = npc_norm($raw);
    $fromExcel = [
        'paid' => 'Payée',
        'unpaid' => 'Impayée',
        'open' => 'Ouverte',
        'partial paid' => 'Paiement partiel',
        'partially paid' => 'Paiement partiel',
        'partial' => 'Paiement partiel',
        'rejected' => 'Rejetée',
        'cancelled' => 'Annulée',
        'canceled' => 'Annulée',
        'waiting for approval' => 'En attente de validation',
        'toll tax' => 'Taxe de péage',
    ];
    if (isset($fromExcel[$n])) return $fromExcel[$n];
    $fromKey = [
        'paid' => 'Payée',
        'unpaid' => 'Impayée',
        'open' => 'Ouverte',
        'partial' => 'Paiement partiel',
        'cancelled' => 'Rejetée',
        'notice' => 'Mise en demeure',
    ];
    if ($key !== '' && isset($fromKey[$key])) return $fromKey[$key];
    if ($fallback !== '') return $fallback;
    return $raw;
}

function npc_step_label_fr(string $etape): string {
    $n = npc_norm($etape);
    if (preg_match('/demeure/', $n)) return 'Mise en demeure';
    if (preg_match('/contrainte|commandement/', $n)) return 'Contrainte et commandement';
    if (preg_match('/note de perception|perception/', $n)) return 'Note de perception';
    return trim($etape);
}

function npc_classify_status(array $std): array {
    $raw = trim((string)($std['statut'] ?? ''));
    $etapeRaw = trim((string)($std['etape'] ?? ''));
    $etape = npc_step_label_fr($etapeRaw);
    $s = npc_norm($raw);
    $e = npc_norm($etapeRaw);
    $attendu = npc_num($std['montantAttendu'] ?? $std['montantDeclare'] ?? null);
    $paye = npc_num($std['montantPaye'] ?? null);
    $reste = npc_num($std['reste'] ?? null);

    $notice = (bool)preg_match('/demeure|contentieux|poursuite|commandement|contrainte|recouvrement force|demand letter/', $e)
        || (bool)preg_match('/demeure|contentieux|commandement|contrainte/', $s);
    $hasUnpaid = ($reste !== null && $reste > 0.009)
        || (bool)preg_match('/unpaid|non paye|impaye/', $s);

    $pack = function (string $key, string $level, string $fallback) use ($raw, $etape, $notice, $hasUnpaid): array {
        return [
            'key' => $key,
            'level' => $level,
            'label' => npc_status_label_fr($raw, $key, $fallback),
            'hint' => $etape !== '' ? $etape : '',
            'notice' => $notice,
            'hasUnpaid' => $hasUnpaid && $key !== 'paid' && $key !== 'cancelled',
            'etape' => $etape,
        ];
    };

    if (preg_match('/annul|cancel|rejet|abandon|supprim|reject|void/', $s)) {
        $st = $pack('cancelled', 'info', 'Rejetée');
        $st['hint'] = 'Clôturée / rejetée';
        return $st;
    }
    if (preg_match('/partiel|partial/', $s)) {
        $st = $pack('partial', 'warn', 'Paiement partiel');
        $st['hint'] = $etape !== '' ? $etape : 'Versement incomplet';
        return $st;
    }
    if (preg_match('/unpaid|non paye|impaye/', $s)) {
        $st = $pack('unpaid', 'bad', 'Impayée');
        return $st;
    }
    if (preg_match('/waiting|en attente|approval|a valider/', $s)) {
        $st = $pack('open', 'open', 'En attente');
        $st['hint'] = $etape !== '' ? $etape : 'En attente de validation';
        return $st;
    }
    if (preg_match('/\bopen\b|ouvert|emise|emis|en cours|a payer|\bdue\b|issued/', $s)) {
        $st = $pack('open', 'open', 'Ouverte');
        $st['hint'] = $etape !== '' ? $etape : 'Émise, en attente de paiement';
        return $st;
    }
    if (preg_match('/\bpaid\b|paye|sold|regl|acquit|valide|settled/', $s)) {
        $st = $pack('paid', 'ok', 'Payée');
        $st['hint'] = $etape !== '' ? $etape : 'Soldée intégralement';
        $st['hasUnpaid'] = false;
        return $st;
    }

    if ($attendu !== null && $paye !== null) {
        if ($attendu > 0 && $paye + 0.009 >= $attendu) {
            return $pack('paid', 'ok', 'Payée');
        }
        if ($paye > 0) {
            return $pack('partial', 'warn', 'Paiement partiel');
        }
        return $pack('open', 'open', 'Ouverte');
    }
    if ($paye !== null && $paye > 0) {
        return $pack('partial', 'warn', 'Paiement enregistré');
    }
    if ($raw !== '') {
        $st = $pack('other', 'info', $raw);
        $st['hint'] = 'Statut Excel non classé';
        return $st;
    }
    return $pack('open', 'open', 'Ouverte');
}

function npc_fetch_notes(PDO $db, array $opts = []): array {
    $withFields = !array_key_exists('fields', $opts) || (bool)$opts['fields'];
    $rows = $db->query('SELECT ref, ref_raw, payload, updated_at FROM notes ORDER BY updated_at DESC, ref')->fetchAll();
    $records = [];
    foreach ($rows as $row) {
        $payload = npc_decode_payload($row['payload'] ?? '{}');
        $std = is_array($payload['std'] ?? null) ? $payload['std'] : [];
        $fields = is_array($payload['fields'] ?? null) ? $payload['fields'] : [];
        $std = npc_hydrate_std($std, $fields);
        $item = [
            'ref'       => $row['ref'],
            'refRaw'    => $row['ref_raw'],
            'std'       => $std,
            'updatedAt' => (int)$row['updated_at'],
            'status'    => npc_classify_status($std),
        ];
        if ($withFields) $item['fields'] = $fields;
        $records[] = $item;
    }
    $byRef = [];
    foreach ($records as $item) {
        $byRef[$item['ref']] = $item;
    }
    foreach (npc_delta_note_records($withFields, true) as $item) {
        $old = $byRef[$item['ref']] ?? null;
        if (!$old || (int)$item['updatedAt'] >= (int)($old['updatedAt'] ?? 0)) {
            $byRef[$item['ref']] = $item;
        }
    }
    return array_values($byRef);
}

function npc_fetch_notes_page(PDO $db, int $offset, int $limit, bool $withFields = false): array {
    $offset = max(0, $offset);
    $limit = max(1, min(400, $limit));
    $total = (int)$db->query('SELECT COUNT(*) AS c FROM notes')->fetchColumn();
    $sql = 'SELECT ref, ref_raw, payload, updated_at FROM notes ORDER BY ref LIMIT ' . $limit . ' OFFSET ' . $offset;
    $rows = $db->query($sql)->fetchAll();
    $records = [];
    foreach ($rows as $row) {
        $payload = npc_decode_payload($row['payload'] ?? '{}');
        $std = is_array($payload['std'] ?? null) ? $payload['std'] : [];
        $fields = is_array($payload['fields'] ?? null) ? $payload['fields'] : [];
        $std = npc_hydrate_std($std, $fields);
        $item = [
            'ref'       => (string)$row['ref'],
            'refRaw'    => (string)$row['ref_raw'],
            'std'       => $std,
            'updatedAt' => (int)$row['updated_at'],
        ];
        if ($withFields) $item['fields'] = $fields;
        $records[] = $item;
    }
    return ['records' => $records, 'total' => $total, 'limit' => $limit, 'offset' => $offset];
}

function npc_note_matches(array $n, string $status, string $q): bool {
    $st = $n['status'] ?? [];
    if ($status !== '' && $status !== 'all') {
        if ($status === 'notice') {
            if (empty($st['notice'])) return false;
        } elseif ($status === 'unpaid') {
            if (empty($st['hasUnpaid'])) return false;
        } elseif (($st['key'] ?? '') !== $status) {
            return false;
        }
    }
    $q = trim($q);
    if ($q === '') return true;
    $cq = npc_compact_ref($q);
    $ql = mb_strtolower($q, 'UTF-8');
    $std = $n['std'] ?? [];
    $blob = mb_strtolower(implode(' ', [
        (string)($n['refRaw'] ?? ''),
        (string)($n['ref'] ?? ''),
        (string)($std['nom'] ?? ''),
        (string)($std['note'] ?? ''),
        (string)($std['tel'] ?? ''),
        (string)($std['statut'] ?? ''),
        (string)($std['etape'] ?? ''),
        (string)($std['ressort'] ?? ''),
    ]), 'UTF-8');
    $cref = npc_compact_ref((string)($n['ref'] ?? ''));
    if ($cq !== '' && strpos($cref, $cq) !== false) return true;
    return strpos($blob, $ql) !== false;
}

function npc_fetch_inspectors(PDO $db): array {
    $list = [];
    $rows = $db->query('SELECT id, payload, updated_at FROM inspectors')->fetchAll();
    foreach ($rows as $row) {
        $p = npc_decode_payload($row['payload'] ?? '{}');
        if ($p) {
            $p['id'] = $row['id'];
            $p['updatedAt'] = (int)$row['updated_at'];
            $list[] = $p;
        }
    }
    $byId = [];
    foreach ($list as $p) {
        $byId[(string)$p['id']] = $p;
    }
    foreach (npc_delta_load()['inspectors'] as $u) {
        if (!is_array($u) || empty($u['id'])) continue;
        $id = (string)$u['id'];
        $old = $byId[$id] ?? null;
        if (!$old || (int)($u['updatedAt'] ?? 0) >= (int)($old['updatedAt'] ?? 0)) {
            $byId[$id] = $u;
        }
    }
    return array_values($byId);
}

function npc_fetch_imports(PDO $db, int $limit = 12): array {
    $limit = max(1, min(50, $limit));
    return $db->query('SELECT id, source, added, updated, total, imported_at FROM imports ORDER BY id DESC LIMIT ' . $limit)->fetchAll();
}

function npc_upsert_inspectors(PDO $db, array $people): int {
    $driver = (string)$db->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'pgsql') {
        $sql = 'INSERT INTO inspectors (id, payload, updated_at) VALUES (?, ?::jsonb, ?)
            ON CONFLICT (id) DO UPDATE SET payload = EXCLUDED.payload, updated_at = EXCLUDED.updated_at';
    } elseif ($driver === 'sqlite') {
        $sql = 'INSERT INTO inspectors (id, payload, updated_at) VALUES (?,?,?)
            ON CONFLICT(id) DO UPDATE SET payload = excluded.payload, updated_at = excluded.updated_at';
    } else {
        $sql = 'INSERT INTO inspectors (id, payload, updated_at) VALUES (?,?,?)
            ON DUPLICATE KEY UPDATE payload = VALUES(payload), updated_at = VALUES(updated_at)';
    }
    $stmt = $db->prepare($sql);
    $n = 0;
    foreach ($people as $u) {
        if (!is_array($u) || empty($u['id'])) continue;
        $id = (string)$u['id'];
        $updatedAt = (string)($u['updatedAt'] ?? (int) round(microtime(true) * 1000));
        $payload = npc_json_encode($u);
        $stmt->execute([$id, $payload, $updatedAt]);
        $n++;
    }
    if ($n && npc_blob_enabled()) {
        $delta = npc_delta_load();
        $byId = [];
        foreach ($delta['inspectors'] as $u) {
            if (is_array($u) && !empty($u['id'])) $byId[(string)$u['id']] = $u;
        }
        foreach ($people as $u) {
            if (!is_array($u) || empty($u['id'])) continue;
            $id = (string)$u['id'];
            $old = $byId[$id] ?? null;
            if (!$old || (int)($u['updatedAt'] ?? 0) >= (int)($old['updatedAt'] ?? 0)) {
                $byId[$id] = $u;
            }
        }
        $delta['inspectors'] = array_values($byId);
        npc_delta_save($delta);
    }
    return $n;
}

function npc_stats(array $notes): array {
    $keys = ['paid' => 0, 'open' => 0, 'partial' => 0, 'unpaid' => 0, 'notice' => 0, 'cancelled' => 0, 'other' => 0];
    $notice = 0;
    $unpaid = 0;
    foreach ($notes as $n) {
        $st = $n['status'] ?? [];
        $k = $st['key'] ?? 'other';
        if ($k === 'unpaid') {
            $keys['open']++;
        } elseif (isset($keys[$k]) && $k !== 'notice') {
            $keys[$k]++;
        } else {
            $keys['other']++;
        }
        if (!empty($st['notice'])) $notice++;
        if (!empty($st['hasUnpaid'])) $unpaid++;
    }
    $keys['notice'] = $notice;
    $keys['unpaid'] = $unpaid;
    $keys['total'] = count($notes);
    return $keys;
}

function npc_hash_pin(string $pin, string $salt): string {
    return hash('sha256', $salt . '|' . $pin);
}

function npc_pin_matches(array $user, string $pin): bool {
    $salt = (string)($user['salt'] ?? '');
    $hash = (string)($user['pinHash'] ?? '');
    if ($hash === '') return false;
    if (hash_equals($hash, npc_hash_pin($pin, $salt))) return true;
    return false;
}

function npc_upsert_notes(PDO $db, array $notes, string $source = 'import admin'): array {
    $driver = (string)$db->getAttribute(PDO::ATTR_DRIVER_NAME);
    $jsonCast = $driver === 'pgsql' ? '::jsonb' : '';
    $stmtGet = $db->prepare('SELECT ref FROM notes WHERE ref = ?');
    $stmtIns = $db->prepare('INSERT INTO notes (ref, ref_raw, payload, updated_at) VALUES (?,?,?' . $jsonCast . ',?)');
    $stmtUpd = $db->prepare('UPDATE notes SET ref_raw = ?, payload = ?' . $jsonCast . ', updated_at = ? WHERE ref = ?');
    $added = 0;
    $updated = 0;
    $db->beginTransaction();
    foreach ($notes as $note) {
        if (!is_array($note)) continue;
        $ref = npc_compact_ref((string)($note['ref'] ?? ''));
        if ($ref === '') continue;
        $refRaw  = (string)($note['refRaw'] ?? $note['ref'] ?? $ref);
        $payload = npc_json_encode([
            'std'    => $note['std'] ?? new stdClass(),
            'fields' => $note['fields'] ?? new stdClass(),
        ]);
        $updatedAt = (string)($note['updatedAt'] ?? (int) round(microtime(true) * 1000));
        $stmtGet->execute([$ref]);
        $exists = $stmtGet->fetch();
        if ($exists) {
            $stmtUpd->execute([$refRaw, $payload, $updatedAt, $ref]);
            $updated++;
        } else {
            $stmtIns->execute([$ref, $refRaw, $payload, $updatedAt]);
            $added++;
        }
    }
    $db->commit();
    $total = $added + $updated;
    $log = $db->prepare('INSERT INTO imports (source, added, updated, total) VALUES (?,?,?,?)');
    $log->execute([$source, $added, $updated, $total]);
    if ($total && npc_blob_enabled()) {
        $delta = npc_delta_load();
        $byRef = [];
        foreach ($delta['notes'] as $n) {
            if (!is_array($n)) continue;
            $r = npc_compact_ref((string)($n['ref'] ?? ''));
            if ($r !== '') $byRef[$r] = $n;
        }
        foreach ($notes as $note) {
            if (!is_array($note)) continue;
            $ref = npc_compact_ref((string)($note['ref'] ?? ''));
            if ($ref === '') continue;
            $note['ref'] = $ref;
            $old = $byRef[$ref] ?? null;
            if (!$old || (int)($note['updatedAt'] ?? 0) >= (int)($old['updatedAt'] ?? 0)) {
                $byRef[$ref] = $note;
            }
        }
        $delta['notes'] = array_values($byRef);
        npc_delta_save($delta);
    }
    return ['added' => $added, 'updated' => $updated, 'total' => $total];
}
