<?php 
    $hasExistingImage = false;
    $imageUrl = '';
    if (isset($t) && !empty($t->template_image)) {
        if (strpos($t->template_image, 'http') === 0 || strpos($t->template_image, 'data:') === 0) {
            $imageUrl = $t->template_image;
            $hasExistingImage = true;
        } elseif (file_exists(FCPATH . 'uploads/templates/' . $t->template_image)) {
            $imageUrl = base_url('uploads/templates/' . $t->template_image);
            $hasExistingImage = true;
        }
    }
?>

<?php if (!empty($error)): ?>
<div style="background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.4); color: #fca5a5; padding: 12px 16px; border-radius: 8px; margin-bottom: 18px; font-size: 13px;">
    ⚠ <?= $error ?>
</div>
<?php endif; ?>

<div class="topbar">
    <div class="page-header">
        <a href="<?= site_url('Admin/templates') ?>" style="display:inline-flex; align-items:center; gap:6px; color:var(--text-muted); text-decoration:none; font-size:12.5px; font-weight:600; margin-bottom:10px;">
            <span>&larr;</span> Kembali ke Daftar Template
        </a>
        <h1><?= isset($t) ? '✏️ Edit Template Photobooth' : ' Tambah Template Baru' ?></h1>
        <p>Upload desain asli (PNG/JPG), atur slot foto dengan drag & resize visual, dan lihat hasil preview real-time.</p>
    </div>
</div>

<style>
    /* Layout Studio */
    .editor-layout {
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
        gap: 28px;
        align-items: start;
    }

    .form-card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: var(--radius-lg);
        padding: 24px;
        margin-bottom: 24px;
        backdrop-filter: blur(16px);
        box-shadow: var(--shadow-md);
    }

    .section-title {
        font-family: 'Outfit', sans-serif;
        font-size: 16px;
        font-weight: 700;
        color: #ffffff;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 6px;
    }

    .section-desc {
        font-size: 12px;
        color: var(--text-muted);
        margin-bottom: 18px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    /* Upload Zone */
    .upload-dropzone {
        border: 2px dashed rgba(139, 92, 246, 0.4);
        border-radius: var(--radius-md);
        background: rgba(139, 92, 246, 0.04);
        padding: 24px;
        text-align: center;
        cursor: pointer;
        transition: all 0.25s ease;
        position: relative;
    }

    .upload-dropzone:hover, .upload-dropzone.dragover {
        border-color: var(--primary);
        background: rgba(139, 92, 246, 0.1);
        transform: scale(1.01);
    }

    .upload-dropzone input[type="file"] {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    .upload-icon {
        font-size: 36px;
        margin-bottom: 8px;
        display: block;
    }

    .upload-text {
        font-size: 13.5px;
        font-weight: 600;
        color: #ffffff;
    }

    .upload-subtext {
        font-size: 11.5px;
        color: var(--text-muted);
        margin-top: 4px;
    }

    .file-preview-card {
        display: none;
        align-items: center;
        gap: 16px;
        padding: 14px 18px;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid var(--card-border);
        border-radius: var(--radius-md);
        margin-top: 14px;
    }

    .file-thumb {
        width: 54px;
        height: 72px;
        object-fit: contain;
        background: #090d16;
        border-radius: 6px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .file-info {
        flex: 1;
    }

    .file-name {
        font-size: 13px;
        font-weight: 600;
        color: #ffffff;
        word-break: break-all;
    }

    .file-meta {
        font-size: 11px;
        color: var(--cyan-light);
        margin-top: 2px;
    }

    /* Category selection */
    .category-pills {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
        margin-top: 6px;
    }

    .cat-radio {
        display: none;
    }

    .cat-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 10px 8px;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--card-border);
        border-radius: var(--radius-md);
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-secondary);
        transition: all 0.2s ease;
        text-align: center;
        text-transform: capitalize;
    }

    .cat-radio:checked + .cat-label {
        background: rgba(139, 92, 246, 0.2);
        border-color: var(--primary);
        color: #ffffff;
        box-shadow: 0 0 15px rgba(139, 92, 246, 0.25);
    }

    /* Visual Editor Area */
    .editor-card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: var(--radius-lg);
        padding: 22px;
        backdrop-filter: blur(16px);
        margin-bottom: 24px;
    }

    .editor-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 16px;
        padding-bottom: 14px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .toolbar-group {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    /* Interactive Canvas */
    .canvas-workspace {
        background: #060910;
        background-image: 
            linear-gradient(45deg, rgba(255, 255, 255, 0.03) 25%, transparent 25%), 
            linear-gradient(-45deg, rgba(255, 255, 255, 0.03) 25%, transparent 25%), 
            linear-gradient(45deg, transparent 75%, rgba(255, 255, 255, 0.03) 75%), 
            linear-gradient(-45deg, transparent 75%, rgba(255, 255, 255, 0.03) 75%);
        background-size: 16px 16px;
        background-position: 0 0, 0 8px, 8px -8px, -8px 0px;
        border: 1px solid var(--card-border);
        border-radius: var(--radius-md);
        min-height: 480px;
        max-height: 640px;
        overflow: auto;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 30px;
        position: relative;
        user-select: none;
    }

    .editor-canvas {
        position: relative;
        background-color: #ffffff;
        background-size: 100% 100%;
        background-position: center;
        background-repeat: no-repeat;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(255, 255, 255, 0.15);
        transform-origin: center center;
        transition: width 0.2s ease, height 0.2s ease;
    }

    /* Interactive Photo Slot Elements */
    .photo-slot {
        position: absolute;
        background: rgba(56, 189, 248, 0.22);
        border: 2px dashed #38bdf8;
        border-radius: 6px;
        cursor: move;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        user-select: none;
        transition: background 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
        box-sizing: border-box;
    }

    .photo-slot {
        transform-origin: center center;
    }

    .photo-slot.active {
        border-style: solid;
        border-color: #8b5cf6;
        background: rgba(139, 92, 246, 0.28);
        box-shadow: 0 0 0 1px rgba(255,255,255,0.7), 0 0 16px rgba(139, 92, 246, 0.6);
        z-index: 15;
    }

    /* Live HUD badge on active slot */
    .slot-hud-badge {
        position: absolute;
        bottom: calc(100% + 8px);
        left: 50%;
        transform: translateX(-50%);
        background: rgba(15, 23, 42, 0.95);
        color: #38bdf8;
        border: 1px solid rgba(56, 189, 248, 0.4);
        border-radius: 4px;
        padding: 3px 8px;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
        pointer-events: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.6);
        display: none;
        z-index: 30;
    }
    .photo-slot.active .slot-hud-badge {
        display: block;
    }

    .slot-label {
        font-family: 'Outfit', sans-serif;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.8);
        pointer-events: none;
        background: rgba(0, 0, 0, 0.45);
        padding: 2px 8px;
        border-radius: 4px;
    }

    .slot-dimensions {
        font-size: 10px;
        color: #e2e8f0;
        pointer-events: none;
        margin-top: 3px;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.8);
    }

    .slot-delete-btn {
        position: absolute;
        top: -10px;
        right: -10px;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #f43f5e;
        color: #ffffff;
        font-size: 11px;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: 2px solid #ffffff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
        transition: transform 0.15s ease;
        z-index: 25;
    }

    .slot-delete-btn:hover {
        transform: scale(1.2);
    }

    /* 8 Resize Handles */
    .handle {
        position: absolute;
        width: 10px;
        height: 10px;
        background: #ffffff;
        border: 2px solid #8b5cf6;
        border-radius: 2px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.6);
        z-index: 20;
        box-sizing: border-box;
        transition: transform 0.1s ease;
    }
    .handle:hover {
        background: #8b5cf6;
        transform: scale(1.25);
    }

    .handle-nw { top: -6px; left: -6px; cursor: nwse-resize; }
    .handle-n  { top: -6px; left: calc(50% - 5px); cursor: ns-resize; }
    .handle-ne { top: -6px; right: -6px; cursor: nesw-resize; }
    .handle-e  { top: calc(50% - 5px); right: -6px; cursor: ew-resize; }
    .handle-se { bottom: -6px; right: -6px; cursor: nwse-resize; }
    .handle-s  { bottom: -6px; left: calc(50% - 5px); cursor: ns-resize; }
    .handle-sw { bottom: -6px; left: -6px; cursor: nesw-resize; }
    .handle-w  { top: calc(50% - 5px); left: -6px; cursor: ew-resize; }

    #btnAutoDetect {
        background: linear-gradient(135deg, #7c3aed, #06b6d4) !important;
        color: #fff !important;
        border: none !important;
        font-weight: 700;
        letter-spacing: 0.3px;
        box-shadow: 0 2px 10px rgba(124,58,237,0.4);
        transition: transform 0.15s, box-shadow 0.15s;
    }
    #btnAutoDetect:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 4px 18px rgba(124,58,237,0.6);
    }
    #btnAutoDetect:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Rotation Handle (Canva / Figma Style) */
    .rot-stem {
        position: absolute;
        top: -24px;
        left: 50%;
        width: 1.5px;
        height: 24px;
        background: #8b5cf6;
        transform: translateX(-50%);
        pointer-events: none;
        z-index: 18;
    }

    .rot-handle {
        position: absolute;
        top: -36px;
        left: 50%;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #ffffff;
        border: 2px solid #8b5cf6;
        transform: translateX(-50%);
        cursor: grab;
        z-index: 22;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 6px rgba(0,0,0,0.5);
        transition: transform 0.15s ease, background 0.15s ease;
    }
    .rot-handle:after {
        content: '';
        width: 4px;
        height: 4px;
        border-radius: 50%;
        background: #8b5cf6;
    }
    .rot-handle:hover, .rot-handle:active {
        background: #8b5cf6;
        cursor: grabbing;
        transform: translateX(-50%) scale(1.25);
    }
    .rot-handle:hover:after, .rot-handle:active:after {
        background: #ffffff;
    }

    /* Slot Inspector Toolbar */
    .slot-inspector-bar {
        background: #0f172a;
        border: 1px solid rgba(139, 92, 246, 0.4);
        border-radius: var(--radius-md);
        padding: 8px 14px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.4);
    }

    .insp-pill {
        background: linear-gradient(135deg, var(--primary), var(--cyan));
        color: #ffffff;
        font-size: 11px;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 6px;
        letter-spacing: 0.5px;
    }

    .insp-item {
        display: flex;
        align-items: center;
        gap: 5px;
        background: rgba(255, 255, 255, 0.05);
        padding: 3px 8px;
        border-radius: 4px;
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .insp-label {
        font-size: 11px;
        font-weight: 700;
        color: var(--text-muted);
    }

    .insp-input {
        background: transparent;
        border: none;
        color: #ffffff;
        font-size: 12px;
        font-weight: 600;
        width: 48px;
        padding: 0;
        outline: none;
        font-family: inherit;
    }
    .insp-input:focus {
        color: var(--cyan-light);
    }

    .insp-unit {
        font-size: 11px;
        color: var(--text-muted);
    }

    .insp-select {
        background: transparent;
        border: none;
        color: #ffffff;
        font-size: 11.5px;
        font-weight: 600;
        outline: none;
        cursor: pointer;
        padding: 0;
        font-family: inherit;
    }
    .insp-select option {
        background: #0f172a;
        color: #ffffff;
    }

    .slot-shape-inner {
        position: absolute;
        inset: 0;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: none;
        box-sizing: border-box;
    }

    .slot-demo-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.88;
        pointer-events: none;
    }

    .slot-shape-overlay {
        position: absolute;
        inset: 0;
        background: rgba(139, 92, 246, 0.12);
        pointer-events: none;
    }

    .insp-actions {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-left: auto;
    }

    /* Custom Shape Vertex Points & Toolbar */
    .custom-shape-toolbar {
        width: 100%;
        margin-top: 8px;
        padding-top: 8px;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .custom-vertex {
        position: absolute;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #06b6d4;
        border: 2px solid #ffffff;
        box-shadow: 0 0 10px rgba(6, 182, 212, 0.8), 0 2px 6px rgba(0,0,0,0.6);
        transform: translate(-50%, -50%);
        cursor: crosshair;
        z-index: 28;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 9px;
        font-weight: 800;
        color: #ffffff;
        user-select: none;
        transition: transform 0.15s ease, background 0.15s ease;
    }
    .custom-vertex:hover {
        background: #38bdf8;
        transform: translate(-50%, -50%) scale(1.3);
        z-index: 30;
    }

    /* Sticky Live Preview Column */
    .preview-sticky {
        position: sticky;
        top: 24px;
    }

    .preview-canvas-box {
        background: #06090f;
        border: 1px dashed rgba(255, 255, 255, 0.12);
        border-radius: var(--radius-md);
        min-height: 480px;
        max-height: 600px;
        overflow: auto;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        position: relative;
    }

    .preview-print {
        position: relative;
        background-color: #ffffff;
        background-size: cover;
        background-position: center;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.7);
        overflow: hidden;
    }

    .preview-photo-slot {
        position: absolute;
        background-size: cover;
        background-position: center;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 4;
    }

    .preview-photo-demo {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .preview-bg-img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        object-fit: fill;
        z-index: 1;
    }

    .preview-overlay-img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        object-fit: fill;
        z-index: 5;
    }

    @media (max-width: 1100px) {
        .editor-layout {
            grid-template-columns: 1fr;
        }
        .preview-sticky {
            position: relative;
            top: 0;
        }
    }
</style>

<form method="post" enctype="multipart/form-data" id="templateForm">
<div class="editor-layout">
    <!-- LEFT: Form Settings & Visual Editor -->
    <div class="editor-main-col">
        
        <!-- STEP 1: Upload Desain Template -->
        <div class="form-card">
            <div class="section-title">
                <span> 1. Upload Desain Template (PNG / JPG)</span>
            </div>
            <div class="section-desc">
                Upload gambar frame photobooth asli (desain kawaii, bunga, birthday, retro, wedding, sticker, karakter, dll). Format <b>PNG transparan</b> sangat disarankan agar foto customer tampak di bawah lubang frame.
            </div>

            <div class="upload-dropzone" id="dropzone">
                <input type="file" name="template_image" id="fileInput" accept="image/png, image/jpeg, image/jpg" onchange="handleFileSelect(this)">
                <span class="upload-icon">🖼️</span>
                <div class="upload-text">Klik untuk pilih file atau seret file desain ke sini</div>
                <div class="upload-subtext">Mendukung format PNG transparan atau JPG (Maks. 15MB)</div>
            </div>
            <input type="hidden" name="template_image_base64" id="templateImageBase64">

            <!-- Preview Card File Uploaded -->
            <div class="file-preview-card" id="filePreviewCard" style="<?= $hasExistingImage ? 'display:flex;' : '' ?>">
                <img src="<?= $imageUrl ?>" id="fileThumbImg" class="file-thumb" alt="Preview">
                <div class="file-info">
                    <div class="file-name" id="fileNameDisplay"><?= $hasExistingImage ? htmlspecialchars($t->template_image) : 'Belum ada file dipilih' ?></div>
                    <div class="file-meta" id="fileDimensionsDisplay">Dimensi: Mendeteksi...</div>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" id="btnSyncDimensions" onclick="applyImageDimensions()" title="Sesuaikan lebar & tinggi kanvas sesuai resolusi asli gambar" style="display:none;">
                     Gunakan Ukuran Asli
                </button>
            </div>
        </div>

        <!-- STEP 2: Informasi Template -->
        <div class="form-card">
            <div class="section-title">
                <span> 2. Informasi & Layout Template</span>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Nama Template <span class="label-hint">Wajib</span></label>
                    <input type="text" name="name" id="inputName" 
                           value="<?= isset($t) ? htmlspecialchars($t->name) : '' ?>" 
                           placeholder="Contoh: Kawaii Bunny 3-Strip" required oninput="handleNameInput(this.value)">
                </div>

                <div class="form-group">
                    <label>Slug Unik <span class="label-hint">URL friendly</span></label>
                    <input type="text" name="slug" id="inputSlug" 
                           value="<?= isset($t) ? htmlspecialchars($t->slug) : '' ?>" 
                           placeholder="kawaii-bunny-3" required>
                </div>
            </div>

            <div class="form-group">
                <label>Kategori & Bentuk Layout</label>
                <div class="category-pills">
                    <?php 
                        $cats = [
                            'strip'    => ['icon' => '🎞️', 'name' => 'Strip (1x3/1x4)'],
                            'grid'     => ['icon' => '🔲', 'name' => 'Grid 2x2'],
                            'single'   => ['icon' => '📷', 'name' => 'Single / Polaroid'],
                            'creative' => ['icon' => '🎨', 'name' => 'Creative'],
                        ];
                        $selectedCat = isset($t) ? $t->category : 'strip';
                        foreach ($cats as $cKey => $cMeta):
                    ?>
                        <label>
                            <input type="radio" name="category" value="<?= $cKey ?>" class="cat-radio" <?= ($selectedCat === $cKey) ? 'checked' : '' ?>>
                            <div class="cat-label">
                                <span style="font-size:16px;"><?= $cMeta['icon'] ?></span>
                                <span><?= $cMeta['name'] ?></span>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-row" style="margin-top:16px;">
                <div class="form-group">
                    <label>Lebar Kanvas / Width (px)</label>
                    <input type="number" name="width" id="inputWidth" value="<?= isset($t) ? $t->width : '420' ?>" required oninput="handleCanvasResize()">
                </div>
                <div class="form-group">
                    <label>Tinggi Kanvas / Height (px)</label>
                    <input type="number" name="height" id="inputHeight" value="<?= isset($t) ? $t->height : '980' ?>" required oninput="handleCanvasResize()">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Jumlah Foto (Otomatis dari slot)</label>
                    <input type="number" name="photo_count" id="inputPhotoCount" value="<?= isset($t) ? $t->photo_count : '3' ?>" min="1" max="12" readonly style="background:rgba(255,255,255,0.04); color:var(--cyan-light); font-weight:700;">
                </div>
                <div class="form-group">
                    <label>Warna Aksen / Brand</label>
                    <input type="text" name="accent" id="inputAccent" value="<?= isset($t) ? $t->accent : '#8b5cf6' ?>" placeholder="#8b5cf6">
                </div>
            </div>

            <div class="form-group" style="margin-top:14px; margin-bottom:0;">
                <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                    <input type="checkbox" name="is_active" value="1" style="width:18px; height:18px; accent-color:var(--emerald);" <?= (!isset($t) || $t->is_active) ? 'checked' : '' ?>>
                    <div>
                        <div style="font-size:13.5px; font-weight:600; color:#ffffff;">Aktifkan Template</div>
                        <div style="font-size:11.5px; color:var(--text-muted);">Template langsung muncul di photobooth website saat dicentang.</div>
                    </div>
                </label>
            </div>
        </div>

        <!-- STEP 3: TEMPLATE EDITOR VISUAL (DRAG & RESIZE) -->
        <div class="editor-card">
            <div class="editor-toolbar">
                <div>
                    <div class="section-title" style="margin:0;">
                        <span>🖱️ 3. Visual Template Editor (Drag & Resize)</span>
                    </div>
                    <div style="font-size:11.5px; color:var(--text-muted); margin-top:2px;">
                        Klik & geser slot foto untuk memindahkan posisi. Tarik sudut kotak untuk resize ukuran.
                    </div>
                </div>

                <div class="toolbar-group">
                    <button type="button" class="btn btn-primary btn-sm" onclick="addNewSlot()">
                        <span> Tambah Slot</span>
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="addNewText()">
                        <span> Tambah Teks</span>
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="duplicateActiveSlot()" title="Salin / Duplikat slot terpilih (Shortcut: Ctrl+D)">
                        <span> Salin Slot</span>
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="centerAllSlots()" title="Posisikan semua slot di tengah secara horizontal">
                        <span> Rata Tengah</span>
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="distributeVertical()" title="Ratakan spasi vertikal antar slot">
                        <span> Spasi Rata</span>
                    </button>
                    <button type="button" id="btnAutoDetect" class="btn btn-sm" onclick="autoDetectSlots()" title="Deteksi otomatis area foto dari gambar template (area putih/terang/transparan)" style="background:linear-gradient(135deg,#7c3aed,#06b6d4);color:#fff;border:none;">
                        <span>🪄 Auto-Detect Slot</span>
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="clearAllSlots()" title="Hapus semua slot foto">
                        <span>Hapus Semua</span>
                    </button>
                </div>
            </div>

            <!-- Slot Inspector Bar (Figma / Canva Free-Transform & Shape Style) -->
            <div class="slot-inspector-bar" id="slotInspectorBar" style="display:none;">
                <div class="insp-pill" id="inspSlotName">FOTO 1</div>

                <div class="insp-item" title="Bentuk Frame Foto">
                    <span class="insp-label">Bentuk</span>
                    <select id="inspShape" class="insp-select" onchange="updateActiveSlotFromInspector()">
                        <option value="rectangle">⏹️ Rectangle (Persegi)</option>
                        <option value="circle">⚪ Circle (Lingkaran)</option>
                        <option value="oval">🥚 Oval (Elips)</option>
                        <option value="triangle">🔺 Triangle (Segitiga)</option>
                        <option value="diamond">💎 Diamond (Belah Ketupat)</option>
                        <option value="custom">✏️ Custom Shape (Bebas)</option>
                        <option value="rounded">🔲 Rounded</option>
                        <option value="heart">❤️ Heart (Hati)</option>
                        <option value="star">⭐ Star (Bintang)</option>
                        <option value="arch">🏛️ Arch (Kubah)</option>
                    </select>
                </div>
                
                <div class="insp-item" title="Posisi Horizontal (X)">
                    <span class="insp-label">X</span>
                    <input type="number" id="inspX" class="insp-input" oninput="updateActiveSlotFromInspector()">
                </div>
                <div class="insp-item" title="Posisi Vertikal (Y)">
                    <span class="insp-label">Y</span>
                    <input type="number" id="inspY" class="insp-input" oninput="updateActiveSlotFromInspector()">
                </div>
                <div class="insp-item" title="Lebar Slot">
                    <span class="insp-label">W</span>
                    <input type="number" id="inspW" class="insp-input" oninput="updateActiveSlotFromInspector()">
                </div>
                <div class="insp-item" title="Tinggi Slot">
                    <span class="insp-label">H</span>
                    <input type="number" id="inspH" class="insp-input" oninput="updateActiveSlotFromInspector()">
                </div>
                <div class="insp-item" title="Rotasi Sudut (-180° s/d 180°)">
                    <span class="insp-label">Rot</span>
                    <input type="number" id="inspRot" class="insp-input" style="width:44px;" oninput="updateActiveSlotFromInspector()">
                    <span class="insp-unit">°</span>
                </div>
                <div class="insp-item" title="Kemiringan Horizontal / Skew X">
                    <span class="insp-label">SkX</span>
                    <input type="number" id="inspSkewX" class="insp-input" style="width:40px;" min="-60" max="60" oninput="updateActiveSlotFromInspector()">
                    <span class="insp-unit">°</span>
                </div>
                <div class="insp-item" title="Kemiringan Vertikal / Skew Y">
                    <span class="insp-label">SkY</span>
                    <input type="number" id="inspSkewY" class="insp-input" style="width:40px;" min="-60" max="60" oninput="updateActiveSlotFromInspector()">
                    <span class="insp-unit">°</span>
                </div>
                <div class="insp-item" id="inspRadiusContainer" title="Kelengkungan Sudut">
                    <span class="insp-label">Rad</span>
                    <input type="number" id="inspRadius" class="insp-input" style="width:40px;" min="0" max="100" oninput="updateActiveSlotFromInspector()">
                </div>

                <div class="insp-actions">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="duplicateActiveSlot()" title="Salin / Duplikat Slot Ini (Ctrl+D)">📋 Salin</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="rotateSlotQuick(90)" title="Putar 90°">🔄 +90°</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="resetSlotTransforms()" title="Reset Rotasi & Skew">Reset</button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteActiveSlot()" title="Hapus slot terpilih">✕ Hapus</button>
                </div>

                <!-- Custom Shape Sub-Toolbar -->
                <div class="custom-shape-toolbar" id="customShapeToolbar" style="display:none;">
                    <span class="insp-label" style="color:var(--cyan-light);">✏️ Custom Mask:</span>
                    <select id="customPresetSelect" class="insp-select" style="background:#1e293b; padding:2px 8px; border-radius:4px; font-size:11px;" onchange="applyCustomPreset(this.value)">
                        <option value="">-- Preset Bentuk Bebas --</option>
                        <option value="quad">🔷 4-Point Quad (Miring/Perspektif)</option>
                        <option value="parallelogram">⯁ Parallelogram</option>
                        <option value="trapezoid">⮝ Trapezoid</option>
                        <option value="hexagon">⬡ Hexagon (6 Titik)</option>
                        <option value="octagon">⬢ Octagon (8 Titik)</option>
                        <option value="shield">🛡️ Shield / Badge</option>
                        <option value="ribbon">🎀 Ribbon Banner</option>
                        <option value="heart">❤️ Heart</option>
                        <option value="star">⭐ Star</option>
                    </select>
                    <button type="button" class="btn btn-secondary btn-sm" style="padding:2px 8px; font-size:11px;" onclick="addCustomPoint()" title="Tambah 1 titik baru ke polygon">➕ Tambah Titik</button>
                    <button type="button" class="btn btn-secondary btn-sm" style="padding:2px 8px; font-size:11px;" onclick="removeLastCustomPoint()" title="Hapus titik terakhir">✕ Hapus Titik</button>
                    <button type="button" class="btn btn-secondary btn-sm" style="padding:2px 8px; font-size:11px;" onclick="resetCustomPoints()" title="Kembalikan ke 4 titik awal">🔄 Reset Titik</button>
                    <span style="font-size:10.5px; color:var(--text-muted); margin-left:auto;">💡 Geser titik angka di kanvas untuk membentuk pola mask foto bebas</span>
                </div>
            </div>

            <!-- Canvas Workspace -->
            <div class="canvas-workspace" id="canvasWorkspace">
                <!-- Interactive Template Canvas -->
                <div class="editor-canvas" id="editorCanvas">
                    <!-- Dynamic slots rendered here -->
                </div>
            </div>

            <!-- Quick Template Presets -->
            <div style="display:flex; align-items:center; justify-content:space-between; margin-top:16px; flex-wrap:wrap; gap:10px;">
                <div style="font-size:12px; color:var(--text-muted);">
                    Preset Cepat:
                    <button type="button" class="btn btn-secondary btn-sm" style="margin-left:6px;" onclick="loadPreset('strip3')">⚡ 3-Foto Strip</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="loadPreset('strip4')">⚡ 4-Foto Strip</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="loadPreset('grid4')">⚡ 4-Foto Grid</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="loadPreset('single')">⚡ Single</button>
                </div>
                <div style="font-size:12px; color:var(--cyan-light); font-weight:700;" id="slotCounterBadge">
                    3 Slot Foto Dikonfigurasi
                </div>
            </div>
        </div>

        <!-- Hidden / Collapsible JSON Fields (Backward Compatibility) -->
        <details style="background:var(--card-bg); border:1px solid var(--card-border); border-radius:var(--radius-md); padding:16px; margin-bottom:24px;">
            <summary style="font-size:12.5px; font-weight:600; color:var(--text-muted); cursor:pointer;">
                ⚙️ Data Teknis JSON (Tersimpan Otomatis oleh Editor)
            </summary>
            <div style="margin-top:14px;">
                <div class="form-group">
                    <label>Photo Slots (JSON)</label>
                    <textarea name="photo_slots" id="inputPhotoSlots" class="code-textarea" style="height:100px;"><?= isset($t) ? $t->photo_slots : '[{"x":24,"y":36,"width":372,"height":250,"borderRadius":8},{"x":24,"y":306,"width":372,"height":250,"borderRadius":8},{"x":24,"y":576,"width":372,"height":250,"borderRadius":8}]' ?></textarea>
                </div>
                <div class="form-group">
                    <label>Background (JSON)</label>
                    <textarea name="background" id="inputBackground" class="code-textarea" style="height:60px;"><?= isset($t) ? $t->background : '{"type":"solid","color":"#ffffff"}' ?></textarea>
                </div>
                <input type="hidden" name="decorations" value="<?= isset($t) ? htmlspecialchars($t->decorations) : '' ?>">
                <input type="hidden" name="text_config" id="inputTextConfig" value="<?= isset($t) ? htmlspecialchars($t->text_config) : '' ?>">
                <input type="hidden" name="border_config" value="<?= isset($t) ? htmlspecialchars($t->border_config) : '' ?>">
                <input type="hidden" name="photo_border" value="<?= isset($t) ? htmlspecialchars($t->photo_border) : '' ?>">
            </div>
        </details>

        <!-- Save Button -->
        <div style="display:flex; align-items:center; gap:14px; margin-bottom:40px;">
            <button type="submit" class="btn btn-primary" style="padding:14px 34px; font-size:14.5px;">
                <span>💾 Simpan Desain Template</span>
            </button>
            <a href="<?= site_url('Admin/templates') ?>" class="btn btn-secondary" style="padding:14px 24px;">
                <span>Batal</span>
            </a>
        </div>
    </div>

    <!-- RIGHT: Live Customer Preview -->
    <div class="editor-side-col">
        <div class="preview-sticky">
            <div class="form-card" style="padding:20px;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; padding-bottom:12px; border-bottom:1px solid rgba(255,255,255,0.08);">
                    <div>
                        <div class="section-title" style="margin:0;">
                            <span> Live Preview template</span>
                        </div>
                        <div style="font-size:11.5px; color:var(--text-muted); margin-top:2px;" id="previewResText">
                            420 × 980 px
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="toggleDemoPhotos()" id="btnToggleDemo">
                        <span> Foto Demo</span>
                    </button>
                </div>

                <div class="preview-canvas-box" id="previewBox">
                    <!-- Real Customer Photobooth Output Preview -->
                    <div class="preview-print" id="previewPrint">
                        <!-- Template Design base layer (image artwork) -->
                        <img id="previewBgImg" class="preview-bg-img" style="display:none;" alt="Template Base">
                        <!-- Photos placed inside slots (on top of background) -->
                        <div id="previewSlotsContainer"></div>
                        <!-- Template Cutout Overlay (if transparent frame) -->
                        <img id="previewOverlayImg" class="preview-overlay-img" style="display:none;" alt="Template Overlay">
                    </div>
                </div>

                <div style="margin-top:14px; padding-top:12px; border-top:1px solid rgba(255,255,255,0.06); font-size:11.5px; color:var(--text-muted); line-height:1.5;">
                    = <b>Tips Photobooth:</b> Customer akan berpose dan foto mereka otomatis mengisi slot Foto 1, Foto 2, dst. Desain PNG Anda akan dicetak membingkai foto mereka secara sempurna.
                </div>
            </div>
        </div>
    </div>
</div>
</form>

<script>
    // --- STATE MANAGEMENT ---
    let slots = [];
    let activeSlotIndex = null;let texts = [];
    let activeTextIndex = null;
    let templateImageSrc = "<?= $imageUrl ?>";
    let naturalImgW = 0;
    let naturalImgH = 0;
    let showDemo = true;

    // Demo portrait images for realistic customer preview
    const demoPhotos = [
        "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=500&auto=format&fit=crop&q=80",
        "https://images.unsplash.com/photo-1517841905240-472988babdf9?w=500&auto=format&fit=crop&q=80",
        "https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=500&auto=format&fit=crop&q=80",
        "https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=500&auto=format&fit=crop&q=80"
    ];

    // Load initial slots from JSON
    try {
        const raw = document.getElementById('inputPhotoSlots').value;
        slots = JSON.parse(raw);
        if (!Array.isArray(slots)) slots = [];
    } catch(e) {
        slots = [
            {"x":24,"y":36,"width":372,"height":250,"borderRadius":8},
            {"x":24,"y":306,"width":372,"height":250,"borderRadius":8},
            {"x":24,"y":576,"width":372,"height":250,"borderRadius":8}
        ];
    }

    // --- INITIALIZATION ---
    window.addEventListener('DOMContentLoaded', () => {
    if (templateImageSrc) {
        checkImageDimensions(templateImageSrc);
    }
    const existingTextJson = document.getElementById('inputTextConfig').value;
    if (existingTextJson) {
        try { texts = JSON.parse(existingTextJson); } catch (e) { texts = []; }
    }
    renderEditorCanvas();
    renderLivePreview();
    updateSlotsJson();
});
    // Auto slug generator from name
    function handleNameInput(val) {
        const slugInput = document.getElementById('inputSlug');
        if (!slugInput.dataset.manual) {
            slugInput.value = val.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
        }
    }
    document.getElementById('inputSlug').addEventListener('input', function() {
        this.dataset.manual = 'true';
    });

    // --- FILE UPLOAD & PREVIEW ---
    function handleFileSelect(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                templateImageSrc = e.target.result;
                const b64Input = document.getElementById('templateImageBase64');
                if (b64Input) b64Input.value = templateImageSrc;

                document.getElementById('fileThumbImg').src = templateImageSrc;
                document.getElementById('fileNameDisplay').innerText = file.name;
                document.getElementById('filePreviewCard').style.display = 'flex';

                checkImageDimensions(templateImageSrc);
                renderEditorCanvas();
                renderLivePreview();
            };

            reader.readAsDataURL(file);
        }
    }

    function checkImageDimensions(src) {
        const img = new Image();
        img.onload = function() {
            naturalImgW = img.naturalWidth;
            naturalImgH = img.naturalHeight;
            document.getElementById('fileDimensionsDisplay').innerText = `Resolusi Asli: ${naturalImgW} × ${naturalImgH} px`;

            const currentW = parseInt(document.getElementById('inputWidth').value) || 0;
            const currentH = parseInt(document.getElementById('inputHeight').value) || 0;

            if (currentW !== naturalImgW || currentH !== naturalImgH) {
                const btnSync = document.getElementById('btnSyncDimensions');
                btnSync.style.display = 'inline-flex';
                btnSync.innerText = `⚡ Terapkan Resolusi (${naturalImgW}×${naturalImgH})`;
            }
        };
        img.src = src;
    }

    function applyImageDimensions() {
        if (naturalImgW > 0 && naturalImgH > 0) {
            document.getElementById('inputWidth').value = naturalImgW;
            document.getElementById('inputHeight').value = naturalImgH;
            document.getElementById('btnSyncDimensions').style.display = 'none';
            handleCanvasResize();
        }
    }

    function handleCanvasResize() {
        renderEditorCanvas();
        renderLivePreview();
    }

    // --- CANVAS COMPUTATION & SCALE ---
    function getEditorScale() {
        const w = parseInt(document.getElementById('inputWidth').value) || 420;
        const h = parseInt(document.getElementById('inputHeight').value) || 980;

        const maxDisplayW = 320;
        const maxDisplayH = 540;

        return Math.min(maxDisplayW / w, maxDisplayH / h, 0.75);
    }

    function getPreviewScale() {
        const w = parseInt(document.getElementById('inputWidth').value) || 420;
        const h = parseInt(document.getElementById('inputHeight').value) || 980;

        const maxDisplayW = 260;
        const maxDisplayH = 480;

        return Math.min(maxDisplayW / w, maxDisplayH / h, 0.65);
    }

    // --- SHAPE CLIPPING HELPER ---
    function getShapeClipStyle(shape, borderRadius, points) {
        switch (shape) {
            case 'circle':
                return {
                    clipPath: 'circle(50% at 50% 50%)',
                    borderRadius: '50%'
                };
            case 'oval':
                return {
                    clipPath: 'ellipse(50% 50% at 50% 50%)',
                    borderRadius: '50%'
                };
            case 'triangle':
                return {
                    clipPath: 'polygon(50% 0%, 0% 100%, 100% 100%)',
                    borderRadius: '0px'
                };
            case 'diamond':
                return {
                    clipPath: 'polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%)',
                    borderRadius: '0px'
                };
            case 'custom':
                if (Array.isArray(points) && points.length >= 3) {
                    const poly = points.map(p => `${p.x}% ${p.y}%`).join(', ');
                    return {
                        clipPath: `polygon(${poly})`,
                        borderRadius: '0px'
                    };
                }
                return {
                    clipPath: 'polygon(10% 5%, 95% 15%, 85% 95%, 5% 85%)',
                    borderRadius: '0px'
                };
            case 'heart':
                return {
                    clipPath: 'polygon(50% 15%, 65% 0%, 85% 0%, 100% 15%, 100% 35%, 85% 55%, 50% 95%, 15% 55%, 0% 35%, 0% 15%, 15% 0%, 35% 0%)',
                    borderRadius: '0px'
                };
            case 'star':
                return {
                    clipPath: 'polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%)',
                    borderRadius: '0px'
                };
            case 'arch':
                return {
                    clipPath: 'none',
                    borderRadius: '50% 50% 0 0 / 30% 30% 0 0'
                };
            case 'rounded':
                return {
                    clipPath: 'none',
                    borderRadius: `${borderRadius || 16}px`
                };
            case 'rectangle':
            default:
                return {
                    clipPath: 'none',
                    borderRadius: `${borderRadius || 0}px`
                };
        }
    }

    // --- RENDER VISUAL EDITOR ---
    function renderEditorCanvas() {
        const w = parseInt(document.getElementById('inputWidth').value) || 420;
        const h = parseInt(document.getElementById('inputHeight').value) || 980;
        const scale = getEditorScale();

        const canvasEl = document.getElementById('editorCanvas');
        const displayW = Math.round(w * scale);
        const displayH = Math.round(h * scale);

        canvasEl.style.width = displayW + 'px';
        canvasEl.style.height = displayH + 'px';

        if (templateImageSrc) {
            canvasEl.style.backgroundImage = `url("${templateImageSrc}")`;
            canvasEl.style.backgroundSize = '100% 100%';
            canvasEl.style.backgroundPosition = 'center';
            canvasEl.style.backgroundRepeat = 'no-repeat';
        } else {
            canvasEl.style.backgroundImage = 'none';
            canvasEl.style.backgroundColor = '#ffffff';
        }

        canvasEl.innerHTML = '';

        // Render each slot with free transform (move, 8-handle rotated resize, rotate, skew) and shapes
        slots.forEach((s, idx) => {
            const slotEl = document.createElement('div');
            const isActive = (idx === activeSlotIndex);
            slotEl.className = 'photo-slot' + (isActive ? ' active' : '');
            slotEl.style.left = (s.x * scale) + 'px';
            slotEl.style.top = (s.y * scale) + 'px';
            slotEl.style.width = (s.width * scale) + 'px';
            slotEl.style.height = (s.height * scale) + 'px';
            slotEl.style.transformOrigin = 'center center';

            // Free transform: Rotation & Skew
            const rot = s.rotation || 0;
            const skX = s.skewX || 0;
            const skY = s.skewY || 0;
            let transformParts = [];
            if (rot) transformParts.push(`rotate(${rot}deg)`);
            if (skX || skY) transformParts.push(`skew(${skX}deg, ${skY}deg)`);
            slotEl.style.transform = transformParts.length > 0 ? transformParts.join(' ') : 'none';

            // Custom Shape Styling
            const shape = s.shape || 'rectangle';
            const shapeStyle = getShapeClipStyle(shape, s.borderRadius ? (s.borderRadius * scale) : 0, s.points);

            // Shape icons for HUD & Label
            const shapeIcons = {
                rectangle: '⏹️',
                circle: '⚪',
                oval: '🥚',
                triangle: '🔺',
                diamond: '💎',
                custom: '✏️',
                rounded: '🔲',
                heart: '❤️',
                star: '⭐',
                arch: '🏛️'
            };
            const sIcon = shapeIcons[shape] || '⏹️';

            let hudTransform = '';
            if (rot) hudTransform += ` · ${rot}°`;
            if (skX || skY) hudTransform += ` · sk(${skX}°,${skY}°)`;

            const demoImg = demoPhotos[idx % demoPhotos.length];

            // Render interactive vertex points if slot is active and custom shape
            let vertexHtml = '';
            if (isActive && shape === 'custom' && Array.isArray(s.points)) {
                vertexHtml = s.points.map((pt, pIdx) => `
                    <div class="custom-vertex" data-vertex-idx="${pIdx}" style="left:${pt.x}%; top:${pt.y}%;" title="Tarik titik ${pIdx + 1} (${pt.x}%, ${pt.y}%)">
                        ${pIdx + 1}
                    </div>
                `).join('');
            }

            slotEl.innerHTML = `
                <!-- Custom Shape Inner Mask with Demo Photo -->
                <div class="slot-shape-inner" style="clip-path:${shapeStyle.clipPath}; border-radius:${shapeStyle.borderRadius};">
                    <img src="${demoImg}" class="slot-demo-img" alt="Foto ${idx + 1}">
                    <div class="slot-shape-overlay"></div>
                </div>

                <div class="slot-hud-badge">
                    ${sIcon} X:${s.x} Y:${s.y} | ${s.width}×${s.height}${hudTransform}
                </div>
                <div class="slot-label">${sIcon} FOTO ${idx + 1}</div>
                <div class="slot-dimensions">${s.width} × ${s.height}${hudTransform}</div>
                <div class="slot-delete-btn" onclick="deleteSlot(${idx}, event)" title="Hapus slot ini">✕</div>
                ${vertexHtml}
                ${isActive ? `
                    <!-- 8 Resize Handles (All sides & corners) -->
                    <div class="handle handle-nw" data-handle="nw" title="Tarik sudut kiri-atas"></div>
                    <div class="handle handle-n"  data-handle="n"  title="Tarik sisi atas"></div>
                    <div class="handle handle-ne" data-handle="ne" title="Tarik sudut kanan-atas"></div>
                    <div class="handle handle-e"  data-handle="e"  title="Tarik sisi kanan"></div>
                    <div class="handle handle-se" data-handle="se" title="Tarik sudut kanan-bawah"></div>
                    <div class="handle handle-s"  data-handle="s"  title="Tarik sisi bawah"></div>
                    <div class="handle handle-sw" data-handle="sw" title="Tarik sudut kiri-bawah"></div>
                    <div class="handle handle-w"  data-handle="w"  title="Tarik sisi kiri"></div>
                    <!-- Rotation Stem & Knob -->
                    <div class="rot-stem"></div>
                    <div class="rot-handle" data-handle="rotate" title="Klik & geser untuk memutar derajat"></div>
                ` : ''}
            `;

            slotEl.addEventListener('mousedown', (e) => startDragOrResize(e, idx, scale, slotEl));

            canvasEl.appendChild(slotEl);
        });

        renderTextsOnCanvas(canvasEl, scale);

        document.getElementById('slotCounterBadge').innerText = `${slots.length} Slot Foto Dikonfigurasi`;
        document.getElementById('inputPhotoCount').value = slots.length;
        syncInspectorWithActiveSlot();
    }

    // --- INTERACTIVE DRAG, 8-WAY ROTATED RESIZE, ROTATE & VERTEX ENGINE ---
    function startDragOrResize(e, slotIdx, scale, slotEl) {
        if (e.target.classList.contains('slot-delete-btn')) return;

        e.preventDefault();
        e.stopPropagation();

        activeSlotIndex = slotIdx;
        renderEditorCanvas(); // highlight active slot and show handles

        const isHandle = e.target.classList.contains('handle');
        const isRotHandle = e.target.classList.contains('rot-handle');
        const vertexEl = e.target.closest('.custom-vertex');
        const vertexIdx = vertexEl ? parseInt(vertexEl.getAttribute('data-vertex-idx')) : null;
        const handleType = isHandle ? e.target.getAttribute('data-handle') : (isRotHandle ? 'rotate' : null);

        const currentSlotEl = slotEl || document.querySelector(`.photo-slot:nth-child(${slotIdx + 1})`);
        const startClientX = e.clientX;
        const startClientY = e.clientY;

        const slot = slots[slotIdx];
        const origX = slot.x;
        const origY = slot.y;
        const origW = slot.width;
        const origH = slot.height;
        const origRot = slot.rotation || 0;
        const origPoints = slot.points ? JSON.parse(JSON.stringify(slot.points)) : null;

        const maxCanvasW = parseInt(document.getElementById('inputWidth').value) || 420;
        const maxCanvasH = parseInt(document.getElementById('inputHeight').value) || 980;

        const origCenterX = origX + origW / 2;
        const origCenterY = origY + origH / 2;

        let slotBoundingRect = currentSlotEl ? currentSlotEl.getBoundingClientRect() : null;
        let screenCenterX = slotBoundingRect ? slotBoundingRect.left + slotBoundingRect.width / 2 : 0;
        let screenCenterY = slotBoundingRect ? slotBoundingRect.top + slotBoundingRect.height / 2 : 0;

        const MIN_SIZE = 30;

        function onMouseMove(moveEvt) {
            if (vertexIdx !== null && origPoints && slot.points && slot.points[vertexIdx]) {
                // 1. DRAG CUSTOM SHAPE VERTEX POINT
                const rect = currentSlotEl.getBoundingClientRect();
                const rad = -(origRot * Math.PI / 180);
                const rawRelX = moveEvt.clientX - (rect.left + rect.width / 2);
                const rawRelY = moveEvt.clientY - (rect.top + rect.height / 2);
                const localX = rawRelX * Math.cos(rad) - rawRelY * Math.sin(rad) + (origW * scale / 2);
                const localY = rawRelX * Math.sin(rad) + rawRelY * Math.cos(rad) + (origH * scale / 2);

                const pctX = Math.round((localX / (origW * scale)) * 100);
                const pctY = Math.round((localY / (origH * scale)) * 100);

                slot.points[vertexIdx].x = Math.max(0, Math.min(100, pctX));
                slot.points[vertexIdx].y = Math.max(0, Math.min(100, pctY));
            } else if (handleType === 'rotate') {
                // 2. ROTATE
                const rad = Math.atan2(moveEvt.clientY - screenCenterY, moveEvt.clientX - screenCenterX);
                let deg = Math.round((rad * 180 / Math.PI) + 90);
                if (deg > 180) deg -= 360;
                if (deg < -180) deg += 360;

                // Snapping to 0°, 45°, 90°, 135°, 180° within ±3°
                const snaps = [0, 45, 90, 135, 180, -45, -90, -135, -180];
                for (const snap of snaps) {
                    if (Math.abs(deg - snap) <= 3) {
                        deg = snap;
                        break;
                    }
                }
                slot.rotation = deg;
            } else if (!isHandle) {
                // 3. MOVE / DRAG (translates slot)
                const dx = (moveEvt.clientX - startClientX) / scale;
                const dy = (moveEvt.clientY - startClientY) / scale;

                let newX = Math.round(origX + dx);
                let newY = Math.round(origY + dy);

                newX = Math.max(0, Math.min(newX, maxCanvasW - slot.width));
                newY = Math.max(0, Math.min(newY, maxCanvasH - slot.height));

                slot.x = newX;
                slot.y = newY;
            } else {
                // 4. 8-WAY RESIZE (Flawless at any rotation angle)
                const screenDx = (moveEvt.clientX - startClientX) / scale;
                const screenDy = (moveEvt.clientY - startClientY) / scale;

                const rotRad = (origRot * Math.PI) / 180;
                const localDx = screenDx * Math.cos(rotRad) + screenDy * Math.sin(rotRad);
                const localDy = -screenDx * Math.sin(rotRad) + screenDy * Math.cos(rotRad);

                let deltaW = 0;
                let deltaH = 0;

                if (handleType === 'e' || handleType === 'se' || handleType === 'ne') {
                    deltaW = localDx;
                } else if (handleType === 'w' || handleType === 'sw' || handleType === 'nw') {
                    deltaW = -localDx;
                }

                if (handleType === 's' || handleType === 'se' || handleType === 'sw') {
                    deltaH = localDy;
                } else if (handleType === 'n' || handleType === 'ne' || handleType === 'nw') {
                    deltaH = -localDy;
                }

                const newW = Math.max(MIN_SIZE, Math.min(Math.round(origW + deltaW), maxCanvasW * 1.5));
                const newH = Math.max(MIN_SIZE, Math.min(Math.round(origH + deltaH), maxCanvasH * 1.5));

                const actualDeltaW = newW - origW;
                const actualDeltaH = newH - origH;

                let actualShiftX = 0;
                let actualShiftY = 0;
                if (handleType.includes('e')) actualShiftX = actualDeltaW / 2;
                if (handleType.includes('w')) actualShiftX = -actualDeltaW / 2;
                if (handleType.includes('s')) actualShiftY = actualDeltaH / 2;
                if (handleType.includes('n')) actualShiftY = -actualDeltaH / 2;

                const worldShiftX = actualShiftX * Math.cos(rotRad) - actualShiftY * Math.sin(rotRad);
                const worldShiftY = actualShiftX * Math.sin(rotRad) + actualShiftY * Math.cos(rotRad);

                const newCenterX = origCenterX + worldShiftX;
                const newCenterY = origCenterY + worldShiftY;

                slot.width = newW;
                slot.height = newH;
                slot.x = Math.round(newCenterX - newW / 2);
                slot.y = Math.round(newCenterY - newH / 2);
            }

            renderEditorCanvas();
            renderLivePreview();
            updateSlotsJson();
            syncInspectorWithActiveSlot();
        }

        function onMouseUp() {
            window.removeEventListener('mousemove', onMouseMove);
            window.removeEventListener('mouseup', onMouseUp);
        }

        window.addEventListener('mousemove', onMouseMove);
        window.addEventListener('mouseup', onMouseUp);
    }

    // --- CUSTOM SHAPE HELPERS ---
    function applyCustomPreset(preset) {
        if (activeSlotIndex === null || !slots[activeSlotIndex]) return;
        const s = slots[activeSlotIndex];
        s.shape = 'custom';
        document.getElementById('inspShape').value = 'custom';

        switch (preset) {
            case 'quad':
                s.points = [ {x: 10, y: 5}, {x: 95, y: 15}, {x: 85, y: 95}, {x: 5, y: 85} ];
                break;
            case 'parallelogram':
                s.points = [ {x: 20, y: 0}, {x: 100, y: 0}, {x: 80, y: 100}, {x: 0, y: 100} ];
                break;
            case 'trapezoid':
                s.points = [ {x: 20, y: 0}, {x: 80, y: 0}, {x: 100, y: 100}, {x: 0, y: 100} ];
                break;
            case 'hexagon':
                s.points = [ {x: 25, y: 0}, {x: 75, y: 0}, {x: 100, y: 50}, {x: 75, y: 100}, {x: 25, y: 100}, {x: 0, y: 50} ];
                break;
            case 'octagon':
                s.points = [ {x: 30, y: 0}, {x: 70, y: 0}, {x: 100, y: 30}, {x: 100, y: 70}, {x: 70, y: 100}, {x: 30, y: 100}, {x: 0, y: 70}, {x: 0, y: 30} ];
                break;
            case 'shield':
                s.points = [ {x: 0, y: 0}, {x: 100, y: 0}, {x: 100, y: 60}, {x: 50, y: 100}, {x: 0, y: 60} ];
                break;
            case 'ribbon':
                s.points = [ {x: 0, y: 0}, {x: 100, y: 0}, {x: 85, y: 50}, {x: 100, y: 100}, {x: 0, y: 100}, {x: 15, y: 50} ];
                break;
            case 'heart':
                s.points = [ {x: 50, y: 15}, {x: 65, y: 0}, {x: 85, y: 0}, {x: 100, y: 15}, {x: 100, y: 35}, {x: 85, y: 55}, {x: 50, y: 95}, {x: 15, y: 55}, {x: 0, y: 35}, {x: 0, y: 15}, {x: 15, y: 0}, {x: 35, y: 0} ];
                break;
            case 'star':
                s.points = [ {x: 50, y: 0}, {x: 61, y: 35}, {x: 98, y: 35}, {x: 68, y: 57}, {x: 79, y: 91}, {x: 50, y: 70}, {x: 21, y: 91}, {x: 32, y: 57}, {x: 2, y: 35}, {x: 39, y: 35} ];
                break;
        }

        renderEditorCanvas();
        renderLivePreview();
        updateSlotsJson();
        syncInspectorWithActiveSlot();
    }

    function addCustomPoint() {
        if (activeSlotIndex === null || !slots[activeSlotIndex]) return;
        const s = slots[activeSlotIndex];
        if (!s.points) s.points = [];
        if (s.points.length >= 2) {
            const pLast = s.points[s.points.length - 1];
            const pFirst = s.points[0];
            s.points.push({
                x: Math.round((pLast.x + pFirst.x) / 2),
                y: Math.round((pLast.y + pFirst.y) / 2)
            });
        } else {
            s.points.push({ x: 50, y: 50 });
        }
        renderEditorCanvas();
        renderLivePreview();
        updateSlotsJson();
        syncInspectorWithActiveSlot();
    }

    function removeLastCustomPoint() {
        if (activeSlotIndex === null || !slots[activeSlotIndex]) return;
        const s = slots[activeSlotIndex];
        if (s.points && s.points.length > 3) {
            s.points.pop();
            renderEditorCanvas();
            renderLivePreview();
            updateSlotsJson();
            syncInspectorWithActiveSlot();
        } else {
            alert('Bentuk kustom minimal harus memiliki 3 titik.');
        }
    }

    function resetCustomPoints() {
        applyCustomPreset('quad');
    }

    // --- AUTO-DETECT SLOTS FROM IMAGE ---
    /**
     * Mendeteksi area foto (putih / transparan / terang) dari gambar template
     * menggunakan Canvas pixel analysis + connected-component labeling (flood fill bitmask).
     * Area yang terdeteksi otomatis diubah menjadi slot foto.
     */
    function autoDetectSlots() {
        if (!templateImageSrc) {
            alert('⚠️ Upload gambar template terlebih dahulu!');
            return;
        }

        const btn = document.getElementById('btnAutoDetect');
        btn.disabled = true;
        btn.textContent = '⏳ Mendeteksi...';

        const canvasW = parseInt(document.getElementById('inputWidth').value) || 420;
        const canvasH = parseInt(document.getElementById('inputHeight').value) || 980;

        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = function () {
            try {
                // Render gambar ke offscreen canvas untuk baca piksel
                const offscreen = document.createElement('canvas');
                // Gunakan dimensi template asli agar koordinat akurat
                offscreen.width  = canvasW;
                offscreen.height = canvasH;
                const ctx = offscreen.getContext('2d');
                ctx.drawImage(img, 0, 0, canvasW, canvasH);

                const { data } = ctx.getImageData(0, 0, canvasW, canvasH);

                // === LANGKAH 1: Buat mask area "cerah / transparan" ===
                // Piksel dianggap "kandidat slot foto" kalau:
                //   • alpha < 30 (transparan), ATAU
                //   • semua channel R,G,B ≥ 200 (sangat putih/terang)
                const STRIDE = canvasW;
                const mask = new Uint8Array(canvasW * canvasH); // 1 = kandidat
                for (let y = 0; y < canvasH; y++) {
                    for (let x = 0; x < canvasW; x++) {
                        const i = (y * canvasW + x) * 4;
                        const r = data[i], g = data[i+1], b = data[i+2], a = data[i+3];
                        const isTransparent = a < 30;
                        const isBright      = (r >= 200 && g >= 200 && b >= 200 && a > 30);
                        // Juga deteksi warna terang campuran (mis. krem, kuning muda)
                        const isLightMixed  = (r >= 220 && g >= 200 && b >= 170 && a > 30);
                        if (isTransparent || isBright || isLightMixed) mask[y * STRIDE + x] = 1;
                    }
                }

                // === LANGKAH 2: Connected-component labeling (4-arah BFS) ===
                const labels  = new Int32Array(canvasW * canvasH).fill(-1);
                let   labelId = 0;
                const regions = []; // { minX, minY, maxX, maxY, size }

                for (let startY = 0; startY < canvasH; startY++) {
                    for (let startX = 0; startX < canvasW; startX++) {
                        const startIdx = startY * STRIDE + startX;
                        if (mask[startIdx] !== 1 || labels[startIdx] !== -1) continue;

                        // BFS flood fill
                        const queue = [startIdx];
                        labels[startIdx] = labelId;
                        let minX = startX, minY = startY, maxX = startX, maxY = startY, size = 0;

                        while (queue.length > 0) {
                            const idx = queue.pop();
                            size++;
                            const cy = Math.floor(idx / STRIDE);
                            const cx = idx % STRIDE;
                            if (cx < minX) minX = cx;
                            if (cx > maxX) maxX = cx;
                            if (cy < minY) minY = cy;
                            if (cy > maxY) maxY = cy;

                            const neighbors = [
                                idx - 1,            // kiri
                                idx + 1,            // kanan
                                idx - STRIDE,       // atas
                                idx + STRIDE,       // bawah
                            ];
                            for (const nIdx of neighbors) {
                                if (nIdx < 0 || nIdx >= mask.length) continue;
                                if (mask[nIdx] === 1 && labels[nIdx] === -1) {
                                    labels[nIdx] = labelId;
                                    queue.push(nIdx);
                                }
                            }
                        }
                        regions.push({ minX, minY, maxX, maxY, size, label: labelId });
                        labelId++;
                    }
                }

                // === LANGKAH 3: Filter region kecil & yang di border ===
                const MIN_AREA = (canvasW * canvasH) * 0.015; // minimal 1.5% dari luas template
                const BORDER_MARGIN = Math.min(canvasW, canvasH) * 0.03; // 3% margin dari tepi

                const validRegions = regions.filter(r => {
                    const rw = r.maxX - r.minX;
                    const rh = r.maxY - r.minY;
                    if (r.size < MIN_AREA) return false;
                    if (rw < 20 || rh < 20) return false;
                    // Buang region yang menyentuh seluruh tepi (kemungkinan background)
                    const touchesAllEdges =
                        r.minX <= BORDER_MARGIN &&
                        r.minY <= BORDER_MARGIN &&
                        r.maxX >= canvasW - BORDER_MARGIN &&
                        r.maxY >= canvasH - BORDER_MARGIN;
                    if (touchesAllEdges) return false;
                    return true;
                });

                // Urutkan dari atas ke bawah
                validRegions.sort((a, b) => a.minY - b.minY);

                if (validRegions.length === 0) {
                    btn.disabled = false;
                    btn.textContent = '🪄 Auto-Detect Slot';
                    alert('❌ Tidak ada area foto yang terdeteksi.\n\nTips:\n• Pastikan area foto berwarna putih / sangat terang, atau transparan (PNG)\n• Coba gunakan file PNG dengan background transparan untuk hasil terbaik');
                    return;
                }

                // === LANGKAH 4: Buat slot dari region yang valid ===
                // Tanya dulu apakah mau replace atau tambah
                const action = slots.length > 0
                    ? confirm(`🪄 Ditemukan ${validRegions.length} area foto.\n\nKlik OK untuk GANTI semua slot yang ada.\nKlik Cancel untuk TAMBAH ke slot yang sudah ada.`)
                    : true;

                if (action) slots = [];

                const PADDING = 4; // sedikit shrink agar tidak overlap border template
                validRegions.forEach(r => {
                    slots.push({
                        x: Math.max(0, r.minX + PADDING),
                        y: Math.max(0, r.minY + PADDING),
                        width:  Math.min(canvasW, r.maxX - r.minX - PADDING * 2),
                        height: Math.min(canvasH, r.maxY - r.minY - PADDING * 2),
                        borderRadius: 4,
                        shape: 'rectangle',
                        rotation: 0,
                    });
                });

                activeSlotIndex = 0;
                renderEditorCanvas();
                renderLivePreview();
                updateSlotsJson();

                btn.disabled = false;
                btn.textContent = '🪄 Auto-Detect Slot';

                // Toast notif
                const toast = document.createElement('div');
                toast.textContent = `✅ ${validRegions.length} slot foto berhasil dideteksi otomatis!`;
                toast.style.cssText = 'position:fixed;bottom:28px;left:50%;transform:translateX(-50%);background:#7c3aed;color:#fff;padding:12px 24px;border-radius:12px;font-size:13px;font-weight:700;z-index:9999;box-shadow:0 4px 24px rgba(0,0,0,0.4);';
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 3500);

            } catch (err) {
                console.error('Auto-detect error:', err);
                btn.disabled = false;
                btn.textContent = '🪄 Auto-Detect Slot';
                alert('Gagal menganalisis gambar. Pastikan gambar sudah terupload dengan benar.');
            }
        };
        img.onerror = () => {
            btn.disabled = false;
            btn.textContent = '🪄 Auto-Detect Slot';
            alert('Gagal memuat gambar untuk analisis.');
        };
        img.src = templateImageSrc;
    }

    // --- SLOT ACTIONS ---
    function addNewSlot() {
        const canvasW = parseInt(document.getElementById('inputWidth').value) || 420;
        const canvasH = parseInt(document.getElementById('inputHeight').value) || 980;

        let defW = Math.round(canvasW * 0.85);
        let defH = Math.round(defW * 0.68);
        let newX = Math.round((canvasW - defW) / 2);
        let newY = 30;

        if (slots.length > 0) {
            const last = slots[slots.length - 1];
            newY = last.y + last.height + 25;
            if (newY + defH > canvasH) {
                newY = Math.max(10, canvasH - defH - 10);
            }
        }

        slots.push({
            x: newX,
            y: newY,
            width: defW,
            height: defH,
            borderRadius: 8,
            shape: 'rectangle',
            rotation: 0
        });

        activeSlotIndex = slots.length - 1;
        renderEditorCanvas();
        renderLivePreview();
        updateSlotsJson();
    }

    function deleteSlot(idx, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        slots.splice(idx, 1);
        if (activeSlotIndex >= slots.length) activeSlotIndex = null;
        renderEditorCanvas();
        renderLivePreview();
        updateSlotsJson();
        syncInspectorWithActiveSlot();
    }

    function deleteActiveSlot() {
        if (activeSlotIndex !== null) {
            deleteSlot(activeSlotIndex);
        }
    }

    function syncInspectorWithActiveSlot() {
        const bar = document.getElementById('slotInspectorBar');
        if (activeSlotIndex === null || !slots[activeSlotIndex]) {
            bar.style.display = 'none';
            return;
        }
        bar.style.display = 'flex';
        const s = slots[activeSlotIndex];
        document.getElementById('inspSlotName').innerText = `FOTO ${activeSlotIndex + 1}`;
        document.getElementById('inspShape').value = s.shape || 'rectangle';
        document.getElementById('inspX').value = s.x;
        document.getElementById('inspY').value = s.y;
        document.getElementById('inspW').value = s.width;
        document.getElementById('inspH').value = s.height;
        document.getElementById('inspRot').value = s.rotation || 0;
        document.getElementById('inspSkewX').value = s.skewX || 0;
        document.getElementById('inspSkewY').value = s.skewY || 0;
        document.getElementById('inspRadius').value = s.borderRadius || 0;

        const customBar = document.getElementById('customShapeToolbar');
        if (customBar) {
            customBar.style.display = (s.shape === 'custom') ? 'flex' : 'none';
        }
    }

    function updateActiveSlotFromInspector() {
        if (activeSlotIndex === null || !slots[activeSlotIndex]) return;
        const s = slots[activeSlotIndex];
        const canvasW = parseInt(document.getElementById('inputWidth').value) || 420;
        const canvasH = parseInt(document.getElementById('inputHeight').value) || 980;

        const chosenShape = document.getElementById('inspShape').value;
        s.shape = chosenShape;
        if (chosenShape === 'custom' && (!s.points || s.points.length < 3)) {
            s.points = [ {x: 10, y: 5}, {x: 95, y: 15}, {x: 85, y: 95}, {x: 5, y: 85} ];
        }

        s.x = Math.max(0, parseInt(document.getElementById('inspX').value) || 0);
        s.y = Math.max(0, parseInt(document.getElementById('inspY').value) || 0);
        s.width = Math.max(20, Math.min(parseInt(document.getElementById('inspW').value) || 100, canvasW - s.x));
        s.height = Math.max(20, Math.min(parseInt(document.getElementById('inspH').value) || 100, canvasH - s.y));
        s.rotation = parseInt(document.getElementById('inspRot').value) || 0;
        s.skewX = parseInt(document.getElementById('inspSkewX').value) || 0;
        s.skewY = parseInt(document.getElementById('inspSkewY').value) || 0;
        s.borderRadius = Math.max(0, parseInt(document.getElementById('inspRadius').value) || 0);

        renderEditorCanvas();
        renderLivePreview();
        updateSlotsJson();

        const customBar = document.getElementById('customShapeToolbar');
        if (customBar) {
            customBar.style.display = (s.shape === 'custom') ? 'flex' : 'none';
        }
    }

    function rotateSlotQuick(deg) {
        if (activeSlotIndex === null || !slots[activeSlotIndex]) return;
        let r = (slots[activeSlotIndex].rotation || 0) + deg;
        if (r > 180) r -= 360;
        if (r < -180) r += 360;
        slots[activeSlotIndex].rotation = r;
        renderEditorCanvas();
        renderLivePreview();
        updateSlotsJson();
        syncInspectorWithActiveSlot();
    }

    function resetSlotTransforms() {
        if (activeSlotIndex === null || !slots[activeSlotIndex]) return;
        slots[activeSlotIndex].rotation = 0;
        slots[activeSlotIndex].skewX = 0;
        slots[activeSlotIndex].skewY = 0;
        renderEditorCanvas();
        renderLivePreview();
        updateSlotsJson();
        syncInspectorWithActiveSlot();
    }

    // Deselect active slot when clicking on canvas background
    document.addEventListener('DOMContentLoaded', () => {
        const workspace = document.getElementById('canvasWorkspace');
        if (workspace) {
            workspace.addEventListener('mousedown', (e) => {
                if (e.target.id === 'canvasWorkspace' || e.target.id === 'editorCanvas') {
                    activeSlotIndex = null;
                    renderEditorCanvas();
                    syncInspectorWithActiveSlot();
                }
            });
        }
    });

    function clearAllSlots() {
        if (confirm('Yakin ingin mengosongkan semua slot foto?')) {
            slots = [];
            activeSlotIndex = null;
            renderEditorCanvas();
            renderLivePreview();
            updateSlotsJson();
            syncInspectorWithActiveSlot();
        }
    }

    function centerAllSlots() {
        const canvasW = parseInt(document.getElementById('inputWidth').value) || 420;
        slots.forEach(s => {
            s.x = Math.round((canvasW - s.width) / 2);
        });
        renderEditorCanvas();
        renderLivePreview();
        updateSlotsJson();
        syncInspectorWithActiveSlot();
    }

    function distributeVertical() {
        if (slots.length < 2) return;
        const canvasH = parseInt(document.getElementById('inputHeight').value) || 980;

        slots.sort((a, b) => a.y - b.y);

        let totalSlotH = 0;
        slots.forEach(s => totalSlotH += s.height);

        const availableSpace = Math.max(0, canvasH - totalSlotH);
        const gap = Math.round(availableSpace / (slots.length + 1));

        let currentY = gap;
        slots.forEach(s => {
            s.y = currentY;
            currentY += s.height + gap;
        });

        renderEditorCanvas();
        renderLivePreview();
        updateSlotsJson();
        syncInspectorWithActiveSlot();
    }

    function updateSlotsJson() {
        document.getElementById('inputPhotoSlots').value = JSON.stringify(slots, null, 2);
        document.getElementById('inputPhotoCount').value = slots.length;
    }

    // --- PRESETS ---
    function loadPreset(type) {
        if (type === 'strip3') {
            document.getElementById('inputWidth').value = 420;
            document.getElementById('inputHeight').value = 980;
            slots = [
                {x: 24, y: 36, width: 372, height: 250, borderRadius: 8, shape: 'rectangle', rotation: 0},
                {x: 24, y: 306, width: 372, height: 250, borderRadius: 8, shape: 'rectangle', rotation: 0},
                {x: 24, y: 576, width: 372, height: 250, borderRadius: 8, shape: 'rectangle', rotation: 0}
            ];
            document.querySelector('input[name="category"][value="strip"]').checked = true;
        } else if (type === 'strip4') {
            document.getElementById('inputWidth').value = 420;
            document.getElementById('inputHeight').value = 1150;
            slots = [
                {x: 24, y: 30, width: 372, height: 220, borderRadius: 8, shape: 'rectangle', rotation: 0},
                {x: 24, y: 270, width: 372, height: 220, borderRadius: 8, shape: 'rectangle', rotation: 0},
                {x: 24, y: 510, width: 372, height: 220, borderRadius: 8, shape: 'rectangle', rotation: 0},
                {x: 24, y: 750, width: 372, height: 220, borderRadius: 8, shape: 'rectangle', rotation: 0}
            ];
            document.querySelector('input[name="category"][value="strip"]').checked = true;
        } else if (type === 'grid4') {
            document.getElementById('inputWidth').value = 600;
            document.getElementById('inputHeight').value = 600;
            slots = [
                {x: 30, y: 30, width: 255, height: 255, borderRadius: 8, shape: 'rectangle', rotation: 0},
                {x: 315, y: 30, width: 255, height: 255, borderRadius: 8, shape: 'rectangle', rotation: 0},
                {x: 30, y: 315, width: 255, height: 255, borderRadius: 8, shape: 'rectangle', rotation: 0},
                {x: 315, y: 315, width: 255, height: 255, borderRadius: 8, shape: 'rectangle', rotation: 0}
            ];
            document.querySelector('input[name="category"][value="grid"]').checked = true;
        } else if (type === 'single') {
            document.getElementById('inputWidth').value = 450;
            document.getElementById('inputHeight').value = 550;
            slots = [
                {x: 35, y: 35, width: 380, height: 380, borderRadius: 6, shape: 'rectangle', rotation: 0}
            ];
            document.querySelector('input[name="category"][value="single"]').checked = true;
        }

        renderEditorCanvas();
        renderLivePreview();
        updateSlotsJson();
    }

    function duplicateActiveSlot() {
        if (activeSlotIndex === null || !slots[activeSlotIndex]) return;

        const original = slots[activeSlotIndex];
        const copy = JSON.parse(JSON.stringify(original));

        copy.x = (copy.x || 0) + 20;
        copy.y = (copy.y || 0) + 20;

        slots.push(copy);
        activeSlotIndex = slots.length - 1;

        renderEditorCanvas();
        renderLivePreview();
        updateSlotsJson();
        syncInspectorWithActiveSlot();
    }

        function updateTextsJson() {
        document.getElementById('inputTextConfig').value = JSON.stringify(texts, null, 2);
    }

    function addNewText() {
        texts.push({
            text: 'Teks Baru',
            x: 50,
            y: 50,
            fontSize: 18,
            color: '#1a1a1a',
            fontFamily: 'sans-serif',
            fontWeight: '600',
            align: 'center',
            letterSpacing: 0,
            rotation: 0,
        });
        activeTextIndex = texts.length - 1;
        activeSlotIndex = null;
        renderEditorCanvas();
        renderLivePreview();
        updateTextsJson();
    }

    function deleteText(idx, e) {
        if (e) e.stopPropagation();
        texts.splice(idx, 1);
        activeTextIndex = null;
        renderEditorCanvas();
        renderLivePreview();
        updateTextsJson();
    }

    function renderTextsOnCanvas(canvasEl, scale) {
        texts.forEach((t, idx) => {
            const isActive = (idx === activeTextIndex);
            const textEl = document.createElement('div');
            textEl.className = 'canvas-text-el' + (isActive ? ' active' : '');
            textEl.style.position = 'absolute';
            textEl.style.left = (t.x * scale) + 'px';
            textEl.style.top = (t.y * scale) + 'px';
            textEl.style.fontSize = ((t.fontSize || 18) * scale) + 'px';
            textEl.style.color = t.color || '#1a1a1a';
            textEl.style.fontFamily = t.fontFamily || 'sans-serif';
            textEl.style.fontWeight = t.fontWeight || '600';
            textEl.style.letterSpacing = (t.letterSpacing || 0) + 'px';
            textEl.style.textAlign = t.align || 'center';
            textEl.style.transform = `translate(-50%, 0) rotate(${t.rotation || 0}deg)`;
            textEl.style.transformOrigin = 'center center';
            textEl.style.cursor = 'move';
            textEl.style.whiteSpace = 'nowrap';
            textEl.style.userSelect = 'none';
            textEl.style.padding = '4px 8px';
            textEl.style.border = isActive ? '1px dashed #8b5cf6' : '1px dashed transparent';
            textEl.style.zIndex = isActive ? 25 : 15;
            textEl.innerText = t.text;

            textEl.addEventListener('mousedown', (e) => {
                e.stopPropagation();
                if (activeTextIndex !== idx || activeSlotIndex !== null) {
                    activeTextIndex = idx;
                    activeSlotIndex = null;
                    renderEditorCanvas();
                }
                startDragText(e, idx, scale, textEl);
            });
            
            textEl.addEventListener('dblclick', (e) => {
                e.stopPropagation();
                textEl.contentEditable = true;
                textEl.focus();
            });

            textEl.addEventListener('blur', () => {
                if (textEl.contentEditable === 'true') {
                    texts[idx].text = textEl.innerText.trim() || 'Teks';
                    textEl.contentEditable = false;
                    renderEditorCanvas();
                    renderLivePreview();
                    updateTextsJson();
                }
            });

            canvasEl.appendChild(textEl);

            if (isActive) {
                const toolbar = document.createElement('div');
                toolbar.style.position = 'absolute';
                toolbar.style.left = (t.x * scale) + 'px';
                toolbar.style.top = ((t.y * scale) - 44) + 'px';
                toolbar.style.transform = 'translateX(-50%)';
                toolbar.style.display = 'flex';
                toolbar.style.gap = '6px';
                toolbar.style.background = '#1a1a1a';
                toolbar.style.padding = '6px 8px';
                toolbar.style.borderRadius = '8px';
                toolbar.style.zIndex = 30;
                toolbar.style.alignItems = 'center';
                                toolbar.innerHTML = `
                    <select onchange="updateTextProp(${idx}, 'fontFamily', this.value)" style="font-size:11px; padding:3px; max-width:110px;">
                        <option value="sans-serif" ${t.fontFamily === 'sans-serif' ? 'selected' : ''}>Sans Serif</option>
                        <option value="serif" ${t.fontFamily === 'serif' ? 'selected' : ''}>Serif</option>
                        <option value="'Poppins', sans-serif" ${t.fontFamily === "'Poppins', sans-serif" ? 'selected' : ''}>Poppins</option>
                        <option value="'Playfair Display', serif" ${t.fontFamily === "'Playfair Display', serif" ? 'selected' : ''}>Playfair Display</option>
                        <option value="'Dancing Script', cursive" ${t.fontFamily === "'Dancing Script', cursive" ? 'selected' : ''}>Dancing Script</option>
                        <option value="'Bebas Neue', sans-serif" ${t.fontFamily === "'Bebas Neue', sans-serif" ? 'selected' : ''}>Bebas Neue</option>
                        <option value="'Oswald', sans-serif" ${t.fontFamily === "'Oswald', sans-serif" ? 'selected' : ''}>Oswald</option>
                        <option value="'Pacifico', cursive" ${t.fontFamily === "'Pacifico', cursive" ? 'selected' : ''}>Pacifico</option>
                        <option value="'Montserrat', sans-serif" ${t.fontFamily === "'Montserrat', sans-serif" ? 'selected' : ''}>Montserrat</option>
                        <option value="'Caveat', cursive" ${t.fontFamily === "'Caveat', cursive" ? 'selected' : ''}>Caveat</option>
                        <option value="'Roboto Mono', monospace" ${t.fontFamily === "'Roboto Mono', monospace" ? 'selected' : ''}>Roboto Mono</option>
                        <option value="'Great Vibes', cursive" ${t.fontFamily === "'Great Vibes', cursive" ? 'selected' : ''}>Great Vibes</option>
                        <option value="'Lobster', cursive" ${t.fontFamily === "'Lobster', cursive" ? 'selected' : ''}>Lobster</option>
                        <option value="'Abril Fatface', serif" ${t.fontFamily === "'Abril Fatface', serif" ? 'selected' : ''}>Abril Fatface</option>
                        <option value="'Josefin Sans', sans-serif" ${t.fontFamily === "'Josefin Sans', sans-serif" ? 'selected' : ''}>Josefin Sans</option>
                    </select>
                    <input type="color" value="${t.color || '#1a1a1a'}" style="width:24px;height:24px;border:none;padding:0;cursor:pointer;" onchange="updateTextProp(${idx}, 'color', this.value)">
                    <input type="number" value="${t.fontSize || 18}" min="8" max="80" style="width:44px;font-size:11px;padding:3px;" onchange="updateTextProp(${idx}, 'fontSize', parseInt(this.value))">
                    <input type="number" value="${t.rotation || 0}" min="-180" max="180" title="Rotasi" style="width:44px;font-size:11px;padding:3px;" onchange="updateTextProp(${idx}, 'rotation', parseInt(this.value))">
                    <button type="button" onclick="deleteText(${idx}, event)" style="background:#ef4444;color:#fff;border:none;border-radius:4px;width:22px;height:22px;cursor:pointer;">✕</button>
                `;
                toolbar.addEventListener('mousedown', (e) => e.stopPropagation());
                canvasEl.appendChild(toolbar);
            }
        });
    }

    function updateTextProp(idx, prop, value) {
        texts[idx][prop] = value;
        renderEditorCanvas();
        renderLivePreview();
        updateTextsJson();
    }

    function startDragText(e, idx, scale, textEl) {
        const canvasEl = document.getElementById('editorCanvas');
        const canvasRect = canvasEl.getBoundingClientRect();

        function onMouseMove(ev) {
            const relX = (ev.clientX - canvasRect.left) / scale;
            const relY = (ev.clientY - canvasRect.top) / scale;
            texts[idx].x = Math.round(relX);
            texts[idx].y = Math.round(relY);
            renderEditorCanvas();
            renderLivePreview();
        }

        function onMouseUp() {
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
            updateTextsJson();
        }

        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
    }

    // --- LIVE CUSTOMER PREVIEW ---
    function toggleDemoPhotos() {
        showDemo = !showDemo;
        document.getElementById('btnToggleDemo').classList.toggle('active', showDemo);
        renderLivePreview();
    }

    function renderLivePreview() {
        const w = parseInt(document.getElementById('inputWidth').value) || 420;
        const h = parseInt(document.getElementById('inputHeight').value) || 980;
        const scale = getPreviewScale();

        document.getElementById('previewResText').innerText = `${w} × ${h} px (Skala ${(scale * 100).toFixed(0)}%)`;

        const printEl = document.getElementById('previewPrint');
        printEl.style.width = Math.round(w * scale) + 'px';
        printEl.style.height = Math.round(h * scale) + 'px';

        const bgImgEl = document.getElementById('previewBgImg');
        const overlayEl = document.getElementById('previewOverlayImg');
        if (templateImageSrc) {
            bgImgEl.src = templateImageSrc;
            bgImgEl.style.display = 'block';
            overlayEl.style.display = 'none';
        } else {
            bgImgEl.style.display = 'none';
            overlayEl.style.display = 'none';
        }

        const container = document.getElementById('previewSlotsContainer');
        container.innerHTML = '';

        slots.forEach((s, idx) => {
            const slotPreview = document.createElement('div');
            slotPreview.className = 'preview-photo-slot';
            slotPreview.style.left = (s.x * scale) + 'px';
            slotPreview.style.top = (s.y * scale) + 'px';
            slotPreview.style.width = (s.width * scale) + 'px';
            slotPreview.style.height = (s.height * scale) + 'px';
            slotPreview.style.transformOrigin = 'center center';
            if (s.rotation) {
                slotPreview.style.transform = `rotate(${s.rotation}deg)`;
            } else {
                slotPreview.style.transform = 'none';
            }

            const shape = s.shape || 'rectangle';
            const clipStyle = getShapeClipStyle(shape, s.borderRadius ? (s.borderRadius * scale) : 0, s.points);
            slotPreview.style.clipPath = clipStyle.clipPath;
            slotPreview.style.borderRadius = clipStyle.borderRadius;

            if (showDemo) {
                const img = document.createElement('img');
                img.className = 'preview-photo-demo';
                img.src = demoPhotos[idx % demoPhotos.length];
                img.alt = `Foto ${idx + 1}`;
                slotPreview.appendChild(img);
            } else {
                slotPreview.style.background = '#1e293b';
                slotPreview.style.border = '1px dashed #64748b';
                slotPreview.innerHTML = `<span style="font-size:11px; color:#94a3b8; font-weight:700;">#${idx + 1}</span>`;
            }

            container.appendChild(slotPreview);
        });
    }
</script>