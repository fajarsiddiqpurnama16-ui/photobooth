
<style>
/* =====================================
   GALERI FOTO
===================================== */

.photo-tools {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    padding: 15px 18px;
}

.photo-tools-right {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.photo-search,
.photo-page-size {
    height: 36px;
    border: 1px solid var(--line);
    border-radius: 8px;
    padding: 0 10px;
    font-size: 13px;
    background: #fff;
    color: var(--text);
}

.photo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
    gap: 16px;
    padding: 18px;
}

.photo-card {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 11px;
    overflow: hidden;
    transition: .2s;
}

.photo-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 7px 20px rgba(15,23,42,.08);
}

.photo-preview {
    width: 100%;
    aspect-ratio: 1 / 1;
    background: #eef1f5;
    cursor: zoom-in;
    overflow: hidden;
}

.photo-preview img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
    transition: transform .25s;
}

.photo-preview:hover img {
    transform: scale(1.04);
}

.photo-info {
    padding: 12px;
}

.photo-name {
    font-size: 13px;
    font-weight: 700;
    color: var(--text);
    overflow-wrap: anywhere;
    margin-bottom: 5px;
}

.photo-date {
    font-size: 12px;
    color: var(--muted);
    margin-bottom: 12px;
}

.photo-actions {
    display: flex;
    gap: 7px;
}

.photo-action {
    flex: 1;
    height: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    border-radius: 7px;
    border: 1px solid;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    background: #fff;
}

.photo-action svg {
    width: 15px;
    height: 15px;
    fill: none;
    stroke: currentColor;
    stroke-width: 1.8;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.photo-view {
    color: #2563eb;
    background: #eff6ff;
    border-color: #dbeafe;
}

.photo-delete {
    color: #dc2626;
    background: #fef2f2;
    border-color: #fecaca;
}

.photo-action:hover {
    filter: brightness(.96);
}

/* =====================================
   PAGINATION
===================================== */

.photo-pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    padding: 15px 18px;
    border-top: 1px solid var(--line);
}

.photo-page-info {
    font-size: 12px;
    color: var(--muted);
}

.photo-page-controls {
    display: flex;
    align-items: center;
    gap: 5px;
}

.photo-page-controls button {
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

.photo-page-controls button.active {
    background: #2563eb;
    color: #fff;
    border-color: #2563eb;
}

.photo-page-controls button:disabled {
    opacity: .4;
    cursor: not-allowed;
}

/* =====================================
   PREVIEW MODAL
===================================== */

#photoModal {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9990;
    background: rgba(8,15,30,.88);
    align-items: center;
    justify-content: center;
    padding: 24px;
}

.photo-modal-content {
    position: relative;
    max-width: min(92vw, 650px);
    max-height: 94vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}

#photoModalImg {
    max-width: 100%;
    max-height: 82vh;
    object-fit: contain;
    border-radius: 10px;
    background: #fff;
    box-shadow: 0 10px 40px rgba(0,0,0,.5);
}

.photo-modal-close {
    position: absolute;
    top: -13px;
    right: -13px;
    width: 36px;
    height: 36px;
    border: 0;
    border-radius: 50%;
    background: #fff;
    color: #111;
    font-size: 17px;
    font-weight: 700;
    cursor: pointer;
    z-index: 2;
}

.photo-modal-footer {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 12px;
    color: #fff;
    font-size: 13px;
    text-align: center;
}

/* =====================================
   KONFIRMASI HAPUS
===================================== */

.photo-confirm-overlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 10000;
    background: rgba(15,23,42,.6);
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.photo-confirm-overlay.open {
    display: flex;
}

.photo-confirm-box {
    width: 100%;
    max-width: 390px;
    padding: 28px;
    background: #fff;
    border-radius: 14px;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0,0,0,.2);
    animation: photoPopup .2s ease;
}

@keyframes photoPopup {
    from {
        opacity: 0;
        transform: scale(.94);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.photo-confirm-icon {
    width: 65px;
    height: 65px;
    margin: 0 auto 16px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fef2f2;
    color: #dc2626;
}

.photo-confirm-icon svg {
    width: 30px;
    height: 30px;
    fill: none;
    stroke: currentColor;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.photo-confirm-title {
    font-size: 19px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 8px;
}

.photo-confirm-message {
    font-size: 13px;
    line-height: 1.6;
    color: #64748b;
    margin-bottom: 22px;
}

.photo-confirm-actions {
    display: flex;
    justify-content: center;
    gap: 10px;
}

.photo-confirm-actions button {
    padding: 10px 18px;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}

.photo-confirm-cancel {
    background: #f1f5f9;
    color: #475569;
}

.photo-confirm-yes {
    background: #dc2626;
    color: #fff;
}

.photo-empty {
    padding: 40px 20px;
    text-align: center;
    color: var(--muted);
}

@media(max-width:600px) {
    .photo-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        padding: 12px;
    }

    .photo-info {
        padding: 9px;
    }

    .photo-actions {
        gap: 5px;
    }

    .photo-action {
        font-size: 11px;
    }
}
</style>


<!-- =====================================
     HEADER
===================================== -->

<div class="topbar">
    <div class="page-header">
        <h1>Galeri Foto</h1>
        <p>Semua hasil foto dari pengunjung.</p>
    </div>
</div>


<!-- =====================================
     GALERI
===================================== -->

<div class="card flush">

    <div class="photo-tools">

        <div>
            <strong>Daftar Foto</strong>

            <span style="font-size:12px;color:var(--muted);margin-left:6px">
                Total: <?= count($photos) ?> foto
            </span>
        </div>

        <?php if (!empty($photos)): ?>

        <div class="photo-tools-right">

            <select id="photoPageSize" class="photo-page-size">
                <option value="5">5 foto</option>
                <option value="10" selected>10 foto</option>
                <option value="25">25 foto</option>
                <option value="50">50 foto</option>
            </select>

            <input type="text"
                   id="photoSearch"
                   class="photo-search"
                   placeholder="Cari template...">

        </div>

        <?php endif; ?>

    </div>


    <?php if (!empty($photos)): ?>

    <div class="photo-grid" id="photoGrid">

        <?php foreach ($photos as $p): ?>

        <?php
            $img = base_url('uploads/photos/' . rawurlencode($p->filename));

            $photoTitle = $p->template_slug . ' · ' .
                          date('d M Y, H:i', strtotime($p->created_at));
        ?>

        <div class="photo-card photo-item"
             data-search="<?= htmlspecialchars($p->template_slug, ENT_QUOTES, 'UTF-8') ?>">

            <!-- FOTO -->
            <div class="photo-preview js-view"
                 data-src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>"
                 data-title="<?= htmlspecialchars($photoTitle, ENT_QUOTES, 'UTF-8') ?>">

                <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>"
                     alt="Foto <?= (int)$p->id ?>"
                     loading="lazy">

            </div>


            <!-- INFORMASI -->
            <div class="photo-info">

                <div class="photo-name">
                    <?= htmlspecialchars($p->template_slug, ENT_QUOTES, 'UTF-8') ?>
                </div>

                <div class="photo-date">
                    <svg style="width:12px;height:12px;vertical-align:middle"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="2">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 7v5l3 2"/>
                    </svg>

                    <?= date('d M Y, H:i', strtotime($p->created_at)) ?>
                </div>


                <!-- AKSI -->
                <div class="photo-actions">

                    <button type="button"
                            class="photo-action photo-view js-view"
                            data-src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>"
                            data-title="<?= htmlspecialchars($photoTitle, ENT_QUOTES, 'UTF-8') ?>">

                        <svg viewBox="0 0 24 24">
                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>

                        Lihat

                    </button>


                    <button type="button"
                            class="photo-action photo-delete js-photo-delete"
                            data-url="<?= base_url('index.php/Photos/delete/' . $p->id) ?>"
                            data-name="<?= htmlspecialchars($p->template_slug, ENT_QUOTES, 'UTF-8') ?>">

                        <svg viewBox="0 0 24 24">
                            <path d="M3 6h18"/>
                            <path d="M8 6V4h8v2"/>
                            <path d="m19 6-1 14H6L5 6"/>
                            <path d="M10 11v6"/>
                            <path d="M14 11v6"/>
                        </svg>

                        Hapus

                    </button>

                </div>

            </div>

        </div>

        <?php endforeach; ?>

    </div>


    <!-- PAGINATION -->
    <div class="photo-pagination">

        <div class="photo-page-info" id="photoPageInfo">
            Menampilkan foto
        </div>

        <div class="photo-page-controls" id="photoPageControls"></div>

    </div>


    <?php else: ?>

    <div class="photo-empty">
        Belum ada foto tersimpan.
    </div>

    <?php endif; ?>

</div>


<!-- =====================================
     MODAL PREVIEW FOTO
===================================== -->

<div id="photoModal">

    <div class="photo-modal-content">

        <button type="button"
                id="photoModalClose"
                class="photo-modal-close"
                aria-label="Tutup">
            ✕
        </button>

        <img id="photoModalImg"
             src=""
             alt="Preview Foto">

        <div class="photo-modal-footer">

            <span id="photoModalTitle"></span>

            <a id="photoModalDownload"
               href="#"
               download
               class="btn btn-secondary btn-sm">

                <svg style="width:14px;height:14px;vertical-align:middle"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <path d="m7 10 5 5 5-5"/>
                    <path d="M12 15V3"/>
                </svg>

                Unduh

            </a>

        </div>

    </div>

</div>


<!-- =====================================
     MODAL KONFIRMASI HAPUS
===================================== -->

<div class="photo-confirm-overlay" id="photoConfirmModal">

    <div class="photo-confirm-box">

        <div class="photo-confirm-icon">

            <svg viewBox="0 0 24 24">
                <path d="M3 6h18"/>
                <path d="M8 6V4h8v2"/>
                <path d="m19 6-1 14H6L5 6"/>
                <path d="M10 11v6"/>
                <path d="M14 11v6"/>
            </svg>

        </div>

        <div class="photo-confirm-title">
            Hapus Foto?
        </div>

        <div class="photo-confirm-message" id="photoConfirmMessage">
            Yakin ingin menghapus foto ini?
        </div>

        <div class="photo-confirm-actions">

            <button type="button"
                    class="photo-confirm-cancel"
                    id="photoConfirmCancel">
                Batal
            </button>

            <button type="button"
                    class="photo-confirm-yes"
                    id="photoConfirmYes">
                Ya, Hapus
            </button>

        </div>

    </div>

</div>


<script>
(function () {

    /* =====================================
       PREVIEW FOTO
    ===================================== */

    const modal = document.getElementById('photoModal');
    const modalImg = document.getElementById('photoModalImg');
    const modalTitle = document.getElementById('photoModalTitle');
    const download = document.getElementById('photoModalDownload');

    function openPhoto(src, text) {

        modalImg.src = src;
        modalTitle.textContent = text || '';
        download.href = src;

        modal.style.display = 'flex';

        document.body.style.overflow = 'hidden';

    }

    function closePhoto() {

        modal.style.display = 'none';

        modalImg.src = '';

        document.body.style.overflow = '';

    }

    document.addEventListener('click', function(e) {

        const btn = e.target.closest('.js-view');

        if (!btn) return;

        openPhoto(
            btn.dataset.src,
            btn.dataset.title
        );

    });

    document.getElementById('photoModalClose')
        .addEventListener('click', closePhoto);

    modal.addEventListener('click', function(e) {

        if (e.target === modal) {
            closePhoto();
        }

    });


    /* =====================================
       KONFIRMASI HAPUS FOTO
    ===================================== */

    const confirmModal = document.getElementById('photoConfirmModal');
    const confirmMessage = document.getElementById('photoConfirmMessage');
    const confirmYes = document.getElementById('photoConfirmYes');
    const confirmCancel = document.getElementById('photoConfirmCancel');

    let deleteUrl = null;

    document.addEventListener('click', function(e) {

        const btn = e.target.closest('.js-photo-delete');

        if (!btn) return;

        const name = btn.dataset.name || 'foto ini';

        deleteUrl = btn.dataset.url;

        confirmMessage.textContent =
            'Yakin ingin menghapus foto dari template "' +
            name + '"? Foto yang dihapus tidak dapat dikembalikan.';

        confirmModal.classList.add('open');

    });

    confirmYes.addEventListener('click', function() {

        if (deleteUrl) {
            window.location.href = deleteUrl;
        }

    });

    function closeConfirm() {

        confirmModal.classList.remove('open');

        deleteUrl = null;

    }

    confirmCancel.addEventListener('click', closeConfirm);

    confirmModal.addEventListener('click', function(e) {

        if (e.target === confirmModal) {
            closeConfirm();
        }

    });


    /* =====================================
       PAGINATION + SEARCH
    ===================================== */

    const grid = document.getElementById('photoGrid');

    if (grid) {

        const items = Array.from(
            grid.querySelectorAll('.photo-item')
        );

        const search = document.getElementById('photoSearch');
        const pageSize = document.getElementById('photoPageSize');
        const pageInfo = document.getElementById('photoPageInfo');
        const pageControls = document.getElementById('photoPageControls');

        let currentPage = 1;

        function getFilteredItems() {

            const keyword = search.value.toLowerCase().trim();

            return items.filter(function(item) {

                return item.dataset.search
                    .toLowerCase()
                    .includes(keyword);

            });

        }

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

                render();

            });

            pageControls.appendChild(button);

        }

        function render() {

            const filtered = getFilteredItems();

            const perPage = parseInt(pageSize.value, 10);

            const total = filtered.length;

            const totalPages = Math.ceil(total / perPage);

            currentPage = Math.min(
                Math.max(1, currentPage),
                Math.max(1, totalPages)
            );

            const start = (currentPage - 1) * perPage;

            const end = Math.min(start + perPage, total);

            /* Sembunyikan semua foto */
            items.forEach(function(item) {
                item.style.display = 'none';
            });

            /* Tampilkan sesuai halaman */
            filtered.slice(start, end).forEach(function(item) {
                item.style.display = '';
            });

            /* Informasi */
            if (total === 0) {

                pageInfo.textContent = 'Tidak ada foto ditemukan';

            } else {

                pageInfo.textContent =
                    'Menampilkan ' + (start + 1) +
                    '–' + end + ' dari ' + total + ' foto';

            }

            /* Pagination */
            pageControls.innerHTML = '';

            if (totalPages <= 1) return;

            createButton(
                '‹',
                currentPage - 1,
                currentPage === 1,
                false
            );

            const first = Math.max(1, currentPage - 2);

            const last = Math.min(totalPages, currentPage + 2);

            for (let i = first; i <= last; i++) {

                createButton(
                    i,
                    i,
                    false,
                    i === currentPage
                );

            }

            createButton(
                '›',
                currentPage + 1,
                currentPage === totalPages,
                false
            );

        }

        search.addEventListener('input', function() {

            currentPage = 1;

            render();

        });

        pageSize.addEventListener('change', function() {

            currentPage = 1;

            render();

        });

        render();

    }


    /* =====================================
       TOMBOL ESCAPE
    ===================================== */

    document.addEventListener('keydown', function(e) {

        if (e.key === 'Escape') {

            closePhoto();

            closeConfirm();

        }

    });

})();
</script>
