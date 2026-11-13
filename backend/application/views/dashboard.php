<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/* ---------- helpers ---------- */
if (!function_exists('dash_spark')) {
    function dash_spark($vals, $color) {
        $w = 120; $h = 36; $n = count($vals);
        if ($n < 2) return '';
        $max = max($vals); $min = min($vals);
        $range = ($max - $min) ?: 1;
        $pts = [];
        foreach ($vals as $i => $v) {
            $x = round($i * ($w / ($n - 1)), 1);
            $y = round($h - 4 - (($v - $min) / $range) * ($h - 8), 1);
            $pts[] = "$x,$y";
        }
        $line = implode(' ', $pts);
        $area = "0,$h " . $line . " $w,$h";
        return '<svg class="spark" viewBox="0 0 ' . $w . ' ' . $h . '" preserveAspectRatio="none">'
             . '<polygon points="' . $area . '" fill="' . $color . '" fill-opacity=".12"/>'
             . '<polyline points="' . $line . '" fill="none" stroke="' . $color . '" stroke-width="1.8" stroke-linejoin="round" stroke-linecap="round"/></svg>';
    }
}
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

$rp = function ($n) { return 'Rp ' . number_format((float)$n, 0, ',', '.'); };

$revs = $trend_7d['revenues'];
$ords = $trend_7d['orders'];
$aovs = [];
foreach ($revs as $i => $r) { $aovs[] = $ords[$i] > 0 ? $r / $ords[$i] : 0; }

$totalOrders = (int)$summary['total_orders'];
$doneRate = $totalOrders > 0 ? round($summary['status_completed'] / $totalOrders * 100, 1) : 0;

$adminName = $this->session->userdata('admin_username') ?: 'Admin';

$totalPay = 0;
foreach ($payment_methods as $pm) { $totalPay += $pm->count; }
if ($totalPay == 0) $totalPay = 1;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | Photobooth Studio Admin</title>
<link rel="icon" href="<?= base_url('favicon.ico') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
:root{
  --navy:#0a1f4e; --navy-2:#132b66; --navy-active:#1d3f95;
  --bg:#f6f7f9; --card:#fff; --line:#e6e8ec;
  --text:#0f172a; --muted:#64748b;
  --blue:#2563eb; --amber:#d99e2b; --teal:#0d9488; --green:#059669; --red:#dc2626;
}
*{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--text);font-family:'Inter',system-ui,sans-serif;font-size:14px;line-height:1.5}
a{color:inherit;text-decoration:none}
.ic{width:18px;height:18px;flex-shrink:0}
.num{font-family:'JetBrains Mono',monospace;font-weight:700;letter-spacing:-.02em}

/* sidebar */
.sidebar{position:fixed;inset:0 auto 0 0;width:260px;background:var(--navy);color:#c9d3ee;display:flex;flex-direction:column;padding:18px 12px;overflow-y:auto}
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

/* main */
.main{margin-left:260px;padding:28px 32px 48px}
.head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:22px}
.head h1{font-size:24px;font-weight:700;letter-spacing:-.02em}
.head p{color:var(--muted);margin-top:2px}
.actions{display:flex;gap:10px}
.btn{display:inline-flex;align-items:center;gap:8px;padding:9px 16px;border-radius:8px;font-weight:600;font-size:13px;border:1px solid var(--line);background:#fff;color:var(--text);cursor:pointer}
.btn:hover{background:#f1f3f6}
.btn.primary{background:var(--blue);border-color:var(--blue);color:#fff}
.btn.primary:hover{background:#1d4fd8}
.btn.sm{padding:5px 10px;font-size:12px}

/* cards */
.card{background:var(--card);border:1px solid var(--line);border-radius:10px}
.kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:16px}
.kpi{padding:16px 18px;border-top:3px solid var(--c)}
.kpi-top{display:flex;justify-content:space-between;align-items:flex-start}
.kpi-ic{width:34px;height:34px;border-radius:8px;display:grid;place-items:center;background:color-mix(in srgb,var(--c) 12%,white);color:var(--c)}
.spark{width:110px;height:34px}
.kpi-label{color:var(--muted);margin-top:14px}
.kpi-val{font-size:24px;margin-top:2px}
.kpi-sub{font-size:12px;color:var(--muted);margin-top:4px}
.kpi-sub b{color:var(--text);font-weight:600}

.row{display:grid;gap:16px;margin-bottom:16px}
.row.r-main{grid-template-columns:2fr 1fr}
.row.r-bottom{grid-template-columns:1fr 1fr}
.card-h{display:flex;justify-content:space-between;align-items:center;padding:16px 20px;border-bottom:1px solid var(--line)}
.card-h h2{font-size:15px;font-weight:600}
.card-h small{display:block;color:var(--muted);font-size:12px;font-weight:400}
.card-h a.link{color:var(--muted);font-size:13px}
.card-h a.link:hover{color:var(--blue)}
.card-b{padding:16px 20px}
.seg{display:flex;background:#f1f3f6;border-radius:8px;padding:3px}
.seg button{border:0;background:transparent;padding:4px 12px;border-radius:6px;font:600 12px 'Inter';color:var(--muted);cursor:pointer}
.seg button.on{background:#fff;color:var(--text);box-shadow:0 1px 2px rgba(0,0,0,.08)}
.chart-box{position:relative;height:300px}

/* summary card */
.big{font-size:34px;margin:2px 0 14px}
.kv{display:flex;justify-content:space-between;padding:11px 0;border-top:1px solid var(--line)}
.kv span{color:var(--muted)}
.kv .num{font-size:14px}

/* activity */
.act{display:flex;gap:12px;align-items:center;padding:12px 20px;border-bottom:1px solid var(--line)}
.act:last-child{border-bottom:0}
.act-ic{width:34px;height:34px;border-radius:50%;display:grid;place-items:center;background:color-mix(in srgb,var(--c) 12%,white);color:var(--c);flex-shrink:0}
.act-t{flex:1;min-width:0}
.act-t b{display:block;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.act-t small{color:var(--muted)}
.act-r{text-align:right}
.act-r .num{font-size:13px;color:var(--green)}
.act-r small{display:block;color:var(--muted);font-size:12px}

/* table */
.tbl-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse}
th{font-size:12px;font-weight:500;color:var(--muted);text-align:left;padding:10px 20px;background:#fafbfc;border-bottom:1px solid var(--line);white-space:nowrap}
td{padding:12px 20px;border-bottom:1px solid var(--line);white-space:nowrap}
tr:last-child td{border-bottom:0}
td small{display:block;color:var(--muted);font-size:12px}
.tag{display:inline-block;font-size:12px;font-weight:600;padding:2px 9px;border-radius:99px}
.tag.done{background:#dcfce7;color:#166534}
.tag.proc{background:#dbeafe;color:#1e40af}
.tag.pend{background:#fef3c7;color:#92400e}

/* bars + top list */
.pm{margin-bottom:14px}
.pm-h{display:flex;justify-content:space-between;font-size:13px;margin-bottom:5px}
.pm-h span{color:var(--muted)}
.bar{height:6px;background:#eef0f4;border-radius:9px;overflow:hidden}
.bar i{display:block;height:100%;background:var(--blue);border-radius:9px}
.top{display:flex;align-items:center;gap:12px;padding:11px 20px;border-bottom:1px solid var(--line)}
.top:last-child{border-bottom:0}
.top-n{width:26px;height:26px;border-radius:7px;background:#eef2ff;color:var(--blue);display:grid;place-items:center;font-weight:700;font-size:12px}
.top div.t{flex:1;min-width:0}
.top b{display:block;font-weight:600}
.top small{color:var(--muted);text-transform:capitalize}
.empty{color:var(--muted);text-align:center;padding:28px 20px}

@media(max-width:1200px){.kpis{grid-template-columns:repeat(2,1fr)}.row.r-main,.row.r-bottom{grid-template-columns:1fr}}
@media(max-width:820px){.sidebar{display:none}.main{margin-left:0;padding:18px}}
@media(max-width:560px){.kpis{grid-template-columns:1fr}}
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

    <a class="active" href="<?= site_url('Admin/dashboard') ?>">
        <?= dash_icon('grid') ?>Dashboard &amp; Analisis
    </a>

    <a href="<?= site_url('Admin/templates') ?>">
        <?= dash_icon('image') ?>Kelola Template
        <span class="pill"><?= (int)$total ?></span>
    </a>

    <a href="<?= site_url('AdminPayments') ?>">
        <?= dash_icon('card') ?>Pembayaran
    </a>

    <a href="<?= site_url('Photos/gallery') ?>">
        <?= dash_icon('folder') ?>Galeri Foto
    </a>

    <a href="<?= site_url('AdminStickers') ?>">
        <?= dash_icon('sticker') ?>Stiker
    </a>

    <a href="<?= site_url('Admin_effects') ?>">
        <?= dash_icon('effects') ?>Effects
    </a>

    <a href="<?= site_url('Admin/orders') ?>">
        <?= dash_icon('receipt') ?>Orderan Masuk
        <span class="pill">Live</span>
    </a>

    <div class="nav-title">Studio tools</div>

    <a href="<?= site_url('Admin/add') ?>">
        <?= dash_icon('plus') ?>Tambah Template
    </a>
</nav>

  <div class="side-user">
    <div class="avatar"><?= strtoupper(substr($adminName, 0, 1)) ?></div>
    <div><b><?= htmlspecialchars($adminName) ?></b><small>Super Admin</small></div>
    <a class="out" href="<?= site_url('Login/logout') ?>" title="Logout" onclick="return confirm('Apakah Anda yakin ingin logout?')"><?= dash_icon('logout') ?></a>
  </div>
</aside>

<main class="main">
  <div class="head">
    <div>
      <h1>Ringkasan Bisnis</h1>
      <p>Penjualan, orderan masuk, dan omset photobooth secara real-time.</p>
    </div>
    <div class="actions">
      <a class="btn" href="<?= site_url('Admin/add_mock_order') ?>" title="Simulasi pesanan untuk tes data grafik"><?= dash_icon('bolt') ?>Simulasi order</a>
      <a class="btn primary" href="<?= site_url('Admin/add') ?>"><?= dash_icon('plus') ?>Tambah template</a>
    </div>
  </div>

  <!-- KPI -->
  <section class="kpis">
    <div class="card kpi" style="--c:var(--blue)">
      <div class="kpi-top"><div class="kpi-ic"><?= dash_icon('trend') ?></div><?= dash_spark($revs, '#2563eb') ?></div>
      <div class="kpi-label">Total penghasilan</div>
      <div class="kpi-val num"><?= $rp($summary['total_revenue']) ?></div>
      <div class="kpi-sub">Bulan ini <b><?= $rp($summary['month_revenue']) ?></b></div>
    </div>
    <div class="card kpi" style="--c:var(--amber)">
      <div class="kpi-top"><div class="kpi-ic"><?= dash_icon('box') ?></div><?= dash_spark($ords, '#d99e2b') ?></div>
      <div class="kpi-label">Orderan masuk</div>
      <div class="kpi-val num"><?= $totalOrders ?></div>
      <div class="kpi-sub">Hari ini <b><?= (int)$summary['today_orders'] ?> pesanan</b> &middot; <?= (int)$summary['status_completed'] ?> selesai</div>
    </div>
    <div class="card kpi" style="--c:var(--teal)">
      <div class="kpi-top"><div class="kpi-ic"><?= dash_icon('bolt') ?></div><?= dash_spark($aovs, '#0d9488') ?></div>
      <div class="kpi-label">Rata-rata order (AOV)</div>
      <div class="kpi-val num"><?= $rp($summary['average_order_val']) ?></div>
      <div class="kpi-sub">Omset hari ini <b><?= $rp($summary['today_revenue']) ?></b></div>
    </div>
    <div class="card kpi" style="--c:var(--blue)">
      <div class="kpi-top"><div class="kpi-ic"><?= dash_icon('image') ?></div></div>
      <div class="kpi-label">Katalog template</div>
      <div class="kpi-val num"><?= (int)$total ?></div>
      <div class="kpi-sub"><b><?= (int)$active ?></b> aktif &middot; <b><?= (int)$inactive ?></b> draft &middot; <a href="<?= site_url('Admin/templates') ?>" style="color:var(--blue);font-weight:600">Kelola</a></div>
    </div>
  </section>

  <!-- Chart + summary -->
  <section class="row r-main">
    <div class="card">
      <div class="card-h">
        <div><h2>Penghasilan vs jumlah order</h2><small>Harian, per periode yang dipilih</small></div>
        <div class="seg">
          <button type="button" class="on" id="btn7" onclick="setRange(7)">7 hari</button>
          <button type="button" id="btn14" onclick="setRange(14)">14 hari</button>
        </div>
      </div>
      <div class="card-b"><div class="chart-box"><canvas id="trendChart"></canvas></div></div>
    </div>

    <div class="card">
      <div class="card-h"><div><h2>Order selesai</h2><small>Dari seluruh order</small></div></div>
      <div class="card-b">
        <div class="big num"><?= $doneRate ?>%</div>
        <div class="kv"><span>Selesai</span><b class="num" style="color:var(--green)"><?= (int)$summary['status_completed'] ?></b></div>
        <div class="kv"><span>Diproses</span><b class="num" style="color:var(--blue)"><?= (int)$summary['status_processing'] ?></b></div>
        <div class="kv"><span>Pending</span><b class="num" style="color:var(--amber)"><?= (int)$summary['status_pending'] ?></b></div>
        <div class="kv"><span>Order lunas</span><b class="num"><?= (int)$summary['paid_orders_count'] ?></b></div>
      </div>
    </div>
  </section>

  <!-- Activity + transactions -->
  <section class="row r-bottom" style="grid-template-columns:1fr 1.6fr">
    <div class="card">
      <div class="card-h"><h2>Aktivitas terbaru</h2><a class="link" href="<?= site_url('Admin/orders') ?>">Lihat semua</a></div>
      <?php if (!empty($recent_orders)): foreach (array_slice($recent_orders, 0, 6) as $o):
          $c = $o->order_status === 'completed' ? 'var(--green)' : ($o->order_status === 'processing' ? 'var(--blue)' : 'var(--amber)'); ?>
        <div class="act" style="--c:<?= $c ?>">
          <div class="act-ic"><?= dash_icon($o->order_status === 'completed' ? 'check' : 'receipt') ?></div>
          <div class="act-t"><b><?= htmlspecialchars($o->customer_name) ?></b><small><?= htmlspecialchars($o->template_name ?: 'Custom') ?> &middot; <?= htmlspecialchars($o->payment_method) ?></small></div>
          <div class="act-r"><span class="num">+<?= $rp($o->total_amount) ?></span><small><?= date('d M, H:i', strtotime($o->created_at)) ?></small></div>
        </div>
      <?php endforeach; else: ?>
        <div class="empty">Belum ada aktivitas.</div>
      <?php endif; ?>
    </div>

    <div class="card">
      <div class="card-h"><h2>Transaksi terakhir</h2><a class="link" href="<?= site_url('Admin/orders') ?>">Lihat semua</a></div>
      <div class="tbl-wrap">
        <table>
          <thead><tr><th>Kode</th><th>Pelanggan</th><th>Template</th><th>Total</th><th>Status</th><th></th></tr></thead>
          <tbody>
          <?php if (!empty($recent_orders)): foreach ($recent_orders as $o): ?>
            <tr>
              <td><b class="num" style="font-size:12px"><?= htmlspecialchars($o->order_code) ?></b></td>
              <td><?= htmlspecialchars($o->customer_name) ?><small><?= htmlspecialchars($o->customer_phone ?: '-') ?></small></td>
              <td><?= htmlspecialchars($o->template_name ?: 'Custom') ?><small><?= htmlspecialchars($o->package_name) ?> (<?= (int)$o->copies ?> cetak)</small></td>
              <td class="num" style="font-size:13px"><?= $rp($o->total_amount) ?></td>
              <td>
                <?php if ($o->order_status === 'completed'): ?><span class="tag done">Selesai</span>
                <?php elseif ($o->order_status === 'processing'): ?><span class="tag proc">Diproses</span>
                <?php else: ?><span class="tag pend">Pending</span><?php endif; ?>
              </td>
              <td>
                <?php if ($o->order_status !== 'completed'): ?>
                  <a class="btn sm" href="<?= site_url('Admin/update_order_status/' . $o->id_order) . '?status=completed&redirect=Admin/dashboard' ?>">Tandai selesai</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="6" class="empty">Belum ada orderan masuk.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- Payment methods + top templates -->
  <section class="row r-bottom">
    <div class="card">
      <div class="card-h"><div><h2>Metode pembayaran</h2><small>Yang paling sering dipakai customer</small></div></div>
      <div class="card-b">
        <?php if (!empty($payment_methods)): foreach ($payment_methods as $pm): $pct = round($pm->count / $totalPay * 100); ?>
          <div class="pm">
            <div class="pm-h"><b><?= htmlspecialchars($pm->payment_method) ?></b><span><?= (int)$pm->count ?> order &middot; <?= $rp($pm->revenue) ?></span></div>
            <div class="bar"><i style="width:<?= $pct ?>%"></i></div>
          </div>
        <?php endforeach; else: ?><div class="empty">Belum ada data.</div><?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-h"><div><h2>Template terlaris</h2><small>Paling sering dipesan</small></div></div>
      <?php if (!empty($top_templates)): $rank = 1; foreach ($top_templates as $tt): ?>
        <div class="top">
          <div class="top-n"><?= $rank++ ?></div>
          <div class="t"><b><?= htmlspecialchars($tt->name) ?></b><small><?= htmlspecialchars($tt->category) ?></small></div>
          <div style="text-align:right"><span class="num" style="font-size:13px"><?= $rp($tt->total_generated ?? 0) ?></span><small style="display:block;color:var(--muted);text-transform:none"><?= (int)($tt->total_sold ?? 0) ?>x dipesan</small></div>
        </div>
      <?php endforeach; else: ?><div class="empty">Belum ada template terjual.</div><?php endif; ?>
    </div>
  </section>
</main>

<script>
const t7  = <?= json_encode($trend_7d) ?>;
const t14 = <?= json_encode($trend_14d) ?>;

Chart.defaults.font.family = 'Inter, sans-serif';
Chart.defaults.color = '#64748b';

const ctx = document.getElementById('trendChart').getContext('2d');
const chart = new Chart(ctx, {
  type: 'line',
  data: {
    labels: t7.labels,
    datasets: [
      { label: 'Penghasilan', data: t7.revenues, yAxisID: 'y',  borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,.28)', fill: true, tension: .4, borderWidth: 2, pointRadius: 0, pointHoverRadius: 4 },
      { label: 'Jumlah order', data: t7.orders,  yAxisID: 'y1', borderColor: '#d99e2b', backgroundColor: 'rgba(217,158,43,.35)', fill: true, tension: .4, borderWidth: 2, pointRadius: 0, pointHoverRadius: 4 }
    ]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
      legend: { position: 'top', align: 'end', labels: { usePointStyle: true, boxWidth: 8 } },
      tooltip: {
        backgroundColor: '#0f172a', padding: 10, cornerRadius: 8,
        callbacks: { label: c => c.dataset.yAxisID === 'y'
          ? 'Penghasilan: Rp ' + c.parsed.y.toLocaleString('id-ID')
          : 'Order: ' + c.parsed.y }
      }
    },
    scales: {
      x:  { grid: { display: false }, border: { display: false } },
      y:  { beginAtZero: true, border: { display: false }, grid: { color: '#e6e8ec', tickBorderDash: [4, 4] },
            ticks: { callback: v => v >= 1e6 ? (v / 1e6).toFixed(1) + 'jt' : v >= 1e3 ? (v / 1e3) + 'rb' : v } },
      y1: { beginAtZero: true, position: 'right', border: { display: false }, grid: { drawOnChartArea: false },
            ticks: { stepSize: 1, precision: 0 } }
    }
  }
});

function setRange(d) {
  document.getElementById('btn7').classList.toggle('on', d === 7);
  document.getElementById('btn14').classList.toggle('on', d === 14);
  const s = d === 7 ? t7 : t14;
  chart.data.labels = s.labels;
  chart.data.datasets[0].data = s.revenues;
  chart.data.datasets[1].data = s.orders;
  chart.update();
}
</script>
</body>
</html>
