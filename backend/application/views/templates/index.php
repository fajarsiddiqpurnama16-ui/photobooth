<div class="topbar">
    <div class="page-header">
        <h1> Kelola Template Photobooth</h1>
        <p>Katalog desain frame dan slot cetak foto yang tersedia untuk customer photobooth studio.</p>
    </div>
    <div class="topbar-actions">
        <a href="<?= site_url('Admin/add') ?>" class="btn btn-primary">
            <span>➕ Tambah Template Baru</span>
        </a>
    </div>
</div>

<style>
    .filter-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
        margin-bottom: 22px;
    }

    .category-tabs {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .cat-tab {
        padding: 7px 14px;
        border-radius: var(--radius-md);
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid var(--card-border);
        color: var(--text-secondary);
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .cat-tab:hover {
        background: rgba(255, 255, 255, 0.08);
        color: #ffffff;
    }

    .cat-tab.active {
        background: var(--gradient-primary);
        color: #ffffff;
        border-color: transparent;
        box-shadow: 0 4px 15px rgba(139, 92, 246, 0.35);
    }

    /* Template Cards Grid */
    .template-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
        margin-top: 10px;
    }

    .template-card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: var(--radius-lg);
        overflow: hidden;
        backdrop-filter: blur(16px);
        transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
        display: flex;
        flex-direction: column;
    }

    .template-card:hover {
        transform: translateY(-4px);
        border-color: rgba(139, 92, 246, 0.4);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
    }

    .template-preview-box {
        height: 180px;
        background: #090e17;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border-bottom: 1px solid var(--card-border);
    }

    .mini-strip-frame {
        width: 70px;
        height: 140px;
        background: #ffffff;
        border-radius: 4px;
        padding: 5px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.6);
        display: flex;
        flex-direction: column;
        gap: 4px;
        align-items: center;
        justify-content: center;
    }

    .mini-slot {
        width: 100%;
        background: #1e293b;
        border-radius: 2px;
        flex: 1;
    }

    .template-body {
        padding: 18px;
        display: flex;
        flex-direction: column;
        flex: 1;
    }

    .template-title {
        font-family: 'Outfit', sans-serif;
        font-size: 16px;
        font-weight: 700;
        color: #ffffff;
        margin-bottom: 4px;
    }

    .template-slug {
        font-family: 'JetBrains Mono', monospace;
        font-size: 11.5px;
        color: var(--cyan-light);
        margin-bottom: 14px;
    }

    .template-meta-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        font-size: 12px;
    }

    .template-footer-actions {
        margin-top: auto;
        padding-top: 14px;
        border-top: 1px solid rgba(255, 255, 255, 0.06);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .view-toggle-btn {
        padding: 6px 12px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--card-border);
        border-radius: 8px;
        color: var(--text-secondary);
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .view-toggle-btn.active {
        background: var(--primary);
        color: #ffffff;
        border-color: var(--primary);
    }
</style>

<!-- Filter & Search Toolbar -->
<div class="filter-bar">
    <div class="category-tabs">
        <button type="button" class="cat-tab active" onclick="filterCategory('all', this)">Semua (<?= count($templates) ?>)</button>
        <button type="button" class="cat-tab" onclick="filterCategory('strip', this)">🎞️ Strip</button>
        <button type="button" class="cat-tab" onclick="filterCategory('grid', this)">🔲 Grid</button>
        <button type="button" class="cat-tab" onclick="filterCategory('single', this)">📷 Single</button>
        <button type="button" class="cat-tab" onclick="filterCategory('creative', this)">🎨 Creative</button>
    </div>

    <div style="display:flex; align-items:center; gap:10px;">
        <input type="text" id="templateSearch" placeholder="Cari template..." 
               style="width:220px; padding:7px 12px; font-size:12.5px;" onkeyup="filterTemplateCards()">
        
        <div style="display:flex; gap:4px;">
            <button type="button" class="view-toggle-btn active" id="btnViewGrid" onclick="switchView('grid')">Cards</button>
            <button type="button" class="view-toggle-btn" id="btnViewTable" onclick="switchView('table')">Tabel</button>
        </div>
    </div>
</div>

<!-- View 1: Card Grid View -->
<div id="cardsView" class="template-grid">
    <?php foreach ($templates as $t): ?>
        <div class="template-card" data-category="<?= $t->category ?>" data-name="<?= strtolower($t->name . ' ' . $t->slug) ?>">
            <!-- Mini preview box -->
            <div class="template-preview-box">
                <?php 
                    $imgSrc = '';
                    if (!empty($t->template_image)) {
                        if (strpos($t->template_image, 'http') === 0 || strpos($t->template_image, 'data:') === 0) {
                            $imgSrc = $t->template_image;
                        } elseif (file_exists(FCPATH . 'uploads/templates/' . $t->template_image)) {
                            $imgSrc = base_url('uploads/templates/' . $t->template_image);
                        }
                    }
                    $hasImg = !empty($imgSrc);
                ?>
                <?php if ($hasImg): ?>
                    <img src="<?= $imgSrc ?>" 
                         alt="<?= htmlspecialchars($t->name) ?>" 
                         style="max-height:160px; max-width:85%; object-fit:contain; filter:drop-shadow(0 8px 20px rgba(0,0,0,0.6)); border-radius:4px;">
                <?php else: ?>
                    <div class="mini-strip-frame" style="width:<?= ($t->category === 'grid' || $t->category === 'single') ? '100px' : '70px' ?>;">
                        <?php 
                            $cnt = min((int)$t->photo_count, 4);
                            if ($cnt <= 0) $cnt = 3;
                            for ($i = 0; $i < $cnt; $i++): 
                        ?>
                            <div class="mini-slot" style="background:<?= ($i % 2 === 0) ? '#3b82f6' : '#8b5cf6' ?>;"></div>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>

                <div style="position:absolute; top:12px; right:12px;">
                    <a href="<?= base_url('index.php/Admin/toggle_active/' . $t->id_template) ?>" style="text-decoration:none;" title="Klik untuk ubah status (Aktif/Nonaktif)">
                        <?php if ($t->is_active): ?>
                            <span class="badge badge-green" style="font-size:10px; cursor:pointer;">● Aktif</span>
                        <?php else: ?>
                            <span class="badge badge-gray" style="font-size:10px; cursor:pointer; background:rgba(239,68,68,0.2); color:#fca5a5; border-color:rgba(239,68,68,0.3);">○ Nonaktif (Klik untuk Aktifkan)</span>
                        <?php endif; ?>
                    </a>
                </div>

                <div style="position:absolute; bottom:10px; left:12px; font-size:10.5px; color:var(--text-muted); font-family:'JetBrains Mono', monospace; background:rgba(0,0,0,0.6); padding:2px 6px; border-radius:4px;">
                    <?= $t->width ?> × <?= $t->height ?> px
                </div>
            </div>

            <!-- Card Body -->
            <div class="template-body">
                <div class="template-title"><?= htmlspecialchars($t->name) ?></div>
                <div class="template-slug">#<?= htmlspecialchars($t->slug) ?></div>

                <div class="template-meta-row">
                    <span class="badge badge-purple" style="text-transform:capitalize;">
                        <?= htmlspecialchars($t->category) ?>
                    </span>
                    <span style="color:var(--text-muted); font-size:11.5px; font-weight:600;">
                        📸 <?= $t->photo_count ?> Foto
                    </span>
                </div>

                <div class="template-footer-actions">
                    <a href="<?= base_url('index.php/Admin/edit/' . $t->id_template) ?>" class="btn btn-secondary btn-sm" style="flex:1; margin-right:8px;">
                        <span>✏️ Edit</span>
                    </a>
                    <a href="<?= base_url('index.php/Admin/delete/' . $t->id_template) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus template ini secara permanen?')">
                        <span>Hapus</span>
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>




<!-- View 2: Table View -->
<div id="tableView" class="card" style="display:none; padding:18px;">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama & Slug</th>
                    <th>Kategori</th>
                    <th>Ukuran (px)</th>
                    <th>Slot Foto</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($templates as $t): ?>
                    <?php 
                        $tableImgSrc = '';
                        if (!empty($t->template_image)) {
                            if (strpos($t->template_image, 'http') === 0 || strpos($t->template_image, 'data:') === 0) {
                                $tableImgSrc = $t->template_image;
                            } elseif (file_exists(FCPATH . 'uploads/templates/' . $t->template_image)) {
                                $tableImgSrc = base_url('uploads/templates/' . $t->template_image);
                            }
                        }
                        $hasTableImg = !empty($tableImgSrc);
                    ?>
                    <tr class="table-row" data-category="<?= $t->category ?>" data-name="<?= strtolower($t->name . ' ' . $t->slug) ?>">
                        <td>
                            <span style="font-family:'JetBrains Mono', monospace; font-weight:700; color:var(--text-muted);">
                                #<?= $t->id_template ?>
                            </span>
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:12px;">
                                <?php if ($hasTableImg): ?>
                                    <img src="<?= $tableImgSrc ?>" 
                                         alt="<?= htmlspecialchars($t->name) ?>" 
                                         style="width:36px; height:48px; object-fit:contain; background:#080d16; border-radius:4px; border:1px solid rgba(255,255,255,0.1);">
                                <?php endif; ?>
                                <div>
                                    <div style="font-weight:700; color:#ffffff; font-size:13.5px;"><?= htmlspecialchars($t->name) ?></div>
                                    <div style="font-family:'JetBrains Mono', monospace; font-size:11.5px; color:var(--cyan-light);">
                                        <?= htmlspecialchars($t->slug) ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-purple" style="text-transform:capitalize;"><?= $t->category ?></span>
                        </td>
                        <td>
                            <span style="font-family:'JetBrains Mono', monospace; font-size:12px; color:var(--text-secondary);">
                                <?= $t->width ?> × <?= $t->height ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-cyan"><?= $t->photo_count ?> Foto</span>
                        </td>
                        <td>
                            <a href="<?= base_url('index.php/Admin/toggle_active/' . $t->id_template) ?>" style="text-decoration:none;" title="Klik untuk ubah status">
                                <?php if ($t->is_active): ?>
                                    <span class="badge badge-green" style="cursor:pointer;">
                                        <span class="badge-dot"></span> Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-gray" style="cursor:pointer; background:rgba(239,68,68,0.2); color:#fca5a5; border-color:rgba(239,68,68,0.3);">Nonaktif</span>
                                <?php endif; ?>
                            </a>
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <a href="<?= base_url('index.php/Admin/edit/' . $t->id_template) ?>" class="btn btn-secondary btn-sm" style="padding:4px 10px; font-size:11.5px;">
                                    Edit
                                </a>
                                <a href="<?= base_url('index.php/Admin/delete/' . $t->id_template) ?>" class="btn btn-danger btn-sm" style="padding:4px 8px; font-size:11.5px;" onclick="return confirm('Hapus template ini?')">
                                    Hapus
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    let currentCategory = 'all';

    function filterCategory(cat, el) {
        currentCategory = cat;
        document.querySelectorAll('.cat-tab').forEach(b => b.classList.remove('active'));
        el.classList.add('active');
        filterTemplateCards();
    }

    function filterTemplateCards() {
        const query = document.getElementById('templateSearch').value.toLowerCase();
        
        // Cards
        document.querySelectorAll('.template-card').forEach(card => {
            const cat = card.getAttribute('data-category');
            const name = card.getAttribute('data-name');
            const matchCat = (currentCategory === 'all' || cat === currentCategory);
            const matchSearch = name.includes(query);
            card.style.display = (matchCat && matchSearch) ? 'flex' : 'none';
        });

        // Table rows
        document.querySelectorAll('.table-row').forEach(row => {
            const cat = row.getAttribute('data-category');
            const name = row.getAttribute('data-name');
            const matchCat = (currentCategory === 'all' || cat === currentCategory);
            const matchSearch = name.includes(query);
            row.style.display = (matchCat && matchSearch) ? '' : 'none';
        });
    }

    function switchView(type) {
        document.getElementById('btnViewGrid').classList.toggle('active', type === 'grid');
        document.getElementById('btnViewTable').classList.toggle('active', type === 'table');

        document.getElementById('cardsView').style.display = (type === 'grid') ? 'grid' : 'none';
        document.getElementById('tableView').style.display = (type === 'table') ? 'block' : 'none';
    }
</script>