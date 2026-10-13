<?php
header('Cache-Control: public, max-age=604800, stale-while-revalidate=86400, stale-if-error=604800');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 604800) . ' GMT');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
<meta name="theme-color" content="#1a5f7a" />
<meta name="description" content="Contrôle hors ligne de l'authenticité et du statut des notes de perception." />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
<meta name="mobile-web-app-capable" content="yes" />
<link rel="manifest" href="manifest.json" />
<link rel="icon" href="icon-192.png" type="image/png" />
<link rel="apple-touch-icon" href="apple-touch-icon.png" sizes="180x180" />
<title>Contrôle des Notes de Perception</title>
<script>
window.__bootT0 = Date.now();
window.setBootProgress = function(pct, step, detail){
  var p = Math.max(0, Math.min(100, Number(pct) || 0));
  var bar = document.getElementById('bootBar');
  var st = document.getElementById('bootStep');
  var d = document.getElementById('bootDetail');
  var n = document.getElementById('bootPct');
  var t = document.getElementById('bootTime');
  if (bar) bar.style.width = p + '%';
  if (st && step) st.textContent = step;
  if (d && detail !== undefined) d.textContent = detail;
  if (n) n.textContent = Math.round(p) + ' %';
  if (t) t.textContent = Math.max(0, Math.round((Date.now() - window.__bootT0) / 1000)) + ' s';
};
window.__bootTick = setInterval(function(){
  var t = document.getElementById('bootTime');
  if (t) t.textContent = Math.max(0, Math.round((Date.now() - window.__bootT0) / 1000)) + ' s';
}, 400);
window.addEventListener('beforeinstallprompt', function(e){
  e.preventDefault();
  window.__deferredInstall = e;
});
if ('serviceWorker' in navigator && location.protocol !== 'file:'){
  navigator.serviceWorker.register('sw.js', { scope: './', updateViaCache: 'none' }).catch(function(){});
}
</script>
<style>
  :root{
    --navy:#163a4a;
    --navy-2:#1a5f7a;
    --ink:#163a4a;
    --muted:#5b7380;
    --paper:#e8f3f7;
    --paper-2:#d7e6ec;
    --card:#ffffff;
    --line:#d3e0e6;
    --amber:#159895;
    --teal:#1f8a70;
    --teal-2:#159895;
    --crimson:#c44b4b;
    --ok:#1f8a70; --ok-bg:#e3f5ef; --ok-line:#8fcfbb;
    --warn:#c4841d; --warn-bg:#f8efd8; --warn-line:#e0c07a;
    --danger:#c44b4b; --danger-bg:#f8e4e4; --danger-line:#e3a3a3;
    --info:#2a7a96; --info-bg:#e4f1f6; --info-line:#9ec5d4;
    --accent:#159895;
    --on-dark:#e8f3f7;
    --radius:16px;
    --shadow:0 0.15rem 0.6rem rgba(26, 95, 122, 0.08);
    --safe-t: env(safe-area-inset-top, 0px);
    --safe-b: env(safe-area-inset-bottom, 0px);
    --display:"Segoe UI", system-ui, -apple-system, sans-serif;
    --sans:"Segoe UI", system-ui, -apple-system, sans-serif;
    --mono:ui-monospace, "Cascadia Mono", Consolas, monospace;
    --dur-1:.16s; --dur-2:.28s; --ease:cubic-bezier(.2,.8,.2,1);
  }
  *{box-sizing:border-box;-webkit-tap-highlight-color:transparent}
  html,body{margin:0;padding:0}
  body{
    font-family:var(--sans); color:var(--ink); font-size:16px; line-height:1.45;
    background-color:#f4f7f9;
    background-image:
      radial-gradient(1200px 400px at 10% -10%, rgba(21, 152, 149, 0.10), transparent 60%),
      radial-gradient(900px 320px at 100% 0%, rgba(26, 95, 122, 0.08), transparent 55%);
    -webkit-font-smoothing:antialiased;
    min-height:100dvh;
  }
  button,input,select{font-family:inherit;font-size:inherit}
  .hidden{display:none !important}
  :focus-visible{outline:3px solid var(--accent); outline-offset:3px}

  .skip{
    position:absolute;left:-999px;top:8px;background:#fff;padding:8px 12px;z-index:200;border-radius:8px;
  }
  .skip:focus{left:8px}

  .topbar{
    position:sticky; top:0; z-index:40;
    background:#102a36; color:var(--on-dark);
    padding:calc(10px + var(--safe-t)) 16px 12px;
    display:flex; align-items:center; justify-content:space-between; gap:12px;
    border-bottom:3px solid var(--accent);
  }
  .brand{display:flex;align-items:center;gap:10px;min-width:0}
  .brand img{width:40px;height:40px;flex:0 0 auto;border-radius:10px;background:#fff;object-fit:contain;padding:2px}
  .brand-txt{display:flex;flex-direction:column;min-width:0}
  .brand-kicker{font-size:.62rem;letter-spacing:.16em;text-transform:uppercase;opacity:.7;font-family:var(--display);font-weight:700}
  .brand-title{font-family:var(--display);font-weight:800;letter-spacing:.02em;font-size:.98rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .net{
    font-size:.68rem;font-weight:700;padding:5px 10px;border-radius:999px;
    background:rgba(244,239,228,.1); border:1px solid rgba(244,239,228,.28);
    display:flex;align-items:center;gap:6px;white-space:nowrap;letter-spacing:.04em;text-transform:uppercase;
    font-family:var(--display);
  }
  .dot{width:8px;height:8px;border-radius:50%;background:#34d399;box-shadow:0 0 0 3px rgba(52,211,153,.2)}
  .net.off .dot{background:#f87171;box-shadow:0 0 0 3px rgba(248,113,113,.18)}

  main{max-width:840px;margin:0 auto;padding:18px 14px calc(102px + var(--safe-b))}
  .view{display:none}
  .view.active{display:block;animation:rise .22s ease}
  @keyframes rise{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}

  .dossier{
    background:var(--card);border-radius:var(--radius);padding:18px 18px 18px 22px;
    box-shadow:var(--shadow);border:1.5px solid var(--line);margin-bottom:14px;
    position:relative;
  }
  .dossier::before{
    content:"";position:absolute;left:0;top:18px;width:7px;height:32px;
    background:var(--accent);border-radius:0 4px 4px 0;
  }
  .dossier h2{margin:0 0 4px;font-family:var(--display);font-size:1.05rem;letter-spacing:.02em}
  .muted{color:var(--muted);font-size:.88rem;margin:0}
  .kicker{
    font-family:var(--display);font-size:.68rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase;
    color:var(--muted);margin:18px 4px 8px;
  }

  .search-card{padding-top:20px}
  .search-label{display:block;font-family:var(--display);font-weight:800;font-size:.95rem;margin-bottom:10px;letter-spacing:.04em;text-transform:uppercase}
  .search-row{position:relative;display:flex;align-items:center}
  #refInput{
    width:100%;padding:18px 46px 18px 16px;border-radius:12px;border:2px solid var(--navy);
    font-family:var(--mono);font-size:1.12rem;font-weight:600;letter-spacing:.04em;background:#fff;color:var(--ink);
    outline:none;
  }
  #refInput:focus{box-shadow:0 0 0 4px rgba(21,152,149,.2);border-color:var(--accent)}
  .clear-btn{
    position:absolute;right:8px;width:34px;height:34px;border-radius:50%;border:0;
    background:var(--paper-2);color:var(--navy);font-size:15px;font-weight:700;cursor:pointer;display:none;
  }
  .clear-btn.show{display:block}
  .actions{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:12px}
  .actions .btn-primary{grid-column:1 / -1}

  .btn{
    border:0;border-radius:12px;padding:14px 16px;font-weight:700;cursor:pointer;
    display:flex;align-items:center;justify-content:center;gap:8px;
    transition:transform .08s, filter .15s;user-select:none;
    font-family:var(--display);letter-spacing:.03em;
  }
  .btn:active{transform:scale(.975)}
  .btn-primary{background:linear-gradient(145deg,#1a5f7a 0%,#159895 100%);color:#fff;font-size:1.02rem;box-shadow:0 .35rem .9rem rgba(21,152,149,.28)}
  .btn-ghost{background:var(--paper);color:var(--navy);border:1.5px solid var(--line)}
  .btn-ok{background:var(--teal);color:#fff}
  .btn-danger{background:var(--danger-bg);color:var(--crimson);border:1.5px solid var(--danger-line)}
  .btn-block{width:100%}
  .btn svg{width:19px;height:19px}

  .stats{display:grid;grid-template-columns:repeat(2,1fr);gap:10px}
  .stat{background:var(--paper);border:1px solid var(--line);border-radius:12px;padding:12px}
  .stat .v{font-family:var(--display);font-size:1.5rem;font-weight:800;color:var(--navy);line-height:1.1}
  .stat .l{font-size:.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;font-weight:700;margin-top:4px}

  .verdict{text-align:center;margin:6px 0 18px;padding:8px 0 4px}
  .stamp{
    width:min(268px,78vw);aspect-ratio:1;margin:0 auto 8px;
    border:5px solid currentColor;border-radius:50%;
    display:grid;place-items:center;
    transform:rotate(-7deg);
    box-shadow:0 0 0 7px var(--paper), 0 0 0 10px currentColor;
    background:rgba(255,255,255,.55);
  }
  .stamp-inner{
    width:86%;height:86%;border:2px dashed currentColor;border-radius:50%;
    display:flex;flex-direction:column;align-items:center;justify-content:center;
    text-align:center;padding:14px;
  }
  .stamp-kicker{font-family:var(--display);font-size:.62rem;font-weight:800;letter-spacing:.16em;text-transform:uppercase;opacity:.85}
  .stamp-title{font-family:var(--display);font-size:1.28rem;font-weight:900;letter-spacing:.06em;line-height:1.15;margin:6px 0}
  .stamp-ref{font-family:var(--mono);font-size:.78rem;font-weight:600;word-break:break-all;opacity:.92}
  .verdict.found{color:var(--teal)}
  .verdict.missing{color:var(--crimson)}

  .chip{
    display:inline-flex;align-items:center;gap:7px;padding:8px 14px;border-radius:999px;
    font-family:var(--display);font-weight:800;font-size:.82rem;letter-spacing:.06em;
  }
  .chip.ok{background:var(--ok-bg);color:var(--ok);border:1px solid var(--ok-line)}
  .chip.warn{background:var(--warn-bg);color:var(--warn);border:1px solid var(--warn-line)}
  .chip.bad{background:var(--danger-bg);color:var(--danger);border:1px solid var(--danger-line)}
  .chip.info{background:var(--info-bg);color:var(--info);border:1px solid var(--info-line)}
  .chip.open{background:var(--info-bg);color:var(--info);border:1px solid var(--info-line)}
  .chip.notice{background:var(--warn-bg);color:var(--warn);border:1px solid var(--warn-line)}

  .info-row{
    display:flex;justify-content:space-between;gap:14px;padding:12px 2px;
    border-bottom:1px dashed var(--line);
  }
  .info-row:last-child{border-bottom:0}
  .info-row .k{color:var(--muted);font-size:.84rem;font-weight:600;flex:1 1 42%}
  .info-row .v{font-weight:700;text-align:right;flex:1 1 58%;word-break:break-word}
  .info-row .v.money{font-variant-numeric:tabular-nums;font-family:var(--mono);color:var(--navy);font-size:.92rem}
  .info-row .v.big{font-size:1.12rem;color:var(--amber)}

  .list-item{
    display:flex;align-items:center;gap:12px;padding:12px;border-radius:13px;
    background:var(--card);border:1.5px solid var(--line);margin-bottom:8px;cursor:pointer;
  }
  .list-item:active{background:var(--paper)}
  .li-ico{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-weight:800;flex:0 0 auto}
  .li-ico.ok{background:var(--ok-bg);color:var(--ok)}
  .li-ico.bad{background:var(--danger-bg);color:var(--danger)}
  .li-main{flex:1;min-width:0}
  .li-title{font-family:var(--mono);font-weight:600;font-size:.92rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
  .li-sub{font-size:.76rem;color:var(--muted)}

  .dropzone{
    border:2.5px dashed #b7cdd6;border-radius:16px;padding:30px 18px;text-align:center;
    background:var(--paper);cursor:pointer;transition:.15s;
  }
  .dropzone.drag{border-color:var(--navy);background:#fff}
  .dropzone .big{font-size:2.1rem;margin-bottom:6px}
  .dropzone .t{font-family:var(--display);font-weight:800;color:var(--navy);letter-spacing:.04em}
  .dropzone .s{font-size:.82rem;color:var(--muted);margin-top:4px}

  .report-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-top:12px}
  .report-box{border-radius:14px;padding:14px;text-align:center;border:1px solid}
  .report-box .n{font-family:var(--display);font-size:1.7rem;font-weight:900;line-height:1}
  .report-box .t{font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;margin-top:4px}
  .rb-green{background:var(--ok-bg);border-color:var(--ok-line);color:var(--ok)}
  .rb-blue{background:var(--info-bg);border-color:var(--info-line);color:var(--info)}
  .rb-amber{background:var(--warn-bg);border-color:var(--warn-line);color:var(--warn)}
  .rb-red{background:var(--danger-bg);border-color:var(--danger-line);color:var(--danger)}

  .progress{height:9px;background:var(--paper-2);border-radius:99px;overflow:hidden;margin-top:12px}
  .progress > i{display:block;height:100%;width:0;background:linear-gradient(90deg,var(--navy),var(--amber));transition:width .2s}

  table.rejects{width:100%;border-collapse:collapse;font-size:.8rem;margin-top:8px}
  table.rejects th,table.rejects td{text-align:left;padding:7px 6px;border-bottom:1px solid var(--line)}
  table.rejects th{color:var(--muted);font-size:.72rem;text-transform:uppercase;letter-spacing:.5px}

  label.f{display:block;font-size:.75rem;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin:12px 0 6px;font-family:var(--display)}
  input.f,select.f{
    width:100%;padding:13px 14px;border-radius:12px;border:1.5px solid var(--line);
    background:#fff;outline:none;font-size:.95rem;
  }
  input.f:focus{border-color:var(--navy)}

  .tabbar{
    position:fixed;left:0;right:0;bottom:0;z-index:50;
    display:grid;grid-template-columns:repeat(5,1fr);
    background:rgba(255,255,255,.92);backdrop-filter:blur(14px);
    border-top:1px solid var(--line);
    padding-bottom:var(--safe-b);
  }
  .tabbar button{
    background:none;border:0;padding:9px 4px 8px;display:flex;flex-direction:column;
    align-items:center;gap:3px;color:var(--muted);font-size:.66rem;font-weight:700;cursor:pointer;
    font-family:var(--display);letter-spacing:.04em;text-transform:uppercase;
  }
  .tabbar button svg{width:22px;height:22px;stroke-width:1.9}
  .tabbar button.active{color:var(--navy)}
  .tabbar button.active svg{stroke-width:2.4}

  .back-btn{
    background:none;border:0;color:var(--navy);font-weight:800;padding:4px 0 12px;
    cursor:pointer;font-size:.9rem;display:flex;align-items:center;gap:6px;
    font-family:var(--display);
  }

  .modal{
    position:fixed;inset:0;z-index:100;background:rgba(16,42,54,.82);
    display:flex;align-items:center;justify-content:center;padding:16px;
  }
  .modal-box{background:#fff;border-radius:20px;width:100%;max-width:460px;overflow:hidden}
  .modal-head{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border-bottom:1px solid var(--line);font-weight:800;font-family:var(--display)}
  .modal-head button{background:var(--paper);border:0;width:32px;height:32px;border-radius:50%;font-size:16px;cursor:pointer}
  #reader{width:100%;min-height:280px;background:#000}
  #reader video, #reader img, #reader canvas{width:100%!important;max-height:62vh;object-fit:cover;display:block;background:#000}
  #scannerModal{z-index:280;padding:0;align-items:stretch}
  #scannerModal .modal-box{
    max-width:none;width:100%;height:100%;max-height:none;border-radius:0;
    display:flex;flex-direction:column;background:#111;color:#F4EFE4;
  }
  #scannerModal .modal-head{border-color:#2a2a2a;color:#F4EFE4}
  #scannerModal .modal-head button{background:#2a2a2a;color:#F4EFE4}
  #scannerModal #reader{flex:1;min-height:42vh;background:#000}
  .scan-actions{padding:12px 16px 20px;display:grid;gap:8px;background:#161616}
  .scan-actions .muted{color:#c9c3b8}
  #scanPick{display:grid;gap:8px;margin-bottom:4px}
  #scanPick button{
    text-align:left;justify-content:flex-start;background:#2a2a2a;color:#F4EFE4;border:1px solid #444;
    font-family:var(--mono);font-size:.82rem;letter-spacing:0;padding:10px 12px;
  }
  #scanCapture, #scanGallery{
    position:absolute;width:1px;height:1px;opacity:0;overflow:hidden;pointer-events:none;
  }
  #scanWork{position:fixed;left:-9999px;top:0;width:320px;height:320px;overflow:hidden}

  .empty{text-align:center;padding:34px 14px;color:var(--muted)}
  .empty .big{font-size:2.4rem;margin-bottom:8px}

  .install-card{
    display:flex;align-items:center;justify-content:space-between;gap:10px;
    background:#102a36;color:var(--on-dark);border-radius:14px;padding:12px 14px;margin-bottom:14px;
  }
  .install-card p{margin:0;font-size:.85rem}
  .install-card b{font-family:var(--display)}
  .install-card .install-actions{display:flex;gap:8px;align-items:center;flex:0 0 auto}
  #installModal .modal-box{max-width:420px;background:var(--card)}
  .install-steps{margin:12px 0 0;padding-left:18px;color:var(--ink);font-size:.92rem;line-height:1.55}
  .install-steps li{margin:0 0 8px}

  .alert-empty{
    background:#fff7ed;border:1.5px solid var(--warn-line);border-radius:14px;padding:12px 14px;margin-bottom:14px;
    color:var(--warn);font-size:.88rem;
  }
  .samples{margin-top:10px;font-size:.82rem;color:var(--muted)}
  .samples button{
    font-family:var(--mono);font-size:.78rem;margin:4px 6px 0 0;padding:6px 10px;border-radius:999px;
    border:1px solid var(--line);background:#fff;color:var(--navy);cursor:pointer;font-weight:600;
  }
  .suggest-list{display:grid;gap:8px;margin-top:12px}

  .month-rail{
    display:flex;gap:8px;overflow-x:auto;padding:2px 2px 10px;
    -webkit-overflow-scrolling:touch;scrollbar-width:thin;
  }
  .month-chip{
    flex:0 0 auto;border:1.5px solid var(--line);background:#fff;color:var(--navy);
    border-radius:999px;padding:8px 12px;font-family:var(--display);font-size:.74rem;
    font-weight:800;letter-spacing:.03em;cursor:pointer;white-space:nowrap;
  }
  .month-chip.on{background:var(--navy-2);color:#fff;border-color:var(--navy-2)}
  .month-chip .n{opacity:.72;margin-left:5px;font-family:var(--mono);font-weight:600}

  .month-block{margin-bottom:14px}
  .month-head{
    display:flex;justify-content:space-between;align-items:baseline;gap:10px;
    font-family:var(--display);font-weight:800;letter-spacing:.04em;text-transform:capitalize;
    padding:6px 4px 8px;position:sticky;top:56px;z-index:6;
    background:linear-gradient(180deg,#f4f7f9 70%, rgba(244,247,249,0));
  }
  .month-head .n{color:var(--muted);font-size:.76rem;text-transform:uppercase;letter-spacing:.08em}

  .note-line{
    display:grid;grid-template-columns:1fr auto;gap:4px 12px;align-items:center;
    padding:12px 14px;border-radius:13px;background:var(--card);
    border:1.5px solid var(--line);margin-bottom:8px;cursor:pointer;text-align:left;width:100%;
    font:inherit;color:inherit;
  }
  .note-line:active{background:var(--paper)}
  .note-line .ref{font-family:var(--mono);font-weight:600;font-size:.9rem}
  .note-line .who{font-size:.8rem;color:var(--muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;grid-column:1 / -1}
  .note-line .amt{font-family:var(--mono);font-weight:700;font-size:.86rem;color:var(--navy);text-align:right}
  .note-line .when{font-size:.72rem;color:var(--muted);text-align:right;grid-column:2}
  @media (min-width:760px){
    .note-line{grid-template-columns:1.3fr 1.5fr .9fr .7fr;gap:8px 14px}
    .note-line .who{grid-column:auto;white-space:nowrap}
    .note-line .when{grid-column:auto}
  }
  .notes-more{margin:10px 0 22px}

  @media (max-width:360px){
    .actions{grid-template-columns:1fr}
    .tabbar button{font-size:.58rem;padding:8px 2px 7px}
    .stats{grid-template-columns:1fr}
    .report-grid{grid-template-columns:1fr}
  }
  @media (min-width:760px){
    main{padding-top:28px}
    .stats{grid-template-columns:repeat(4,1fr)}
    .widgets{grid-template-columns:repeat(3,1fr)}
  }
  @media print{
    .tabbar,.topbar,.btn,.back-btn{display:none !important}
    body{background:#fff}
    main{padding:0}
  }
  @media (prefers-reduced-motion: reduce){
    .view.active,.btn,.stamp,.skel,.boot-spin,.busy-spin{animation:none !important;transition:none !important;transform:none !important}
  }

  /* ---------- App chrome ---------- */
  .boot{
    position:fixed;inset:0;z-index:300;background:#102a36;color:var(--on-dark);
    display:flex;flex-direction:column;align-items:center;justify-content:center;gap:14px;
    padding:24px 22px calc(24px + var(--safe-b));
    font-family:system-ui,-apple-system,'Segoe UI',sans-serif;
    transition:opacity var(--dur-2) var(--ease), visibility var(--dur-2);
  }
  .boot.off{opacity:0;visibility:hidden;pointer-events:none}
  .boot-mark{width:148px;height:148px;border-radius:28px;background:#fff;display:grid;place-items:center;box-shadow:0 12px 32px rgba(0,0,0,.22);padding:10px}
  .boot-mark img{width:100%;height:100%;object-fit:contain}
  .boot-name{font-family:var(--display);font-weight:800;letter-spacing:.12em;text-transform:uppercase;font-size:.82rem}
  .boot-step{margin:2px 0 0;font-size:.95rem;font-weight:700;text-align:center;max-width:300px;line-height:1.35}
  .boot-detail{margin:0;font-size:.78rem;opacity:.62;text-align:center;max-width:300px;min-height:1.2em;line-height:1.4}
  .boot-progress{width:min(280px,78vw);margin-top:4px}
  .boot-track{height:7px;border-radius:99px;background:rgba(244,239,228,.16);overflow:hidden}
  .boot-track > i{display:block;height:100%;width:4%;background:var(--on-dark);border-radius:99px;transition:width .4s ease}
  .boot-meta{display:flex;justify-content:space-between;margin-top:8px;font-size:.72rem;opacity:.55;font-variant-numeric:tabular-nums}
  .boot-spin,.busy-spin{
    width:28px;height:28px;border-radius:50%;
    border:3px solid rgba(232,243,247,.2);border-top-color:var(--on-dark);
    animation:spin .7s linear infinite;
  }
  @keyframes spin{to{transform:rotate(360deg)}}
  .busy{
    position:fixed;inset:0;z-index:220;background:rgba(16,42,54,.28);
    display:flex;align-items:center;justify-content:center;
    backdrop-filter:blur(6px);
  }
  .busy-card{
    background:var(--card);border-radius:18px;padding:22px 26px;text-align:center;
    box-shadow:var(--shadow);min-width:160px;
  }
  .busy-card p{margin:10px 0 0;font-weight:700;font-family:var(--display)}

  .hello{margin:2px 2px 14px}
  .hello .hi{font-size:.82rem;color:var(--muted);margin:0}
  .hello h1{margin:2px 0 0;font-family:var(--display);font-size:1.7rem;letter-spacing:-.03em;line-height:1.1}

  .quick{
    display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin:12px 0 4px;
  }
  .quick button{
    background:var(--paper);border:1.5px solid var(--line);border-radius:16px;
    padding:12px 6px 10px;display:flex;flex-direction:column;align-items:center;gap:6px;
    font-family:var(--display);font-size:.68rem;font-weight:800;color:var(--navy);cursor:pointer;
    min-height:76px;
  }
  .quick button .qi{
    width:36px;height:36px;border-radius:12px;display:grid;place-items:center;background:linear-gradient(145deg,#1a5f7a,#159895);color:#fff;
  }
  .quick button .qi svg{width:18px;height:18px}
  .quick button:active{transform:scale(.97)}

  .widgets{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-top:12px}
  .widget{
    background:var(--card);border:1px solid var(--line);border-radius:16px;padding:14px 14px 12px;
    min-height:102px;display:flex;flex-direction:column;justify-content:space-between;
    text-align:left;font:inherit;color:inherit;cursor:pointer;width:100%;
    box-shadow:var(--shadow);
  }
  .widget:active{transform:scale(.98)}
  .widget .ico{width:28px;height:28px;border-radius:9px;display:grid;place-items:center;margin-bottom:8px}
  .widget .ico svg{width:16px;height:16px}
  .widget .v{font-family:var(--display);font-size:1.55rem;font-weight:800;line-height:1;letter-spacing:-.03em}
  .widget .l{font-size:.78rem;color:var(--ink);font-weight:800;margin-top:6px}
  .widget .h{font-size:.68rem;color:var(--muted);font-weight:500;margin-top:3px;line-height:1.3}
  .w-navy .ico{background:linear-gradient(145deg,#1a5f7a,#159895);color:#fff}
  .w-ok .ico{background:var(--ok-bg);color:var(--ok)}
  .w-warn .ico{background:var(--warn-bg);color:var(--warn)}
  .w-bad .ico{background:var(--danger-bg);color:var(--danger)}
  .w-open .ico{background:var(--info-bg);color:var(--info)}
  .w-notice .ico{background:var(--warn-bg);color:var(--warn)}

  .chart-card{padding-bottom:16px}
  .chart-wrap{display:grid;grid-template-columns:120px 1fr;gap:12px;align-items:center;margin-top:10px}
  .legend{display:grid;gap:7px;font-size:.78rem}
  .legend i{display:inline-block;width:8px;height:8px;border-radius:50%;margin-right:6px}
  .bars{display:flex;align-items:flex-end;gap:6px;height:110px;margin-top:12px;padding-bottom:22px}
  .bars .bar{
    flex:1;background:linear-gradient(180deg,var(--navy),var(--navy-2));border-radius:8px 8px 4px 4px;
    min-height:4px;position:relative;
  }
  .bars .lab{position:absolute;left:50%;bottom:-18px;transform:translateX(-50%);font-size:.62rem;color:var(--muted);white-space:nowrap}

  .skel{
    background:linear-gradient(90deg,var(--paper-2) 25%, #f4f7f9 37%, var(--paper-2) 63%);
    background-size:400% 100%;animation:shimmer 1.2s ease infinite;border-radius:10px;
  }
  @keyframes shimmer{0%{background-position:100% 0}100%{background-position:0 0}}
  .skel-line{height:12px;margin:8px 0}
  .skel-row{height:58px;margin-bottom:8px;border-radius:13px}
  .skel-widget{height:92px;border-radius:16px}

  .list-item{position:relative;padding-right:28px}
  .list-item::after{
    content:"";position:absolute;right:12px;top:50%;width:7px;height:7px;border-right:2px solid #9bb3bd;border-top:2px solid #9bb3bd;
    transform:translateY(-50%) rotate(45deg);
  }
  .note-line{position:relative;padding-right:28px}
  .note-line::after{
    content:"";position:absolute;right:12px;top:50%;width:7px;height:7px;border-right:2px solid #9bb3bd;border-top:2px solid #9bb3bd;
    transform:translateY(-50%) rotate(45deg);
  }

  .tabbar{
    padding-top:6px;
    box-shadow:0 -8px 24px rgba(22,58,74,.06);
  }
  .tabbar button{position:relative;min-height:52px}
  .tabbar button.active::before{
    content:"";position:absolute;top:0;left:50%;transform:translateX(-50%);
    width:18px;height:3px;border-radius:0 0 4px 4px;background:var(--amber);
  }
  .tab-badge{
    position:absolute;top:4px;right:calc(50% - 18px);min-width:16px;height:16px;padding:0 4px;
    background:var(--amber);color:#fff;border-radius:99px;font-size:.58rem;font-weight:800;
    display:flex;align-items:center;justify-content:center;
  }

  .ios-row{
    display:flex;align-items:center;gap:12px;width:100%;padding:13px 4px;border:0;background:none;
    border-bottom:1px solid var(--line);cursor:pointer;text-align:left;color:inherit;font:inherit;
  }
  .ios-row:last-child{border-bottom:0}
  .ios-row .qi{width:32px;height:32px;border-radius:9px;display:grid;place-items:center;flex:0 0 auto;color:#fff}
  .ios-row .qi svg{width:16px;height:16px}
  .ios-row span{flex:1;font-weight:650}
  .ios-row small{color:var(--muted)}

  body.locked main, body.locked .tabbar, body.locked .topbar{visibility:hidden;pointer-events:none}
  .lock{
    position:fixed;inset:0;z-index:260;background:#102a36;color:var(--on-dark);
    display:flex;flex-direction:column;align-items:center;justify-content:center;
    padding:24px 20px calc(24px + var(--safe-b));
    overflow:auto;
  }
  .lock-mark{width:120px;height:120px;border-radius:24px;background:#fff;display:grid;place-items:center;margin-bottom:18px;padding:8px;box-shadow:0 8px 24px rgba(0,0,0,.18)}
  .lock-mark img{width:100%;height:100%;object-fit:contain}
  .lock h1{margin:0;font-family:var(--display);font-size:1.35rem;letter-spacing:.04em}
  .lock p{margin:8px 0 0;opacity:.75;font-size:.9rem;text-align:center;max-width:280px}
  .pin-dots{display:flex;gap:12px;margin:28px 0 18px}
  .pin-dots i{width:12px;height:12px;border-radius:50%;border:2px solid rgba(232,243,247,.45);background:transparent}
  .pin-dots i.on{background:var(--on-dark);border-color:var(--on-dark)}
  .pin-pad{display:grid;grid-template-columns:repeat(3,72px);gap:12px;justify-content:center}
  .pin-pad button{
    width:72px;height:72px;border-radius:50%;border:0;background:rgba(232,243,247,.1);color:var(--on-dark);
    font-family:var(--display);font-size:1.4rem;font-weight:800;cursor:pointer;
  }
  .pin-pad button:active{background:rgba(232,243,247,.22)}
  .pin-pad .ghost{background:transparent;font-size:.8rem;letter-spacing:.06em}
  .lock.shake .pin-dots{animation:shake .35s ease}
  @keyframes shake{25%{transform:translateX(-6px)}75%{transform:translateX(6px)}}
  .lock-hint{margin-top:18px;font-size:.78rem;opacity:.6;text-align:center}
  @media (max-height: 740px){
    .lock-mark{width:88px;height:88px;margin-bottom:8px}
    .lock h1{font-size:1.15rem}
    .pin-dots{margin:14px 0 10px}
    .pin-pad{grid-template-columns:repeat(3,58px);gap:8px}
    .pin-pad button{width:58px;height:58px;font-size:1.15rem}
  }

  .who-chip{
    display:flex;align-items:center;gap:8px;font-size:.78rem;color:var(--muted);margin-top:4px;
  }
  .who-chip b{color:var(--navy)}

  .insp-card{
    display:flex;align-items:center;gap:12px;padding:12px;border-radius:14px;
    background:var(--card);border:1.5px solid var(--line);margin-bottom:8px;text-align:left;width:100%;
    font:inherit;color:inherit;cursor:pointer;
  }
  .insp-ava{
    width:42px;height:42px;border-radius:14px;background:linear-gradient(145deg,#1a5f7a,#159895);color:#fff;
    display:grid;place-items:center;font-family:var(--display);font-weight:800;flex:0 0 auto;
  }
  .insp-ava.off{background:#9ca3af}
  .insp-main{flex:1;min-width:0}
  .insp-main b{display:block}
  .insp-main small{color:var(--muted)}

  body.role-inspector .admin-only{display:none !important}
  body.role-admin .inspector-only{display:none !important}
  body.role-admin .tabbar{grid-template-columns:repeat(5,1fr)}
  body.role-inspector .tabbar{grid-template-columns:repeat(4,1fr)}
</style>
</head>
<body>
<div id="boot" class="boot" role="status" aria-live="polite" aria-busy="true">
  <div class="boot-mark" aria-hidden="true">
    <img src="logo-dgipt.jpg" alt="" />
  </div>
  <div class="boot-name">DGIPT</div>
  <p class="boot-step" id="bootStep">Chargement de l'application…</p>
  <p class="boot-detail" id="bootDetail">Premier lancement : téléchargement et préparation de la base.</p>
  <div class="boot-spin" aria-hidden="true"></div>
  <div class="boot-progress">
    <div class="boot-track"><i id="bootBar"></i></div>
    <div class="boot-meta"><span id="bootPct">4 %</span><span id="bootTime">0 s</span></div>
  </div>
</div>
<div id="busy" class="busy hidden" role="alert">
  <div class="busy-card">
    <div class="busy-spin" style="margin:0 auto;border-top-color:var(--navy);border-color:#d7e6ec;border-top-color:var(--navy)"></div>
    <p id="busyText">Vérification…</p>
  </div>
</div>
<a class="skip" href="#refInput">Aller à la recherche</a>

<header class="topbar">
  <div class="brand">
    <img src="logo-dgipt.jpg" alt="DGIPT" />
    <div class="brand-txt">
      <span class="brand-kicker">DGIPT · Tshopo</span>
      <span class="brand-title" id="appTitle">Accueil</span>
    </div>
  </div>
  <div class="net off" id="netBadge"><span class="dot"></span><span id="netText">Hors ligne</span></div>
</header>

<main>

  <section id="view-home" class="view active">
    <div class="hello">
      <p class="hi" id="helloLine">Agent de terrain</p>
      <h1>Vérifier une note</h1>
      <div class="who-chip hidden" id="whoChip">Connecté : <b id="whoName">—</b></div>
    </div>

    <div id="installBanner" class="install-card hidden">
      <p><b>Installer l'application</b><br>Comme une app native, même hors connexion.</p>
      <div class="install-actions">
        <button class="btn btn-ghost" id="btnInstallHome" type="button" style="padding:8px 12px;background:#e8f3f7;color:var(--navy);flex:0 0 auto">Installer</button>
        <button class="btn" id="btnInstallLater" type="button" aria-label="Plus tard" style="padding:8px 10px;background:transparent;color:#e8f3f7;min-width:0">Plus tard</button>
      </div>
    </div>

    <div id="httpsWarn" class="alert-empty hidden">
      <b>Le hors-ligne et l’installation exigent HTTPS.</b>
      En HTTP, le téléphone ne peut ni installer l’app ni recharger les notes sans réseau.
      <a class="btn btn-primary btn-block" id="httpsOpen" href="#" style="margin-top:12px">Ouvrir en HTTPS</a>
    </div>

    <div id="emptyWarn" class="alert-empty hidden">
      <b>Aucune note sur cet appareil.</b>
      Le téléphone et le PC ne partagent pas automatiquement IndexedDB :
      les notes passent par le serveur WAMP.
      <button type="button" class="btn btn-primary btn-block" id="btnPullHome" style="margin-top:12px">Récupérer les notes du serveur</button>
    </div>

    <div class="dossier search-card">
      <label class="search-label" for="refInput">Numéro de référence</label>
      <div class="search-row">
        <input id="refInput" type="search" enterkeyhint="search" inputmode="text" autocomplete="off"
               autocapitalize="characters" spellcheck="false" placeholder="NP-2024-000123" />
        <button class="clear-btn" id="clearBtn" type="button" aria-label="Effacer">✕</button>
      </div>
      <div class="actions">
        <button class="btn btn-primary" id="btnSearch" type="button">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
          Vérifier
        </button>
        <button class="btn btn-ghost" id="btnScanQR" type="button">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3h-3zM21 14v3M14 21h7"/></svg>
          QR Code
        </button>
        <button class="btn btn-ghost" id="btnScanBar" type="button">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 5v14M7 5v14M11 5v10M15 5v14M19 5v14"/></svg>
          Code-barres
        </button>
      </div>
      <div class="quick">
        <button type="button" data-view="notes">
          <span class="qi"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg></span>
          Liste
        </button>
        <button type="button" class="admin-only" data-view="import">
          <span class="qi"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0-4-4m4 4 4-4"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg></span>
          Import
        </button>
        <button type="button" data-view="history">
          <span class="qi"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
          Activité
        </button>
        <button type="button" id="btnScanQuick" data-scan="qr">
          <span class="qi"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h7v7"/></svg></span>
          Scanner
        </button>
      </div>
    </div>

    <div class="kicker">Tableau de bord</div>
    <div id="dashSkeleton">
      <div class="widgets">
        <div class="skel skel-widget"></div><div class="skel skel-widget"></div>
        <div class="skel skel-widget"></div><div class="skel skel-widget"></div>
      </div>
    </div>
    <div id="dashBoard" class="hidden">
      <div class="widgets">
        <button type="button" class="widget w-navy" data-status="all">
          <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg></div>
          <div class="v" id="statNotes">0</div>
          <div class="l">Toutes les notes</div>
          <div class="h">Base locale de cet appareil</div>
        </button>
        <button type="button" class="widget w-ok" data-status="paid">
          <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="m5 12 5 5L20 7"/></svg></div>
          <div class="v" id="statPaid">0</div>
          <div class="l">Payées</div>
          <div class="h">Soldées intégralement</div>
        </button>
        <button type="button" class="widget w-open" data-status="open">
          <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M8 12h8"/></svg></div>
          <div class="v" id="statOpen">0</div>
          <div class="l">Ouvertes</div>
          <div class="h">Émises, en attente de paiement</div>
        </button>
        <button type="button" class="widget w-warn" data-status="partial">
          <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg></div>
          <div class="v" id="statPartial">0</div>
          <div class="l">Partielles</div>
          <div class="h">Versement incomplet</div>
        </button>
        <button type="button" class="widget w-bad" data-status="unpaid">
          <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="m9 9 6 6M15 9l-6 6"/></svg></div>
          <div class="v" id="statUnpaid">0</div>
          <div class="l">Impayées</div>
          <div class="h">Reste à recouvrer</div>
        </button>
        <button type="button" class="widget w-notice" data-status="notice">
          <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5h2v8h-2zM11 16h2v2h-2z"/></svg></div>
          <div class="v" id="statNotice">0</div>
          <div class="l">Mises en demeure</div>
          <div class="h">Recouvrement / contrainte</div>
        </button>
      </div>

      <div class="dossier chart-card" style="margin-top:12px">
        <h2>Statuts</h2>
        <p class="muted" id="metaLine">Chargement…</p>
        <div class="chart-wrap">
          <div id="statusRing"></div>
          <div class="legend" id="statusLegend"></div>
        </div>
      </div>

      <div class="dossier chart-card">
        <h2>Volume par mois</h2>
        <p class="muted">Répartition des notes importées</p>
        <div class="bars" id="monthBars"></div>
      </div>
    </div>

    <div class="samples hidden" id="refSamples"></div>
    <button class="btn btn-ghost btn-block hidden" type="button" data-view="notes" id="btnOpenNotes" style="margin-top:12px">Voir toutes les notes</button>

    <div class="kicker">Contrôles récents</div>
    <div id="homeHistorySkel" class="hidden">
      <div class="skel skel-row"></div><div class="skel skel-row"></div><div class="skel skel-row"></div>
    </div>
    <div id="homeHistory"></div>
  </section>

  <section id="view-result" class="view">
    <button class="back-btn" type="button" id="resultBack" data-view="home">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
      <span id="resultBackLabel">Nouveau contrôle</span>
    </button>
    <div id="resultBanner" aria-live="assertive"></div>
    <div id="resultBody"></div>
  </section>

  <section id="view-import" class="view">
    <div class="dossier">
      <h2>Importer un fichier Excel</h2>
      <p class="muted">Le fichier doit contenir une colonne <b>Reference Number</b> (ou « Numéro de référence »). Les nouvelles notes sont ajoutées, les existantes mises à jour, les doublons ignorés.</p>

      <div class="dropzone" id="dropzone" style="margin-top:14px" role="button" tabindex="0">
        <div class="big">📄</div>
        <div class="t">Choisir un fichier .xlsx / .xls / .csv</div>
        <div class="s">ou déposez le fichier ici</div>
        <input type="file" id="fileInput" accept=".xlsx,.xls,.csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv" class="hidden" />
      </div>

      <div id="importProgressWrap" class="hidden">
        <div class="progress"><i id="importBar"></i></div>
        <p class="muted" id="importStatus" style="margin-top:8px;text-align:center"></p>
      </div>

      <div id="importReport"></div>
    </div>

    <div class="dossier">
      <h2>Dernière importation</h2>
      <p class="muted" id="lastImportLine">Aucune importation enregistrée.</p>
    </div>
  </section>

  <section id="view-notes" class="view">
    <div class="dossier">
      <h2>Notes importées</h2>
      <p class="muted" id="notesCountLine">Chargement…</p>
      <label class="f" for="notesQuery">Recherche</label>
      <input class="f" id="notesQuery" type="search" placeholder="Référence, nom, téléphone, statut, ressort…" autocomplete="off" />
      <div class="month-rail" id="statusRail" style="margin-top:10px"></div>
      <label class="f" for="notesDateField">Classer par mois selon</label>
      <select class="f" id="notesDateField">
        <option value="auto">Date d'émission (sinon paiement / import)</option>
        <option value="emission">Date d'émission</option>
        <option value="paiement">Date de paiement</option>
        <option value="import">Date d'importation</option>
      </select>
    </div>
    <div class="month-rail" id="monthRail"></div>
    <div id="notesSkeleton" class="hidden">
      <div class="skel skel-row"></div><div class="skel skel-row"></div>
      <div class="skel skel-row"></div><div class="skel skel-row"></div>
      <div class="skel skel-row"></div>
    </div>
    <div id="notesList"></div>
  </section>

  <section id="view-history" class="view">
    <div class="dossier">
      <div style="display:flex;align-items:center;justify-content:space-between;gap:10px">
        <h2 style="margin:0">Historique des contrôles</h2>
        <button class="btn btn-danger" id="clearHistoryBtn" type="button" style="padding:8px 12px;font-size:.78rem">Vider</button>
      </div>
    </div>
    <div id="historyList"></div>
  </section>

  <section id="view-settings" class="view">
    <div class="dossier">
      <h2>Session</h2>
      <p class="muted" id="sessionLine">Non connecté</p>
      <div style="display:grid;gap:10px;margin-top:12px">
        <button class="btn btn-ghost btn-block" id="btnChangePin" type="button">Changer mon code PIN</button>
        <button class="btn btn-danger btn-block" id="btnLogout" type="button">Se déconnecter</button>
      </div>
    </div>

    <div class="dossier admin-only">
      <h2>Synchronisation serveur</h2>
      <p class="muted">Les notes importées sur le PC sont envoyées à MySQL, puis récupérées par les téléphones sur le même réseau (<code>http://IP-DU-PC/controle/</code>).</p>
      <label class="f" for="serverUrl">URL du serveur (api.php)</label>
      <input class="f" id="serverUrl" type="url" placeholder="http://192.168.x.x/controle/api.php" />
      <div style="display:grid;grid-template-columns:1fr;gap:10px;margin-top:14px">
        <button class="btn btn-ok btn-block" id="btnSync" type="button">Télécharger les notes du serveur</button>
        <button class="btn btn-ghost btn-block" id="btnPush" type="button">Envoyer la base locale vers le serveur</button>
        <button class="btn btn-ghost btn-block" id="btnSaveUrl" type="button">Enregistrer l'URL</button>
      </div>
      <p class="muted" id="syncStatus" style="margin-top:10px"></p>
    </div>

    <div class="dossier admin-only">
      <h2>Sauvegarde des données</h2>
      <div style="display:grid;gap:10px;margin-top:10px">
        <button class="btn btn-ghost btn-block" id="btnExport" type="button">Exporter la base (JSON)</button>
        <button class="btn btn-ghost btn-block" id="btnImportJson" type="button">Restaurer une sauvegarde</button>
        <input type="file" id="jsonInput" accept=".json,application/json" class="hidden" />
      </div>
    </div>

    <div class="dossier">
      <h2>Application</h2>
      <div style="display:grid;gap:10px;margin-top:10px">
        <button class="btn btn-ghost btn-block" id="btnInstall" type="button">Installer l'application</button>
        <button class="btn btn-primary btn-block" id="btnOfflinePack" type="button">Enregistrer une copie hors-ligne</button>
        <button class="btn btn-danger btn-block admin-only" id="btnWipe" type="button">Vider la base locale</button>
      </div>
      <p class="muted" style="margin-top:12px" id="aboutLine">Version 1.2 — Stockage local IndexedDB.</p>
      <p class="muted" style="margin-top:8px" id="offlineLine">Vérification du mode hors ligne…</p>
      <p class="muted admin-only" style="margin-top:10px"><a href="admin.php">Ouvrir la console d’administration web</a> (gestion hors PWA).</p>
    </div>
  </section>

  <section id="view-admin" class="view">
    <div class="dossier">
      <h2>Inspecteurs de terrain</h2>
      <p class="muted">Chaque inspecteur se connecte avec son propre code PIN. L’administrateur gère les accès et l’import des notes.</p>
      <button class="btn btn-primary btn-block" id="btnAddInsp" type="button" style="margin-top:12px">Ajouter un inspecteur</button>
    </div>
    <div id="inspList"></div>
  </section>

</main>

<nav class="tabbar" aria-label="Navigation principale">
  <button data-view="home" class="active" type="button">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/></svg>
    Accueil
  </button>
  <button data-view="notes" type="button">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M8 6h13M8 12h13M8 18h13"/><path d="M3 6h.01M3 12h.01M3 18h.01"/></svg>
    Notes
    <span class="tab-badge hidden" id="tabNotesBadge">0</span>
  </button>
  <button data-view="import" data-role="admin" class="admin-only" type="button">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12m0 0-4-4m4 4 4-4"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
    Importer
  </button>
  <button data-view="admin" data-role="admin" class="admin-only" type="button">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    Équipe
  </button>
  <button data-view="history" data-role="inspector" class="inspector-only" type="button">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
    Historique
    <span class="tab-badge hidden" id="tabHistBadge">0</span>
  </button>
  <button data-view="settings" type="button">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
    Réglages
  </button>
</nav>

<div id="lockScreen" class="lock hidden" role="dialog" aria-modal="true" aria-labelledby="lockTitle">
  <div class="lock-mark" aria-hidden="true">
    <img src="logo-dgipt.jpg" alt="DGIPT" />
  </div>
  <h1 id="lockTitle">Code PIN</h1>
  <p id="lockSub">Saisissez votre code pour ouvrir l'application</p>
  <div class="pin-dots" id="pinDots" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
  <div class="pin-pad" id="pinPad">
    <button type="button" data-k="1">1</button>
    <button type="button" data-k="2">2</button>
    <button type="button" data-k="3">3</button>
    <button type="button" data-k="4">4</button>
    <button type="button" data-k="5">5</button>
    <button type="button" data-k="6">6</button>
    <button type="button" data-k="7">7</button>
    <button type="button" data-k="8">8</button>
    <button type="button" data-k="9">9</button>
    <button type="button" class="ghost" data-k="del">Effacer</button>
    <button type="button" data-k="0">0</button>
    <button type="button" class="ghost" data-k="ok">OK</button>
  </div>
  <p class="lock-hint" id="lockHint"></p>
  <button type="button" id="pinCancel" class="hidden" style="margin-top:14px;background:none;border:0;color:rgba(244,239,228,.7);font-weight:700;cursor:pointer">Annuler</button>
</div>

<div class="modal hidden" id="inspModal" role="dialog" aria-modal="true">
  <div class="modal-box">
    <div class="modal-head">
      <span id="inspModalTitle">Inspecteur</span>
      <button id="inspModalClose" type="button" aria-label="Fermer">✕</button>
    </div>
    <div style="padding:16px">
      <input type="hidden" id="inspId" />
      <label class="f" for="inspName">Nom complet</label>
      <input class="f" id="inspName" type="text" autocomplete="name" />
      <label class="f" for="inspMat">Matricule</label>
      <input class="f" id="inspMat" type="text" autocomplete="off" />
      <label class="f" for="inspRessort">Ressort / zone</label>
      <input class="f" id="inspRessort" type="text" />
      <label class="f" for="inspRole">Rôle</label>
      <select class="f" id="inspRole">
        <option value="inspector">Inspecteur de terrain</option>
        <option value="admin">Administrateur</option>
      </select>
      <label class="f" for="inspPin">Code PIN (4 à 6 chiffres)</label>
      <input class="f" id="inspPin" type="password" inputmode="numeric" maxlength="6" placeholder="Laisser vide pour ne pas changer" />
      <label class="f" style="display:flex;align-items:center;gap:8px;text-transform:none;letter-spacing:0">
        <input type="checkbox" id="inspActive" checked /> Compte actif
      </label>
      <button class="btn btn-primary btn-block" id="inspSave" type="button" style="margin-top:16px">Enregistrer</button>
      <button class="btn btn-danger btn-block hidden" id="inspDelete" type="button" style="margin-top:8px">Désactiver / retirer</button>
    </div>
  </div>
</div>

<div class="modal hidden" id="scannerModal" role="dialog" aria-modal="true" aria-labelledby="scannerTitle">
  <div class="modal-box">
    <div class="modal-head">
      <span id="scannerTitle">Scanner</span>
      <button id="scanClose" type="button" aria-label="Fermer">✕</button>
    </div>
    <div id="reader"></div>
    <div class="scan-actions">
      <p class="muted" id="scanHint">Placez le code dans le cadre, ou photographiez-le.</p>
      <div id="scanPick" class="hidden"></div>
      <label class="btn btn-primary btn-block" for="scanCapture">Photographier le code</label>
      <button class="btn btn-ghost btn-block" id="btnScanLive" type="button">Caméra en direct</button>
      <label class="btn btn-ghost btn-block" for="scanGallery">Choisir une image</label>
    </div>
    <input id="scanCapture" type="file" accept="image/*" capture="environment" />
    <input id="scanGallery" type="file" accept="image/*" />
    <div id="scanWork" aria-hidden="true"></div>
  </div>
</div>

<div class="modal hidden" id="installModal" role="dialog" aria-modal="true" aria-labelledby="installTitle">
  <div class="modal-box">
    <div class="modal-head">
      <span id="installTitle">Installer l'application</span>
      <button id="installModalClose" type="button" aria-label="Fermer">✕</button>
    </div>
    <div style="padding:16px 18px 20px">
      <p class="muted" id="installLead">Ajoutez Notes NP à l’écran d’accueil pour l’ouvrir comme une application.</p>
      <ol class="install-steps" id="installSteps"></ol>
      <button class="btn btn-primary btn-block" id="btnInstallNative" type="button" style="margin-top:16px">Installer maintenant</button>
    </div>
  </div>
</div>

<script>
'use strict';
if (window.setBootProgress) setBootProgress(8, 'Démarrage de l’application…', 'Lecture du programme');

const $  = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

function loadVendorScript(src){
  return new Promise((resolve, reject) => {
    const s = document.createElement('script');
    s.src = src;
    s.async = true;
    s.onload = resolve;
    s.onerror = () => reject(new Error('Échec de chargement : ' + src));
    document.head.appendChild(s);
  });
}
function ensureVendors(){
  if (window.__npcVendors) return window.__npcVendors;
  window.__npcVendors = Promise.all([
    (typeof XLSX === 'undefined' ? loadVendorScript('vendor/xlsx.full.min.js') : Promise.resolve()),
    (typeof Html5Qrcode === 'undefined' ? loadVendorScript('vendor/html5-qrcode.min.js') : Promise.resolve())
  ]).catch(err => { console.warn(err); window.__npcVendors = null; throw err; });
  return window.__npcVendors;
}
function hideBoot(){
  const b = $('#boot');
  if (window.__bootTick){ clearInterval(window.__bootTick); window.__bootTick = null; }
  if (!b || b.classList.contains('off')) return;
  b.setAttribute('aria-busy', 'false');
  b.classList.add('off');
}

function norm(s){
  return String(s ?? '')
    .replace(/([a-z])([A-Z])/g, '$1 $2')
    .replace(/([A-Z]+)([A-Z][a-z])/g, '$1 $2')
    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    .toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim();
}
/** Clé de recherche : majuscules, sans espaces ni tirets. */
function compactRef(s){
  return String(s ?? '')
    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    .toUpperCase()
    .replace(/[^A-Z0-9]/g, '');
}
function normRef(s){ return compactRef(s) || String(s ?? '').trim().toUpperCase().replace(/\s+/g, ''); }
function numericLoose(s){
  const c = compactRef(s);
  if (!c || !/^\d+$/.test(c)) return '';
  return c.replace(/^0+/, '') || '0';
}

function cellToStr(v){
  if (v === null || v === undefined) return '';
  if (v instanceof Date) return v.toLocaleDateString('fr-FR');
  if (typeof v === 'number'){
    if (Number.isFinite(v) && Math.abs(v) >= 1e11){
      return v.toLocaleString('en-US', { useGrouping: false, maximumFractionDigits: 0 });
    }
    if (Number.isInteger(v) && Math.abs(v) < 1e15) return String(v);
    return String(v);
  }
  let s = String(v).replace(/\u00A0/g, ' ').trim();
  if (/^\d+[.,]?\d*e[+\-]?\d+$/i.test(s.replace(/\s/g, ''))){
    const n = Number(s.replace(/\s/g, '').replace(',', '.'));
    if (Number.isFinite(n)) return n.toLocaleString('en-US', { useGrouping: false, maximumFractionDigits: 0 });
  }
  return s;
}
function toDateStr(v){
  if (v === null || v === undefined || v === '') return '';
  if (v instanceof Date) return isNaN(v) ? '' : v.toLocaleDateString('fr-FR');
  if (typeof v === 'number'){
    const d = new Date(Math.round((v - 25569) * 86400 * 1000));
    return isNaN(d) ? String(v) : d.toLocaleDateString('fr-FR');
  }
  return String(v).trim();
}
const FR_MONTHS = ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
function parseAnyDate(v){
  if (v === null || v === undefined || v === '') return null;
  if (v instanceof Date) return isNaN(v.getTime()) ? null : v;
  if (typeof v === 'number'){
    if (v > 1e11){ const d = new Date(v); return isNaN(d.getTime()) ? null : d; }
    if (v > 20000 && v < 120000){
      const d = new Date(Math.round((v - 25569) * 86400 * 1000));
      return isNaN(d.getTime()) ? null : d;
    }
  }
  const s = String(v).trim();
  let m = s.match(/^(\d{4})-(\d{1,2})-(\d{1,2})/);
  if (m) return new Date(+m[1], +m[2] - 1, +m[3]);
  m = s.match(/^(\d{1,2})[\/.\-](\d{1,2})[\/.\-](\d{2,4})/);
  if (m){
    let day = +m[1], month = +m[2], year = +m[3];
    if (year < 100) year += year >= 70 ? 1900 : 2000;
    if (month > 12 && day <= 12){ const t = day; day = month; month = t; }
    const d = new Date(year, month - 1, day);
    return isNaN(d.getTime()) ? null : d;
  }
  const t = Date.parse(s);
  return isNaN(t) ? null : new Date(t);
}
function toNumber(v){
  if (v === null || v === undefined || v === '') return null;
  if (typeof v === 'number') return isFinite(v) ? v : null;
  if (v instanceof Date) return null;
  let s = String(v).replace(/[^\d.,-]/g, '');
  if (!s || s === '-') return null;
  const lastC = s.lastIndexOf(','), lastD = s.lastIndexOf('.');
  if (lastC > -1 && lastD > -1){
    if (lastC > lastD) s = s.replace(/\./g, '').replace(/,/g, '.');
    else s = s.replace(/,/g, '');
  } else if (lastC > -1){
    const dec = s.length - lastC - 1;
    s = dec === 3 && s.length > 4 ? s.replace(/,/g, '') : s.replace(/,/g, '.');
  } else if (lastD > -1){
    const dec = s.length - lastD - 1;
    if (dec === 3 && s.length > 4) s = s.replace(/\./g, '');
  }
  const n = parseFloat(s);
  return isNaN(n) ? null : n;
}
function fmtMoney(n, devise){
  if (n === null || n === undefined || n === '') return '';
  const num = typeof n === 'number' ? n : toNumber(n);
  if (num === null) return String(n);
  const txt = num.toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
  return devise ? txt + ' ' + devise : txt;
}
function fmtDateTime(ts){
  const d = new Date(ts);
  return d.toLocaleDateString('fr-FR') + ' à ' + d.toLocaleTimeString('fr-FR', { hour:'2-digit', minute:'2-digit' });
}
function toast(msg){
  const el = document.createElement('div');
  el.textContent = msg;
  el.style.cssText = 'position:fixed;left:50%;bottom:96px;transform:translateX(-50%);background:#163a4a;color:#e8f3f7;'+
    'padding:12px 18px;border-radius:12px;font-size:.88rem;font-weight:600;z-index:200;box-shadow:0 10px 30px rgba(14,34,64,.35);'+
    'max-width:88vw;text-align:center;opacity:0;transition:opacity .2s;font-family:IBM Plex Sans,sans-serif';
  document.body.appendChild(el);
  requestAnimationFrame(() => el.style.opacity = '1');
  setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 250); }, 2600);
}

const DB_NAME = 'npc_db', DB_VER = 2;
let db = null;

function openDB(){
  return new Promise((resolve, reject) => {
    const req = indexedDB.open(DB_NAME, DB_VER);
    req.onupgradeneeded = e => {
      const d = e.target.result;
      if (!d.objectStoreNames.contains('notes')){
        const s = d.createObjectStore('notes', { keyPath: 'ref' });
        s.createIndex('nom', 'std.nom', { unique: false });
        s.createIndex('updatedAt', 'updatedAt', { unique: false });
      }
      if (!d.objectStoreNames.contains('meta'))    d.createObjectStore('meta', { keyPath: 'k' });
      if (!d.objectStoreNames.contains('history')) d.createObjectStore('history', { keyPath: 'id', autoIncrement: true });
      if (!d.objectStoreNames.contains('inspectors')){
        const s = d.createObjectStore('inspectors', { keyPath: 'id' });
        s.createIndex('matricule', 'matricule', { unique: false });
        s.createIndex('role', 'role', { unique: false });
      }
    };
    req.onsuccess = () => { db = req.result; resolve(db); };
    req.onerror   = () => reject(req.error);
  });
}
const store = (name, mode = 'readonly') => db.transaction(name, mode).objectStore(name);
const reqP  = r => new Promise((res, rej) => { r.onsuccess = () => res(r.result); r.onerror = () => rej(r.error); });

const IDB = {
  getNote:  ref => reqP(store('notes').get(ref)),
  count:    ()  => reqP(store('notes').count()),
  allKeys:  ()  => reqP(store('notes').getAllKeys()),
  getAll:   ()  => reqP(store('notes').getAll()),
  getNotesByKeys(keys){
    if (!keys.length) return Promise.resolve([]);
    return new Promise((resolve, reject) => {
      const t = db.transaction('notes', 'readonly');
      const s = t.objectStore('notes');
      const out = [];
      keys.forEach(k => {
        const r = s.get(k);
        r.onsuccess = () => { if (r.result) out.push(r.result); };
      });
      t.oncomplete = () => resolve(out);
      t.onerror = t.onabort = () => reject(t.error);
    });
  },
  clearNotes: () => reqP(store('notes', 'readwrite').clear()),

  putNotes(records){
    return new Promise((resolve, reject) => {
      const CHUNK = 1500;
      let i = 0;
      const step = () => {
        if (i >= records.length) return resolve(records.length);
        const slice = records.slice(i, i + CHUNK);
        const t = db.transaction('notes', 'readwrite');
        const s = t.objectStore('notes');
        slice.forEach(r => s.put(r));
        t.oncomplete = () => { i += CHUNK; step(); };
        t.onerror = t.onabort = () => reject(t.error);
      };
      step();
    });
  },

  getMeta: k => reqP(store('meta').get(k)).then(r => r ? r.v : undefined),
  setMeta: (k, v) => reqP(store('meta', 'readwrite').put({ k, v })),

  addHistory(h) {
    return reqP(store('history', 'readwrite').add(h));
  },
  getHistory(limit = 100){
    return reqP(store('history').getAll()).then(list => list.reverse().slice(0, limit));
  },
  countHistory: () => reqP(store('history').count()),
  clearHistory(){ return reqP(store('history', 'readwrite').clear()); },

  getInspector: id => reqP(store('inspectors').get(id)),
  allInspectors: () => reqP(store('inspectors').getAll()),
  putInspector: rec => reqP(store('inspectors', 'readwrite').put(rec)),
  deleteInspector: id => reqP(store('inspectors', 'readwrite').delete(id)),
  countInspectors: () => reqP(store('inspectors').count())
};

const FIELD_DEFS = [
  ['ref',             'Numéro de référence',                ['reference number','numero de reference','num reference','n reference','no reference','reference no','ref number','reference','numero reference','n de reference','no de reference','code reference','code de reference','num ref','no ref','n ref','ref','refs']],
  ['note',            'Numéro de note',                     ['numero de note','note number','numero note','no note','num note','note no','note']],
  ['nom',             'Nom du contribuable',                ['nom du contribuable','nom contribuable','nom et prenom','nom et prenoms','contribuable','taxpayer name','taxpayer','raison sociale','nom du redevable','redevable','responsible to pay','nom']],
  ['nif',             'NIF / Identifiant',                  ['nif','numero identification','identifiant','nin','num contribuable','ifu']],
  ['tel',             'Téléphone',                          ['telephone','phone number','numero de telephone','tel','phone','contact','mobile']],
  ['adresse',         'Adresse',                            ['adresse','address','localisation','quartier']],
  ['type',            "Type d'impôt / taxe",                ["type d impot",'type impot','type de taxe','type taxe','impot','taxe','nature impot','nature de l impot','tax type','type']],
  ['periode',         'Période',                            ['periode','period','exercice','annee','mois']],
  ['montantDeclare',  'Montant déclaré',                    ['montant declare','declared amount','montant declaration','declared cdf','declared usd']],
  ['montantAttendu',  'Montant attendu',                    ['montant attendu','expected amount','montant du','montant emis','montant total']],
  ['montantPaye',     'Montant payé',                       ['montant paye','paid amount','amount paid','montant regle','versement','paid cdf','paid usd']],
  ['reste',           'Reste à payer',                      ['reste a payer','unpaid cdf','unpaid usd','solde restant','reliquat','remaining','reste','balance']],
  ['devise',          'Devise',                             ['currency name','devise','monnaie']],
  ['dateEmission',    "Date d'émission",                    ['date d emission','date emission','issue date','date de creation','date creation']],
  ['datePaiement',    'Date de paiement',                   ['date de paiement','date paiement','payment date','paid date']],
  ['statut',          'Statut',                             ['statut','status','etat','situation']],
  ['etape',           'Étape',                              ['tax step','etape fiscale','etape']],
  ['agentEmission',   'Agent émetteur',                     ['agent ayant emis','agent emetteur','issued by','agent emission','emis par','agent']],
  ['agentValidation', 'Agent validateur / approbateur',     ['agent ayant valide','agent validateur','approved by','validated by','agent validation','valide par','approuve par','superviseur']],
  ['ressort',         'Ressort / Service',                  ['ressort','service','bureau','direction','centre','antenne','agence','region']],
  ['observation',     'Observation',                        ['observation','observations','remarque','commentaire','note interne']]
];

const STD_LABELS = {};
FIELD_DEFS.forEach(([k, label]) => STD_LABELS[k] = label);
const MONEY_KEYS = ['montantDeclare','montantAttendu','montantPaye','reste'];

function detectField(header){
  const h = norm(header);
  if (!h) return null;
  if (h === 'currency rate' || h === 'difference cdf' || h === 'difference usd') return null;
  let best = null, bestLen = 0;
  for (const [key, , pats] of FIELD_DEFS){
    for (const p of pats){
      if (p === h) return key;
      if (p.length >= 8 && h.includes(p) && p.length > bestLen){
        best = key;
        bestLen = p.length;
      }
    }
  }
  return best;
}

const state = {
  cfg: { serverUrl: '' },
  deferredInstall: null,
  scanner: null,
  scanMode: 'qr',
  backView: 'home',
  notesCache: null,
  notesIndex: null,
  notesIndexGen: 0,
  notesIndexPromise: null,
  notesShown: 30,
  notesQuery: '',
  notesMonth: 'all',
  notesStatus: 'all',
  notesDateField: 'auto',
  user: null,
  pinBuffer: '',
  pinMode: 'login',
  pinFails: 0
};
function loadCfg(){
  try { Object.assign(state.cfg, JSON.parse(localStorage.getItem('npc_cfg') || '{}')); } catch(e){}
  const def = defaultApiUrl();
  const cur = String(state.cfg.serverUrl || '').trim();
  if (!cur || /localhost|127\.0\.0\.1/i.test(cur) && !/localhost|127\.0\.0\.1/i.test(location.hostname)){
    state.cfg.serverUrl = def;
    saveCfg();
  } else if (!cur){
    state.cfg.serverUrl = def;
    saveCfg();
  }
}
function defaultApiUrl(){
  const path = location.pathname.replace(/index\.php.*$/i, '');
  const base = path.endsWith('/') ? path : path + '/';
  return location.origin + base + 'api.php';
}
function apiUrl(){
  return String(state.cfg.serverUrl || '').trim() || defaultApiUrl();
}
function apiAction(action){
  const url = apiUrl();
  return url + (url.includes('?') ? '&' : '?') + 'action=' + encodeURIComponent(action);
}
function saveCfg(){
  try { localStorage.setItem('npc_cfg', JSON.stringify(state.cfg)); } catch(e){}
}

async function applyServerRecords(records, opts = {}){
  if (!Array.isArray(records) || !records.length) return { added: 0, updated: 0 };
  const existing = new Set(await IDB.allKeys());
  const toPut = [];
  let added = 0, updated = 0;
  for (const r of records){
    if (!r || !r.ref) continue;
    const rec = {
      ref: String(r.ref),
      refRaw: r.refRaw || r.ref,
      std: r.std && typeof r.std === 'object' ? r.std : {},
      fields: r.fields && typeof r.fields === 'object' ? r.fields : {},
      updatedAt: Number(r.updatedAt) || Date.now()
    };
    if (!existing.has(rec.ref)){ toPut.push(rec); added++; }
    else { toPut.push(rec); updated++; }
  }
  if (toPut.length) await IDB.putNotes(toPut);
  if (toPut.length && !opts.keepCache) invalidateNotesCache();
  return { added, updated };
}
async function applyServerInspectors(list){
  if (!Array.isArray(list) || !list.length) return 0;
  let n = 0;
  for (const u of list){
    if (!u || !u.id) continue;
    const old = await IDB.getInspector(u.id);
    if (!old || (Number(u.updatedAt) || 0) >= (Number(old.updatedAt) || 0)){
      await IDB.putInspector(u);
      n++;
      if (state.user && state.user.id === u.id) state.user = u;
    }
  }
  return n;
}
async function fetchApi(url, waitMs, extra){
  const ctrl = new AbortController();
  const timer = setTimeout(() => ctrl.abort(), waitMs);
  try {
    const res = await fetch(url, Object.assign({ cache: 'no-store', signal: ctrl.signal }, extra || {}));
    const raw = await res.text();
    if (!raw) throw new Error('Réponse serveur vide');
    let data;
    try { data = JSON.parse(raw); }
    catch (e){ throw new Error('Réponse serveur illisible'); }
    if (!res.ok || (data && data.ok === false)) throw new Error((data && data.error) || ('HTTP ' + res.status));
    return data;
  } catch (err){
    if (err && err.name === 'AbortError') throw new Error('Délai dépassé');
    throw err;
  } finally {
    clearTimeout(timer);
  }
}
async function silentSync(opts = {}){
  const doPull = opts.pull !== false;
  const doPush = !!opts.push;
  const waitMs = Number(opts.timeout) > 0 ? Number(opts.timeout) : 25000;
  const pageMs = Math.max(waitMs, 25000);
  const say = (p, m, d) => {
    if (typeof opts.onProgress === 'function') opts.onProgress(p, m, d);
  };
  const out = { ok: false, notes: 0, inspectors: 0, error: '', offline: false };
  if (!navigator.onLine){
    out.offline = true;
    out.error = 'hors ligne';
    say(100, 'Hors ligne', 'Les données déjà présentes restent disponibles.');
    return out;
  }
  let savedAny = false;
  try {
    say(8, 'Vérification du serveur…', 'WAMP / MySQL');
    await fetchApi(apiAction('setup'), Math.min(pageMs, 12000)).catch(() => ({}));
    if (doPull){
      say(18, 'Téléchargement des notes…', 'Récupération depuis le PC');
      const PAGE = 200;
      let offset = 0;
      let total = null;
      let added = 0, updated = 0;
      let first = true;
      let samples = [];
      while (true){
        const pack = Math.floor(offset / PAGE) + 1;
        let data = null;
        let lastErr = null;
        for (let attempt = 0; attempt < 3; attempt++){
          try {
            data = await fetchApi(apiAction('pull') + '&limit=' + PAGE + '&offset=' + offset + '&fields=0', pageMs);
            lastErr = null;
            break;
          } catch (e){
            lastErr = e;
            const retryable = /Délai|illisible|HTTP 5|Failed to fetch|NetworkError/i.test(String(e && e.message));
            if (!retryable || attempt === 2) throw e;
            say(18, 'Nouvelle tentative…', 'Paquet ' + pack);
            await new Promise(r => setTimeout(r, 700 * (attempt + 1)));
          }
        }
        if (!data) throw lastErr || new Error('Téléchargement interrompu');
        const records = Array.isArray(data.records) ? data.records : (Array.isArray(data.notes) ? data.notes : []);
        if (typeof data.total === 'number') total = data.total;
        if (!samples.length) samples = records.slice(0, 6).map(r => r.refRaw || r.ref);
        if (first){
          const inspectors = Array.isArray(data.inspectors) ? data.inspectors : [];
          out.inspectors = await applyServerInspectors(inspectors);
          first = false;
        }
        const native = records.length && records[0] && records[0].ref && (records[0].std || records[0].fields);
        let meta = { added: 0, updated: 0 };
        if (native){
          meta = await applyServerRecords(records, { keepCache: true });
        } else if (records.length){
          const headers = Array.from(new Set(records.flatMap(o => Object.keys(o).filter(k => !String(k).startsWith('_')))));
          if (!headers.some(h => detectField(h) === 'ref')) headers.unshift('Reference Number');
          const matrix = [headers, ...records.map(o => headers.map(h => o[h] ?? o['Reference Number'] ?? o._ref ?? ''))];
          const ingested = ingestRows(matrix);
          meta = await commitRecords(ingested.records, 'Synchronisation serveur');
        }
        added += meta.added || 0;
        updated += meta.updated || 0;
        if (added + updated) savedAny = true;
        offset += records.length;
        const denom = total || Math.max(offset, 1);
        const pct = 18 + Math.min(62, Math.round((offset / denom) * 62));
        say(pct, 'Téléchargement des notes…',
          (total != null)
            ? (offset.toLocaleString('fr-FR') + ' / ' + total.toLocaleString('fr-FR'))
            : (offset.toLocaleString('fr-FR') + ' note(s)'));
        if (!records.length || data.done || (total != null && offset >= total)) break;
      }
      if (savedAny) invalidateNotesCache();
      out.notes = added + updated;
      if (out.notes){
        await IDB.setMeta('lastImport', {
          date: Date.now(),
          source: 'Synchronisation serveur',
          added, updated,
          total: total || (added + updated),
          samples: samples
        });
      }
      say(84, out.notes ? 'Notes enregistrées sur l’appareil' : 'Aucune note à synchroniser',
        out.notes ? out.notes.toLocaleString('fr-FR') + ' ligne(s)' : 'Le serveur est joignable');
    }
    if (doPush){
      say(88, 'Envoi des données locales…', 'Partage avec les autres appareils');
      const notes = await IDB.getAll();
      if (notes.length){
        const CHUNK = 25;
        for (let i = 0; i < notes.length; i += CHUNK){
          const slice = notes.slice(i, i + CHUNK);
          say(88, 'Envoi des données locales…',
            Math.min(i + CHUNK, notes.length).toLocaleString('fr-FR') + ' / ' + notes.length.toLocaleString('fr-FR'));
          const data = await fetchApi(apiAction('push'), Math.max(pageMs, 60000), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ notes: slice, source: 'sync auto' })
          });
          if (data.ok === false) throw new Error(data.error || 'Envoi impossible');
        }
      }
      const inspectors = await IDB.allInspectors();
      if (inspectors.length){
        await fetchApi(apiAction('push_inspectors'), pageMs, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ inspectors })
        }).catch(() => ({}));
      }
    }
    say(100, 'Synchronisation terminée', '');
    out.ok = true;
    return out;
  } catch (err){
    if (savedAny) invalidateNotesCache();
    out.error = err.message || String(err);
    say(100, 'Synchronisation interrompue', out.error);
    return out;
  }
}

function isAdmin(){ return !!(state.user && state.user.role === 'admin'); }
function isLogged(){ return !!state.user; }

function randomSalt(){
  const a = new Uint8Array(12);
  crypto.getRandomValues(a);
  return [...a].map(b => b.toString(16).padStart(2, '0')).join('');
}
function sha256hex(str){
  const bytes = new TextEncoder().encode(String(str));
  const K = [
    0x428a2f98,0x71374491,0xb5c0fbcf,0xe9b5dba5,0x3956c25b,0x59f111f1,0x923f82a4,0xab1c5ed5,
    0xd807aa98,0x12835b01,0x243185be,0x550c7dc3,0x72be5d74,0x80deb1fe,0x9bdc06a7,0xc19bf174,
    0xe49b69c1,0xefbe4786,0x0fc19dc6,0x240ca1cc,0x2de92c6f,0x4a7484aa,0x5cb0a9dc,0x76f988da,
    0x983e5152,0xa831c66d,0xb00327c8,0xbf597fc7,0xc6e00bf3,0xd5a79147,0x06ca6351,0x14292967,
    0x27b70a85,0x2e1b2138,0x4d2c6dfc,0x53380d13,0x650a7354,0x766a0abb,0x81c2c92e,0x92722c85,
    0xa2bfe8a1,0xa81a664b,0xc24b8b70,0xc76c51a3,0xd192e819,0xd6990624,0xf40e3585,0x106aa070,
    0x19a4c116,0x1e376c08,0x2748774c,0x34b0bcb5,0x391c0cb3,0x4ed8aa4a,0x5b9cca4f,0x682e6ff3,
    0x748f82ee,0x78a5636f,0x84c87814,0x8cc70208,0x90befffa,0xa4506ceb,0xbef9a3f7,0xc67178f2
  ];
  const rr = (x, n) => (x >>> n) | (x << (32 - n));
  const bitLen = bytes.length * 8;
  const padLen = ((bytes.length + 8) >> 6 << 6) + 64;
  const w = new Uint8Array(padLen);
  w.set(bytes);
  w[bytes.length] = 0x80;
  const view = new DataView(w.buffer);
  view.setUint32(padLen - 4, bitLen, false);
  let h0 = 0x6a09e667, h1 = 0xbb67ae85, h2 = 0x3c6ef372, h3 = 0xa54ff53a;
  let h4 = 0x510e527f, h5 = 0x9b05688c, h6 = 0x1f83d9ab, h7 = 0x5be0cd19;
  const W = new Uint32Array(64);
  for (let i = 0; i < padLen; i += 64){
    for (let t = 0; t < 16; t++) W[t] = view.getUint32(i + t * 4, false);
    for (let t = 16; t < 64; t++){
      const s0 = rr(W[t - 15], 7) ^ rr(W[t - 15], 18) ^ (W[t - 15] >>> 3);
      const s1 = rr(W[t - 2], 17) ^ rr(W[t - 2], 19) ^ (W[t - 2] >>> 10);
      W[t] = (W[t - 16] + s0 + W[t - 7] + s1) >>> 0;
    }
    let a = h0, b = h1, c = h2, d = h3, e = h4, f = h5, g = h6, h = h7;
    for (let t = 0; t < 64; t++){
      const S1 = rr(e, 6) ^ rr(e, 11) ^ rr(e, 25);
      const ch = (e & f) ^ (~e & g);
      const t1 = (h + S1 + ch + K[t] + W[t]) >>> 0;
      const S0 = rr(a, 2) ^ rr(a, 13) ^ rr(a, 22);
      const maj = (a & b) ^ (a & c) ^ (b & c);
      const t2 = (S0 + maj) >>> 0;
      h = g; g = f; f = e; e = (d + t1) >>> 0;
      d = c; c = b; b = a; a = (t1 + t2) >>> 0;
    }
    h0 = (h0 + a) >>> 0; h1 = (h1 + b) >>> 0; h2 = (h2 + c) >>> 0; h3 = (h3 + d) >>> 0;
    h4 = (h4 + e) >>> 0; h5 = (h5 + f) >>> 0; h6 = (h6 + g) >>> 0; h7 = (h7 + h) >>> 0;
  }
  return [h0, h1, h2, h3, h4, h5, h6, h7].map(n => n.toString(16).padStart(8, '0')).join('');
}
function hashPinLegacy(pin, salt){
  const msg = new TextEncoder().encode(String(salt) + '|' + String(pin));
  let h = 2166136261;
  for (const c of msg) h = Math.imul(h ^ c, 16777619);
  return (h >>> 0).toString(16);
}
function hashPin(pin, salt){
  return sha256hex(String(salt) + '|' + String(pin));
}
function pinMatches(u, pin){
  if (!u) return false;
  const expected = String(u.pinHash || '');
  if (!expected) return false;
  const canonical = hashPin(pin, u.salt || '');
  if (canonical === expected) return true;
  if (hashPinLegacy(pin, u.salt || '') === expected) return true;
  return false;
}
function applyRoleUI(){
  document.body.classList.toggle('role-admin', isAdmin());
  document.body.classList.toggle('role-inspector', isLogged() && !isAdmin());
  document.body.classList.toggle('locked', !isLogged());
  const u = state.user;
  if ($('#whoChip')){
    $('#whoChip').classList.toggle('hidden', !u);
    if (u && $('#whoName')) $('#whoName').textContent = u.name;
  }
  if ($('#helloLine') && u){
    const hour = new Date().getHours();
    const hi = hour < 12 ? 'Bonjour' : (hour < 18 ? 'Bon après-midi' : 'Bonsoir');
    $('#helloLine').textContent = hi + ', ' + u.name.split(' ')[0];
  }
}
function showLock(on, sub){
  const el = $('#lockScreen');
  if (!el) return;
  el.classList.toggle('hidden', !on);
  document.body.classList.toggle('locked', on || !isLogged());
  if (sub) $('#lockSub').textContent = sub;
  state.pinBuffer = '';
  renderPinDots();
  if (on && state.pinMode === 'login') state.pinFails = 0;
  const canCancel = on && state.pinMode === 'change' && state.user && !state.user.mustChangePin;
  if ($('#pinCancel')) $('#pinCancel').classList.toggle('hidden', !canCancel);
}
function renderPinDots(){
  const n = Math.max(4, state.pinBuffer.length);
  const count = Math.min(6, Math.max(4, n));
  $('#pinDots').innerHTML = Array.from({ length: count }, (_, i) =>
    `<i class="${i < state.pinBuffer.length ? 'on' : ''}"></i>`).join('');
}
async function ensureAuthSeed(){
  const list = await IDB.allInspectors();
  const admins = list.filter(u => u.role === 'admin' && u.active !== false);

  async function writeAdmin0000(base){
    const salt = randomSalt();
    const rec = Object.assign({}, base || {}, {
      id: (base && base.id) || 'u_admin',
      name: (base && base.name) || 'Administrateur',
      matricule: (base && base.matricule) || 'ADMIN',
      role: 'admin',
      active: true,
      salt,
      pinHash: hashPin('0000', salt),
      mustChangePin: true,
      createdAt: (base && base.createdAt) || Date.now(),
      updatedAt: Date.now()
    });
    await IDB.putInspector(rec);
    return rec;
  }

  if (await findByPin('0000')) return !list.length || admins.some(a => a.mustChangePin);

  if (!admins.length){
    await writeAdmin0000(null);
    return true;
  }

  const pending = admins.find(a => a.mustChangePin || !a.pinHash || !a.salt);
  if (pending){
    await writeAdmin0000(pending);
    return true;
  }

  await writeAdmin0000(admins[0]);
  return true;
}
async function findByPin(pin){
  const list = await IDB.allInspectors();
  for (const u of list){
    if (u.active === false) continue;
    if (pinMatches(u, pin)) return u;
  }
  if (pin === '0000'){
    return list.find(u => u.role === 'admin' && u.active !== false) || null;
  }
  return null;
}
async function pinInUse(pin, exceptId){
  const list = await IDB.allInspectors();
  for (const u of list){
    if (u.id === exceptId) continue;
    if (pinMatches(u, pin)) return true;
  }
  return false;
}
function persistSession(user){
  try {
    const id = user ? user.id : '';
    sessionStorage.setItem('npc_uid', id);
    localStorage.setItem('npc_uid', id);
  } catch(e){}
}
async function restoreSession(){
  try {
    const id = sessionStorage.getItem('npc_uid') || localStorage.getItem('npc_uid');
    if (!id) return false;
    const u = await IDB.getInspector(id);
    if (!u || !u.active) return false;
    state.user = u;
    applyRoleUI();
    return true;
  } catch(e){ return false; }
}
async function loginWithPin(pin){
  if (!/^\d{4,6}$/.test(pin)) return { ok: false, error: 'Le PIN doit contenir 4 à 6 chiffres.' };
  if (state.pinFails >= 5) return { ok: false, error: 'Trop d’essais. Patientez un instant.' };
  const u = await findByPin(pin);
  if (!u){
    state.pinFails++;
    if (state.pinFails >= 5) setTimeout(() => { state.pinFails = 0; }, 20000);
    return { ok: false, error: 'Code PIN incorrect.' };
  }
  state.pinFails = 0;
  u.lastLogin = Date.now();
  u.updatedAt = Date.now();
  await IDB.putInspector(u);
  state.user = u;
  persistSession(u);
  applyRoleUI();
  showLock(false);
  go('home');
  await refreshHome();
  refreshInstallOffer();
  if (u.mustChangePin){
    toast('Connecté en administrateur. Changez le PIN 0000 dans Paramètres.');
  }
  silentSync({ pull: true, push: isAdmin() }).then(r => {
    if (r.ok && (r.notes || r.inspectors)) refreshHome();
  });
  return { ok: true };
}
async function completePinChange(pin){
  if (!state.user) return;
  if (!/^\d{4,6}$/.test(pin) || pin === '0000'){
    toast('PIN invalide. 4 à 6 chiffres, pas 0000.');
    return;
  }
  if (await pinInUse(pin, state.user.id)){
    toast('Ce PIN est déjà utilisé par un autre compte.');
    return;
  }
  const salt = randomSalt();
  state.user.salt = salt;
  state.user.pinHash = await hashPin(pin, salt);
  state.user.mustChangePin = false;
  state.user.updatedAt = Date.now();
  await IDB.putInspector(state.user);
  persistSession(state.user);
  state.pinMode = 'login';
  showLock(false);
  toast('Code PIN enregistré.');
  go('home');
  await refreshHome();
  refreshInstallOffer();
  silentSync({ pull: true, push: isAdmin() }).then(r => {
    if (r.ok && (r.notes || r.inspectors)) refreshHome();
  });
}
function logout(){
  state.user = null;
  persistSession(null);
  state.pinMode = 'login';
  applyRoleUI();
  showLock(true, 'Saisissez votre code PIN');
  $('#lockHint').textContent = 'Administrateur : tapez 0000';
}

async function onPinKey(k){
  if (k === 'del'){
    state.pinBuffer = state.pinBuffer.slice(0, -1);
    renderPinDots();
    return;
  }
  if (k === 'ok'){
    if (state.pinBuffer.length < 4) return;
    const pin = state.pinBuffer;
    state.pinBuffer = '';
    renderPinDots();
    if (state.pinMode === 'change') await completePinChange(pin);
    else {
      const r = await loginWithPin(pin);
      if (!r.ok){
        $('#lockScreen').classList.add('shake');
        setTimeout(() => $('#lockScreen').classList.remove('shake'), 400);
        $('#lockHint').textContent = r.error + ' — Admin : tapez 0000';
      }
    }
    return;
  }
  if (!/^\d$/.test(k) || state.pinBuffer.length >= 6) return;
  state.pinBuffer += k;
  renderPinDots();
  if (state.pinMode !== 'change' && state.pinBuffer.length >= 4){
    const pin = state.pinBuffer;
    const u = await findByPin(pin);
    if (u) await onPinKey('ok');
    else if (pin.length === 6) await onPinKey('ok');
  }
}

function setBusy(on, text){
  const el = $('#busy');
  if (!el) return;
  if (text && $('#busyText')) $('#busyText').textContent = text;
  el.classList.toggle('hidden', !on);
}
function setBadge(sel, n){
  const el = $(sel);
  if (!el) return;
  el.textContent = n > 99 ? '99+' : String(n);
  el.classList.toggle('hidden', !n);
}
function renderStatusChart(parts){
  const sum = parts.reduce((a, p) => a + p.v, 0);
  const r = 38, circ = 2 * Math.PI * r;
  let off = 0;
  const total = sum || 1;
  const segs = parts.filter(p => p.v > 0).map(p => {
    const len = (p.v / total) * circ;
    const el = `<circle cx="54" cy="54" r="${r}" fill="none" stroke="${p.color}" stroke-width="12" stroke-linecap="round" stroke-dasharray="${len.toFixed(2)} ${(circ - len).toFixed(2)}" stroke-dashoffset="${(-off).toFixed(2)}" transform="rotate(-90 54 54)"/>`;
    off += len;
    return el;
  }).join('');
  $('#statusRing').innerHTML = `<svg viewBox="0 0 108 108" width="120" height="120" aria-hidden="true">
    <circle cx="54" cy="54" r="${r}" fill="none" stroke="#d7e6ec" stroke-width="12"/>
    ${segs}
    <text x="54" y="51" text-anchor="middle" font-size="18" font-weight="800" fill="#163a4a">${sum}</text>
    <text x="54" y="66" text-anchor="middle" font-size="8" fill="#5b7380">notes</text>
  </svg>`;
  $('#statusLegend').innerHTML = parts.map(p =>
    `<div><i style="background:${p.color}"></i>${esc(p.label)} · <b>${p.v}</b></div>`
  ).join('');
}
function renderMonthBars(map){
  const box = $('#monthBars');
  if (!box) return;
  const keys = Object.keys(map).filter(k => k !== 'unknown').sort().slice(-6);
  if (!keys.length){
    box.innerHTML = '<p class="muted" style="width:100%;text-align:center;padding-top:24px">Pas encore de dates exploitables.</p>';
    return;
  }
  const max = Math.max(...keys.map(k => map[k]), 1);
  box.innerHTML = keys.map(k => {
    const n = map[k];
    const h = Math.max(8, Math.round((n / max) * 100));
    const parts = k.split('-');
    const lab = (FR_MONTHS[+parts[1] - 1] || '').slice(0, 3) + ' ' + String(parts[0]).slice(2);
    return `<div class="bar" style="height:${h}%" title="${n} notes"><span class="lab">${esc(lab)}</span></div>`;
  }).join('');
}

function go(view){
  if (!isLogged() && view !== 'lock') { showLock(true); return; }
  if ((view === 'import' || view === 'admin') && !isAdmin()){
    toast("Accès réservé à l'administrateur.");
    return;
  }
  if (view !== 'result') state.backView = view === 'notes' ? 'notes' : (view === 'home' ? 'home' : state.backView);
  $$('.view').forEach(v => v.classList.toggle('active', v.id === 'view-' + view));
  const tab = view === 'result' ? (state.backView === 'notes' ? 'notes' : 'home') : view;
  $$('.tabbar button').forEach(b => b.classList.toggle('active', b.dataset.view === tab));
  const titles = { home:'Accueil', notes:'Notes', import:'Import', history:'Activité', settings:'Réglages', result:'Contrôle', admin:'Équipe' };
  if ($('#appTitle')) $('#appTitle').textContent = titles[view] || 'Notes NP';
  try { if (navigator.vibrate) navigator.vibrate(7); } catch(e){}
  window.scrollTo(0, 0);
  if (view === 'home')     refreshHome();
  if (view === 'notes')    renderNotesPage();
  if (view === 'history')  renderHistory();
  if (view === 'settings') renderSettings();
  if (view === 'import')   refreshImportMeta();
  if (view === 'admin')    renderAdmin();
}
document.addEventListener('click', e => {
  const b = e.target.closest('[data-view]');
  if (b){ e.preventDefault(); go(b.dataset.view); }
});

function uniqueStrings(arr){
  const out = [];
  const seen = new Set();
  arr.forEach(s => {
    const v = String(s || '');
    if (!v || seen.has(v)) return;
    seen.add(v);
    out.push(v);
  });
  return out;
}

function noteCompact(n){
  return compactRef(n.refRaw || n.ref || '');
}

async function findNote(raw){
  const q = String(raw || '').trim();
  const compact = compactRef(q);
  const legacy = q.toUpperCase().replace(/\s+/g, '');
  const num = numericLoose(q);

  for (const k of uniqueStrings([compact, legacy, q, q.toUpperCase()])){
    try {
      const rec = await IDB.getNote(k);
      if (rec) return { rec, suggestions: [] };
    } catch(e){}
  }

  const total = await IDB.count();
  if (!total) return { rec: null, empty: true, suggestions: [] };
  if (!compact) return { rec: null, suggestions: [] };

  const loadRows = async rows => {
    const out = [];
    for (const r of rows.slice(0, 8)){
      const rec = await IDB.getNote(r.ref);
      if (rec) out.push(rec);
    }
    return out;
  };

  if (!state.notesIndex){
    try {
      const saved = await IDB.getMeta('notesIndexLite');
      if (saved && Array.isArray(saved.rows) && saved.rows.length) state.notesIndex = saved.rows;
    } catch (e){}
  }

  if (state.notesIndex){
    const matches = [];
    const suggestions = [];
    state.notesIndex.forEach(row => {
      const cNum = numericLoose(row.refRaw || row.ref);
      const hit = row.cRef === compact || row.cRaw === compact || row.cNote === compact
        || (num && cNum && num === cNum)
        || (num && numericLoose(row.note) === num);
      if (hit) matches.push(row);
      else if (compact.length >= 5 && (
        (row.cRef && row.cRef.includes(compact)) ||
        (row.cRef && row.cRef.length >= 5 && compact.includes(row.cRef)) ||
        (row.cNote && row.cNote.includes(compact))
      )) suggestions.push(row);
    });
    if (matches.length === 1){
      const rec = await IDB.getNote(matches[0].ref);
      if (rec) return { rec, suggestions: [] };
    }
    if (matches.length > 1) return { rec: null, suggestions: await loadRows(matches) };
    if (suggestions.length === 1){
      const rec = await IDB.getNote(suggestions[0].ref);
      if (rec) return { rec, suggestions: [] };
    }
    return { rec: null, suggestions: await loadRows(suggestions) };
  }

  return { rec: null, suggestions: [] };
}

async function doSearch(raw){
  const q = String(raw || '').trim();
  if (!q){ toast('Veuillez saisir un numéro de référence.'); return; }
  const key = compactRef(q) || q.toUpperCase();
  if (!key){ toast('Numéro de référence invalide.'); return; }

  setBusy(true, 'Recherche…');
  let rec = null, suggestions = [], empty = false;
  try {
    const found = await findNote(q);
    rec = found.rec || null;
    suggestions = found.suggestions || [];
    empty = !!found.empty;
  } catch(err){ console.error(err); }
  finally { setBusy(false); }

  try { await IDB.addHistory({ ref: key, raw: q, found: !!rec, at: Date.now(), userId: state.user && state.user.id, userName: state.user && state.user.name }); } catch(e){}

  state.backView = 'home';
  prepareResultBack();
  renderResult(rec, q, { suggestions, empty });
  go('result');
}

function fieldLookup(fields){
  const map = {};
  Object.keys(fields || {}).forEach(k => { map[norm(k)] = fields[k]; });
  return map;
}
function pickField(std, fmap, names){
  for (const name of names){
    if (std && std[name] != null && std[name] !== '') return std[name];
    const nk = norm(name);
    if (fmap[nk] != null && fmap[nk] !== '') return fmap[nk];
  }
  return null;
}
function hydrateNote(input){
  const note = (input && (input.std || input.fields)) ? input : { std: input || {} };
  const std = Object.assign({}, note.std || {});
  const fmap = fieldLookup(note.fields || {});
  const statut = pickField(std, fmap, ['statut','status','etat','situation']);
  if (statut != null) std.statut = String(statut).trim();
  const etape = pickField(std, fmap, ['etape','tax step','etape fiscale']);
  if (etape != null) std.etape = String(etape).trim();
  const nom = pickField(std, fmap, ['nom','responsible to pay','taxpayer']);
  if (!std.nom && nom) std.nom = String(nom).trim();
  const tel = pickField(std, fmap, ['tel','phone number','telephone']);
  if (!std.tel && tel) std.tel = String(tel).trim();
  const curName = pickField({}, fmap, ['currency name','devise','monnaie']);
  if (curName && !isFinite(Number(curName))) std.devise = String(curName).trim();
  else if (std.devise != null && isFinite(Number(std.devise)) && Number(std.devise) > 20)
    std.devise = curName ? String(curName).trim() : 'CDF';
  const isUsd = norm(String(std.devise || '')) === 'usd';
  const paidC = toNumber(pickField(std, fmap, ['montantPaye','paid cdf','montant paye']));
  const paidU = toNumber(pickField({}, fmap, ['paid usd']));
  const declC = toNumber(pickField(std, fmap, ['montantDeclare','declared cdf']));
  const declU = toNumber(pickField({}, fmap, ['declared usd']));
  const unpC  = toNumber(pickField(std, fmap, ['reste','unpaid cdf','reste a payer']));
  const unpU  = toNumber(pickField({}, fmap, ['unpaid usd']));
  if (std.montantPaye == null || std.montantPaye === '') std.montantPaye = isUsd ? (paidU ?? paidC) : (paidC ?? paidU);
  else std.montantPaye = toNumber(std.montantPaye);
  if (std.montantDeclare == null || std.montantDeclare === '') std.montantDeclare = isUsd ? (declU ?? declC) : (declC ?? declU);
  else std.montantDeclare = toNumber(std.montantDeclare);
  if (std.reste == null || std.reste === '') std.reste = isUsd ? (unpU ?? unpC) : (unpC ?? unpU);
  else std.reste = toNumber(std.reste);
  if (std.montantAttendu == null || std.montantAttendu === ''){
    if (std.montantDeclare != null) std.montantAttendu = std.montantDeclare;
    else if (std.reste != null && std.montantPaye != null) std.montantAttendu = std.reste + std.montantPaye;
  } else std.montantAttendu = toNumber(std.montantAttendu);
  const emi = pickField(std, fmap, ['dateEmission','issue date']);
  if (!std.dateEmission && emi) std.dateEmission = emi;
  const payd = pickField(std, fmap, ['datePaiement','paid date','payment date']);
  if (!std.datePaiement && payd) std.datePaiement = payd;
  return std;
}
function frenchStatusLabel(raw, key){
  const n = norm(raw);
  const fromExcel = {
    'paid': 'Payée',
    'unpaid': 'Impayée',
    'open': 'Ouverte',
    'partial paid': 'Paiement partiel',
    'partially paid': 'Paiement partiel',
    'partial': 'Paiement partiel',
    'rejected': 'Rejetée',
    'cancelled': 'Annulée',
    'canceled': 'Annulée',
    'waiting for approval': 'En attente de validation',
    'toll tax': 'Taxe de péage'
  };
  if (fromExcel[n]) return fromExcel[n];
  const fromKey = {
    paid: 'Payée', unpaid: 'Impayée', open: 'Ouverte',
    partial: 'Paiement partiel', cancelled: 'Rejetée',
    notice: 'Mise en demeure'
  };
  if (key && fromKey[key]) return fromKey[key];
  return raw || fromKey[key] || '';
}
function frenchStepLabel(etape){
  const n = norm(etape);
  if (/demeure/.test(n)) return 'Mise en demeure';
  if (/contrainte|commandement/.test(n)) return 'Contrainte et commandement';
  if (/note de perception|perception/.test(n)) return 'Note de perception';
  return String(etape || '').trim();
}
function classifyNote(input){
  const std = hydrateNote(input);
  const raw = String(std.statut || '').trim();
  const etape = frenchStepLabel(std.etape || '');
  const s = norm(raw);
  const e = norm(String(std.etape || ''));
  const attendu = toNumber(std.montantAttendu ?? std.montantDeclare);
  const paye = toNumber(std.montantPaye);
  const reste = toNumber(std.reste);
  const notice = /demeure|contentieux|poursuite|commandement|contrainte|recouvrement force|demand letter/.test(e)
    || /demeure|contentieux|commandement|contrainte/.test(s);
  const hasUnpaid = (reste != null && reste > 0.009) || /unpaid|non paye|impaye/.test(s);
  const pack = (key, level, fallback, hint) => ({
    key, level, label: frenchStatusLabel(raw, key) || fallback, hint: hint || etape || '',
    notice, hasUnpaid: !!(hasUnpaid && key !== 'paid' && key !== 'cancelled'), etape, std
  });
  if (/annul|cancel|rejet|abandon|supprim|reject|void/.test(s))
    return pack('cancelled', 'info', 'Rejetée', 'Clôturée / rejetée');
  if (/partiel|partial/.test(s))
    return pack('partial', 'warn', 'Paiement partiel', etape || 'Versement incomplet');
  if (/unpaid|non paye|impaye/.test(s))
    return pack('unpaid', 'bad', 'Impayée', etape || 'Aucun paiement recensé');
  if (/waiting|en attente|approval|a valider/.test(s))
    return pack('open', 'open', 'En attente', etape || 'En attente de validation');
  if (/\bopen\b|ouvert|emise|emis|en cours|a payer|\bdue\b|issued/.test(s))
    return pack('open', 'open', 'Ouverte', etape || 'Émise, en attente de paiement');
  if (/\bpaid\b|paye|sold|regl|acquit|valide|settled/.test(s)){
    const st = pack('paid', 'ok', 'Payée', etape || 'Soldée intégralement');
    st.hasUnpaid = false;
    return st;
  }
  if (attendu !== null && paye !== null){
    if (attendu > 0 && paye + 0.009 >= attendu) return pack('paid', 'ok', 'Payée', 'Montant attendu couvert');
    if (paye > 0) return pack('partial', 'warn', 'Paiement partiel', 'Reste à payer');
    return pack('open', 'open', 'Ouverte', (reste && reste > 0) ? 'Reste à recouvrer' : 'Aucun versement');
  }
  if (paye !== null && paye > 0) return pack('partial', 'warn', 'Paiement enregistré', etape);
  if (raw) return pack('other', 'info', raw, 'Statut Excel non classé');
  return pack('open', 'open', 'Ouverte', 'Statut non renseigné');
}
function computeStatus(input){ return classifyNote(input); }
function noteMatchesStatus(n, key){
  if (!key || key === 'all') return true;
  const st = classifyNote(n);
  if (key === 'notice') return !!st.notice;
  if (key === 'unpaid') return !!st.hasUnpaid;
  return st.key === key;
}
const STATUS_FILTERS = [
  ['all', 'Tous'],
  ['paid', 'Payées'],
  ['open', 'Ouvertes'],
  ['partial', 'Partielles'],
  ['unpaid', 'Impayées'],
  ['notice', 'Mises en demeure'],
  ['cancelled', 'Annulées']
];
function openNotesByStatus(key){
  state.notesStatus = key || 'all';
  state.notesMonth = 'all';
  state.notesShown = 30;
  go('notes');
}

function renderResult(rec, query, extra = {}){
  const banner = $('#resultBanner');
  const body   = $('#resultBody');
  const shown  = rec ? (rec.refRaw || rec.ref) : query;
  const suggestions = extra.suggestions || [];

  if (!rec){
    banner.innerHTML = `
      <div class="verdict missing">
        <div class="stamp">
          <div class="stamp-inner">
            <div class="stamp-kicker">Non répertoriée</div>
            <div class="stamp-title">NOTE<br>INTROUVABLE</div>
            <div class="stamp-ref">${esc(shown)}</div>
          </div>
        </div>
      </div>`;
    const suggestHtml = suggestions.length ? `
        <h2 style="margin-top:14px">Références proches</h2>
        <div class="suggest-list">
          ${suggestions.map(n => `
            <div class="list-item" data-ref="${esc(n.refRaw || n.ref)}">
              <div class="li-ico ok">?</div>
              <div class="li-main">
                <div class="li-title">${esc(n.refRaw || n.ref)}</div>
                <div class="li-sub">${esc(hydrateNote(n).nom || (n.std && n.std.note) || 'Ouvrir cette note')}</div>
              </div>
            </div>`).join('')}
        </div>` : '';
    body.innerHTML = `
      <div class="dossier">
        <h2>${extra.empty ? 'La base locale est vide' : "Cette référence n'existe pas"}</h2>
        <p class="muted">${extra.empty
          ? "Importez d'abord un fichier Excel dans l'onglet Importer, puis relancez le contrôle."
          : "Aucun enregistrement correspondant dans la base locale. Les tirets, espaces et majuscules sont ignorés."}</p>
        ${suggestHtml}
        <ul class="muted" style="margin:10px 0 0;padding-left:18px">
          <li>Utilisez le numéro de référence (pas seulement le nom du contribuable).</li>
          <li>Réimportez le fichier Excel si le nombre de notes en base est à 0.</li>
        </ul>
      </div>
      <button class="btn btn-primary btn-block" type="button" data-view="${state.backView === 'notes' ? 'notes' : 'home'}">${state.backView === 'notes' ? 'Retour à la liste' : 'Nouveau contrôle'}</button>`;
    body.querySelectorAll('.list-item').forEach(el => {
      el.addEventListener('click', () => doSearch(el.dataset.ref));
    });
    return;
  }

  const std  = hydrateNote(rec);
  const st   = classifyNote(rec);

  banner.innerHTML = `
    <div class="verdict found">
      <div class="stamp">
        <div class="stamp-inner">
          <div class="stamp-kicker">Authentique</div>
          <div class="stamp-title">NOTE<br>TROUVÉE</div>
          <div class="stamp-ref">${esc(shown)}</div>
        </div>
      </div>
    </div>`;

  const attendu = (typeof std.montantAttendu === 'number') ? std.montantAttendu
                : (typeof std.montantDeclare === 'number') ? std.montantDeclare : null;
  if ((std.reste === null || std.reste === undefined || std.reste === '') &&
      attendu !== null && typeof std.montantPaye === 'number'){
    std.reste = Math.max(0, attendu - std.montantPaye);
  }

  const rows = [];
  rows.push({ k: STD_LABELS.ref, v: String(shown), money: false, big: false });
  Object.keys(STD_LABELS).forEach(k => {
    if (k === 'ref') return;
    const v = std[k];
    if (v === null || v === undefined || v === '') return;
    let val;
    if (MONEY_KEYS.includes(k)) val = fmtMoney(v, std.devise);
    else if (k === 'statut') val = st.label || frenchStatusLabel(v);
    else if (k === 'etape') val = frenchStepLabel(v);
    else val = String(v);
    rows.push({ k: STD_LABELS[k], v: val, money: MONEY_KEYS.includes(k), big: (k === 'reste') });
  });

  const extras = [];
  Object.keys(rec.fields || {}).forEach(h => {
    if (detectField(h)) return;
    const v = rec.fields[h];
    if (v === null || v === undefined || String(v).trim() === '') return;
    extras.push({ k: h, v: String(v) });
  });

  const htmlRows = arr => arr.map(r => `
      <div class="info-row">
        <div class="k">${esc(r.k)}</div>
        <div class="v ${r.money ? 'money' : ''} ${r.big ? 'big' : ''}">${esc(r.v)}</div>
      </div>`).join('');

  body.innerHTML = `
    <div class="dossier" style="text-align:center">
      <span class="chip ${st.level}">${esc(st.label)}</span>
    </div>

    <div class="dossier">
      <h2>Informations de la note</h2>
      <div style="margin-top:6px">${htmlRows(rows) || '<p class="muted">Aucune information exploitable.</p>'}</div>
    </div>

    ${extras.length ? `
    <div class="dossier">
      <h2>Autres informations</h2>
      <div style="margin-top:6px">${htmlRows(extras)}</div>
    </div>` : ''}

    <p class="muted" style="text-align:center;font-size:.78rem;margin-bottom:14px">
      Dernière mise à jour de cette note : ${esc(fmtDateTime(rec.updatedAt || Date.now()))}
    </p>

    <div style="display:grid;gap:10px">
      <button class="btn btn-ghost btn-block" type="button" id="btnCopyRef">Copier la référence</button>
      <button class="btn btn-primary btn-block" type="button" data-view="${state.backView === 'notes' ? 'notes' : 'home'}">${state.backView === 'notes' ? 'Retour à la liste' : 'Nouveau contrôle'}</button>
    </div>`;

  const copyBtn = $('#btnCopyRef');
  if (copyBtn){
    copyBtn.addEventListener('click', async () => {
      try {
        await navigator.clipboard.writeText(shown);
        toast('Référence copiée.');
      } catch(e){
        toast(shown);
      }
    });
  }
}

function findHeaderRow(rows){
  const max = Math.min(rows.length, 30);
  let best = null, bestScore = -1;
  for (let i = 0; i < max; i++){
    const raw = rows[i] || [];
    const headers = raw.map(h => String(h ?? '').trim());
    const filled = headers.filter(Boolean).length;
    if (filled < 2) continue;
    const map = headers.map(detectField);
    let refCol = map.indexOf('ref');
    let usedNoteAsRef = false;
    if (refCol < 0){
      const noteCol = map.indexOf('note');
      if (noteCol >= 0 && String(headers[noteCol]).length <= 80){
        refCol = noteCol;
        usedNoteAsRef = true;
      }
    }
    if (refCol < 0) continue;
    if (String(headers[refCol]).length > 80) continue;
    const mapped = map.filter(Boolean).length;
    let score = mapped * 10 + filled;
    const h = norm(headers[refCol]);
    if (h === 'reference number' || h === 'numero de reference') score += 80;
    else if (h.includes('reference')) score += 40;
    if (usedNoteAsRef) score -= 15;
    if (score > bestScore){
      bestScore = score;
      best = { headerIdx: i, headers, map, refCol, usedNoteAsRef, refHeader: headers[refCol] };
    }
  }
  return best;
}

function ingestRows(rows){
  const head = findHeaderRow(rows);
  if (!head) throw new Error("Aucune colonne « Reference Number » n'a été trouvée dans le fichier.");

  const { headerIdx, headers, map, refCol } = head;
  const records = [];
  const rejected = [];
  const seen = new Set();
  let duplicates = 0;

  for (let i = headerIdx + 1; i < rows.length; i++){
    const row = rows[i];
    if (!row || !row.some(c => String(c ?? '').trim() !== '')) continue;

    const rawRef = cellToStr(row[refCol]);
    if (!rawRef){
      rejected.push({ line: i + 1, reason: 'Numéro de référence manquant' });
      continue;
    }
    const key = compactRef(rawRef) || normRef(rawRef);
    if (!key){
      rejected.push({ line: i + 1, reason: 'Numéro de référence invalide' });
      continue;
    }
    if (seen.has(key)){ duplicates++; continue; }
    seen.add(key);

    const fields = {};
    const std = {};

    headers.forEach((h, j) => {
      const v = row[j];
      if (h) fields[h] = (v instanceof Date) ? v.toLocaleDateString('fr-FR') : (v === undefined || v === null ? '' : cellToStr(v));
      const k = map[j];
      if (k && !(k in std)) std[k] = v;
    });

    std.ref = rawRef;
    MONEY_KEYS.forEach(k => { if (k in std) std[k] = toNumber(std[k]); });
    ['dateEmission', 'datePaiement'].forEach(k => { if (k in std) std[k] = toDateStr(std[k]); });
    ['note','nom','nif','tel','adresse','type','periode','devise','statut','etape','agentEmission','agentValidation','ressort','observation']
      .forEach(k => { if (k in std) std[k] = cellToStr(std[k]); });
    if (std.devise && isFinite(Number(std.devise)) && Number(std.devise) > 20){
      const cur = Object.keys(fields).find(h => norm(h) === 'currency name');
      if (cur) std.devise = cellToStr(fields[cur]);
    }

    records.push({ ref: key, refRaw: rawRef, std, fields, updatedAt: Date.now() });
  }

  return { records, rejected, duplicates, refHeader: head.refHeader, usedNoteAsRef: !!head.usedNoteAsRef };
}

async function commitRecords(records, sourceLabel){
  const existing = new Set(await IDB.allKeys());
  let added = 0, updated = 0;
  records.forEach(r => existing.has(r.ref) ? updated++ : added++);

  await IDB.putNotes(records);
  invalidateNotesCache();

  const meta = {
    date: Date.now(),
    source: sourceLabel,
    added, updated,
    total: records.length,
    samples: records.slice(0, 6).map(r => r.refRaw)
  };
  await IDB.setMeta('lastImport', meta);
  return meta;
}

function setProgress(pct, text){
  $('#importProgressWrap').classList.remove('hidden');
  $('#importBar').style.width = Math.max(0, Math.min(100, pct)) + '%';
  if (text) $('#importStatus').textContent = text;
}
function hideProgress(){ $('#importProgressWrap').classList.add('hidden'); }

async function handleExcelFile(file){
  if (!isAdmin()){ toast("L'import est réservé à l'administrateur."); return; }
  if (!file) return;
  if (typeof XLSX === 'undefined'){
    setBusy(true, 'Chargement d’Excel…');
    try { await ensureVendors(); }
    catch(e){ setBusy(false); toast("Bibliothèque Excel indisponible. Vérifiez le réseau."); return; }
    setBusy(false);
  }
  if (typeof XLSX === 'undefined'){
    toast("Bibliothèque Excel indisponible. Ouvrez l'application une fois en ligne.");
    return;
  }
  $('#importReport').innerHTML = '';
  try {
    setProgress(5, 'Lecture du fichier…');
    const fileName = (file.name || '').toLowerCase();
    let wb;
    if (fileName.endsWith('.csv') || fileName.endsWith('.txt')){
      const text = await file.text();
      const first = (text.split(/\r?\n/).find(l => l.trim()) || '');
      const FS = (first.match(/;/g) || []).length > (first.match(/,/g) || []).length ? ';' : ',';
      wb = XLSX.read(text, { type: 'string', FS, cellDates: true });
    } else {
      const buf = await file.arrayBuffer();
      wb = XLSX.read(buf, { type: 'array', cellDates: true });
    }

    setProgress(25, 'Analyse du classeur…');

    setProgress(45, 'Recherche des colonnes…');
    let rows = null, sheetName = null;
    for (const name of wb.SheetNames){
      const matrix = XLSX.utils.sheet_to_json(wb.Sheets[name], { header: 1, blankrows: false, defval: '', raw: false });
      if (findHeaderRow(matrix)){ rows = matrix; sheetName = name; break; }
    }
    if (!rows) throw new Error("Aucune colonne « Reference Number » trouvée dans le classeur. Vérifiez la première feuille et le nom de la colonne.");

    setProgress(60, 'Traitement des lignes…');
    const { records, rejected, duplicates, refHeader, usedNoteAsRef } = ingestRows(rows);

    setProgress(85, 'Enregistrement dans la base locale…');
    const meta = await commitRecords(records, file.name);

    setProgress(100, 'Terminé');
    setTimeout(hideProgress, 900);

    renderReport({
      fileName: file.name,
      sheetName,
      total: records.length,
      added: meta.added,
      updated: meta.updated,
      duplicates,
      rejected,
      refHeader,
      usedNoteAsRef,
      samples: meta.samples || []
    });

    refreshHome();
    refreshImportMeta();
    toast(`Importation réussie : ${meta.added} ajout(s), ${meta.updated} mise(s) à jour.`);
    const sent = await silentSync({ push: true, pull: false, timeout: 20000 });
    if (sent.ok) toast('Notes envoyées au serveur. Les téléphones pourront les récupérer.');
    else if (!sent.offline) toast('Import local OK, mais envoi serveur échoué : ' + (sent.error || ''));
  } catch (err){
    console.error(err);
    hideProgress();
    $('#importReport').innerHTML = `
      <div class="dossier" style="border-color:var(--danger-line);background:var(--danger-bg);margin-top:14px">
        <h2 style="color:var(--danger)">Échec de l'importation</h2>
        <p class="muted" style="color:#7f1d1d">${esc(err.message || err)}</p>
      </div>`;
  }
}

function renderReport(r){
  const rows = r.rejected.slice(0, 40).map(x =>
    `<tr><td>${x.line}</td><td>${esc(x.reason)}</td></tr>`).join('');

  $('#importReport').innerHTML = `
    <div class="dossier" style="border-color:var(--ok-line);background:var(--ok-bg);margin-top:14px">
      <h2 style="color:var(--ok)">Importation terminée</h2>
      <p class="muted">${esc(r.fileName)}${r.sheetName ? ' — feuille « ' + esc(r.sheetName) + ' »' : ''}</p>
      <p class="muted">Colonne identifiant : <b>${esc(r.refHeader || 'Reference Number')}</b>${r.usedNoteAsRef ? ' (numéro de note utilisé comme référence)' : ''}</p>
      ${r.samples && r.samples.length ? `<p class="muted">Exemples importés : ${r.samples.map(s => '<b>' + esc(s) + '</b>').join(' · ')}</p>` : ''}
      <div class="report-grid">
        <div class="report-box rb-green"><div class="n">${r.added}</div><div class="t">Notes ajoutées</div></div>
        <div class="report-box rb-blue"><div class="n">${r.updated}</div><div class="t">Notes mises à jour</div></div>
        <div class="report-box rb-amber"><div class="n">${r.duplicates}</div><div class="t">Doublons ignorés</div></div>
        <div class="report-box rb-red"><div class="n">${r.rejected.length}</div><div class="t">Lignes rejetées</div></div>
      </div>
      <p class="muted" style="margin-top:12px">Total de lignes valides traitées : <b>${r.total}</b></p>
      <button class="btn btn-primary btn-block" type="button" data-view="notes" style="margin-top:12px">Consulter les notes importées</button>
    </div>
    ${r.rejected.length ? `
      <div class="dossier">
        <h2>Lignes rejetées</h2>
        <table class="rejects">
          <thead><tr><th>Ligne</th><th>Motif</th></tr></thead>
          <tbody>${rows}</tbody>
        </table>
        ${r.rejected.length > 40 ? `<p class="muted" style="margin-top:8px">… et ${r.rejected.length - 40} autre(s).</p>` : ''}
      </div>` : ''}`;
}

function renderListItems(items, container){
  if (!items.length){
    container.innerHTML = `<div class="empty"><div class="big">—</div><p>Aucun contrôle pour le moment.</p></div>`;
    return;
  }
  container.innerHTML = items.map(it => `
    <div class="list-item" data-ref="${esc(it.ref)}">
      <div class="li-ico ${it.found ? 'ok' : 'bad'}">${it.found ? '✓' : '✕'}</div>
      <div class="li-main">
        <div class="li-title">${esc(it.raw || it.ref)}</div>
        <div class="li-sub">${it.found ? 'Note trouvée' : 'Note introuvable'} · ${esc(fmtDateTime(it.at))}${it.userName ? ' · ' + esc(it.userName) : ''}</div>
      </div>
    </div>`).join('');

  container.querySelectorAll('.list-item').forEach(el => {
    el.addEventListener('click', () => doSearch(el.dataset.ref));
  });
}

async function renderHistory(){
  let items = await IDB.getHistory(150);
  if (state.user && !isAdmin()) items = items.filter(it => it.userId === state.user.id);
  renderListItems(items, $('#historyList'));
}

function prepareResultBack(){
  const v = state.backView === 'notes' ? 'notes' : 'home';
  const btn = $('#resultBack');
  const lab = $('#resultBackLabel');
  if (btn) btn.dataset.view = v;
  if (lab) lab.textContent = v === 'notes' ? 'Retour à la liste' : 'Nouveau contrôle';
}

function invalidateNotesCache(){
  state.notesCache = null;
  state.notesIndex = null;
  state.notesIndexGen++;
  state.notesShown = 30;
  if (state._notesObs){ state._notesObs.disconnect(); state._notesObs = null; }
  IDB.setMeta('notesIndexLite', null).catch(() => {});
}

function noteSortDate(n, field){
  const std = n.std || {};
  if (field === 'emission') return parseAnyDate(std.dateEmission);
  if (field === 'paiement') return parseAnyDate(std.datePaiement);
  if (field === 'import') return n.updatedAt ? new Date(n.updatedAt) : null;
  return parseAnyDate(std.dateEmission) || parseAnyDate(std.datePaiement) || (n.updatedAt ? new Date(n.updatedAt) : null);
}
function monthKeyFromDate(d){
  if (!d || isNaN(d.getTime())) return 'unknown';
  return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
}
function monthLabel(key){
  if (key === 'unknown') return 'Mois non précisé';
  const [y, m] = key.split('-');
  return FR_MONTHS[+m - 1] + ' ' + y;
}
function tsOf(d){
  return d && !isNaN(d.getTime()) ? d.getTime() : 0;
}
function summarizeNote(n){
  const st = classifyNote(n);
  const std = st.std || {};
  const tsEm = tsOf(parseAnyDate(std.dateEmission));
  const tsPay = tsOf(parseAnyDate(std.datePaiement));
  const tsImp = Number(n.updatedAt) || 0;
  const amtVal = std.montantAttendu ?? std.montantDeclare ?? std.montantPaye;
  const key = st.key === 'unpaid' ? 'open' : st.key;
  return {
    ref: n.ref,
    refRaw: n.refRaw || n.ref,
    nom: std.nom || '',
    note: std.note || '',
    amt: fmtMoney(amtVal, std.devise),
    key,
    notice: !!st.notice,
    hasUnpaid: !!st.hasUnpaid,
    level: st.notice && key !== 'paid' ? 'notice' : st.level,
    label: st.notice && key !== 'paid' ? (st.label + ' · mise en demeure') : st.label,
    tsEm, tsPay, tsImp,
    tsAuto: tsEm || tsPay || tsImp,
    q: [
      n.ref, n.refRaw, std.note, std.nom, std.tel, std.nif, std.statut,
      std.ressort, std.type, std.agentEmission, std.agentValidation, std.periode
    ].map(x => String(x || '').toLowerCase()).join(' '),
    cRef: compactRef(n.ref),
    cRaw: compactRef(n.refRaw),
    cNote: compactRef(std.note)
  };
}
function rowTs(row, field){
  if (field === 'emission') return row.tsEm || 0;
  if (field === 'paiement') return row.tsPay || 0;
  if (field === 'import') return row.tsImp || 0;
  return row.tsAuto || 0;
}
function rowMonth(row, field){
  const ts = rowTs(row, field);
  return ts ? monthKeyFromDate(new Date(ts)) : 'unknown';
}
function noteRowMatchesStatus(row, key){
  if (!key || key === 'all') return true;
  if (key === 'notice') return !!row.notice;
  if (key === 'unpaid') return !!row.hasUnpaid;
  return row.key === key;
}
function noteRowMatchesQuery(row){
  const raw = (state.notesQuery || '').trim();
  if (!raw) return true;
  const compact = compactRef(raw);
  if (compact && ((row.cRef && row.cRef.includes(compact)) || (row.cRaw && row.cRaw.includes(compact)) || (row.cNote && row.cNote.includes(compact))))
    return true;
  return row.q.includes(raw.toLowerCase());
}
function paintHomeTally(rows){
  const tally = { paid: 0, open: 0, partial: 0, unpaid: 0, notice: 0, cancelled: 0, other: 0 };
  const months = {};
  let noticeN = 0, unpaidN = 0;
  (rows || []).forEach(row => {
    const k = row.key || 'other';
    tally[k] = (tally[k] || 0) + 1;
    if (row.notice) noticeN++;
    if (row.hasUnpaid) unpaidN++;
    const mk = rowMonth(row, 'auto');
    months[mk] = (months[mk] || 0) + 1;
  });
  tally.notice = noticeN;
  tally.unpaid = unpaidN;
  $('#statPaid').textContent = tally.paid.toLocaleString('fr-FR');
  $('#statOpen').textContent = tally.open.toLocaleString('fr-FR');
  $('#statPartial').textContent = tally.partial.toLocaleString('fr-FR');
  $('#statUnpaid').textContent = tally.unpaid.toLocaleString('fr-FR');
  $('#statNotice').textContent = tally.notice.toLocaleString('fr-FR');
  renderStatusChart([
    { v: tally.paid, color: '#1f8a70', label: 'Payées' },
    { v: tally.open, color: '#2a7a96', label: 'Ouvertes' },
    { v: tally.partial, color: '#c4841d', label: 'Partielles' },
    { v: tally.unpaid, color: '#c44b4b', label: 'Impayées' },
    { v: tally.notice, color: '#159895', label: 'Mises en demeure' },
    { v: tally.cancelled + tally.other, color: '#5b7380', label: 'Autres' }
  ]);
  renderMonthBars(months);
}
async function buildNotesIndex(){
  const gen = state.notesIndexGen;
  const keys = await IDB.allKeys();
  const index = [];
  const BATCH = 80;
  for (let i = 0; i < keys.length; i += BATCH){
    if (gen !== state.notesIndexGen) return null;
    try {
      const recs = await IDB.getNotesByKeys(keys.slice(i, i + BATCH));
      recs.forEach(rec => index.push(summarizeNote(rec)));
    } catch (err){ console.error(err); }
    if (gen !== state.notesIndexGen) return null;
    state.notesIndex = index;
    const more = i + BATCH < keys.length;
    if ($('#view-notes') && $('#view-notes').classList.contains('active'))
      paintNotesFromIndex(index, more);
    if (more) await new Promise(r => setTimeout(r, 0));
  }
  if (gen !== state.notesIndexGen) return null;
  state.notesIndex = index;
  IDB.setMeta('notesIndexLite', { at: Date.now(), n: index.length, rows: index }).catch(() => {});
  if ($('#view-notes') && $('#view-notes').classList.contains('active'))
    paintNotesFromIndex(index, false);
  if ($('#view-home') && $('#view-home').classList.contains('active'))
    paintHomeTally(index);
  return index;
}
async function ensureNotesIndex(){
  if (state.notesIndex && !state.notesIndexPromise) return state.notesIndex;
  if (!state.notesIndex){
    try {
      const saved = await IDB.getMeta('notesIndexLite');
      if (saved && Array.isArray(saved.rows) && saved.rows.length) state.notesIndex = saved.rows;
    } catch (e){}
  }
  const gen = state.notesIndexGen;
  if (!state.notesIndexPromise){
    const p = buildNotesIndex().finally(() => {
      if (state.notesIndexPromise === p) state.notesIndexPromise = null;
    });
    state.notesIndexPromise = p;
  }
  if (state.notesIndex) return state.notesIndex;
  const idx = await state.notesIndexPromise;
  if (state.notesIndexGen !== gen) return ensureNotesIndex();
  return idx || state.notesIndex || [];
}
function noteLineHtmlFromIndex(row){
  const ts = rowTs(row, state.notesDateField);
  const when = ts ? new Date(ts).toLocaleDateString('fr-FR') : '—';
  return `<button type="button" class="note-line" data-ref="${esc(row.refRaw || row.ref)}">
    <div class="ref">${esc(row.refRaw || row.ref)}</div>
    <div class="amt">${esc(row.amt || '—')}</div>
    <div class="who">${esc(row.nom || row.note || 'Sans nom')} · <span class="chip ${row.level}" style="padding:2px 8px;font-size:.65rem;vertical-align:middle">${esc(row.label)}</span></div>
    <div class="when">${esc(when)}</div>
  </button>`;
}
function paintNotesFromIndex(all, indexing){
  const field = state.notesDateField;
  const rail = $('#monthRail');
  const srail = $('#statusRail');
  const list = $('#notesList');
  if (!list) return;

  if (!all.length){
    if (rail) rail.innerHTML = '';
    if (srail) srail.innerHTML = '';
    $('#notesCountLine').textContent = indexing ? 'Préparation de la liste…' : 'Aucune note importée pour le moment.';
    if (indexing){
      $('#notesSkeleton').classList.remove('hidden');
      list.innerHTML = '';
      return;
    }
    $('#notesSkeleton').classList.add('hidden');
    list.innerHTML = `<div class="empty"><div class="big">—</div><p>Importez un fichier Excel pour voir la liste ici.</p>
      <button class="btn btn-primary" type="button" data-view="import" style="margin:12px auto 0;display:inline-flex">Importer</button></div>`;
    return;
  }
  $('#notesSkeleton').classList.add('hidden');

  const statusCounts = { all: 0, paid: 0, open: 0, partial: 0, unpaid: 0, notice: 0, cancelled: 0 };
  const monthCounts = new Map();
  const queried = [];
  for (let i = 0; i < all.length; i++){
    const row = all[i];
    if (!noteRowMatchesQuery(row)) continue;
    statusCounts.all++;
    if (statusCounts[row.key] != null) statusCounts[row.key]++;
    if (row.notice) statusCounts.notice++;
    if (row.hasUnpaid) statusCounts.unpaid++;
    if (!noteRowMatchesStatus(row, state.notesStatus)) continue;
    queried.push(row);
    const mk = rowMonth(row, field);
    monthCounts.set(mk, (monthCounts.get(mk) || 0) + 1);
  }
  queried.sort((a, b) => rowTs(b, field) - rowTs(a, field));

  const monthKeys = [...monthCounts.keys()].sort((a, b) => {
    if (a === 'unknown') return 1;
    if (b === 'unknown') return -1;
    return b.localeCompare(a);
  });
  const filtered = state.notesMonth === 'all' ? queried : queried.filter(r => rowMonth(r, field) === state.notesMonth);
  const shown = Math.min(10, filtered.length);
  const slice = filtered.slice(0, shown);
  const indexingTxt = indexing ? ` · préparation ${all.length.toLocaleString('fr-FR')}…` : '';
  $('#notesCountLine').textContent = `${filtered.length.toLocaleString('fr-FR')} note(s) · ${shown.toLocaleString('fr-FR')} affichée(s) sur ${all.length.toLocaleString('fr-FR')}${indexingTxt}`;

  if (srail){
    srail.innerHTML = STATUS_FILTERS.map(([k, lab]) =>
      `<button type="button" class="month-chip ${state.notesStatus === k ? 'on' : ''}" data-status="${k}">${lab} <span class="n">${statusCounts[k] || 0}</span></button>`
    ).join('');
  }
  if (rail){
    rail.innerHTML = `<button type="button" class="month-chip ${state.notesMonth === 'all' ? 'on' : ''}" data-month="all">Tous <span class="n">${queried.length}</span></button>` +
      monthKeys.map(k => `<button type="button" class="month-chip ${state.notesMonth === k ? 'on' : ''}" data-month="${esc(k)}">${esc(monthLabel(k))} <span class="n">${monthCounts.get(k)}</span></button>`).join('');
  }

  if (!queried.length){
    list.innerHTML = `<div class="empty"><p>Aucune note ne correspond à « ${esc(state.notesQuery)} ».</p></div>`;
    return;
  }
  if (!filtered.length){
    list.innerHTML = `<div class="empty"><p>Aucune note pour ce mois.</p></div>`;
    return;
  }

  const groups = [];
  const gmap = new Map();
  slice.forEach(row => {
    const k = rowMonth(row, field);
    if (!gmap.has(k)){
      const g = { key: k, label: monthLabel(k), items: [] };
      gmap.set(k, g);
      groups.push(g);
    }
    gmap.get(k).items.push(row);
  });
  list.innerHTML = groups.map(g => `<section class="month-block">
      <div class="month-head"><span>${esc(g.label)}</span><span class="n">${monthCounts.get(g.key) || g.items.length} note(s)</span></div>
      ${g.items.map(noteLineHtmlFromIndex).join('')}
    </section>`).join('');
}
function openNoteFromList(raw){
  state.backView = 'notes';
  prepareResultBack();
  doSearchFromList(raw);
}
async function doSearchFromList(raw){
  setBusy(true, 'Ouverture…');
  try {
    const found = await findNote(raw);
    renderResult(found.rec || null, raw, { suggestions: found.suggestions || [], empty: !!found.empty });
    go('result');
  } finally { setBusy(false); }
}

async function renderNotesPage(){
  const qEl = $('#notesQuery');
  const fEl = $('#notesDateField');
  if (qEl && qEl.value !== state.notesQuery) qEl.value = state.notesQuery;
  if (fEl && fEl.value !== state.notesDateField) fEl.value = state.notesDateField;

  if (state.notesIndex){
    paintNotesFromIndex(state.notesIndex, !!state.notesIndexPromise);
    if (state.notesIndexPromise) await state.notesIndexPromise;
    return;
  }
  $('#notesCountLine').textContent = 'Préparation de la liste…';
  $('#notesSkeleton').classList.remove('hidden');
  $('#notesList').innerHTML = '';
  const idx = await ensureNotesIndex();
  paintNotesFromIndex(idx || [], false);
}

async function refreshHome(){
  try {
    $('#dashSkeleton').classList.remove('hidden');
    $('#dashBoard').classList.add('hidden');
    $('#homeHistorySkel').classList.remove('hidden');

    const hour = new Date().getHours();
    if ($('#helloLine')) $('#helloLine').textContent = hour < 12 ? 'Bonjour' : (hour < 18 ? 'Bon après-midi' : 'Bonsoir');

    const total = await IDB.count();
    $('#statNotes').textContent = total.toLocaleString('fr-FR');
    $('#emptyWarn').classList.toggle('hidden', total > 0);
    $('#btnOpenNotes').classList.toggle('hidden', total === 0);
    setBadge('#tabNotesBadge', total);

    const sampleBox = $('#refSamples');
    if (total > 0){
      const last = await IDB.getMeta('lastImport');
      let samples = (last && last.samples) || [];
      if (!samples.length){
        const keys = await IDB.allKeys();
        samples = keys.slice(0, 5);
      }
      sampleBox.classList.remove('hidden');
      sampleBox.innerHTML = 'Exemples : ' +
        samples.slice(0, 5).map(s => `<button type="button" class="sample-ref" data-ref="${esc(s)}">${esc(s)}</button>`).join('');
      sampleBox.querySelectorAll('.sample-ref').forEach(btn => {
        btn.addEventListener('click', () => {
          $('#refInput').value = btn.dataset.ref;
          $('#clearBtn').classList.add('show');
          doSearch(btn.dataset.ref);
        });
      });
    } else {
      sampleBox.classList.add('hidden');
      sampleBox.innerHTML = '';
    }

    const hist = await IDB.getHistory(8);
    const checks = await IDB.countHistory();
    setBadge('#tabHistBadge', checks);

    const last = await IDB.getMeta('lastImport');
    $('#metaLine').textContent = last
      ? `Dernière importation : ${fmtDateTime(last.date)}`
      : 'Aucune donnée importée pour le moment.';

    if (total){
      if (state.notesIndex) paintHomeTally(state.notesIndex);
      else {
        ['#statPaid','#statOpen','#statPartial','#statUnpaid','#statNotice'].forEach(id => {
          const el = $(id); if (el) el.textContent = '…';
        });
        ensureNotesIndex().then(idx => {
          if ($('#view-home') && $('#view-home').classList.contains('active')) paintHomeTally(idx || []);
        });
      }
    } else {
      paintHomeTally([]);
    }

    renderListItems(hist.slice(0, 4), $('#homeHistory'));
    $('#dashSkeleton').classList.add('hidden');
    $('#dashBoard').classList.remove('hidden');
    $('#homeHistorySkel').classList.add('hidden');
  } catch(e){ console.error(e); $('#dashSkeleton').classList.add('hidden'); $('#dashBoard').classList.remove('hidden'); }
}

async function refreshImportMeta(){
  const last = await IDB.getMeta('lastImport');
  $('#lastImportLine').innerHTML = last
    ? `${esc(fmtDateTime(last.date))}<br>
       ${esc(last.source || '—')}<br>
       ${last.added} ajout(s) · ${last.updated} mise(s) à jour`
    : 'Aucune importation enregistrée.';
}

async function renderSettings(){
  if ($('#serverUrl')) $('#serverUrl').value = state.cfg.serverUrl || '';
  const total = await IDB.count();
  const u = state.user;
  if ($('#sessionLine')){
    $('#sessionLine').textContent = u
      ? `${u.name} · ${u.role === 'admin' ? 'Administrateur' : 'Inspecteur de terrain'}${u.matricule ? ' · ' + u.matricule : ''}`
      : 'Non connecté';
  }
  $('#aboutLine').textContent = `Version 1.2 — ${total.toLocaleString('fr-FR')} notes en local · ${u ? u.name : 'hors session'}.`;
  const off = $('#offlineLine');
  if (off){
    const sw = !!(navigator.serviceWorker && navigator.serviceWorker.controller);
    const secure = window.isSecureContext;
    if (sw){
      off.textContent = 'Mode hors ligne actif : les notes restent consultables sans réseau.';
    } else {
      off.textContent = 'Les notes déjà téléchargées restent sur cet appareil. Pour les ouvrir sans Wi‑Fi après fermeture de Chrome, utilisez « Enregistrer une copie hors-ligne ».';
    }
  }
}

async function renderAdmin(){
  if (!isAdmin()) return;
  const list = (await IDB.allInspectors()).sort((a, b) => String(a.name).localeCompare(String(b.name), 'fr'));
  if (!list.length){
    $('#inspList').innerHTML = `<div class="empty"><p>Aucun inspecteur enregistré.</p></div>`;
    return;
  }
  $('#inspList').innerHTML = list.map(u => `
    <button class="insp-card" type="button" data-id="${esc(u.id)}">
      <div class="insp-ava ${u.active ? '' : 'off'}">${esc((u.name || '?').charAt(0).toUpperCase())}</div>
      <div class="insp-main">
        <b>${esc(u.name)}</b>
        <small>${esc(u.matricule || '—')} · ${u.role === 'admin' ? 'Admin' : 'Inspecteur'} · ${u.active ? 'Actif' : 'Inactif'}${u.ressort ? ' · ' + esc(u.ressort) : ''}</small>
      </div>
    </button>`).join('');
  $('#inspList').querySelectorAll('.insp-card').forEach(btn => {
    btn.addEventListener('click', () => openInspModal(btn.dataset.id));
  });
}

function resetInspForm(){
  $('#inspId').value = '';
  $('#inspName').value = '';
  $('#inspMat').value = '';
  $('#inspRessort').value = '';
  $('#inspRole').value = 'inspector';
  $('#inspPin').value = '';
  $('#inspPin').placeholder = '4 à 6 chiffres';
  $('#inspActive').checked = true;
  $('#inspDelete').classList.add('hidden');
  $('#inspModalTitle').textContent = 'Nouvel inspecteur';
}
async function openInspModal(id){
  resetInspForm();
  if (id){
    const u = await IDB.getInspector(id);
    if (!u) return;
    $('#inspId').value = u.id;
    $('#inspName').value = u.name || '';
    $('#inspMat').value = u.matricule || '';
    $('#inspRessort').value = u.ressort || '';
    $('#inspRole').value = u.role || 'inspector';
    $('#inspActive').checked = !!u.active;
    $('#inspPin').placeholder = 'Laisser vide pour conserver le PIN';
    $('#inspDelete').classList.remove('hidden');
    $('#inspModalTitle').textContent = 'Fiche inspecteur';
  }
  $('#inspModal').classList.remove('hidden');
}
async function saveInspector(){
  const name = $('#inspName').value.trim();
  const matricule = $('#inspMat').value.trim();
  const pin = $('#inspPin').value.trim();
  const id = $('#inspId').value || ('u_' + Date.now());
  if (!name){ toast('Le nom est obligatoire.'); return; }
  const existing = $('#inspId').value ? await IDB.getInspector(id) : null;
  if (!existing && !/^\d{4,6}$/.test(pin)){
    toast('Un PIN de 4 à 6 chiffres est obligatoire.');
    return;
  }
  if (pin && !/^\d{4,6}$/.test(pin)){
    toast('PIN invalide (4 à 6 chiffres).');
    return;
  }
  if (pin && await pinInUse(pin, id)){
    toast('Ce PIN est déjà attribué.');
    return;
  }
  const rec = existing || { id, createdAt: Date.now() };
  rec.name = name;
  rec.matricule = matricule;
  rec.ressort = $('#inspRessort').value.trim();
  rec.role = $('#inspRole').value === 'admin' ? 'admin' : 'inspector';
  rec.active = $('#inspActive').checked;
  rec.updatedAt = Date.now();
  if (pin){
    rec.salt = randomSalt();
    rec.pinHash = await hashPin(pin, rec.salt);
    rec.mustChangePin = false;
  }
  await IDB.putInspector(rec);
  if (state.user && state.user.id === rec.id) state.user = rec;
  $('#inspModal').classList.add('hidden');
  toast('Inspecteur enregistré.');
  renderAdmin();
  silentSync({ push: true, pull: false });
}
async function deactivateInspector(){
  const id = $('#inspId').value;
  if (!id) return;
  if (state.user && state.user.id === id){
    toast('Vous ne pouvez pas retirer votre propre compte.');
    return;
  }
  const all = await IDB.allInspectors();
  const target = all.find(u => u.id === id);
  const admins = all.filter(u => u.role === 'admin' && u.active && u.id !== id);
  if (target && target.role === 'admin' && !admins.length){
    toast('Impossible de retirer le dernier administrateur.');
    return;
  }
  if (target){
    target.active = false;
    target.updatedAt = Date.now();
    await IDB.putInspector(target);
  }
  $('#inspModal').classList.add('hidden');
  toast('Compte désactivé.');
  renderAdmin();
  silentSync({ push: true, pull: false });
}

async function syncFromServer(){
  const st = $('#syncStatus');
  if (st) st.textContent = 'Connexion au serveur…';
  setBusy(true, 'Téléchargement des notes…');
  const r = await silentSync({
    pull: true,
    push: false,
    timeout: 25000,
    onProgress: (p, m, d) => {
      if (st) st.textContent = d ? (m + ' — ' + d) : m;
      setBusy(true, d || m);
    }
  });
  setBusy(false);
  if (!r.ok){
    const msg = r.offline ? 'Hors ligne.' : ('Échec : ' + (r.error || ''));
    if (st) st.textContent = msg;
    toast(msg);
    return r;
  }
  await refreshHome();
  await refreshImportMeta();
  const total = await IDB.count();
  const msg = total
    ? `Synchronisé : ${total.toLocaleString('fr-FR')} note(s) sur cet appareil.`
    : 'Serveur joignable, mais aucune note n’y a encore été envoyée. Sur le PC admin, réimportez ou cliquez « Envoyer la base locale ».';
  if (st) st.textContent = msg;
  toast(msg);
  return r;
}

async function ingestOfflinePack(){
  const pack = window.__NPC_PACK;
  if (!pack || !Array.isArray(pack.notes) || !pack.notes.length) return 0;
  await IDB.putNotes(pack.notes);
  if (Array.isArray(pack.inspectors)){
    for (const u of pack.inspectors){
      if (u && u.id) await IDB.putInspector(u);
    }
  }
  invalidateNotesCache();
  return pack.notes.length;
}

async function saveOfflineCopy(){
  const total = await IDB.count();
  if (!total){
    toast('Aucune note à enregistrer. Récupérez d’abord les notes du serveur.');
    return;
  }
  setBusy(true, 'Préparation de la copie hors-ligne…');
  try {
    const notes = [];
    const keys = await IDB.allKeys();
    for (let i = 0; i < keys.length; i += 80){
      const recs = await IDB.getNotesByKeys(keys.slice(i, i + 80));
      recs.forEach(n => notes.push({
        ref: n.ref,
        refRaw: n.refRaw || n.ref,
        std: n.std && typeof n.std === 'object' ? n.std : {},
        fields: {},
        updatedAt: n.updatedAt || Date.now()
      }));
      setBusy(true, 'Copie ' + Math.min(i + 80, keys.length).toLocaleString('fr-FR') + ' / ' + keys.length.toLocaleString('fr-FR'));
    }
    const inspectors = await IDB.allInspectors();
    let page = '';
    try {
      const res = await fetch(location.pathname.replace(/[^/]+$/, '') + 'index.php', { cache: 'no-store' });
      page = await res.text();
    } catch (e) {
      page = '<!DOCTYPE html>' + document.documentElement.outerHTML;
    }
    const inject = '<script>window.__NPC_PACK=' + JSON.stringify({ notes, inspectors, at: Date.now() }) + '<\/script>\n';
    if (/<\/body>/i.test(page)) page = page.replace(/<\/body>/i, inject + '</body>');
    else page += inject;
    const blob = new Blob([page], { type: 'text/html;charset=utf-8' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'DGIPT-hors-ligne.html';
    document.body.appendChild(a);
    a.click();
    a.remove();
    setTimeout(() => URL.revokeObjectURL(a.href), 2000);
    toast('Fichier enregistré dans Téléchargements. Ouvrez-le sans réseau.');
  } catch (err){
    console.error(err);
    toast('Impossible de créer la copie hors-ligne.');
  } finally { setBusy(false); }
}

async function pushToServer(){
  const st = $('#syncStatus');
  if (st) st.textContent = 'Envoi des notes…';
  const r = await silentSync({ pull: false, push: true, timeout: 20000 });
  if (!r.ok){
    const msg = r.offline ? 'Hors ligne.' : ('Échec : ' + (r.error || ''));
    if (st) st.textContent = msg;
    toast(msg);
    return r;
  }
  if (st) st.textContent = 'Base locale envoyée au serveur.';
  toast('Les téléphones pourront récupérer les notes.');
  return r;
}

const REF_SCAN_KEYS = ['ref','reference','referencenumber','referenceno','reference_number','npref','numref','coderef','code','numero','id','doc','document','note','notenumber','np'];

function looksLikeUrl(s){
  return /^(https?:\/\/|www\.)/i.test(s) || (s.includes('://') && s.length > 12);
}
function looksLikeRef(s){
  const t = String(s || '').trim();
  if (!t || t.length > 80) return false;
  if (looksLikeUrl(t) || t.includes('{') || t.includes('<')) return false;
  const c = compactRef(t);
  if (c.length < 6 || c.length > 32) return false;
  if (!/\d/.test(c)) return false;
  if (/^(HTTP|HTTPS|WWW|HTML|NULL|TRUE|FALSE|INDEX|PHP|COM|ORG|VERIFY|LOGIN|ADMIN|HOME|PAGE)$/.test(c)) return false;
  return true;
}
function walkScanJson(v, out, depth){
  if (depth > 4 || v == null) return;
  if (typeof v === 'string' || typeof v === 'number'){ out.push(String(v)); return; }
  if (Array.isArray(v)){ v.slice(0, 20).forEach(x => walkScanJson(x, out, depth + 1)); return; }
  if (typeof v === 'object'){
    Object.keys(v).forEach(k => {
      const nk = norm(k).replace(/\s+/g, '');
      const val = v[k];
      if (REF_SCAN_KEYS.some(x => nk.includes(x) || x.includes(nk))) out.unshift(val);
      walkScanJson(val, out, depth + 1);
    });
  }
}
function extractRefCandidates(text){
  const t = String(text || '').trim();
  const out = [];
  const add = v => {
    const s = String(v == null ? '' : v).trim();
    if (!s || s.length > 240) return;
    out.push(s);
  };
  add(t);

  try { walkScanJson(JSON.parse(t), out, 0); } catch(e){}

  const qStr = t.includes('?') ? t.slice(t.indexOf('?') + 1).split('#')[0] : '';
  if (qStr){
    try {
      new URLSearchParams(qStr).forEach((v, k) => {
        const nk = norm(k).replace(/\s+/g, '');
        if (REF_SCAN_KEYS.some(x => nk.includes(x))) out.unshift(v);
        else add(v);
      });
    } catch(e){}
  }
  try {
    const u = new URL(t.startsWith('www.') ? 'http://' + t : t);
    u.searchParams.forEach((v, k) => {
      const nk = norm(k).replace(/\s+/g, '');
      if (REF_SCAN_KEYS.some(x => nk.includes(x))) out.unshift(v);
      else add(v);
    });
    u.pathname.split('/').forEach(p => add(decodeURIComponent(p || '')));
    if (u.hash) add(u.hash.replace(/^#/, ''));
  } catch(e){}

  String(t).split(/[\n\r;|&]+/).forEach(line => {
    const m = line.match(/^\s*([^:=]{1,40})\s*[:=]\s*(.+)\s*$/);
    if (m){
      const nk = norm(m[1]).replace(/\s+/g, '');
      if (REF_SCAN_KEYS.some(x => nk.includes(x))) out.unshift(m[2]);
      else add(m[2]);
    } else add(line);
  });

  const xml = t.matchAll(/<([^>]+)>([^<]{3,80})<\/\1>/gi);
  for (const m of xml){
    const nk = norm(m[1]).replace(/\s+/g, '');
    if (REF_SCAN_KEYS.some(x => nk.includes(x))) out.unshift(m[2]);
    else add(m[2]);
  }

  const styled = t.match(/[A-Z]{1,8}[-/_. ]?\d{2,4}[-/_. ]?\d{3,}/gi) || [];
  styled.forEach(add);

  return uniqueStrings(out);
}
function scoreScanCandidate(s){
  let n = 0;
  if (looksLikeRef(s)) n += 8;
  if (/[A-Z]{1,6}[-/ ]?\d{4}[-/ ]?\d{3,}/i.test(s)) n += 10;
  if (looksLikeUrl(s)) n -= 12;
  if (/[{}<>=]/.test(s)) n -= 6;
  if (/^\d{8,14}$/.test(compactRef(s))) n -= 3;
  const len = compactRef(s).length;
  if (len >= 8 && len <= 22) n += 3;
  return n;
}
async function matchNotesInText(raw){
  const texts = Array.isArray(raw) ? raw : [raw];
  const compactAll = texts.map(t => compactRef(t)).filter(Boolean);
  if (!compactAll.length) return [];
  const hits = [];
  if (!state.notesIndex){
    try {
      const saved = await IDB.getMeta('notesIndexLite');
      if (saved && Array.isArray(saved.rows)) state.notesIndex = saved.rows;
    } catch (e){}
  }
  if (state.notesIndex){
    for (const row of state.notesIndex){
      const keys = [row.cRef, row.cNote, row.cRaw].filter(k => k && k.length >= 5);
      const ok = keys.some(k => compactAll.some(scan => scan === k || (k.length >= 6 && scan.includes(k))));
      if (!ok) continue;
      const rec = await IDB.getNote(row.ref);
      if (rec) hits.push(rec);
    }
  }
  hits.sort((a, b) => noteCompact(b).length - noteCompact(a).length);
  return hits;
}
function showScanPicks(items, raw){
  const box = $('#scanPick');
  if (!box) return;
  box.classList.remove('hidden');
  box.innerHTML = items.map((it, i) =>
    `<button type="button" class="btn" data-i="${i}">${esc(it.label)}</button>`
  ).join('');
  box.querySelectorAll('button').forEach(btn => {
    btn.addEventListener('click', () => {
      const it = items[Number(btn.dataset.i)];
      if (it && it.value) applyScanValue(it.value, { skipResolve: true });
    });
  });
  $('#scanHint').textContent = raw && raw !== items[0].value
    ? 'Plusieurs valeurs lues. Choisissez la référence de la note.'
    : 'Choisissez la référence à contrôler.';
}
async function resolveScanText(rawList){
  const texts = uniqueStrings((Array.isArray(rawList) ? rawList : [rawList]).map(t => String(t || '').trim()).filter(Boolean));
  if (!texts.length) return { value: '', picks: [] };

  const hits = await matchNotesInText(texts);
  if (hits.length === 1){
    const v = hits[0].refRaw || hits[0].ref;
    return { value: v, rec: hits[0], picks: [] };
  }
  if (hits.length > 1){
    return {
      value: '',
      picks: hits.slice(0, 8).map(n => ({
        value: n.refRaw || n.ref,
        label: (n.refRaw || n.ref) + (n.std && n.std.nom ? ' · ' + n.std.nom : '')
      }))
    };
  }

  const cands = uniqueStrings(texts.flatMap(extractRefCandidates))
    .filter(s => !looksLikeUrl(s))
    .sort((a, b) => scoreScanCandidate(b) - scoreScanCandidate(a));
  const best = cands.find(looksLikeRef) || cands[0] || texts[0];
  const others = cands.filter(c => c !== best && looksLikeRef(c)).slice(0, 5);
  if (others.length){
    return {
      value: '',
      picks: [{ value: best, label: best }, ...others.map(v => ({ value: v, label: v }))]
    };
  }
  return { value: best, picks: [] };
}

function cleanScan(text){
  const t = String(text || '').trim();
  const cands = extractRefCandidates(t);
  return cands.find(looksLikeRef) || cands[0] || t;
}

function isMobileScan(){
  return /Android|iPhone|iPad|iPod/i.test(navigator.userAgent || '') || window.matchMedia('(pointer:coarse)').matches;
}
function canUseLiveCamera(){
  return !!(window.isSecureContext && navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
}
function scanConfig(mode){
  const box = (vw, vh) => {
    const side = Math.max(140, Math.floor(Math.min(vw || 280, vh || 280) * 0.72));
    return mode === 'qr' ? { width: side, height: side } : { width: side, height: Math.max(90, Math.floor(side * 0.42)) };
  };
  const cfg = { fps: 8, qrbox: box, aspectRatio: 1.333, disableFlip: false };
  try {
    if (typeof Html5QrcodeSupportedFormats === 'undefined') return cfg;
    cfg.formatsToSupport = mode === 'qr'
      ? [Html5QrcodeSupportedFormats.QR_CODE]
      : [
          Html5QrcodeSupportedFormats.CODE_128, Html5QrcodeSupportedFormats.CODE_39,
          Html5QrcodeSupportedFormats.CODE_93, Html5QrcodeSupportedFormats.EAN_13,
          Html5QrcodeSupportedFormats.EAN_8, Html5QrcodeSupportedFormats.UPC_A,
          Html5QrcodeSupportedFormats.ITF, Html5QrcodeSupportedFormats.CODABAR
        ];
  } catch(e){}
  return cfg;
}
function makeScanReader(elId){
  const formats = [];
  try {
    if (typeof Html5QrcodeSupportedFormats !== 'undefined'){
      if ((state.scanMode || 'qr') === 'bar'){
        formats.push(Html5QrcodeSupportedFormats.CODE_128, Html5QrcodeSupportedFormats.CODE_39,
          Html5QrcodeSupportedFormats.CODE_93, Html5QrcodeSupportedFormats.EAN_13,
          Html5QrcodeSupportedFormats.EAN_8, Html5QrcodeSupportedFormats.UPC_A,
          Html5QrcodeSupportedFormats.ITF, Html5QrcodeSupportedFormats.CODABAR);
      } else {
        formats.push(Html5QrcodeSupportedFormats.QR_CODE);
      }
    }
  } catch(e){}
  const cfg = { verbose: false, useBarCodeDetectorIfSupported: false };
  if (formats.length) cfg.formatsToSupport = formats;
  const qr = new Html5Qrcode(elId || 'reader', cfg);
  state.scanner = qr;
  return qr;
}
async function applyScanValue(decoded, opts = {}){
  const raw = String(decoded || '').trim();
  if (!raw){ toast('Code vide.'); return; }
  await stopLiveCamera();
  let value = raw;
  if (!opts.skipResolve){
    const r = await resolveScanText(opts.all && opts.all.length ? opts.all : raw);
    if (r.picks && r.picks.length){
      $('#scannerModal').classList.remove('hidden');
      showScanPicks(r.picks, raw);
      return;
    }
    value = r.value || cleanScan(raw);
  }
  await stopScan();
  $('#refInput').value = value;
  $('#clearBtn').classList.toggle('show', !!value);
  toast(value !== raw && raw.length > 32 ? 'Référence extraite : ' + value : 'Code lu : ' + value);
  doSearch(value);
}
async function stopLiveCamera(){
  const qr = state.scanner;
  state.scanner = null;
  if (!qr) return;
  try { await qr.stop(); } catch(e){}
  try { qr.clear(); } catch(e){}
}
async function startLiveCamera(){
  if (typeof Html5Qrcode === 'undefined'){
    await ensureVendors();
  }
  if (typeof Html5Qrcode === 'undefined') throw new Error('Bibliothèque de scan absente');
  if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia){
    throw new Error('Caméra bloquée (HTTP). Photographiez le code.');
  }
  await stopLiveCamera();
  $('#reader').innerHTML = '';
  if ($('#scanPick')){ $('#scanPick').classList.add('hidden'); $('#scanPick').innerHTML = ''; }
  await new Promise(r => requestAnimationFrame(() => requestAnimationFrame(r)));
  const qr = makeScanReader();
  const onOk = decoded => applyScanValue(decoded);
  const cfg = scanConfig(state.scanMode || 'qr');
  let lastErr = null;
  try {
    await qr.start({ facingMode: { ideal: 'environment' } }, cfg, onOk, () => {});
    return;
  } catch (e1){ lastErr = e1; }
  try {
    const cams = await Html5Qrcode.getCameras();
    if (!cams || !cams.length) throw lastErr || new Error('Aucune caméra');
    const back = cams.find(c => /back|rear|environment|arrière|world/i.test(c.label || ''));
    const cam = back || cams[cams.length - 1] || cams[0];
    await qr.start(cam.id, cfg, onOk, () => {});
    return;
  } catch (e2){ lastErr = e2; }
  try {
    await qr.start({ facingMode: 'user' }, cfg, onOk, () => {});
  } catch (e3){
    throw lastErr || e3;
  }
}
function drawScaled(img, maxSide){
  const w = img.width || img.naturalWidth || 0;
  const h = img.height || img.naturalHeight || 0;
  const scale = Math.min(1, maxSide / Math.max(w, h, 1));
  const c = document.createElement('canvas');
  c.width = Math.max(1, Math.round(w * scale));
  c.height = Math.max(1, Math.round(h * scale));
  const ctx = c.getContext('2d', { willReadFrequently: true });
  ctx.imageSmoothingEnabled = true;
  ctx.imageSmoothingQuality = 'high';
  ctx.drawImage(img, 0, 0, c.width, c.height);
  return c;
}
function cropCanvas(src, x, y, w, h){
  const c = document.createElement('canvas');
  c.width = Math.max(1, Math.round(w));
  c.height = Math.max(1, Math.round(h));
  c.getContext('2d').drawImage(src, x, y, w, h, 0, 0, c.width, c.height);
  return c;
}
function contrastCanvas(src){
  const c = document.createElement('canvas');
  c.width = src.width;
  c.height = src.height;
  const ctx = c.getContext('2d', { willReadFrequently: true });
  ctx.drawImage(src, 0, 0);
  const img = ctx.getImageData(0, 0, c.width, c.height);
  const d = img.data;
  for (let i = 0; i < d.length; i += 4){
    let g = 0.299 * d[i] + 0.587 * d[i + 1] + 0.114 * d[i + 2];
    g = (g - 128) * 1.7 + 128;
    g = g < 80 ? 0 : g > 175 ? 255 : g;
    d[i] = d[i + 1] = d[i + 2] = g;
  }
  ctx.putImageData(img, 0, 0);
  return c;
}
function scanRegions(src){
  const w = src.width, h = src.height;
  const out = [src, contrastCanvas(src)];
  const rw = w * 0.58, rh = h * 0.58;
  [[0, 0], [w - rw, 0], [0, h - rh], [w - rw, h - rh], [(w - rw) / 2, (h - rh) / 2]].forEach(([x, y]) => {
    out.push(cropCanvas(src, x, y, rw, rh));
  });
  return out;
}
async function detectOnCanvas(canvas){
  const found = [];
  if ('BarcodeDetector' in window){
    try {
      const want = (state.scanMode === 'bar')
        ? ['code_128','code_39','ean_13','ean_8','upc_a','codabar','itf']
        : ['qr_code'];
      const extra = (state.scanMode === 'bar') ? [] : ['aztec','data_matrix','pdf417'];
      const det = new BarcodeDetector({ formats: want.concat(extra) });
      const codes = await det.detect(canvas);
      (codes || []).forEach(c => { if (c && c.rawValue) found.push({ format: c.format || 'qr', text: c.rawValue }); });
    } catch(e){}
  }
  return found;
}
async function html5ScanBlob(blob){
  if (typeof Html5Qrcode === 'undefined' || !blob) return '';
  await stopLiveCamera();
  const file = new File([blob], 'scan.jpg', { type: blob.type || 'image/jpeg' });
  const qr = makeScanReader('scanWork');
  try {
    return await qr.scanFile(file, false);
  } catch(e){
    return '';
  } finally {
    try { qr.clear(); } catch(e){}
    if (state.scanner === qr) state.scanner = null;
  }
}
async function readCodesFromFile(file){
  const texts = [];
  const add = t => {
    const s = String(t || '').trim();
    if (s) texts.push(s);
  };
  let img = null;
  try {
    img = await createImageBitmap(file, { imageOrientation: 'from-image' });
  } catch(e){
    try { img = await createImageBitmap(file); } catch(e2){}
  }
  if (!img){
    const qr = makeScanReader();
    try { const t = await qr.scanFile(file, true); if (t) texts.push(t); } catch(e){}
    return uniqueStrings(texts);
  }

  const scaled = drawScaled(img, 1280);
  try { img.close && img.close(); } catch(e){}

  const preview = $('#reader');
  if (preview){
    preview.innerHTML = '';
    const shown = document.createElement('canvas');
    shown.width = scaled.width;
    shown.height = scaled.height;
    shown.getContext('2d').drawImage(scaled, 0, 0);
    shown.style.width = '100%';
    preview.appendChild(shown);
  }

  const regions = scanRegions(scaled);
  for (const c of regions){
    const codes = await detectOnCanvas(c);
    const qrFirst = codes.filter(x => /qr|aztec|matrix|pdf/i.test(x.format || 'qr'));
    const rest = codes.filter(x => !qrFirst.includes(x));
    (state.scanMode === 'bar' ? codes : qrFirst.concat(rest)).forEach(x => add(x.text));
    if (texts.length) break;
  }

  if (!texts.length){
    for (const max of [1280, 900]){
      const c = max === 1280 ? scaled : drawScaled(scaled, max);
      const blob = await new Promise(r => c.toBlob(r, 'image/jpeg', 0.92));
      const t = await html5ScanBlob(blob);
      if (t){ add(t); break; }
    }
  }
  if (!texts.length){
    const blob = await new Promise(r => contrastCanvas(scaled).toBlob(r, 'image/jpeg', 0.92));
    const t = await html5ScanBlob(blob);
    if (t) add(t);
  }
  return uniqueStrings(texts);
}
async function decodeScanFile(file){
  if (!file) return;
  $('#scanHint').textContent = 'Lecture de l’image…';
  if ($('#scanPick')){ $('#scanPick').classList.add('hidden'); $('#scanPick').innerHTML = ''; }
  if (typeof Html5Qrcode === 'undefined'){
    $('#scanHint').textContent = 'Chargement du lecteur de codes…';
    try { await ensureVendors(); } catch(e){}
  }
  await stopLiveCamera();
  try {
    const texts = await readCodesFromFile(file);
    if (!texts.length) throw new Error('none');
    await applyScanValue(texts[0], { all: texts });
  } catch (err){
    console.error(err);
    $('#scanHint').textContent = 'Code non lu. Cadrez uniquement le QR, de face, bien éclairé, sans flash.';
    toast('Impossible de lire ce code. Recadrez le QR de plus près.');
  } finally {
    $('#scanCapture').value = '';
    $('#scanGallery').value = '';
  }
}
async function startScan(mode){
  state.scanMode = mode === 'bar' ? 'bar' : 'qr';
  const modal = $('#scannerModal');
  $('#scannerTitle').textContent = state.scanMode === 'qr' ? 'Scanner un QR Code' : 'Scanner un code-barres';
  $('#reader').innerHTML = '<div style="padding:28px 18px;text-align:center;color:#ddd">Préparation du scanner…</div>';
  $('#scanHint').textContent = 'Placez le code dans le cadre, ou photographiez-le.';
  if ($('#scanPick')){ $('#scanPick').classList.add('hidden'); $('#scanPick').innerHTML = ''; }
  modal.classList.remove('hidden');

  const usePhoto = !canUseLiveCamera() && isMobileScan();
  if (usePhoto){
    $('#scanHint').textContent = 'La caméra en direct est bloquée en HTTP. Photographiez le code : cela ouvre l’appareil photo du téléphone.';
    try { $('#scanCapture').value = ''; $('#scanCapture').click(); } catch(e){}
    return;
  }

  try {
    await startLiveCamera();
    $('#scanHint').textContent = 'Placez le code dans le cadre.';
  } catch (err){
    console.error(err);
    $('#reader').innerHTML = `<div style="padding:28px 18px;text-align:center;color:#ddd">Caméra en direct indisponible.<br><br>Utilisez <b>Photographier le code</b>.</div>`;
    $('#scanHint').textContent = (err && err.message) ? err.message : 'Photographiez le code pour le lire.';
    if (isMobileScan()){
      try { $('#scanCapture').value = ''; $('#scanCapture').click(); } catch(e){}
    } else {
      toast('Caméra bloquée. Photographiez le code ou choisissez une image.');
    }
  }
}

async function stopScan(){
  await stopLiveCamera();
  $('#scannerModal').classList.add('hidden');
  $('#reader').innerHTML = '';
  if ($('#scanPick')){ $('#scanPick').classList.add('hidden'); $('#scanPick').innerHTML = ''; }
  $('#scanCapture').value = '';
  $('#scanGallery').value = '';
}

function isStandalone(){
  return window.matchMedia('(display-mode: standalone)').matches
    || window.matchMedia('(display-mode: fullscreen)').matches
    || window.navigator.standalone === true
    || document.referrer.includes('android-app://');
}
function installPlatform(){
  const ua = navigator.userAgent || '';
  if (/iPhone|iPad|iPod/i.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1)) return 'ios';
  if (/Android/i.test(ua)) return 'android';
  return 'desktop';
}
function installEvent(){
  return state.deferredInstall || window.__deferredInstall || null;
}
function refreshInstallOffer(){
  const stand = isStandalone();
  const dismissed = localStorage.getItem('npc_install_dismiss') === '1';
  if ($('#btnInstall')) $('#btnInstall').classList.toggle('hidden', stand);
  if ($('#installBanner')) $('#installBanner').classList.toggle('hidden', stand || dismissed || !isLogged());
}
function httpsAppUrl(){
  return 'https://' + location.host + location.pathname.replace(/index\.php.*$/i, '') + (location.pathname.endsWith('/') || /index\.php/i.test(location.pathname) ? '' : '/');
}
function refreshHttpsWarn(){
  const box = $('#httpsWarn');
  const link = $('#httpsOpen');
  if (!box) return;
  const need = !window.isSecureContext && !/^(localhost|127\.0\.0\.1)$/i.test(location.hostname);
  box.classList.toggle('hidden', !need);
  if (link){
    const url = httpsAppUrl();
    link.setAttribute('href', url);
    link.textContent = 'Ouvrir ' + url;
  }
}
function openInstallHelp(){
  const plat = installPlatform();
  const native = !!installEvent();
  const lead = $('#installLead');
  const steps = $('#installSteps');
  const nativeBtn = $('#btnInstallNative');
  const httpsUrl = httpsAppUrl();
  if (!window.isSecureContext && plat !== 'ios'){
    lead.textContent = 'Sur téléphone, Chrome n’installe l’app et n’active le hors-ligne que en HTTPS.';
    steps.innerHTML = `<li>Ouvrez <b>${esc(httpsUrl)}</b> (pas http).</li>
      <li>Si un avertissement de certificat apparaît, appuyez sur <b>Avancé</b> puis <b>Continuer</b>.</li>
      <li>Connectez-vous, récupérez les notes, puis menu ⋮ → <b>Installer l’application</b>.</li>`;
    nativeBtn.classList.add('hidden');
  } else if (native){
    lead.textContent = 'Votre navigateur peut installer Notes NP comme une application.';
    steps.innerHTML = '<li>Appuyez sur <b>Installer maintenant</b>.</li><li>Validez la demande du navigateur.</li>';
    nativeBtn.classList.remove('hidden');
  } else if (plat === 'ios'){
    lead.textContent = 'Sur iPhone / iPad, l’installation se fait depuis Safari, de préférence en HTTPS.';
    steps.innerHTML = `<li>Ouvrez <b>${esc(httpsUrl)}</b> dans <b>Safari</b> (pas Chrome).</li>
      <li>Si Safari affiche un avertissement, allez dans <b>Réglages → Safari</b> ou validez <b>Visiter ce site web</b>.</li>
      <li>Appuyez sur le bouton <b>Partager</b> (carré avec flèche).</li>
      <li>Choisissez <b>Sur l’écran d’accueil</b>, puis <b>Ajouter</b>.</li>`;
    nativeBtn.classList.add('hidden');
  } else if (plat === 'android'){
    lead.textContent = 'Ajoutez l’app à l’écran d’accueil depuis le menu Chrome.';
    steps.innerHTML = '<li>Appuyez sur les <b>trois points</b> en haut à droite.</li><li>Choisissez <b>Ajouter à l’écran d’accueil</b> ou <b>Installer l’application</b>.</li><li>Validez. L’icône Notes NP apparaît alors comme une app.</li>';
    nativeBtn.classList.add('hidden');
  } else {
    lead.textContent = 'Sur ordinateur, utilisez le menu du navigateur si l’installation automatique n’apparaît pas.';
    steps.innerHTML = '<li>Ouvrez le menu Chrome (⋮).</li><li>Choisissez <b>Installer Notes NP</b> ou <b>Créer un raccourci</b>.</li><li>Sur téléphone, ouvrez la même adresse Wi‑Fi puis le menu ⋮ → Ajouter à l’écran d’accueil.</li>';
    nativeBtn.classList.toggle('hidden', !native);
  }
  $('#installModal').classList.remove('hidden');
}
async function promptInstall(){
  const ev = installEvent();
  if (ev && typeof ev.prompt === 'function'){
    try {
      ev.prompt();
      await ev.userChoice;
    } catch(e){}
    state.deferredInstall = null;
    window.__deferredInstall = null;
    localStorage.setItem('npc_install_dismiss', '1');
    refreshInstallOffer();
    $('#installModal').classList.add('hidden');
    toast('Application installée ou raccourci créé.');
    return;
  }
  openInstallHelp();
}
function showInstall(show){
  if (show === false){
    localStorage.setItem('npc_install_dismiss', '1');
  }
  refreshInstallOffer();
}

function bindEvents(){
  if (window.__deferredInstall) state.deferredInstall = window.__deferredInstall;
  refreshHttpsWarn();
  $$('#dashBoard .widget[data-status]').forEach(w => {
    w.addEventListener('click', () => openNotesByStatus(w.dataset.status));
  });
  $('#btnSearch').addEventListener('click', () => doSearch($('#refInput').value));
  $('#refInput').addEventListener('keydown', e => { if (e.key === 'Enter'){ e.preventDefault(); doSearch($('#refInput').value); } });
  $('#refInput').addEventListener('input', () => $('#clearBtn').classList.toggle('show', $('#refInput').value.length > 0));
  $('#refInput').addEventListener('paste', () => setTimeout(() => $('#clearBtn').classList.toggle('show', $('#refInput').value.length > 0), 0));
  $('#clearBtn').addEventListener('click', () => { $('#refInput').value = ''; $('#clearBtn').classList.remove('show'); $('#refInput').focus(); });
  $('#btnScanQR').addEventListener('click', () => startScan('qr'));
  $('#btnScanBar').addEventListener('click', () => startScan('bar'));
  $('#scanClose').addEventListener('click', stopScan);
  const qscan = $('#btnScanQuick');
  if (qscan) qscan.addEventListener('click', () => startScan('qr'));
  $('#btnScanLive').addEventListener('click', async () => {
    $('#scanHint').textContent = 'Ouverture de la caméra…';
    try {
      await startLiveCamera();
      $('#scanHint').textContent = 'Placez le code dans le cadre.';
    } catch (err){
      $('#scanHint').textContent = 'Caméra en direct indisponible. Photographiez le code.';
      toast((err && err.message) || 'Caméra bloquée en HTTP.');
    }
  });
  $('#scanCapture').addEventListener('change', e => decodeScanFile(e.target.files && e.target.files[0]));
  $('#scanGallery').addEventListener('change', e => decodeScanFile(e.target.files && e.target.files[0]));

  $('#pinPad').addEventListener('click', e => {
    const b = e.target.closest('button[data-k]');
    if (b) onPinKey(b.dataset.k);
  });
  document.addEventListener('keydown', e => {
    if (!$('#lockScreen') || $('#lockScreen').classList.contains('hidden')) return;
    if (e.key >= '0' && e.key <= '9'){ e.preventDefault(); onPinKey(e.key); }
    if (e.key === 'Backspace'){ e.preventDefault(); onPinKey('del'); }
    if (e.key === 'Enter'){ e.preventDefault(); onPinKey('ok'); }
  });
  $('#pinCancel').addEventListener('click', () => {
    if (state.user && state.pinMode === 'change' && !state.user.mustChangePin){
      state.pinMode = 'login';
      showLock(false);
    }
  });
  $('#btnLogout').addEventListener('click', logout);
  $('#btnChangePin').addEventListener('click', () => {
    state.pinMode = 'change';
    $('#pinCancel').classList.remove('hidden');
    showLock(true, 'Saisissez votre nouveau code PIN');
  });
  $('#btnAddInsp').addEventListener('click', () => openInspModal(''));
  $('#inspModalClose').addEventListener('click', () => $('#inspModal').classList.add('hidden'));
  $('#inspSave').addEventListener('click', saveInspector);
  $('#inspDelete').addEventListener('click', deactivateInspector);

  let notesTimer = 0;
  $('#notesQuery').addEventListener('input', () => {
    state.notesQuery = $('#notesQuery').value;
    state.notesShown = 30;
    clearTimeout(notesTimer);
    notesTimer = setTimeout(() => renderNotesPage(), 160);
  });
  $('#notesDateField').addEventListener('change', () => {
    state.notesDateField = $('#notesDateField').value;
    state.notesMonth = 'all';
    state.notesShown = 30;
    renderNotesPage();
  });
  $('#statusRail').addEventListener('click', e => {
    const btn = e.target.closest('[data-status]');
    if (!btn) return;
    state.notesStatus = btn.dataset.status;
    state.notesShown = 30;
    renderNotesPage();
  });
  $('#monthRail').addEventListener('click', e => {
    const btn = e.target.closest('[data-month]');
    if (!btn) return;
    state.notesMonth = btn.dataset.month;
    state.notesShown = 30;
    renderNotesPage();
  });
  $('#notesList').addEventListener('click', e => {
    const more = e.target.closest('.notes-more');
    if (more){
      state.notesShown = (state.notesShown || 30) + 40;
      paintNotesFromIndex(state.notesIndex || [], !!state.notesIndexPromise);
      return;
    }
    const line = e.target.closest('.note-line');
    if (line) openNoteFromList(line.dataset.ref);
  });

  const dz = $('#dropzone'), fi = $('#fileInput');
  dz.addEventListener('click', () => fi.click());
  dz.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); fi.click(); } });
  fi.addEventListener('change', e => { if (e.target.files[0]) handleExcelFile(e.target.files[0]); fi.value = ''; });

  ['dragenter','dragover'].forEach(ev => dz.addEventListener(ev, e => { e.preventDefault(); dz.classList.add('drag'); }));
  ['dragleave','drop'].forEach(ev => dz.addEventListener(ev, e => { e.preventDefault(); dz.classList.remove('drag'); }));
  dz.addEventListener('drop', e => {
    const f = e.dataTransfer.files && e.dataTransfer.files[0];
    if (f) handleExcelFile(f);
  });

  $('#clearHistoryBtn').addEventListener('click', async () => {
    if (confirm("Vider tout l'historique des contrôles ?")){
      await IDB.clearHistory();
      renderHistory();
      refreshHome();
      toast('Historique vidé.');
    }
  });

  $('#btnSaveUrl').addEventListener('click', () => {
    state.cfg.serverUrl = $('#serverUrl').value.trim();
    saveCfg();
    toast('URL enregistrée.');
  });
  $('#btnSync').addEventListener('click', syncFromServer);
  $('#btnPush').addEventListener('click', pushToServer);
  if ($('#btnPullHome')) $('#btnPullHome').addEventListener('click', async () => {
    toast('Récupération des notes…');
    const r = await syncFromServer();
    if (r && r.ok && !r.notes) toast('Le serveur n’a pas encore de notes. Ouvrez l’app sur le PC après l’import.');
  });

  $('#btnExport').addEventListener('click', async () => {
    const notes = await IDB.getAll();
    const payload = { app: 'npc', version: 1, exportedAt: new Date().toISOString(), count: notes.length, notes };
    const blob = new Blob([JSON.stringify(payload)], { type: 'application/json' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'notes-perception-' + new Date().toISOString().slice(0, 10) + '.json';
    a.click();
    URL.revokeObjectURL(a.href);
    toast('Sauvegarde exportée.');
  });

  $('#btnImportJson').addEventListener('click', () => $('#jsonInput').click());
  $('#jsonInput').addEventListener('change', async e => {
    const f = e.target.files[0];
    if (!f) return;
    try {
      const data = JSON.parse(await f.text());
      const notes = Array.isArray(data) ? data : (data.notes || []);
      await IDB.putNotes(notes);
      invalidateNotesCache();
      toast(`${notes.length} note(s) restaurée(s).`);
      refreshHome(); refreshImportMeta();
    } catch(err){
      toast('Fichier de sauvegarde invalide.');
    }
    e.target.value = '';
  });

  if ($('#btnOfflinePack')) $('#btnOfflinePack').addEventListener('click', saveOfflineCopy);
  $('#btnWipe').addEventListener('click', async () => {
    if (confirm('Supprimer DÉFINITIVEMENT toutes les notes de la base locale ?')){
      await IDB.clearNotes();
      await IDB.clearHistory();
      await IDB.setMeta('lastImport', null);
      invalidateNotesCache();
      refreshHome(); refreshImportMeta();
      toast('Base locale vidée.');
    }
  });

  const promptInstallClick = () => promptInstall();
  window.addEventListener('beforeinstallprompt', e => {
    e.preventDefault();
    window.__deferredInstall = e;
    state.deferredInstall = e;
    refreshInstallOffer();
  });
  window.addEventListener('appinstalled', () => {
    state.deferredInstall = null;
    window.__deferredInstall = null;
    localStorage.setItem('npc_install_dismiss', '1');
    refreshInstallOffer();
    toast('Application installée.');
  });
  $('#btnInstall').addEventListener('click', promptInstallClick);
  $('#btnInstallHome').addEventListener('click', promptInstallClick);
  $('#btnInstallLater').addEventListener('click', () => {
    localStorage.setItem('npc_install_dismiss', '1');
    refreshInstallOffer();
  });
  $('#installModalClose').addEventListener('click', () => $('#installModal').classList.add('hidden'));
  $('#btnInstallNative').addEventListener('click', promptInstallClick);

  const updateNet = () => {
    const on = navigator.onLine;
    $('#netBadge').classList.toggle('off', !on);
    $('#netText').textContent = on ? 'En ligne' : 'Hors ligne';
  };
  window.addEventListener('online', () => {
    updateNet();
    silentSync({ pull: true, push: isAdmin(), timeout: 8000 }).then(r => {
      if (r.ok && (r.notes || r.inspectors)) refreshHome();
    });
  });
  window.addEventListener('offline', () => {
    updateNet();
    toast('Sans réseau : les notes déjà téléchargées restent disponibles ici.');
  });
  updateNet();
}

(async function init(){
  if (window.setBootProgress) setBootProgress(14, 'Préparation…', 'Configuration de l’appareil');
  loadCfg();
  try {
    if (window.setBootProgress) setBootProgress(28, 'Ouverture de la base locale…', 'IndexedDB sur cet appareil');
    await openDB();
  } catch (err){
    console.error(err);
    if (window.setBootProgress) setBootProgress(100, 'Base locale inaccessible', 'Vérifiez la navigation privée.');
    alert("Impossible d'ouvrir la base de données locale. Vérifiez que le stockage n'est pas bloqué (navigation privée).");
    hideBoot();
    return;
  }
  try { if (navigator.storage && navigator.storage.persist) await navigator.storage.persist(); } catch (e){}
  try { await ingestOfflinePack(); } catch (e){ console.warn(e); }

  if (window.setBootProgress) setBootProgress(48, 'Activation de l’interface…', 'Sans attendre le serveur');
  bindEvents();
  if (window.setBootProgress) setBootProgress(62, 'Comptes utilisateurs…', 'PIN et rôles en local');
  await ensureAuthSeed();
  if (window.setBootProgress) setBootProgress(78, 'Ouverture de la session…', '');
  const restored = await restoreSession();
  if (!restored){
    $('#lockHint').textContent = 'Administrateur : tapez 0000';
    showLock(true, 'Saisissez votre code PIN');
  } else {
    showLock(false);
    applyRoleUI();
    if (window.setBootProgress) setBootProgress(90, 'Chargement du tableau de bord…', 'Données déjà présentes sur l’appareil');
    await refreshHome();
    await refreshImportMeta();
    go('home');
    refreshInstallOffer();
  }
  if (window.setBootProgress) setBootProgress(100, navigator.onLine ? 'Prêt' : 'Prêt (hors ligne)', 'Les notes locales restent disponibles');
  requestAnimationFrame(() => setTimeout(hideBoot, 120));
  ensureNotesIndex().catch(() => {});
  ensureVendors().catch(() => {});

  silentSync({
    pull: true,
    push: false,
    timeout: 25000,
    onProgress: (p, m, d) => {
      if (window.setBootProgress && $('#boot') && !$('#boot').classList.contains('off')) setBootProgress(Math.min(99, 78 + p * 0.15), m, d);
    }
  }).then(async pulled => {
    if (pulled.ok && (pulled.notes || pulled.inspectors)){
      await refreshHome();
      await refreshImportMeta();
    }
    const hasLocal = (await IDB.count()) > 0 || (await IDB.countInspectors()) > 0;
    if (hasLocal && navigator.onLine && isAdmin()){
      silentSync({ pull: false, push: true, timeout: 60000 }).catch(() => {});
    }
  });

  if ('serviceWorker' in navigator && location.protocol !== 'file:'){
    navigator.serviceWorker.register('sw.js', { scope: './', updateViaCache: 'none' })
      .then(reg => {
        if (reg.waiting) reg.waiting.postMessage({ type: 'SKIP_WAITING' });
        if (reg.installing){
          reg.installing.addEventListener('statechange', () => {
            if (reg.active) renderSettings();
          });
        }
      })
      .catch(() => {});
  }
})();
</script>
</body>
</html>
