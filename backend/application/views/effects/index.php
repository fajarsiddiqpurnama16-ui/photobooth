
<style>
/* =========================
   EFFECTS PAGE
========================= */

.fx-wrap {
    width: 100%;
    max-width: 100%;
    padding: 20px 24px;
    margin: 0;
    box-sizing: border-box;
}

.fx-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 20px;
}

.fx-title {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
    color: #1e293b;
}

.fx-subtitle {
    margin: 5px 0 0;
    font-size: 13px;
    color: #64748b;
}

/* BUTTON */

.fx-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 16px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #fff;
    color: #334155;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: .2s;
}

.fx-btn:hover {
    background: #f8fafc;
    text-decoration: none;
    color: #1e293b;
}

.fx-primary {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}

.fx-primary:hover {
    background: #1d4ed8;
    color: #fff;
}

/* CARD */

.fx-card {
    width: 100%;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(15,23,42,.05);
    overflow: hidden;
}

.fx-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #e2e8f0;
}

.fx-card-header h3 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #334155;
}

.fx-count {
    font-size: 12px;
    color: #64748b;
}

/* TABLE */

.fx-table-wrap {
    width: 100%;
    overflow-x: auto;
}

.fx-table {
    width: 100%;
    min-width: 650px;
    border-collapse: collapse;
    background: #fff;
}

.fx-table th {
    padding: 14px 18px;
    background: #f8fafc;
    color: #64748b;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    text-align: left;
    white-space: nowrap;
    border-bottom: 1px solid #e2e8f0;
}

.fx-table td {
    padding: 13px 18px;
    color: #334155;
    font-size: 13px;
    border-bottom: 1px solid #edf0f5;
    vertical-align: middle;
}

.fx-table tbody tr:hover {
    background: #f8fafc;
}

.fx-table tbody tr:last-child td {
    border-bottom: none;
}

.fx-center {
    text-align: center !important;
}

.fx-icon {
    width: 65px;
    text-align: center !important;
    font-size: 25px !important;
}

.fx-name {
    font-weight: 700;
    color: #1e293b;
}

/* STATUS */

.fx-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 5px 12px;
    border-radius: 30px;
    font-size: 11px;
    font-weight: 700;
}

.fx-on {
    background: #dcfce7;
    color: #15803d;
}

.fx-off {
    background: #f1f5f9;
    color: #64748b;
}

/* ACTION ICON */

.fx-act {
    display: flex;
    align-items: center;
    gap: 7px;
}

.fx-action {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid transparent;
    border-radius: 8px;
    text-decoration: none;
    transition: .2s;
}

.fx-action svg {
    width: 16px;
    height: 16px;
    fill: none;
    stroke: currentColor;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.fx-edit {
    color: #2563eb;
    background: #eff6ff;
    border-color: #dbeafe;
}

.fx-edit:hover {
    color: #fff;
    background: #2563eb;
}

.fx-toggle {
    color: #d97706;
    background: #fffbeb;
    border-color: #fef3c7;
}

.fx-toggle:hover {
    color: #fff;
    background: #d97706;
}

.fx-delete {
    color: #dc2626;
    background: #fef2f2;
    border-color: #fee2e2;
}

.fx-delete:hover {
    color: #fff;
    background: #dc2626;
}

/* PAGINATION */

.fx-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    padding: 14px 20px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
}

.fx-info {
    font-size: 12px;
    color: #64748b;
}

.fx-pagination {
    display: flex;
    align-items: center;
    gap: 5px;
}

.fx-page-btn {
    min-width: 34px;
    height: 34px;
    padding: 0 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e2e8f0;
    border-radius: 7px;
    background: #fff;
    color: #475569;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: .2s;
}

.fx-page-btn:hover:not(:disabled) {
    background: #eff6ff;
    color: #2563eb;
    border-color: #bfdbfe;
}

.fx-page-btn.active {
    background: #2563eb;
    color: #fff;
    border-color: #2563eb;
}

.fx-page-btn:disabled {
    opacity: .4;
    cursor: not-allowed;
}

.fx-page-btn svg {
    width: 14px;
    height: 14px;
    fill: none;
    stroke: currentColor;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}

/* EMPTY */

.fx-empty {
    padding: 55px 20px;
    text-align: center;
    color: #64748b;
}

.fx-empty h4 {
    margin: 0 0 8px;
    font-size: 16px;
    color: #334155;
}

.fx-empty p {
    font-size: 13px;
    margin-bottom: 18px;
}

/* RESPONSIVE */

@media(max-width:768px) {
    .fx-wrap {
        padding: 14px 10px;
    }

    .fx-title {
        font-size: 20px;
    }

    .fx-card-footer {
        padding: 12px;
    }

    .fx-pagination {
        flex-wrap: wrap;
    }
}
</style>


<div class="fx-wrap">

    <!-- HEADER -->
    <div class="fx-head">

        <div>
            <h2 class="fx-title">Efek Wajah</h2>
            <p class="fx-subtitle">
                Kelola dan atur efek wajah untuk kebutuhan photobooth.
            </p>
        </div>

        <a class="fx-btn fx-primary"
           href="<?= site_url('Admin_effects/add') ?>">
            <span>+</span>
            Tambah Efek
        </a>

    </div>


    <!-- CARD -->
    <div class="fx-card">

        <div class="fx-card-header">

            <h3>Daftar Efek</h3>

            <span class="fx-count">
                Total: <?= count($effects); ?> efek
            </span>

        </div>


        <?php if (empty($effects)): ?>

            <div class="fx-empty">
                <h4>Belum Ada Efek</h4>

                <p>
                    Tambahkan efek wajah pertama untuk photobooth kamu.
                </p>

                <a class="fx-btn fx-primary"
                   href="<?= site_url('Admin_effects/add') ?>">
                    + Tambah Efek
                </a>
            </div>

        <?php else: ?>

            <div class="fx-table-wrap">

                <table class="fx-table" id="fxTable">

                    <thead>
                        <tr>
                            <th class="fx-center">Ikon</th>
                            <th>Nama Efek</th>
                            <th>Lapisan</th>
                            <th>Urutan</th>
                            <th>Status</th>
                            <th style="width:150px;">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php foreach ($effects as $e): ?>

                        <?php
                            $n = count(json_decode($e->layers, true) ?: []);
                        ?>

                        <tr>

                            <td class="fx-icon">
                                <?= htmlspecialchars($e->icon, ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <td>
                                <span class="fx-name">
                                    <?= htmlspecialchars($e->name, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>

                            <td><?= $n ?> lapisan</td>

                            <td><?= (int) $e->sort_order ?></td>

                            <td>
                                <span class="fx-badge <?= $e->is_active ? 'fx-on' : 'fx-off' ?>">
                                    <?= $e->is_active ? 'Aktif' : 'Nonaktif' ?>
                                </span>
                            </td>

                            <td>
                                <div class="fx-act">

                                    <!-- EDIT -->
                                    <a class="fx-action fx-edit"
                                       href="<?= site_url('Admin_effects/edit/' . $e->id_effect) ?>"
                                       title="Edit Efek"
                                       aria-label="Edit Efek">

                                        <svg viewBox="0 0 24 24">
                                            <path d="M12 20h9"/>
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                                        </svg>

                                    </a>


                                  <a class="fx-action fx-toggle"
                                    href="<?= site_url('Admin_effects/toggle/' . $e->id_effect) ?>"
                                    title="<?= $e->is_active ? 'Nonaktifkan' : 'Aktifkan' ?>"
                                    data-confirm="<?= $e->is_active ? 'nonaktifkan' : 'aktifkan' ?>"
                                    data-name="<?= htmlspecialchars($e->name, ENT_QUOTES, 'UTF-8') ?>">

                                      <?php if ($e->is_active): ?>
                                          <svg viewBox="0 0 24 24">
                                              <path d="M12 2v10"/>
                                              <path d="M6.2 5.5a9 9 0 1 0 11.6 0"/>
                                          </svg>
                                      <?php else: ?>
                                          <svg viewBox="0 0 24 24">
                                              <path d="m5 12 4 4L19 6"/>
                                          </svg>
                                      <?php endif; ?>

                                  </a>


                               <!-- DELETE -->
                              <a class="fx-action fx-delete"
                                href="<?= site_url('Admin_effects/delete/' . $e->id_effect) ?>"
                                title="Hapus Efek"
                                data-confirm="hapus"
                                data-name="<?= htmlspecialchars($e->name, ENT_QUOTES, 'UTF-8') ?>">

                                  <svg viewBox="0 0 24 24">
                                      <path d="M3 6h18"/>
                                      <path d="M8 6V4h8v2"/>
                                      <path d="m19 6-1 14H6L5 6"/>
                                      <path d="M10 11v5"/>
                                      <path d="M14 11v5"/>
                                  </svg>

                              </a>

                                </div>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>


            <!-- PAGINATION -->
            <div class="fx-card-footer">

                <div class="fx-info" id="fxInfo">
                    Menampilkan data
                </div>

                <div class="fx-pagination" id="fxPagination">

                    <button type="button"
                            class="fx-page-btn"
                            id="fxPrev">

                        <svg viewBox="0 0 24 24">
                            <path d="m15 18-6-6 6-6"/>
                        </svg>

                        Previous
                    </button>

                    <div id="fxPageNumbers"
                         class="fx-pagination">
                    </div>

                    <button type="button"
                            class="fx-page-btn"
                            id="fxNext">

                        Next

                        <svg viewBox="0 0 24 24">
                            <path d="m9 18 6-6-6-6"/>
                        </svg>

                    </button>

                </div>

            </div>

        <?php endif; ?>

    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const table = document.getElementById('fxTable');

    if (!table) return;

    const rows = Array.from(
        table.querySelectorAll('tbody tr')
    );

    const perPage = 10;

    let currentPage = 1;

    const totalData = rows.length;

    const totalPages = Math.ceil(totalData / perPage);

    const info = document.getElementById('fxInfo');

    const prev = document.getElementById('fxPrev');

    const next = document.getElementById('fxNext');

    const pageNumbers = document.getElementById('fxPageNumbers');


    function renderPagination() {

        const start = (currentPage - 1) * perPage;

        const end = Math.min(start + perPage, totalData);

        // Tampilkan data sesuai halaman
        rows.forEach(function (row, index) {

            row.style.display =
                (index >= start && index < end) ? '' : 'none';

        });


        // Informasi data
        info.textContent =
            'Menampilkan ' + (totalData ? start + 1 : 0) +
            '–' + end + ' dari ' + totalData + ' efek wajah';


        // Tombol Previous dan Next
        prev.disabled = currentPage === 1;

        next.disabled = currentPage === totalPages;


        // Nomor halaman
        pageNumbers.innerHTML = '';

        for (let i = 1; i <= totalPages; i++) {

            const button = document.createElement('button');

            button.type = 'button';

            button.className = 'fx-page-btn';

            button.textContent = i;

            if (i === currentPage) {
                button.classList.add('active');
                button.setAttribute('aria-current', 'page');
            }

            button.addEventListener('click', function () {

                currentPage = i;

                renderPagination();

            });

            pageNumbers.appendChild(button);

        }

    }


    // Previous
    prev.addEventListener('click', function () {

        if (currentPage > 1) {

            currentPage--;

            renderPagination();

        }

    });


    // Next
    next.addEventListener('click', function () {

        if (currentPage < totalPages) {

            currentPage++;

            renderPagination();

        }

    });


    renderPagination();

});
</script>

<style>
.fx-confirm-overlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 99999;
    background: rgba(15, 23, 42, .55);
    align-items: center;
    justify-content: center;
    padding: 15px;
}

.fx-confirm-overlay.show {
    display: flex;
}

.fx-confirm-box {
    width: 100%;
    max-width: 390px;
    padding: 28px;
    background: #fff;
    border-radius: 14px;
    text-align: center;
    box-shadow: 0 15px 50px rgba(0,0,0,.2);
    animation: fxPopup .2s ease;
}

@keyframes fxPopup {
    from {
        opacity: 0;
        transform: scale(.92);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.fx-confirm-icon {
    width: 65px;
    height: 65px;
    margin: 0 auto 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #fef2f2;
    color: #dc2626;
}

.fx-confirm-icon svg {
    width: 30px;
    height: 30px;
    fill: none;
    stroke: currentColor;
    stroke-width: 2;
}

.fx-confirm-title {
    font-size: 19px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 9px;
}

.fx-confirm-message {
    font-size: 13px;
    color: #64748b;
    line-height: 1.6;
    margin-bottom: 22px;
}

.fx-confirm-actions {
    display: flex;
    justify-content: center;
    gap: 10px;
}

.fx-confirm-actions button {
    padding: 10px 18px;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}

.fx-confirm-cancel {
    background: #f1f5f9;
    color: #475569;
}

.fx-confirm-yes {
    background: #dc2626;
    color: #fff;
}

.fx-confirm-yes.blue {
    background: #2563eb;
}
</style>


<div class="fx-confirm-overlay" id="fxConfirm">

    <div class="fx-confirm-box">

        <div class="fx-confirm-icon" id="fxConfirmIcon">
            <svg viewBox="0 0 24 24">
                <path d="M3 6h18"/>
                <path d="M8 6V4h8v2"/>
                <path d="m19 6-1 14H6L5 6"/>
            </svg>
        </div>

        <div class="fx-confirm-title" id="fxConfirmTitle">
            Konfirmasi
        </div>

        <div class="fx-confirm-message" id="fxConfirmMessage">
            Apakah kamu yakin?
        </div>

        <div class="fx-confirm-actions">

            <button type="button"
                    class="fx-confirm-cancel"
                    id="fxConfirmCancel">
                Batal
            </button>

            <button type="button"
                    class="fx-confirm-yes"
                    id="fxConfirmYes">
                Ya, Lanjutkan
            </button>

        </div>

    </div>

</div>


<script>
(function () {

    const modal = document.getElementById('fxConfirm');
    const title = document.getElementById('fxConfirmTitle');
    const message = document.getElementById('fxConfirmMessage');
    const yes = document.getElementById('fxConfirmYes');
    const cancel = document.getElementById('fxConfirmCancel');
    const icon = document.getElementById('fxConfirmIcon');

    let targetUrl = '';

    document.addEventListener('click', function (e) {

        const link = e.target.closest('a[data-confirm]');

        if (!link) return;

        e.preventDefault();

        const action = link.dataset.confirm;
        const name = link.dataset.name || 'efek ini';

        targetUrl = link.href;

        if (action === 'hapus') {

            title.textContent = 'Hapus Efek?';

            message.textContent =
                'Yakin ingin menghapus "' + name +
                '"? Efek dan seluruh gambar lapisannya akan dihapus permanen.';

            yes.textContent = 'Ya, Hapus';

            yes.classList.remove('blue');

            icon.style.background = '#fef2f2';
            icon.style.color = '#dc2626';

            icon.innerHTML = `
                <svg viewBox="0 0 24 24">
                    <path d="M3 6h18"/>
                    <path d="M8 6V4h8v2"/>
                    <path d="m19 6-1 14H6L5 6"/>
                </svg>
            `;

        } else if (action === 'nonaktifkan') {

            title.textContent = 'Nonaktifkan Efek?';

            message.textContent =
                'Yakin ingin menonaktifkan "' + name +
                '"? Efek ini tidak akan tersedia untuk digunakan.';

            yes.textContent = 'Ya, Nonaktifkan';

            yes.classList.remove('blue');

            icon.style.background = '#fffbeb';
            icon.style.color = '#d97706';

            icon.innerHTML = `
                <svg viewBox="0 0 24 24">
                    <path d="M12 2v10"/>
                    <path d="M6.2 5.5a9 9 0 1 0 11.6 0"/>
                </svg>
            `;

        } else {

            title.textContent = 'Aktifkan Efek?';

            message.textContent =
                'Yakin ingin mengaktifkan kembali "' + name + '"?';

            yes.textContent = 'Ya, Aktifkan';

            yes.classList.add('blue');

            icon.style.background = '#eff6ff';
            icon.style.color = '#2563eb';

            icon.innerHTML = `
                <svg viewBox="0 0 24 24">
                    <path d="m5 12 4 4L19 6"/>
                </svg>
            `;
        }

        modal.classList.add('show');

    });


    // Tombol Ya
    yes.addEventListener('click', function () {

        if (targetUrl) {
            window.location.href = targetUrl;
        }

    });


    // Tombol Batal
    cancel.addEventListener('click', function () {

        modal.classList.remove('show');
        targetUrl = '';

    });


    // Klik area luar popup
    modal.addEventListener('click', function (e) {

        if (e.target === modal) {
            modal.classList.remove('show');
            targetUrl = '';
        }

    });


    // Tombol ESC
    document.addEventListener('keydown', function (e) {

        if (e.key === 'Escape') {
            modal.classList.remove('show');
            targetUrl = '';
        }

    });

})();
</script>
