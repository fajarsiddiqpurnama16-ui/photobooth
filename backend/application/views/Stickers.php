
<style>
.st-thumb {
    width: 56px;
    height: 56px;
    object-fit: contain;
    border-radius: 8px;
    border: 1px solid var(--line);
    background: repeating-conic-gradient(#f1f5f9 0% 25%, #fff 0% 50%) 50%/12px 12px;
}

.st-form {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: flex-end;
    padding: 18px 20px;
}

.st-form label {
    display: flex;
    flex-direction: column;
    gap: 6px;
    font-size: 13px;
    color: var(--muted);
}

.st-form input[type=text] {
    height: 36px;
    padding: 0 10px;
    border: 1px solid var(--line);
    border-radius: 8px;
    min-width: 220px;
}

.st-form input[type=file] {
    font-size: 13px;
    padding: 6px 0;
}

/* Tabel */
.st-table {
    width: 100%;
    border-collapse: collapse;
}

.st-table th,
.st-table td {
    padding: 12px 16px;
    border-bottom: 1px solid var(--line);
    text-align: left;
    vertical-align: middle;
}

.st-table th {
    font-size: 12px;
    text-transform: uppercase;
    color: var(--muted);
    background: #f8fafc;
}

.st-table tbody tr:hover {
    background: #f8fafc;
}

.st-actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}

.st-icon-btn {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    border: 1px solid;
    cursor: pointer;
    text-decoration: none;
    background: #fff;
    transition: .2s;
}

.st-icon-btn svg {
    width: 17px;
    height: 17px;
    fill: none;
    stroke: currentColor;
    stroke-width: 1.8;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.st-edit {
    color: #2563eb;
    border-color: #dbeafe;
    background: #eff6ff;
}

.st-toggle {
    color: #d97706;
    border-color: #fde68a;
    background: #fffbeb;
}

.st-delete {
    color: #dc2626;
    border-color: #fecaca;
    background: #fef2f2;
}

.st-icon-btn:hover {
    filter: brightness(.96);
    transform: translateY(-1px);
}

.st-pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    padding: 15px 18px;
    border-top: 1px solid var(--line);
}

.st-page-info {
    font-size: 12px;
    color: var(--muted);
}

.st-page-controls {
    display: flex;
    align-items: center;
    gap: 5px;
}

.st-page-controls button {
    min-width: 34px;
    height: 34px;
    padding: 0 9px;
    border: 1px solid var(--line);
    border-radius: 7px;
    background: #fff;
    color: #475569;
    cursor: pointer;
    font-size: 13px;
}

.st-page-controls button.active {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}

.st-page-controls button:disabled {
    opacity: .4;
    cursor: not-allowed;
}

.st-page-size,
.st-search {
    height: 35px;
    border: 1px solid var(--line);
    border-radius: 7px;
    padding: 0 9px;
    font-size: 13px;
    background: #fff;
    color: #334155;
}

.st-table-tools {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    padding: 14px 18px;
    flex-wrap: wrap;
}

.st-empty {
    padding: 30px;
    text-align: center;
    color: var(--muted);
}

/* Modal */
.st-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15,23,42,.55);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    z-index: 99999;
}

.st-modal-overlay.open {
    display: flex;
}

.st-modal {
    background: #fff;
    border-radius: 14px;
    width: 100%;
    max-width: 400px;
    padding: 28px;
    text-align: center;
    box-shadow: 0 24px 60px rgba(15,23,42,.25);
    animation: stPopup .2s ease;
}

@keyframes stPopup {
    from {
        opacity: 0;
        transform: scale(.94);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.st-modal-icon {
    width: 65px;
    height: 65px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    margin: 0 auto 16px;
}

.st-modal-icon svg {
    width: 30px;
    height: 30px;
    fill: none;
    stroke: currentColor;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.st-modal-title {
    font-size: 19px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 8px;
}

.st-modal-message {
    font-size: 13px;
    line-height: 1.6;
    color: #64748b;
    margin-bottom: 22px;
}

.st-modal-actions {
    display: flex;
    justify-content: center;
    gap: 10px;
}

.st-modal-actions button {
    padding: 10px 18px;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}

.st-modal-cancel {
    background: #f1f5f9;
    color: #475569;
}

.st-modal-confirm {
    background: #dc2626;
    color: #fff;
}

.st-modal-confirm.blue {
    background: #2563eb;
}

.st-modal-confirm.amber {
    background: #d97706;
}

@media(max-width:600px) {
    .st-table-tools,
    .st-pagination {
        align-items: flex-start;
    }

    .st-table th,
    .st-table td {
        padding: 10px;
    }

    .st-modal {
        padding: 22px;
    }
}
</style>


<!-- HEADER -->
<div class="topbar">
    <div class="page-header">
        <h1>Stiker</h1>
        <p>Tambah stiker (kucing, Nailong, dll) yang bisa dipilih customer di hasil foto.</p>
    </div>
</div>


<!-- FORM TAMBAH STIKER -->
<div class="card" style="margin-bottom:16px">

    <form class="st-form"
          action="<?= base_url('index.php/AdminStickers/add') ?>"
          method="post"
          enctype="multipart/form-data">

        <label>
            Nama stiker
            <input type="text"
                   name="name"
                   placeholder="Contoh: Kucing lidah"
                   required
                   maxlength="100">
        </label>

        <label>
            Gambar (PNG / WEBP transparan, maks 2 MB)
            <input type="file"
                   name="image"
                   accept="image/png,image/webp"
                   required>
        </label>

        <button type="submit" class="btn btn-primary">
            + Tambah Stiker
        </button>

    </form>
</div>


<!-- DAFTAR STIKER -->
<div class="card flush">

    <div class="st-table-tools">

        <div>
            <strong>Daftar Stiker</strong>
            <span style="font-size:12px;color:var(--muted);margin-left:6px">
                Total: <?= count($stickers) ?> stiker
            </span>
        </div>

        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">

            <select id="stPageSize" class="st-page-size">
                <option value="5">5 data</option>
                <option value="10" selected>10 data</option>
                <option value="25">25 data</option>
                <option value="50">50 data</option>
            </select>

            <input type="text"
                   id="stSearch"
                   class="st-search"
                   placeholder="Cari stiker...">

        </div>

    </div>


    <div class="table-responsive">

        <table class="st-table">

            <thead>
                <tr>
                    <th>Gambar</th>
                    <th>Nama</th>
                    <th>Status</th>
                    <th style="text-align:right">Aksi</th>
                </tr>
            </thead>

            <tbody id="stTableBody">

                <?php foreach ($stickers as $s): ?>

                <tr class="st-row">

                    <td>
                        <img class="st-thumb"
                             src="<?= base_url('uploads/stickers/' . rawurlencode($s->filename)) ?>"
                             alt="<?= htmlspecialchars($s->name, ENT_QUOTES, 'UTF-8') ?>">
                    </td>

                    <td>
                        <b><?= htmlspecialchars($s->name, ENT_QUOTES, 'UTF-8') ?></b>
                    </td>

                    <td>

                        <?php if ($s->is_active): ?>

                            <span class="badge badge-green">
                                Aktif
                            </span>

                        <?php else: ?>

                            <span class="badge badge-amber">
                                Nonaktif
                            </span>

                        <?php endif; ?>

                    </td>

                    <td>

                        <div class="st-actions">

                            <!-- TOGGLE -->
                            <a href="<?= base_url('index.php/AdminStickers/toggle/' . $s->id) ?>"
                               class="st-icon-btn st-toggle js-confirm"
                               data-action="<?= $s->is_active ? 'nonaktifkan' : 'aktifkan' ?>"
                               data-name="<?= htmlspecialchars($s->name, ENT_QUOTES, 'UTF-8') ?>"
                               title="<?= $s->is_active ? 'Nonaktifkan' : 'Aktifkan' ?>">

                                <?php if ($s->is_active): ?>

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


                            <!-- HAPUS -->
                            <button type="button"
                                    class="st-icon-btn st-delete js-confirm"
                                    data-url="<?= base_url('index.php/AdminStickers/delete/' . $s->id) ?>"
                                    data-action="hapus"
                                    data-name="<?= htmlspecialchars($s->name, ENT_QUOTES, 'UTF-8') ?>"
                                    title="Hapus">

                                <svg viewBox="0 0 24 24">
                                    <path d="M3 6h18"/>
                                    <path d="M8 6V4h8v2"/>
                                    <path d="m19 6-1 14H6L5 6"/>
                                    <path d="M10 11v6"/>
                                    <path d="M14 11v6"/>
                                </svg>

                            </button>

                        </div>

                    </td>

                </tr>

                <?php endforeach; ?>

                <?php if (empty($stickers)): ?>

                <tr>
                    <td colspan="4" class="st-empty">
                        Belum ada stiker.
                    </td>
                </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>


    <!-- PAGINATION -->
    <div class="st-pagination" id="stPagination">

        <div class="st-page-info" id="stPageInfo">
            Menampilkan 0 data
        </div>

        <div class="st-page-controls" id="stPageControls"></div>

    </div>

</div>


<!-- MODAL KONFIRMASI -->
<div class="st-modal-overlay" id="stConfirmModal">

    <div class="st-modal">

        <div class="st-modal-icon" id="stModalIcon">

            <svg viewBox="0 0 24 24">
                <path d="M3 6h18"/>
                <path d="M8 6V4h8v2"/>
                <path d="m19 6-1 14H6L5 6"/>
            </svg>

        </div>

        <div class="st-modal-title" id="stModalTitle">
            Konfirmasi
        </div>

        <div class="st-modal-message" id="stModalMessage">
            Apakah kamu yakin?
        </div>

        <div class="st-modal-actions">

            <button type="button"
                    class="st-modal-cancel"
                    id="stModalCancel">
                Batal
            </button>

            <button type="button"
                    class="st-modal-confirm"
                    id="stModalYes">
                Ya, Lanjutkan
            </button>

        </div>

    </div>

</div>


<script>
(function () {

    /* =====================================
       KONFIRMASI AKSI
    ===================================== */

    const modal = document.getElementById('stConfirmModal');
    const modalTitle = document.getElementById('stModalTitle');
    const modalMessage = document.getElementById('stModalMessage');
    const modalIcon = document.getElementById('stModalIcon');
    const modalYes = document.getElementById('stModalYes');
    const modalCancel = document.getElementById('stModalCancel');

    let targetUrl = null;

    document.addEventListener('click', function(e) {

        const btn = e.target.closest('.js-confirm');

        if (!btn) return;

        e.preventDefault();

        const action = btn.dataset.action;
        const name = btn.dataset.name || 'stiker ini';

        targetUrl = btn.dataset.url || btn.href;

        modalYes.className = 'st-modal-confirm';

        if (action === 'hapus') {

            modalTitle.textContent = 'Hapus Stiker?';

            modalMessage.textContent =
                'Yakin ingin menghapus "' + name +
                '"? Stiker ini akan dihapus permanen.';

            modalYes.textContent = 'Ya, Hapus';

            modalIcon.style.background = '#fef2f2';
            modalIcon.style.color = '#dc2626';

            modalIcon.innerHTML = `
                <svg viewBox="0 0 24 24">
                    <path d="M3 6h18"/>
                    <path d="M8 6V4h8v2"/>
                    <path d="m19 6-1 14H6L5 6"/>
                </svg>
            `;

        } else if (action === 'nonaktifkan') {

            modalTitle.textContent = 'Nonaktifkan Stiker?';

            modalMessage.textContent =
                'Yakin ingin menonaktifkan "' + name +
                '"? Stiker ini tidak akan tersedia untuk customer.';

            modalYes.textContent = 'Ya, Nonaktifkan';

            modalYes.classList.add('amber');

            modalIcon.style.background = '#fffbeb';
            modalIcon.style.color = '#d97706';

            modalIcon.innerHTML = `
                <svg viewBox="0 0 24 24">
                    <path d="M12 2v10"/>
                    <path d="M6.2 5.5a9 9 0 1 0 11.6 0"/>
                </svg>
            `;

        } else {

            modalTitle.textContent = 'Aktifkan Stiker?';

            modalMessage.textContent =
                'Yakin ingin mengaktifkan kembali "' + name +
                '" agar bisa digunakan customer?';

            modalYes.textContent = 'Ya, Aktifkan';

            modalYes.classList.add('blue');

            modalIcon.style.background = '#eff6ff';
            modalIcon.style.color = '#2563eb';

            modalIcon.innerHTML = `
                <svg viewBox="0 0 24 24">
                    <path d="m5 12 4 4L19 6"/>
                </svg>
            `;
        }

        modal.classList.add('open');

    });


    modalYes.addEventListener('click', function() {

        if (targetUrl) {
            window.location.href = targetUrl;
        }

    });


    function closeModal() {
        modal.classList.remove('open');
        targetUrl = null;
    }

    modalCancel.addEventListener('click', closeModal);

    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModal();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });


    /* =====================================
       PAGINATION + SEARCH
    ===================================== */

    const tbody = document.getElementById('stTableBody');
    const search = document.getElementById('stSearch');
    const pageSize = document.getElementById('stPageSize');
    const pageInfo = document.getElementById('stPageInfo');
    const pageControls = document.getElementById('stPageControls');

    const allRows = Array.from(
        tbody.querySelectorAll('tr.st-row')
    );

    let currentPage = 1;

    function getFilteredRows() {

        const keyword = search.value.toLowerCase().trim();

        return allRows.filter(function(row) {
            return row.textContent.toLowerCase().includes(keyword);
        });

    }

    function renderPagination() {

        const filteredRows = getFilteredRows();

        const perPage = parseInt(pageSize.value, 10);

        const total = filteredRows.length;

        const totalPages = Math.ceil(total / perPage);

        if (currentPage > totalPages) {
            currentPage = Math.max(1, totalPages);
        }

        const start = (currentPage - 1) * perPage;

        const end = Math.min(start + perPage, total);

        allRows.forEach(function(row) {
            row.style.display = 'none';
        });

        filteredRows.slice(start, end).forEach(function(row) {
            row.style.display = '';
        });

        /* Informasi data */
        if (total === 0) {

            pageInfo.textContent = 'Tidak ada data ditemukan';

        } else {

            pageInfo.textContent =
                'Menampilkan ' + (start + 1) +
                '–' + end + ' dari ' + total + ' stiker';

        }

        /* Nomor halaman */
        pageControls.innerHTML = '';

        if (totalPages <= 1) return;

        function createButton(label, page, disabled, active) {

            const button = document.createElement('button');

            button.type = 'button';
            button.textContent = label;
            button.disabled = disabled;

            if (active) {
                button.classList.add('active');
            }

            button.addEventListener('click', function() {
                currentPage = page;
                renderPagination();
            });

            pageControls.appendChild(button);

        }

        /* Previous */
        createButton(
            '‹',
            currentPage - 1,
            currentPage === 1,
            false
        );

        /* Nomor halaman */
        let firstPage = Math.max(1, currentPage - 2);
        let lastPage = Math.min(totalPages, currentPage + 2);

        for (let i = firstPage; i <= lastPage; i++) {

            createButton(
                i,
                i,
                false,
                i === currentPage
            );

        }

        /* Next */
        createButton(
            '›',
            currentPage + 1,
            currentPage === totalPages,
            false
        );

    }

    search.addEventListener('input', function() {
        currentPage = 1;
        renderPagination();
    });

    pageSize.addEventListener('change', function() {
        currentPage = 1;
        renderPagination();
    });

    renderPagination();

})();
</script>
