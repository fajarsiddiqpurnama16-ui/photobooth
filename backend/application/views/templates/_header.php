<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/* ---------- icon helper (sama dengan dashboard.php) ---------- */
if (!function_exists('dash_icon')) {
    function dash_icon($name) {
        $p = [
            'grid'    => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
            'image'   => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/>',
            'card'    => '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/>',
            'folder'  => '<path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>',
            'receipt' => '<path d="M6 2h12v20l-3-2-3 2-3-2-3 2z"/><path d="M9 7h6M9 11h6"/>',
            'plus'    => '<path d="M12 5v14M5 12h14"/>',
            'trend'   => '<path d="M3 17l6-6 4 4 8-8"/><path d="M15 7h6v6"/>',
            'box'     => '<path d="M21 8l-9-5-9 5v8l9 5 9-5z"/><path d="M3 8l9 5 9-5M12 13v8"/>',
            'bolt'    => '<path d="M13 2L4 14h7l-1 8 9-12h-7z"/>',
            'logout'  => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
            'check'   => '<path d="M20 6L9 17l-5-5"/>',
            'sticker' => '<circle cx="12" cy="12" r="9"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><path d="M9 9h.01M15 9h.01"/>',
            'effects' => '<path d="m12 3 1.9 5.8L20 11l-6.1 2.2L12 19l-1.9-5.8L4 11l6.1-2.2L12 3Z"/><path d="m19 14 1.2 2.8L23 18l-2.8 1.2L19 22l-1.2-2.8L15 18l2.8-1.2L19 14Z"/>',
        ];
        return '<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . ($p[$name] ?? '') . '</svg>';
    }
}

$adminName = $this->session->userdata('admin_username') ?: 'Admin';

/* ---------- menu aktif otomatis dari URL ----------
   Bisa dioverride dari controller: $data['nav_active'] = 'templates'; */
if (!isset($nav_active)) {
    $uri = strtolower(uri_string());
    if (preg_match('#admin/orders|update_order_status#', $uri))   $nav_active = 'orders';
    elseif (strpos($uri, 'adminpayments') !== false)              $nav_active = 'payments';
    elseif (strpos($uri, 'adminpayments') !== false)              $nav_active = 'payments';
    elseif (strpos($uri, 'adminstickers') !== false)              $nav_active = 'stickers';
    elseif (strpos($uri, 'photos') !== false)                     $nav_active = 'gallery';
    elseif (preg_match('#admin/add($|/)#', $uri))                 $nav_active = 'add';
    elseif (preg_match('#admin/(templates|edit)#', $uri))         $nav_active = 'templates';
    else                                                          $nav_active = 'dashboard';
}

/* jumlah template untuk pill di menu */
$navTotal = null;
if (isset($this->Templates_model) && method_exists($this->Templates_model, 'getAll')) {
    $navTotal = count($this->Templates_model->getAll());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($page_title) ? htmlspecialchars($page_title) . ' | Photobooth Studio Admin' : 'Photobooth Studio Admin' ?></title>
<link rel="icon" href="<?= base_url('favicon.ico') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
<!-- Font tambahan untuk fitur "Tambah Teks" di editor template -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Playfair+Display:wght@400;700&family=Dancing+Script:wght@600;700&family=Bebas+Neue&family=Oswald:wght@400;600&family=Pacifico&family=Montserrat:wght@400;600;800&family=Caveat:wght@600;700&family=Roboto+Mono:wght@500&family=Great+Vibes&family=Lobster&family=Abril+Fatface&family=Josefin+Sans:wght@400;600&display=swap" rel="stylesheet">
<style>
:root{
  --navy:#0a1f4e; --navy-2:#132b66; --navy-active:#1d3f95;
  --bg:#f6f7f9; --card:#fff; --line:#e6e8ec;
  --text:#0f172a; --muted:#64748b;
  --blue:#2563eb; --amber:#b7791f; --teal:#0d9488; --green:#059669; --red:#dc2626;

  /* alias untuk view lama (templates/form/orders) supaya otomatis ikut tema terang */
  --card-bg:#fff; --card-border:var(--line);
  --text-main:var(--text); --text-secondary:#475569; --text-muted:var(--muted);
  --primary:var(--blue); --primary-light:#3b82f6; --primary-dark:#1d4fd8;
  --cyan-light:#0e7490; --emerald:#059669; --rose:#dc2626;
  --gradient-primary:#2563eb;
  --shadow-sm:none; --shadow-md:none; --shadow-lg:0 8px 24px rgba(15,23,42,.12);
  --radius-sm:6px; --radius-md:8px; --radius-lg:10px; --radius-xl:14px;
}
*{box-sizing:border-box;margin:0;padding:0}
html,body{min-height:100vh}
body{background:var(--bg);color:var(--text);font-family:'Inter',system-ui,sans-serif;font-size:14px;line-height:1.5}
a{color:inherit;text-decoration:none}
.ic{width:18px;height:18px;flex-shrink:0}

/* ============ SIDEBAR (identik dengan dashboard) ============ */
.sidebar{position:fixed;inset:0 auto 0 0;width:260px;background:var(--navy);color:#c9d3ee;display:flex;flex-direction:column;padding:18px 12px;overflow-y:auto;z-index:50}
.brand{display:flex;align-items:center;gap:10px;padding:4px 8px 18px}
.brand-mark{width:34px;height:34px;border-radius:9px;background:#3b82f6;display:grid;place-items:center;color:#fff}
.brand b{display:block;color:#fff;font-size:15px;line-height:1.2}
.brand small{font-size:11px;color:#8ea2d6;letter-spacing:.04em}
.nav-title{font-size:11px;font-weight:600;color:#8ea2d6;padding:14px 10px 6px;letter-spacing:.06em}
.nav a{display:flex;align-items:center;gap:12px;padding:9px 12px;margin:2px 0;border-radius:8px;font-weight:500;color:#c9d3ee}
.nav a:hover{background:var(--navy-2);color:#fff}
.nav a.active{background:var(--navy-active);color:#fff}
.nav .pill{margin-left:auto;font-size:11px;font-weight:600;background:rgba(255,255,255,.12);color:#fff;padding:1px 8px;border-radius:99px}
.side-user{margin-top:auto;display:flex;align-items:center;gap:10px;padding:14px 8px 0;border-top:1px solid rgba(255,255,255,.1)}
.avatar{width:34px;height:34px;border-radius:50%;background:#3b82f6;color:#fff;display:grid;place-items:center;font-weight:700}
.side-user b{display:block;color:#fff;font-size:13px;line-height:1.2}
.side-user small{font-size:11px;color:#8ea2d6}
.side-user .out{margin-left:auto;color:#8ea2d6;padding:6px;border-radius:6px}
.side-user .out:hover{background:var(--navy-2);color:#fff}

/* ============ MAIN + HEADER HALAMAN ============ */
.main{margin-left:260px;padding:28px 32px 48px;min-height:100vh}
.topbar,.head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:22px}
.page-header h1,.head h1{font-size:24px;font-weight:700;letter-spacing:-.02em;color:var(--text);display:flex;align-items:center;gap:10px}
.page-header p,.head p{color:var(--muted);margin-top:2px;font-size:14px}
.topbar-actions,.actions{display:flex;align-items:center;gap:10px}

/* ============ BUTTON ============ */
.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:9px 16px;border-radius:8px;font-weight:600;font-size:13px;font-family:inherit;border:1px solid var(--line);background:#fff;color:var(--text);cursor:pointer;line-height:1.4}
.btn:hover{background:#f1f3f6}
.btn.primary,.btn-primary{background:var(--blue);border-color:var(--blue);color:#fff}
.btn.primary:hover,.btn-primary:hover{background:#1d4fd8;border-color:#1d4fd8;color:#fff}
.btn-secondary{background:#fff;border-color:var(--line);color:var(--text)}
.btn-secondary:hover{background:#f1f3f6}
.btn.sm,.btn-sm{padding:5px 10px;font-size:12px;border-radius:7px}
.btn-success{background:#ecfdf5;border-color:#a7f3d0;color:#047857}
.btn-success:hover{background:#059669;border-color:#059669;color:#fff}
.btn-warning{background:#fffbeb;border-color:#fde68a;color:#92400e}
.btn-warning:hover{background:#d97706;border-color:#d97706;color:#fff}
.btn-danger,.btn-delete{background:#fef2f2;border-color:#fecaca;color:#b91c1c}
.btn-danger:hover,.btn-delete:hover{background:#dc2626;border-color:#dc2626;color:#fff}
.btn-edit{background:#fff;border-color:var(--line);color:var(--text)}
.btn-edit:hover{background:#f1f3f6}

/* ============ CARD ============ */
.card{background:var(--card);border:1px solid var(--line);border-radius:10px;padding:20px}
.card.flush{padding:0;overflow:hidden}
.card-header,.card-h{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
.card-title,.card-h h2{font-size:15px;font-weight:600;color:var(--text)}
.card-subtitle{font-size:12px;color:var(--muted)}
.desc{color:var(--muted);margin-bottom:16px}
.empty{color:var(--muted);text-align:center;padding:28px 20px}

/* ============ BADGE ============ */
.badge{display:inline-flex;align-items:center;gap:6px;padding:2px 10px;border-radius:99px;font-size:12px;font-weight:600}
.badge-dot{width:6px;height:6px;border-radius:50%;background:currentColor}
.badge-green,.badge-active{background:#dcfce7;color:#166534}
.badge-amber{background:#fef3c7;color:#92400e}
.badge-cyan{background:#dbeafe;color:#1e40af}
.badge-purple{background:#ede9fe;color:#5b21b6}
.badge-red{background:#fee2e2;color:#991b1b}
.badge-gray{background:#f1f5f9;color:#475569}

/* ============ TABLE ============ */
.table-responsive,.tbl-wrap{width:100%;overflow-x:auto}
table{width:100%;border-collapse:collapse;text-align:left}
th{font-size:12px;font-weight:500;color:var(--muted);padding:10px 16px;background:#fafbfc;border-bottom:1px solid var(--line);white-space:nowrap}
td{padding:12px 16px;font-size:13px;color:var(--text-secondary);border-bottom:1px solid var(--line);vertical-align:middle}
tbody tr:hover td{background:#f8fafc}
tbody tr:last-child td{border-bottom:0}

/* ============ FORM (zero-specificity supaya class di view tetap menang) ============ */
:where(.form-group){margin-bottom:18px}
:where(label){display:flex;align-items:center;justify-content:space-between;font-size:13px;font-weight:600;color:var(--text);margin-bottom:6px}
:where(label .label-hint){font-size:11px;color:var(--muted);font-weight:400}
:where(input[type="text"],input[type="number"],input[type="email"],input[type="password"],select,textarea){
  width:100%;background:#fff;border:1px solid var(--line);border-radius:8px;padding:9px 12px;color:var(--text);font-family:inherit;font-size:13.5px;outline:none}
:where(input[type="text"],input[type="number"],input[type="email"],input[type="password"],select,textarea):focus{border-color:var(--blue);box-shadow:0 0 0 3px rgba(37,99,235,.15)}
:where(textarea){min-height:100px;line-height:1.6;resize:vertical}
:where(input[type="file"]){font-family:inherit;font-size:13px}
.code-textarea{font-family:'JetBrains Mono',monospace;font-size:12.5px;background:#0f172a;color:#7dd3fc;border-color:#0f172a;tab-size:2}

/* ============ PENYESUAIAN VIEW LAMA (templates, form, orders) ============ */
.main [style*="color:#ffffff"],.main [style*="color: #ffffff"]{color:var(--text)!important}
.main [style*="#fca5a5"]{color:#b91c1c!important}
.main [style*="background:#080d16"]{background:#eef1f5!important}

.main .template-title{color:var(--text)}
.main .template-preview-box{background:#eef1f5}
.main .template-card:hover{box-shadow:0 8px 22px rgba(15,23,42,.10);border-color:#cbd5e1}
.main .cat-tab.active{box-shadow:none}
.main .view-toggle-btn{background:#fff}
.main .view-toggle-btn.active{background:var(--blue);color:#fff}

.main .section-title,.main .upload-text,.main .file-name{color:var(--text)}
.main .upload-dropzone{border-color:rgba(37,99,235,.45);background:rgba(37,99,235,.04)}
.main .upload-dropzone:hover,.main .upload-dropzone.dragover{border-color:var(--blue);background:rgba(37,99,235,.09);transform:none}
.main .file-preview-card{background:#f8fafc}
.main .file-thumb,.main .preview-canvas-box{background:#eef1f5}
.main .cat-label{background:#fff}
.main .cat-radio:checked + .cat-label{background:#dbeafe;border-color:var(--blue);color:#1e3a8a;box-shadow:none}
.main .canvas-workspace{background-color:#dfe4ec;border-color:var(--line);
  background-image:linear-gradient(45deg,rgba(0,0,0,.05) 25%,transparent 25%),linear-gradient(-45deg,rgba(0,0,0,.05) 25%,transparent 25%),linear-gradient(45deg,transparent 75%,rgba(0,0,0,.05) 75%),linear-gradient(-45deg,transparent 75%,rgba(0,0,0,.05) 75%)}
.main .editor-toolbar{border-bottom-color:var(--line)}
/* toolbar inspector tetap gelap (palet alat), warnanya dipertegas */
.main .slot-inspector-bar [style*="--cyan-light"]{color:#38bdf8!important}
.main .insp-input:focus{color:#38bdf8}
.main .insp-input:focus,.main .insp-select:focus{box-shadow:none}

/* ============ RESPONSIVE ============ */
@media(max-width:820px){.sidebar{display:none}.main{margin-left:0;padding:18px}}
</style>
</head>
<body>

<aside class="sidebar">
  <a class="brand" href="<?= site_url('Admin/dashboard') ?>">
    <span class="brand-mark"><?= dash_icon('image') ?></span>
    <span><b>Photobooth</b><small>Studio Admin</small></span>
  </a>

  <nav class="nav">
    <div class="nav-title">Menu utama</div>
    <a class="<?= $nav_active === 'dashboard' ? 'active' : '' ?>" href="<?= site_url('Admin/dashboard') ?>"><?= dash_icon('grid') ?>Dashboard &amp; Analisis</a>
    <a class="<?= $nav_active === 'templates' ? 'active' : '' ?>" href="<?= site_url('Admin/templates') ?>"><?= dash_icon('image') ?>Kelola Template<?php if ($navTotal !== null): ?><span class="pill"><?= $navTotal ?></span><?php endif; ?></a>
    <a class="<?= $nav_active === 'payments' ? 'active' : '' ?>" href="<?= site_url('AdminPayments') ?>"><?= dash_icon('card') ?>Pembayaran</a>
    <a class="<?= $nav_active === 'gallery' ? 'active' : '' ?>" href="<?= site_url('Photos/gallery') ?>"><?= dash_icon('folder') ?>Galeri Foto</a>
    <a class="<?= $nav_active === 'stickers' ? 'active' : '' ?>" href="<?= site_url('AdminStickers') ?>"><?= dash_icon('sticker') ?>Stiker</a>
   <a class="<?= $nav_active === 'effects' ? 'active' : '' ?>"
   href="<?= site_url('Admin_effects') ?>">
    <?= dash_icon('effects') ?>Effects
</a>
    <a class="<?= $nav_active === 'orders' ? 'active' : '' ?>" href="<?= site_url('Admin/orders') ?>"><?= dash_icon('receipt') ?>Orderan Masuk<span class="pill">Live</span></a>
    <div class="nav-title">Studio tools</div>
    <a class="<?= $nav_active === 'add' ? 'active' : '' ?>" href="<?= site_url('Admin/add') ?>"><?= dash_icon('plus') ?>Tambah Template</a>
  </nav>

  <div class="side-user">
    <div class="avatar"><?= strtoupper(substr($adminName, 0, 1)) ?></div>
    <div><b><?= htmlspecialchars($adminName) ?></b><small>Super Admin</small></div>
    <a class="out" href="<?= site_url('Login/logout') ?>" title="Logout" onclick="return confirm('Apakah Anda yakin ingin logout?')"><?= dash_icon('logout') ?></a>
  </div>
</aside>

<main class="main">