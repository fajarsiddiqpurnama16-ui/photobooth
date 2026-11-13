<div class="topbar">
    <div class="page-header">
        <a href="<?= base_url('index.php/AdminPayments/settings') ?>" style="display:inline-block; font-size:13px; font-weight:600; margin-bottom:8px;">&larr; Kembali ke pengaturan</a>
        <h1>Panel Landing</h1>
        <p>Kartu info yang tampil di bawah tombol "Enter for payment".</p>
    </div>
</div>


<div class="card" style="max-width:560px; margin-bottom:20px;">
    <form method="post" enctype="multipart/form-data" action="<?= base_url('index.php/AdminPayments/card_save') ?>">
        <input type="hidden" name="id" value="<?= $edit ? (int)$edit->id : 0 ?>">

        <div class="form-group">
            <label>Gambar <span class="label-hint">Opsional, disarankan landscape 16:9 (800×450)</span></label>
            <?php if ($edit && !empty($edit->image)): ?>
                <div style="margin-bottom:8px;">
                    <img src="<?= base_url('uploads/landing/' . $edit->image) ?>" style="width:220px; border-radius:8px; border:1px solid var(--line);">
                    <label style="display:flex; align-items:center; gap:8px; justify-content:flex-start; margin-top:6px;">
                        <input type="checkbox" name="remove_image" value="1" style="width:auto;"> Hapus gambar ini
                    </label>
                </div>
            <?php endif; ?>
            <input type="file" name="image" accept="image/*">
        </div>

        <div class="form-group">
            <label>Ikon</label>
            <select name="icon">
                <?php foreach (['sparkles','gift','tag','clock','printer','camera','heart','star', 'map-pin'] as $ic): ?>
                    <option value="<?= $ic ?>" <?= ($edit && $edit->icon === $ic) ? 'selected' : '' ?>><?= $ic ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Judul</label>
            <input type="text" name="title" maxlength="80" value="<?= $edit ? htmlspecialchars($edit->title) : '' ?>">
        </div>
        <div class="form-group">
            <label>Keterangan</label>
            <input type="text" name="description" maxlength="200" value="<?= $edit ? htmlspecialchars($edit->description) : '' ?>">
        </div>
        <div class="form-group">
            <label>Urutan</label>
            <input type="number" name="sort_order" value="<?= $edit ? (int)$edit->sort_order : 0 ?>">
        </div>
        <div class="form-group">
            <label style="display:flex; align-items:center; gap:8px; justify-content:flex-start;">
                <input type="checkbox" name="is_active" value="1" style="width:auto;" <?= (!$edit || $edit->is_active) ? 'checked' : '' ?>>
                Tampilkan di landing
            </label>
        </div>
        <button type="submit" class="btn btn-primary"><?= $edit ? 'Simpan perubahan' : 'Tambah panel' ?></button>
        <?php if ($edit): ?>
            <a href="<?= base_url('index.php/AdminPayments/cards') ?>" class="btn btn-secondary">Batal</a>
        <?php endif; ?>
    </form>
</div>

<div class="card" style="max-width:560px;">
    <table>
        <thead><tr><th>Urutan</th><th>Panel</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php foreach ($cards as $c): ?>
            <tr>
                <td><?= (int)$c->sort_order ?></td>
                <td>
                    <?php if (!empty($c->image)): ?>
                        <img src="<?= base_url('uploads/landing/' . $c->image) ?>" style="width:90px; border-radius:6px; display:block; margin-bottom:4px;">
                    <?php endif; ?>
                    <strong><?= htmlspecialchars($c->title) ?></strong><br><small><?= htmlspecialchars($c->description) ?></small>
                </td>
                <td><?= $c->is_active ? 'Tampil' : 'Disembunyikan' ?></td>
                <td>
                    <a href="<?= base_url('index.php/AdminPayments/cards?edit=' . $c->id) ?>">Ubah</a> ·
                    <a href="<?= base_url('index.php/AdminPayments/card_delete/' . $c->id) ?>" onclick="return confirm('Hapus panel ini?')">Hapus</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($cards)): ?>
            <tr><td colspan="4">Belum ada panel.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>