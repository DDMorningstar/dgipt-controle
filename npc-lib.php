<?php
declare(strict_types=1);

function npc_config(): array {
    $file = __DIR__ . '/config.php';
    if (!is_file($file)) {
        throw new RuntimeException('Fichier config.php introuvable.');
    }
    return require $file;
}

function npc_db(array $config, bool $withDb = true): mysqli {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $mysqli = mysqli_init();
    if (!$mysqli) {
        throw new RuntimeException('Impossible d’initialiser MySQL.');
    }
    if (!empty($config['db_ssl'])) {
        $mysqli->ssl_set(null, null, null, null, null);
        if (defined('MYSQLI_OPT_SSL_VERIFY_SERVER_CERT')) {
            $mysqli->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
        }
    }
    $ok = $mysqli->real_connect(
        (string)$config['db_host'],
        (string)$config['db_user'],
        (string)$config['db_pass'],
        $withDb ? (string)$config['db_name'] : '',
        (int)($config['db_port'] ?? 3306)
    );
    if (!$ok) {
        throw new RuntimeException('Connexion MySQL impossible : ' . $mysqli->connect_error);
    }
    $mysqli->set_charset((string)($config['db_charset'] ?? 'utf8mb4'));
    return $mysqli;
}

function npc_ensure_schema(mysqli $db): void {
    $db->query("CREATE TABLE IF NOT EXISTS notes (
        ref         VARCHAR(191)  NOT NULL,
        ref_raw     VARCHAR(255)  NOT NULL,
        payload     JSON          NOT NULL,
        updated_at  BIGINT        NOT NULL,
        PRIMARY KEY (ref)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $db->query("CREATE TABLE IF NOT EXISTS imports (
        id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
        source      VARCHAR(255)  DEFAULT NULL,
        added       INT           NOT NULL DEFAULT 0,
        updated     INT           NOT NULL DEFAULT 0,
        total       INT           NOT NULL DEFAULT 0,
        imported_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $db->query("CREATE TABLE IF NOT EXISTS inspectors (
        id          VARCHAR(64)   NOT NULL,
        payload     JSON          NOT NULL,
        updated_at  BIGINT        NOT NULL,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

function npc_setup(array $config): void {
    try {
        $root = npc_db($config, false);
        $name = $root->real_escape_string((string)$config['db_name']);
        $root->query("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $root->select_db((string)$config['db_name']);
        npc_ensure_schema($root);
        $root->close();
        return;
    } catch (Throwable $e) {
        $db = npc_db($config, true);
        npc_ensure_schema($db);
        $db->close();
    }
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

function npc_fetch_notes(mysqli $db, array $opts = []): array {
    $withFields = !array_key_exists('fields', $opts) || (bool)$opts['fields'];
    $sql = 'SELECT ref, ref_raw, payload, updated_at FROM notes ORDER BY updated_at DESC, ref';
    $res = $db->query($sql);
    $records = [];
    while ($row = $res->fetch_assoc()) {
        $payload = json_decode($row['payload'] ?? '{}', true) ?: [];
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
    return $records;
}

function npc_fetch_notes_page(mysqli $db, int $offset, int $limit, bool $withFields = false): array {
    $offset = max(0, $offset);
    $limit = max(1, min(400, $limit));
    $total = 0;
    $cres = $db->query('SELECT COUNT(*) AS c FROM notes');
    if ($cres) $total = (int)($cres->fetch_assoc()['c'] ?? 0);
    $records = [];
    $sql = 'SELECT ref, ref_raw, payload, updated_at FROM notes ORDER BY ref LIMIT ' . $limit . ' OFFSET ' . $offset;
    $res = $db->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $payload = json_decode($row['payload'] ?? '{}', true) ?: [];
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

function npc_fetch_inspectors(mysqli $db): array {
    $list = [];
    $res = $db->query('SELECT id, payload, updated_at FROM inspectors');
    while ($row = $res->fetch_assoc()) {
        $p = json_decode($row['payload'] ?? '{}', true);
        if (is_array($p)) {
            $p['id'] = $row['id'];
            $p['updatedAt'] = (int)$row['updated_at'];
            $list[] = $p;
        }
    }
    return $list;
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

function npc_upsert_notes(mysqli $db, array $notes, string $source = 'import admin'): array {
    $stmtGet = $db->prepare('SELECT ref FROM notes WHERE ref = ?');
    $stmtIns = $db->prepare('INSERT INTO notes (ref, ref_raw, payload, updated_at) VALUES (?,?,?,?)');
    $stmtUpd = $db->prepare('UPDATE notes SET ref_raw = ?, payload = ?, updated_at = ? WHERE ref = ?');
    $added = 0;
    $updated = 0;
    $db->begin_transaction();
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
        $stmtGet->bind_param('s', $ref);
        $stmtGet->execute();
        $exists = $stmtGet->get_result()->fetch_assoc();
        if ($exists) {
            $stmtUpd->bind_param('ssss', $refRaw, $payload, $updatedAt, $ref);
            $stmtUpd->execute();
            $updated++;
        } else {
            $stmtIns->bind_param('ssss', $ref, $refRaw, $payload, $updatedAt);
            $stmtIns->execute();
            $added++;
        }
    }
    $db->commit();
    $total = $added + $updated;
    $log = $db->prepare('INSERT INTO imports (source, added, updated, total) VALUES (?,?,?,?)');
    $log->bind_param('siii', $source, $added, $updated, $total);
    $log->execute();
    return ['added' => $added, 'updated' => $updated, 'total' => $total];
}
