<?php
/* Data tiap baris dikirim ke JS untuk popup detail.
   Semua kolom dari tabel ikut ditampilkan, jadi nggak perlu tau nama kolomnya. */
$rowsJs = [];
foreach ($requests as $r) {
    $row = [];
    foreach (get_object_vars($r) as $k => $v) {
        if ($v === null || $v === '') { $row[$k] = '-'; continue; }
        if (preg_match('/(_at|date|time|waktu)$/i', $k) && strtotime($v)) {
            $row[$k] = date('d M Y, H:i:s', strtotime($v));
        } elseif (preg_match('/(amount|price|total|nominal|harga)/i', $k) && is_numeric($v)) {
            $row[$k] = 'Rp ' . number_format((float)$v, 0, ',', '.');
        } elseif ($k === 'status') {
            $row[$k] = ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'][$v] ?? $v;
        } else {
            $row[$k] = (string)$v;
        }
    }
    $rowsJs[$r->id] = $row;
}
?>
<style>

    /* =========================
   PAYMENT PAGINATION
========================= */

.payment-pagination-wrap {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 18px 4px 4px;
    flex-wrap: wrap;
}

.payment-pagination-info {
    color: var(--muted);
    font-size: 13px;
    white-space: nowrap;
}

.payment-pagination {
    display: flex;
    align-items: center;
    gap: 6px;
}

.payment-page-numbers {
    display: flex;
    align-items: center;
    gap: 6px;
}


.payment-page-btn,
.payment-page-number {
    height: 34px;
    min-width: 34px;
    padding: 0 10px;
    border: 1px solid var(--line);
    background: #fff;
    color: var(--text);
    border-radius: 7px;
    font-size: 13px;
    cursor: pointer;
    transition: .15s ease;
}

.payment-page-btn:hover:not(:disabled),
.payment-page-number:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
}

.payment-page-number.active {
    background: #0f2f6b;
    border-color: #0f2f6b;
    color: #fff;
    font-weight: 600;
}

.payment-page-btn:disabled {
    opacity: .45;
    cursor: not-allowed;
}

.payment-per-page {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--muted);
    font-size: 13px;
}

.payment-per-page select {
    height: 34px;
    padding: 0 28px 0 10px;
    border: 1px solid var(--line);
    border-radius: 7px;
    background: #fff;
    color: var(--text);
    font-size: 13px;
    cursor: pointer;
}

@media (max-width: 700px) {
    .payment-pagination-wrap {
        align-items: flex-start;
        flex-direction: column;
    }

    .payment-pagination {
        width: 100%;
        justify-content: center;
    }

    .payment-per-page {
        width: 100%;
        justify-content: flex-end;
    }
}

.modal-overlay{position:fixed;inset:0;background:rgba(15,23,42,.45);display:none;align-items:center;justify-content:center;padding:20px;z-index:100}
.modal-overlay.open{display:flex}
.modal{background:#fff;border-radius:12px;width:100%;max-width:420px;box-shadow:0 24px 60px rgba(15,23,42,.25);overflow:hidden}
.modal-h{padding:18px 22px 0}
.modal-h h2{font-size:17px;font-weight:700;letter-spacing:-.01em}
.modal-h p{color:var(--muted);margin-top:4px}
.modal-b{padding:16px 22px}
.modal-f{display:flex;justify-content:flex-end;gap:10px;padding:14px 22px 20px}
.dl{border:1px solid var(--line);border-radius:8px;overflow:hidden}
.dl div{display:flex;justify-content:space-between;gap:16px;padding:9px 12px;border-bottom:1px solid var(--line)}
.dl div:last-child{border-bottom:0}
.dl span{color:var(--muted)}
.dl b{font-weight:600;text-align:right;word-break:break-word}
.col-aksi{width:1%;white-space:nowrap}
.aksi{display:flex;align-items:center;justify-content:flex-end;gap:12px;padding-right:8px}
.aksi-main{display:flex;align-items:center;justify-content:center;gap:6px;width:126px}
.aksi-main .js-action[data-act="approve"]{min-width:64px}
.aksi-main .js-action[data-act="reject"]{min-width:56px}
.aksi-h{width:100%;display:flex;align-items:center;justify-content:center;gap:6px}
.btn-icon{width:34px;padding:6px 0}
.btn-icon .ic{width:16px;height:16px}
.btn-yes-danger{background:#dc2626;border-color:#dc2626;color:#fff}
.btn-yes-danger:hover{background:#b91c1c;border-color:#b91c1c;color:#fff}
</style>

<div class="topbar">
    <div class="page-header">
        <h1>Konfirmasi Pembayaran</h1>
        <p>Setujui atau tolak pembayaran QRIS yang masuk.</p>
    </div>
    <div class="topbar-actions">
        <a href="<?= base_url('index.php/AdminPayments/settings') ?>" class="btn">Pengaturan QRIS &amp; harga</a>
    </div>
</div>

<div class="card flush">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Waktu</th>
                    <th>Status</th>
                    <th class="col-aksi"><div class="aksi"><div class="aksi-main"><span class="aksi-h">Aksi</span></div></div></th>
                </tr>
            </thead>
            <tbody id="paymentTableBody">
                <?php foreach ($requests as $r): ?>
                <tr class="payment-data-row">
                    <td><b style="font-family:'JetBrains Mono',monospace; font-size:12.5px; color:var(--text);"><?= htmlspecialchars($r->code) ?></b></td>
                    <td><?= date('d M, H:i', strtotime($r->created_at)) ?></td>
                    <td>
                        <?php if ($r->status === 'pending'): ?>
                            <span class="badge badge-amber">Menunggu</span>
                        <?php elseif ($r->status === 'approved'): ?>
                            <span class="badge badge-green">Disetujui</span>
                        <?php else: ?>
                            <span class="badge badge-red">Ditolak</span>
                        <?php endif; ?>
                    </td>
                    <td class="col-aksi">
                        <div class="aksi">
                            <div class="aksi-main">
                                <?php if ($r->status === 'pending'): ?>
                                    <button type="button" class="btn btn-primary btn-sm js-action" data-act="approve" data-id="<?= (int)$r->id ?>" data-url="<?= base_url('index.php/AdminPayments/approve/' . $r->id) ?>">Setujui</button>
                                    <button type="button" class="btn btn-danger btn-sm js-action" data-act="reject" data-id="<?= (int)$r->id ?>" data-url="<?= base_url('index.php/AdminPayments/reject/' . $r->id) ?>">Tolak</button>
                                <?php else: ?>
                                    <span class="aksi-h">
                                        <button type="button" class="btn btn-secondary btn-sm btn-icon js-detail" data-id="<?= (int)$r->id ?>" title="Lihat detail" aria-label="Lihat detail">
                                            <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm btn-icon js-action" data-act="delete" data-id="<?= (int)$r->id ?>" data-url="<?= base_url('index.php/AdminPayments/delete/' . $r->id) ?>" title="Hapus" aria-label="Hapus">
                                            <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                        </button>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($requests)): ?>
                <tr><td colspan="4" class="empty">Belum ada request pembayaran.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

<!-- Pagination -->
<div class="payment-pagination-wrap">
    <div class="payment-pagination-info" id="paymentPaginationInfo">
        Menampilkan 0–0 dari 0
    </div>

    <div class="payment-pagination">
        <button type="button" id="paymentPrevBtn" class="payment-page-btn">
            ‹ Previous
        </button>

        <div id="paymentPageNumbers" class="payment-page-numbers"></div>

        <button type="button" id="paymentNextBtn" class="payment-page-btn">
            Next ›
        </button>
    </div>

    <div class="payment-per-page">
        <span>Per Halaman:</span>

        <select id="paymentPerPage">
            <option value="8" selected>8</option>
            <option value="10">10</option>
            <option value="20">20</option>
            <option value="50">50</option>
        </select>
    </div>
</div>

</div>
</div>

<!-- Popup konfirmasi ya / tidak -->
<!-- Popup konfirmasi ya / tidak -->
<div class="modal-overlay" id="confirmModal" role="dialog" aria-modal="true" aria-labelledby="cmTitle">
    <div class="modal">
        <div class="modal-h">
            <h2 id="cmTitle"></h2>
            <p id="cmDesc"></p>
        </div>
        <div class="modal-b"><div class="dl" id="cmSummary"></div></div>
        <div class="modal-f">
            <button type="button" class="btn" id="cmNo">Tidak</button>
            <button type="button" class="btn btn-primary" id="cmYes">Ya</button>
        </div>
    </div>
</div>

<!-- Popup detail (icon mata) -->
<div class="modal-overlay" id="detailModal" role="dialog" aria-modal="true" aria-labelledby="dmTitle">
    <div class="modal">
        <div class="modal-h"><h2 id="dmTitle">Detail pembayaran</h2></div>
        <div class="modal-b"><div class="dl" id="dmBody"></div></div>
        <div class="modal-f"><button type="button" class="btn" id="dmClose">Tutup</button></div>
    </div>
</div>

<script>
    const rows = <?= json_encode($rowsJs, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    const labels = { id: 'ID', code: 'Kode', status: 'Status', created_at: 'Waktu request', updated_at: 'Terakhir diubah' };
    const esc = s => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const label = k => labels[k] || k.replace(/_/g, ' ').replace(/^./, c => c.toUpperCase());
    const listHtml = (data, keys) => keys.filter(k => k in data)
        .map(k => '<div><span>' + esc(label(k)) + '</span><b>' + esc(data[k]) + '</b></div>').join('');

    const confirmModal = document.getElementById('confirmModal');
    const detailModal  = document.getElementById('detailModal');
    const cmYes = document.getElementById('cmYes');
    let pendingUrl = null;

    function openModal(m)  { m.classList.add('open'); }
    function closeModals() { confirmModal.classList.remove('open'); detailModal.classList.remove('open'); pendingUrl = null; }

    // Klik Setujui / Tolak -> popup ya/tidak
    document.querySelectorAll('.js-action').forEach(btn => {
        btn.addEventListener('click', () => {
            const data = rows[btn.dataset.id];
            const act = btn.dataset.act;
            pendingUrl = btn.dataset.url;
            const texts = {
                approve: ['Setujui pembayaran ini?', 'Pastikan uang sudah masuk sebelum menyetujui.', 'Ya, setujui', 'btn-primary'],
                reject:  ['Tolak pembayaran ini?', 'Request ini akan ditandai ditolak.', 'Ya, tolak', 'btn-yes-danger'],
                delete:  ['Hapus data pembayaran ini?', 'Data dihapus permanen dari daftar ini. Orderan di Orderan Masuk tidak ikut terhapus.', 'Ya, hapus', 'btn-yes-danger'],
            }[act];
            document.getElementById('cmTitle').textContent = texts[0];
            document.getElementById('cmDesc').textContent  = texts[1];
            document.getElementById('cmSummary').innerHTML = listHtml(data, ['code', 'status', 'created_at']);
            cmYes.textContent = texts[2];
            cmYes.className = 'btn ' + texts[3];
            cmYes.disabled = false;
            openModal(confirmModal);
            document.getElementById('cmNo').focus();
        });
    });

    cmYes.addEventListener('click', () => {
        if (!pendingUrl) return;
        cmYes.disabled = true;
        window.location.href = pendingUrl;
    });
    document.getElementById('cmNo').addEventListener('click', closeModals);

    // Klik icon mata -> popup detail (semua kolom)
    document.querySelectorAll('.js-detail').forEach(btn => {
        btn.addEventListener('click', () => {
            const data = rows[btn.dataset.id];
            const first = ['code', 'status', 'created_at'];
            const rest = Object.keys(data).filter(k => !first.includes(k));
            document.getElementById('dmBody').innerHTML = listHtml(data, first.concat(rest));
            openModal(detailModal);
        });
    });
    document.getElementById('dmClose').addEventListener('click', closeModals);

    // Klik area gelap atau tekan Esc untuk menutup
    [confirmModal, detailModal].forEach(m => m.addEventListener('click', e => { if (e.target === m) closeModals(); }));
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModals(); });

    // Auto-refresh tiap 8 detik, tapi ditahan kalau popup lagi terbuka
    setInterval(() => {
        if (!document.querySelector('.modal-overlay.open')) location.reload();
    }, 8000);

    /* =========================
   PAYMENT TABLE PAGINATION
========================= */

(function () {
    const paymentTableBody = document.getElementById('paymentTableBody');
    const paginationInfo = document.getElementById('paymentPaginationInfo');
    const pageNumbers = document.getElementById('paymentPageNumbers');
    const prevBtn = document.getElementById('paymentPrevBtn');
    const nextBtn = document.getElementById('paymentNextBtn');
    const perPageSelect = document.getElementById('paymentPerPage');

    if (!paymentTableBody) return;

    const paymentRows = Array.from(
        paymentTableBody.querySelectorAll('.payment-data-row')
    );

    let currentPage = 1;
    let perPage = 8;

    function renderPaymentPagination() {
        const totalData = paymentRows.length;
        const totalPages = Math.max(1, Math.ceil(totalData / perPage));

        if (currentPage > totalPages) {
            currentPage = totalPages;
        }

        // Tentukan data yang tampil
        const startIndex = (currentPage - 1) * perPage;
        const endIndex = Math.min(startIndex + perPage, totalData);

        paymentRows.forEach((row, index) => {
            row.style.display =
                index >= startIndex && index < endIndex
                    ? ''
                    : 'none';
        });

        // Teks "Menampilkan 1–8 dari 20"
        if (totalData === 0) {
            paginationInfo.textContent = 'Menampilkan 0–0 dari 0';
        } else {
            paginationInfo.textContent =
                `Menampilkan ${startIndex + 1}–${endIndex} dari ${totalData}`;
        }

        // Tombol Previous
        prevBtn.disabled = currentPage === 1 || totalData === 0;

        // Tombol Next
        nextBtn.disabled =
            currentPage === totalPages || totalData === 0;

        // Nomor halaman
        pageNumbers.innerHTML = '';

        for (let page = 1; page <= totalPages; page++) {
            const button = document.createElement('button');

            button.type = 'button';
            button.className = 'payment-page-number';

            if (page === currentPage) {
                button.classList.add('active');
            }

            button.textContent = page;

            button.addEventListener('click', function () {
                currentPage = page;
                renderPaymentPagination();
            });

            pageNumbers.appendChild(button);
        }
    }

    // Previous
    prevBtn.addEventListener('click', function () {
        if (currentPage > 1) {
            currentPage--;
            renderPaymentPagination();
        }
    });

    // Next
    nextBtn.addEventListener('click', function () {
        const totalPages = Math.ceil(paymentRows.length / perPage);

        if (currentPage < totalPages) {
            currentPage++;
            renderPaymentPagination();
        }
    });

    // Per halaman
    perPageSelect.addEventListener('change', function () {
        perPage = parseInt(this.value, 10);
        currentPage = 1;

        renderPaymentPagination();
    });

    // Jalankan pertama kali
    renderPaymentPagination();
})();



</script>