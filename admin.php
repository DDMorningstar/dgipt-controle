<?php
declare(strict_types=1);
require __DIR__ . '/npc-lib.php';

$config = npc_config();
try {
    npc_setup($config);
    $db = npc_db($config, true);
    npc_ensure_schema($db);
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Base indisponible : ' . $e->getMessage();
    exit;
}

function admin_cookie_secret(): string {
    $s = getenv('API_KEY') ?: getenv('ADMIN_SECRET') ?: '';
    return $s !== '' ? $s : 'npc-dgipt-admin';
}
function admin_set_user(array $u): void {
    $payload = json_encode([
        'id' => (string)($u['id'] ?? ''),
        'name' => (string)($u['name'] ?? 'Administrateur'),
        'role' => 'admin',
        'exp' => time() + 86400 * 7,
    ], JSON_UNESCAPED_UNICODE);
    $sig = hash_hmac('sha256', $payload, admin_cookie_secret());
    $val = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=') . '.' . $sig;
    setcookie('npc_admin', $val, [
        'expires' => time() + 86400 * 7,
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    $_COOKIE['npc_admin'] = $val;
}
function admin_clear_user(): void {
    setcookie('npc_admin', '', ['expires' => time() - 3600, 'path' => '/']);
    unset($_COOKIE['npc_admin']);
}
function admin_user(): ?array {
    $c = (string)($_COOKIE['npc_admin'] ?? '');
    if ($c === '' || !str_contains($c, '.')) return null;
    [$b64, $sig] = explode('.', $c, 2);
    $payload = base64_decode(strtr($b64, '-_', '+/')) ?: '';
    if ($payload === '' || !hash_equals(hash_hmac('sha256', $payload, admin_cookie_secret()), $sig)) return null;
    $u = json_decode($payload, true);
    if (!is_array($u) || ($u['role'] ?? '') !== 'admin') return null;
    if ((int)($u['exp'] ?? 0) < time()) return null;
    return $u;
}
function require_admin_ajax(): array {
    $u = admin_user();
    if (!$u) {
        http_response_code(401);
        echo npc_json_encode(['ok' => false, 'error' => 'Session expiree. Reconnectez-vous.']);
        exit;
    }
    return $u;
}

$ajax = (string)($_GET['ajax'] ?? $_POST['ajax'] ?? '');
if ($ajax !== '') {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    @set_time_limit(120);
    try {
        if ($ajax === 'login') {
            $pin = (string)($_POST['pin'] ?? '');
            if (!preg_match('/^\d{4,6}$/', $pin)) {
                echo npc_json_encode(['ok' => false, 'error' => 'PIN invalide.']);
                exit;
            }
            $people = npc_fetch_inspectors($db);
            $found = null;
            foreach ($people as $u) {
                if (($u['active'] ?? true) === false) continue;
                if (($u['role'] ?? '') !== 'admin') continue;
                if (npc_pin_matches($u, $pin) || $pin === '0000') {
                    $found = $u;
                    break;
                }
            }
            if (!$found && $pin === '0000') {
                $found = ['id' => 'u_admin', 'name' => 'Administrateur', 'role' => 'admin', 'matricule' => 'ADMIN'];
            }
            if (!$found) {
                echo npc_json_encode(['ok' => false, 'error' => 'Acces refuse.']);
                exit;
            }
            $user = [
                'id' => (string)($found['id'] ?? 'u_admin'),
                'name' => (string)($found['name'] ?? 'Administrateur'),
                'role' => 'admin',
            ];
            admin_set_user($user);
            echo npc_json_encode(['ok' => true, 'user' => $user]);
            exit;
        }
        if ($ajax === 'logout') {
            admin_clear_user();
            echo npc_json_encode(['ok' => true]);
            exit;
        }

        $me = require_admin_ajax();

        if ($ajax === 'dashboard') {
            $notes = npc_fetch_notes($db, ['fields' => false]);
            $stats = npc_stats($notes);
            $inspectors = npc_fetch_inspectors($db);
            $imports = npc_fetch_imports($db, 12);
            $recent = [];
            foreach (array_slice($notes, 0, 12) as $n) {
                unset($n['fields']);
                $recent[] = $n;
            }
            echo npc_json_encode([
                'ok' => true,
                'user' => $me,
                'stats' => $stats,
                'inspectors' => count($inspectors),
                'imports' => $imports,
                'recent' => $recent,
            ]);
            exit;
        }

        if ($ajax === 'notes') {
            $status = (string)($_GET['status'] ?? 'all');
            $q = trim((string)($_GET['q'] ?? ''));
            $notes = npc_fetch_notes($db, ['fields' => false]);
            $filtered = [];
            foreach ($notes as $n) {
                if (npc_note_matches($n, $status, $q)) $filtered[] = $n;
            }
            echo npc_json_encode([
                'ok' => true,
                'count' => count($filtered),
                'notes' => array_slice($filtered, 0, 250),
            ]);
            exit;
        }

        if ($ajax === 'inspectors') {
            $list = npc_fetch_inspectors($db);
            foreach ($list as &$u) {
                unset($u['pinHash'], $u['salt']);
            }
            unset($u);
            echo npc_json_encode(['ok' => true, 'inspectors' => $list]);
            exit;
        }

        if ($ajax === 'inspector_save') {
            $raw = file_get_contents('php://input') ?: '';
            $body = json_decode($raw, true);
            if (!is_array($body)) $body = $_POST;
            $id = trim((string)($body['id'] ?? ''));
            if ($id === '') $id = 'u_' . (string)round(microtime(true) * 1000);
            $name = trim((string)($body['name'] ?? ''));
            $pin = trim((string)($body['pin'] ?? ''));
            if ($name === '') {
                echo npc_json_encode(['ok' => false, 'error' => 'Le nom est obligatoire.']);
                exit;
            }
            $existing = null;
            foreach (npc_fetch_inspectors($db) as $u) {
                if (($u['id'] ?? '') === $id) { $existing = $u; break; }
            }
            if (!$existing && !preg_match('/^\d{4,6}$/', $pin)) {
                echo npc_json_encode(['ok' => false, 'error' => 'PIN de 4 a 6 chiffres obligatoire.']);
                exit;
            }
            if ($pin !== '' && !preg_match('/^\d{4,6}$/', $pin)) {
                echo npc_json_encode(['ok' => false, 'error' => 'PIN invalide.']);
                exit;
            }
            $rec = $existing ?: ['id' => $id, 'createdAt' => (int) round(microtime(true) * 1000)];
            $rec['id'] = $id;
            $rec['name'] = $name;
            $rec['matricule'] = trim((string)($body['matricule'] ?? ''));
            $rec['ressort'] = trim((string)($body['ressort'] ?? ''));
            $rec['role'] = (($body['role'] ?? '') === 'admin') ? 'admin' : 'inspector';
            $rec['active'] = !isset($body['active']) || $body['active'] === true || $body['active'] === '1' || $body['active'] === 1;
            $rec['updatedAt'] = (int) round(microtime(true) * 1000);
            if ($pin !== '') {
                $rec['salt'] = bin2hex(random_bytes(8));
                $rec['pinHash'] = npc_hash_pin($pin, $rec['salt']);
                $rec['mustChangePin'] = false;
            }
            npc_upsert_inspectors($db, [$rec]);
            echo npc_json_encode(['ok' => true, 'id' => $id]);
            exit;
        }

        if ($ajax === 'import') {
            $raw = file_get_contents('php://input') ?: '';
            $body = json_decode($raw, true);
            if (!is_array($body) || !isset($body['notes']) || !is_array($body['notes'])) {
                echo npc_json_encode(['ok' => false, 'error' => 'Format d import invalide.']);
                exit;
            }
            $source = (string)($body['source'] ?? 'import console admin');
            $r = npc_upsert_notes($db, $body['notes'], $source);
            echo npc_json_encode(['ok' => true] + $r);
            exit;
        }

        echo npc_json_encode(['ok' => false, 'error' => 'Action inconnue']);
    } catch (Throwable $e) {
        http_response_code(500);
        echo npc_json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

$logged = admin_user();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="robots" content="noindex" />
<title>Console administration — Notes NP</title>
<link rel="icon" href="logo-dgipt.jpg" type="image/jpeg" />
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
<style>
  :root{
    --ink:#163a4a; --muted:#5b7380; --paper:#f4f7f9; --card:#fff;
    --line:#d3e0e6; --navy:#163a4a; --navy2:#1a5f7a; --gold:#159895;
    --ok:#1f8a70; --warn:#c4841d; --bad:#c44b4b; --open:#2a7a96; --notice:#159895;
  }
  *{box-sizing:border-box}
  html,body{margin:0;min-height:100%;background:var(--paper);color:var(--ink);
    font-family:Figtree,Segoe UI,system-ui,sans-serif;}
  body{display:grid;grid-template-columns:250px 1fr;min-height:100vh;
    background:
      radial-gradient(1200px 400px at 10% -10%, rgba(21,152,149,.10), transparent 60%),
      radial-gradient(900px 320px at 100% 0%, rgba(26,95,122,.08), transparent 55%),
      var(--paper);}
  body.locked{grid-template-columns:1fr}
  a{color:inherit}
  .rail{
    background:linear-gradient(180deg,#163a4a,#102a36);color:#e8f3f7;
    padding:26px 16px;display:flex;flex-direction:column;gap:6px;
    border-right:6px solid var(--gold);
  }
  .brand{font-size:1.4rem;font-weight:800;letter-spacing:-.03em;margin:0 8px 16px;line-height:1.15;display:flex;align-items:center;gap:10px}
  .brand img{width:46px;height:46px;border-radius:10px;background:#fff;object-fit:contain;padding:3px;flex:0 0 auto}
  .brand small{display:block;font-size:.68rem;letter-spacing:.16em;text-transform:uppercase;opacity:.62;margin-top:8px;font-weight:700}
  .rail button,.rail a{
    display:block;width:100%;text-align:left;background:transparent;border:0;color:#e8f3f7;
    padding:11px 12px;border-radius:10px;font:inherit;font-weight:650;cursor:pointer;text-decoration:none;
  }
  .rail button.on,.rail a.on{background:linear-gradient(90deg,rgba(21,152,149,.95),rgba(26,95,122,.92))}
  .rail .spacer{flex:1}
  .main{padding:28px 32px 56px;max-width:1200px}
  h1{font-size:2rem;margin:0 0 6px;letter-spacing:-.03em}
  .lede{color:var(--muted);margin:0 0 22px;line-height:1.45}
  .kpis{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:22px}
  .kpi{
    background:var(--card);border:1px solid var(--line);border-radius:16px;padding:16px 16px 14px;
    cursor:pointer;text-align:left;font:inherit;color:inherit;width:100%;
    box-shadow:inset 5px 0 0 var(--navy);
  }
  .kpi:hover{border-color:var(--navy)}
  .kpi .n{font-variant-numeric:tabular-nums;font-size:1.85rem;font-weight:800;line-height:1}
  .kpi .l{font-size:.8rem;font-weight:800;margin-top:8px}
  .kpi .h{font-size:.72rem;color:var(--muted);margin-top:4px;line-height:1.35}
  .kpi.paid{box-shadow:inset 5px 0 0 var(--ok)} .kpi.paid .n{color:var(--ok)}
  .kpi.open{box-shadow:inset 5px 0 0 var(--open)} .kpi.open .n{color:var(--open)}
  .kpi.partial{box-shadow:inset 5px 0 0 var(--warn)} .kpi.partial .n{color:var(--warn)}
  .kpi.unpaid{box-shadow:inset 5px 0 0 var(--bad)} .kpi.unpaid .n{color:var(--bad)}
  .kpi.notice{box-shadow:inset 5px 0 0 var(--notice)} .kpi.notice .n{color:var(--notice)}
  .panel{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:18px;margin-bottom:16px}
  .panel h2{margin:0 0 12px;font-size:1.05rem}
  table{width:100%;border-collapse:collapse;font-size:.88rem}
  th,td{padding:9px 8px;border-bottom:1px solid var(--line);text-align:left;vertical-align:top}
  th{font-size:.72rem;letter-spacing:.06em;text-transform:uppercase;color:var(--muted)}
  .chip{display:inline-block;padding:3px 8px;border-radius:999px;font-size:.72rem;font-weight:700}
  .chip.ok{background:#e3f5ef;color:var(--ok)} .chip.warn{background:#f8efd8;color:var(--warn)}
  .chip.bad{background:#f8e4e4;color:var(--bad)} .chip.open{background:#e4f1f6;color:var(--open)}
  .chip.notice{background:#e3f5f3;color:var(--notice)} .chip.info{background:#eef3f5;color:var(--muted)}
  .toolbar{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px}
  input,select,textarea{border:1.5px solid var(--line);border-radius:10px;padding:10px 12px;font:inherit;background:#fff}
  .btn{border:0;border-radius:10px;padding:10px 14px;font-weight:700;cursor:pointer;font:inherit}
  .btn-navy{background:linear-gradient(145deg,#1a5f7a,#159895);color:#fff}
  .btn-ghost{background:#e8f3f7;color:var(--ink)}
  .lock{min-height:100vh;display:grid;place-items:center;background:#102a36;color:#e8f3f7}
  .lock-box{width:min(400px,92vw);background:#163a4a;border-radius:20px;padding:28px;border-top:6px solid var(--gold)}
  .lock-box h1{color:#e8f3f7;font-size:1.55rem}
  .lock-box input{width:100%;margin:12px 0 16px;letter-spacing:.28em;text-align:center;font-size:1.4rem}
  .err{color:#fecaca;font-size:.88rem;min-height:1.2em}
  .hidden{display:none !important}
  .drop{border:1.5px dashed #b7cdd6;border-radius:14px;padding:22px;text-align:center;background:#fff;cursor:pointer}
  .msg{font-size:.88rem;color:var(--muted);min-height:1.2em}
  .grid2{display:grid;grid-template-columns:1fr 1fr;gap:10px}
  @media (max-width:900px){
    body{grid-template-columns:1fr}
    .rail{flex-direction:row;flex-wrap:wrap;align-items:center;border-right:0;border-bottom:6px solid var(--gold)}
    .brand{width:100%;margin-bottom:4px}
    .kpis,.grid2{grid-template-columns:1fr 1fr}
    .main{padding:18px 14px 40px}
  }
</style>
</head>
<body class="<?= $logged ? '' : 'locked' ?>">
<?php if (!$logged): ?>
  <div class="lock">
    <form class="lock-box" id="loginForm">
      <div class="brand"><img src="logo-dgipt.jpg" alt="DGIPT" /> <span>DGIPT<small>Console web — hors application terrain</small></span></div>
      <h1>Administration</h1>
      <p class="lede" style="color:#c9c3b8">PIN administrateur. Les inspecteurs utilisent l'application, pas cet ecran.</p>
      <input name="pin" id="pin" inputmode="numeric" maxlength="6" autocomplete="one-time-code" placeholder="????" />
      <button class="btn btn-navy" type="submit" style="width:100%">Entrer</button>
      <p class="err" id="loginErr"></p>
    </form>
  </div>
<?php else: ?>
  <aside class="rail">
    <p class="brand"><img src="logo-dgipt.jpg" alt="DGIPT" /> <span>DGIPT<small>Pilotage plateforme</small></span></p>
    <button type="button" class="on" data-page="dash">Tableau de bord</button>
    <button type="button" data-page="notes">Notes</button>
    <button type="button" data-page="import">Import Excel</button>
    <button type="button" data-page="team">Inspecteurs</button>
    <a href="index.php">App terrain (PWA)</a>
    <div class="spacer"></div>
    <button type="button" id="btnOut">Deconnexion</button>
  </aside>
  <main class="main">
    <section id="page-dash">
      <h1>Tableau de bord</h1>
      <p class="lede" id="hello">Chargement des indicateurs?</p>
      <div class="kpis" id="kpis"></div>
      <div class="panel">
        <h2>Dernieres notes synchronisees</h2>
        <div id="recent"></div>
      </div>
      <div class="panel">
        <h2>Importations serveur</h2>
        <div id="imports"></div>
      </div>
    </section>
    <section id="page-notes" class="hidden">
      <h1>Notes</h1>
      <p class="lede">Filtrez par statut : payee, ouverte, partielle, impayee, mise en demeure.</p>
      <div class="toolbar">
        <input id="q" type="search" placeholder="Reference, contribuable, telephone..." style="flex:1;min-width:180px" />
        <select id="st">
          <option value="all">Tous les statuts</option>
          <option value="paid">Payees</option>
          <option value="open">Ouvertes</option>
          <option value="partial">Partielles</option>
          <option value="unpaid">Impayees</option>
          <option value="notice">Mises en demeure / contraintes</option>
          <option value="cancelled">Rejetees</option>
          <option value="other">Autres</option>
        </select>
      </div>
      <div class="panel" id="notesBox">Chargement?</div>
    </section>
    <section id="page-import" class="hidden">
      <h1>Import Excel</h1>
      <p class="lede">Chargez le fichier des notes directement dans MySQL. Les telephones recuperent ensuite la base via l'app.</p>
      <div class="panel">
        <div class="drop" id="drop">Deposez un fichier .xlsx / .xls / .csv<br><small>ou cliquez pour parcourir</small></div>
        <input id="file" type="file" accept=".xlsx,.xls,.csv,text/csv" class="hidden" />
        <p class="msg" id="impMsg"></p>
      </div>
    </section>
    <section id="page-team" class="hidden">
      <h1>Inspecteurs</h1>
      <p class="lede">Comptes PIN pour l'application terrain. PIN 0000 reste un acces admin de secours.</p>
      <div class="panel">
        <div class="grid2">
          <input id="nm" placeholder="Nom" />
          <input id="mat" placeholder="Matricule" />
          <input id="res" placeholder="Ressort / antenne" />
          <select id="rl"><option value="inspector">Inspecteur</option><option value="admin">Administrateur</option></select>
          <input id="pn" inputmode="numeric" maxlength="6" placeholder="PIN 4 a 6 chiffres" />
          <button class="btn btn-navy" type="button" id="saveInsp">Enregistrer</button>
        </div>
        <p class="msg" id="teamMsg"></p>
      </div>
      <div class="panel" id="teamBox">Chargement?</div>
    </section>
  </main>
<?php endif; ?>
<script src="vendor/xlsx.full.min.js"></script>
<script>
const $ = s => document.querySelector(s);
const $$ = s => Array.from(document.querySelectorAll(s));
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
function chip(st){
  const notice = st && st.notice;
  const lv = notice && (st.key !== 'paid') ? 'notice' : ((st && st.level) || 'info');
  const lab = (st && st.label) || '?';
  const extra = notice ? ' ? Mise en demeure' : '';
  return `<span class="chip ${esc(lv)}">${esc(lab)}${extra}</span>`;
}
function money(n, d){
  if (n == null || n === '') return '?';
  const x = Number(n);
  if (!Number.isFinite(x)) return esc(n);
  return x.toLocaleString('fr-FR') + (d ? ' ' + d : '');
}
function go(page){
  $$('[data-page]').forEach(b => b.classList.toggle('on', b.dataset.page === page));
  ['dash','notes','import','team'].forEach(p => {
    const el = document.getElementById('page-' + p);
    if (el) el.classList.toggle('hidden', p !== page);
  });
  if (page === 'notes') loadNotes();
  if (page === 'team') loadTeam();
}
function tableNotes(rows){
  if (!rows.length) return '<p>Aucune note.</p>';
  return `<table><thead><tr><th>Reference</th><th>Contribuable</th><th>Statut</th><th>Attendu</th><th>Paye</th><th>Reste</th></tr></thead><tbody>` +
    rows.map(n => {
      const s = n.std || {};
      return `<tr>
        <td><b>${esc(n.refRaw || n.ref)}</b></td>
        <td>${esc(s.nom || '?')}<br><small style="color:#6a6158">${esc(s.tel || s.etape || '')}</small></td>
        <td>${chip(n.status)}<br><small style="color:#6a6158">${esc((n.status && n.status.etape) || (n.status && n.status.hint) || '')}</small></td>
        <td>${money(s.montantAttendu ?? s.montantDeclare, s.devise)}</td>
        <td>${money(s.montantPaye, s.devise)}</td>
        <td>${money(s.reste, s.devise)}</td>
      </tr>`;
    }).join('') + '</tbody></table>';
}

<?php if (!$logged): ?>
$('#loginForm').addEventListener('submit', async e => {
  e.preventDefault();
  const fd = new FormData();
  fd.append('ajax', 'login');
  fd.append('pin', $('#pin').value.trim());
  const r = await fetch('admin.php', { method: 'POST', body: fd });
  const t = await r.text();
  let d = {};
  try { d = JSON.parse(t); } catch(err){ $('#loginErr').textContent = t || 'Reponse serveur vide.'; return; }
  if (!d.ok){ $('#loginErr').textContent = d.error || 'Echec'; return; }
  location.reload();
});
$('#pin').addEventListener('input', () => {
  if ($('#pin').value.trim().length >= 4) $('#loginForm').requestSubmit();
});
<?php else: ?>
$$('.rail [data-page]').forEach(b => b.addEventListener('click', () => go(b.dataset.page)));
$('#btnOut').addEventListener('click', async () => {
  const fd = new FormData(); fd.append('ajax','logout');
  await fetch('admin.php', { method:'POST', body: fd });
  location.reload();
});

async function loadJson(url, opt){
  const r = await fetch(url, opt);
  const t = await r.text();
  if (!t) throw new Error('Reponse vide du serveur');
  return JSON.parse(t);
}

async function loadDash(){
  try {
    const d = await loadJson('admin.php?ajax=dashboard');
    if (!d.ok){ $('#hello').textContent = d.error || 'Erreur dashboard'; return; }
    $('#hello').textContent = `Connecte : ${d.user.name}. ${Number(d.stats.total||0).toLocaleString('fr-FR')} note(s) sur le serveur ? ${d.inspectors} compte(s).`;
    const items = [
      ['total','Toutes les notes','Ensemble de la base MySQL','all', d.stats.total],
      ['paid','Payees','Soldees integralement','paid', d.stats.paid],
      ['open','Ouvertes','Emises, en attente de paiement','open', d.stats.open],
      ['partial','Partielles','Versement incomplet','partial', d.stats.partial],
      ['unpaid','Impayees','Reste a recouvrer','unpaid', d.stats.unpaid],
      ['notice','Mises en demeure','Recouvrement / contrainte','notice', d.stats.notice]
    ];
    $('#kpis').innerHTML = items.map(([k,l,h,st,n]) =>
      `<button type="button" class="kpi ${k}" data-st="${st}"><div class="n">${Number(n||0).toLocaleString('fr-FR')}</div><div class="l">${l}</div><div class="h">${h}</div></button>`
    ).join('');
    $('#kpis').querySelectorAll('.kpi').forEach(b => b.addEventListener('click', () => {
      $('#st').value = b.dataset.st;
      go('notes');
    }));
    $('#recent').innerHTML = tableNotes(d.recent || []);
    const im = d.imports || [];
    $('#imports').innerHTML = im.length ? `<table><thead><tr><th>Date</th><th>Source</th><th>Ajouts</th><th>Maj</th></tr></thead><tbody>` +
      im.map(x => `<tr><td>${esc(x.imported_at)}</td><td>${esc(x.source)}</td><td>${x.added}</td><td>${x.updated}</td></tr>`).join('') +
      '</tbody></table>' : '<p>Aucune importation serveur pour le moment.</p>';
  } catch (e) {
    $('#hello').textContent = 'Impossible de charger le tableau de bord : ' + e.message;
  }
}
async function loadNotes(){
  try {
    const q = encodeURIComponent($('#q').value.trim());
    const st = encodeURIComponent($('#st').value);
    const d = await loadJson('admin.php?ajax=notes&q=' + q + '&status=' + st);
    $('#notesBox').innerHTML = d.ok
      ? `<p class="lede">${Number(d.count||0).toLocaleString('fr-FR')} note(s)${d.count > (d.notes||[]).length ? ' ? 250 premieres affichees' : ''}</p>` + tableNotes(d.notes || [])
      : (d.error || 'Erreur');
  } catch (e) {
    $('#notesBox').textContent = e.message;
  }
}
async function loadTeam(){
  try {
    const d = await loadJson('admin.php?ajax=inspectors');
    const list = d.inspectors || [];
    $('#teamBox').innerHTML = list.length ? `<table><thead><tr><th>Nom</th><th>Matricule</th><th>Role</th><th>Etat</th></tr></thead><tbody>` +
      list.map(u => `<tr><td>${esc(u.name)}</td><td>${esc(u.matricule || '?')}</td><td>${u.role === 'admin' ? 'Administrateur' : 'Inspecteur'}</td><td>${u.active === false ? 'Inactif' : 'Actif'}</td></tr>`).join('') +
      '</tbody></table>' : '<p>Aucun compte. Creez-en un ci-dessus.</p>';
  } catch (e) {
    $('#teamBox').textContent = e.message;
  }
}
$('#q').addEventListener('input', () => { clearTimeout(window.__nq); window.__nq = setTimeout(loadNotes, 280); });
$('#st').addEventListener('change', loadNotes);

$('#saveInsp').addEventListener('click', async () => {
  $('#teamMsg').textContent = 'Enregistrement?';
  try {
    const d = await loadJson('admin.php?ajax=inspector_save', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({
        name: $('#nm').value.trim(),
        matricule: $('#mat').value.trim(),
        ressort: $('#res').value.trim(),
        role: $('#rl').value,
        pin: $('#pn').value.trim(),
        active: true
      })
    });
    if (!d.ok){ $('#teamMsg').textContent = d.error; return; }
    $('#nm').value = $('#mat').value = $('#res').value = $('#pn').value = '';
    $('#teamMsg').textContent = 'Compte enregistre. Il sera visible dans l\'app apres synchronisation.';
    loadTeam();
  } catch (e) { $('#teamMsg').textContent = e.message; }
});

function compactRef(s){ return String(s||'').toUpperCase().replace(/[^A-Z0-9]/g,''); }
function cell(v){
  if (v == null) return '';
  if (v instanceof Date) return v.toLocaleDateString('fr-FR');
  return String(v).trim();
}
function ingestSheet(wb){
  const sheet = wb.Sheets[wb.SheetNames[0]];
  const rows = XLSX.utils.sheet_to_json(sheet, { header: 1, raw: false, defval: '' });
  if (!rows.length) throw new Error('Fichier vide');
  let headerIdx = 0, headers = rows[0].map(cell);
  for (let i = 0; i < Math.min(rows.length, 12); i++){
    const h = rows[i].map(cell);
    if (h.some(x => /reference|statut|status|paid cdf/i.test(x))){ headerIdx = i; headers = h; break; }
  }
  const refCol = headers.findIndex(h => /reference/i.test(h));
  if (refCol < 0) throw new Error('Colonne Reference Number introuvable');
  const records = [];
  for (let i = headerIdx + 1; i < rows.length; i++){
    const row = rows[i] || [];
    const rawRef = cell(row[refCol]);
    if (!rawRef) continue;
    const ref = compactRef(rawRef);
    if (!ref) continue;
    const fields = {};
    headers.forEach((h, j) => { if (h) fields[h] = cell(row[j]); });
    records.push({
      ref, refRaw: rawRef, updatedAt: Date.now(),
      std: { ref: rawRef, statut: fields.Status || fields.Statut || '', etape: fields['Tax Step'] || '' },
      fields
    });
  }
  return records;
}
async function importFile(file){
  $('#impMsg').textContent = 'Lecture de ' + file.name + '?';
  const buf = await file.arrayBuffer();
  const wb = XLSX.read(buf, { type: 'array' });
  const notes = ingestSheet(wb);
  if (!notes.length){ $('#impMsg').textContent = 'Aucune ligne valide.'; return; }
  const chunk = 400;
  let added = 0, updated = 0;
  for (let i = 0; i < notes.length; i += chunk){
    $('#impMsg').textContent = `Envoi ${Math.min(i+chunk, notes.length)} / ${notes.length}?`;
    const d = await loadJson('admin.php?ajax=import', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ notes: notes.slice(i, i+chunk), source: file.name })
    });
    if (!d.ok) throw new Error(d.error || 'Import refuse');
    added += d.added || 0; updated += d.updated || 0;
  }
  $('#impMsg').textContent = `${notes.length} note(s) : ${added} ajout(s), ${updated} mise(s) a jour.`;
}
$('#drop').addEventListener('click', () => $('#file').click());
$('#file').addEventListener('change', e => { if (e.target.files[0]) importFile(e.target.files[0]).catch(err => $('#impMsg').textContent = err.message); });
$('#drop').addEventListener('dragover', e => { e.preventDefault(); });
$('#drop').addEventListener('drop', e => {
  e.preventDefault();
  const f = e.dataTransfer.files[0];
  if (f) importFile(f).catch(err => $('#impMsg').textContent = err.message);
});

loadDash();
<?php endif; ?>
</script>
</body>
</html>
