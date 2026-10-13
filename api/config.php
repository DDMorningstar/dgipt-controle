<?php
/**
 * Configuration serveur. Sur Vercel, utilisez les variables d'environnement.
 */
function npc_env_config(): array {
    $cfg = [
        'db_host' => getenv('DB_HOST') ?: '127.0.0.1',
        'db_name' => getenv('DB_NAME') ?: 'npc',
        'db_user' => getenv('DB_USER') ?: 'root',
        'db_pass' => getenv('DB_PASS') !== false ? (string)getenv('DB_PASS') : '',
        'db_charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
        'db_port' => (int)(getenv('DB_PORT') ?: 3306),
        'db_ssl' => in_array(strtolower((string)getenv('DB_SSL')), ['1', 'true', 'yes'], true),
        'db_driver' => getenv('DB_DRIVER') ?: 'mysql',
        'api_key' => getenv('API_KEY') ?: '',
    ];
    $url = getenv('DATABASE_URL') ?: getenv('POSTGRES_URL') ?: getenv('POSTGRES_PRISMA_URL')
        ?: getenv('MYSQL_URL') ?: getenv('MYSQL_PUBLIC_URL') ?: '';
    if ($url !== '') {
        $p = parse_url($url);
        if (is_array($p)) {
            $scheme = strtolower((string)($p['scheme'] ?? ''));
            if (in_array($scheme, ['postgres', 'postgresql', 'pgsql'], true)) {
                $cfg['db_driver'] = 'pgsql';
                $cfg['db_ssl'] = true;
                if (empty($p['port'])) $cfg['db_port'] = 5432;
            } elseif (in_array($scheme, ['mysql', 'mysqli', 'mariadb'], true)) {
                $cfg['db_driver'] = 'mysql';
            }
            if (!empty($p['host'])) $cfg['db_host'] = $p['host'];
            if (!empty($p['port'])) $cfg['db_port'] = (int)$p['port'];
            if (isset($p['user'])) $cfg['db_user'] = urldecode($p['user']);
            if (isset($p['pass'])) $cfg['db_pass'] = urldecode($p['pass']);
            if (!empty($p['path'])) $cfg['db_name'] = ltrim($p['path'], '/');
            $query = (string)($p['query'] ?? '');
            if ($query !== '' && (stripos($query, 'ssl') !== false || stripos($query, 'sslmode') !== false)) {
                $cfg['db_ssl'] = true;
            }
        }
        if (stripos($url, 'ssl=true') !== false || stripos($url, 'sslmode=require') !== false) {
            $cfg['db_ssl'] = true;
        }
        if (stripos($url, 'planetscale') !== false || stripos($url, 'aiven') !== false || stripos($url, 'railway') !== false || stripos($url, 'neon.tech') !== false) {
            $cfg['db_ssl'] = true;
        }
    }
    return $cfg;
}

return npc_env_config();
