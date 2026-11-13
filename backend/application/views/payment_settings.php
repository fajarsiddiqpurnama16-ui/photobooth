<div class="topbar">
    <div class="page-header">
        <a href="<?= base_url('index.php/AdminPayments') ?>" style="display:inline-block; color:var(--muted); font-size:13px; font-weight:600; margin-bottom:8px;">&larr; Kembali ke konfirmasi pembayaran</a>
        <h1>Pengaturan Pembayaran</h1>
        <p>Upload QRIS dan atur harga sesi.</p>
    </div>
</div>

<div class="card" style="max-width:560px;">
    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label>Gambar QRIS <span class="label-hint">Upload baru untuk mengganti</span></label>
            <?php if (!empty($s->qris_image)): ?>
                <div style="margin-bottom:10px;">
                    <img src="<?= base_url('uploads/qris/' . $s->qris_image) ?>" alt="QRIS saat ini" style="width:180px; border-radius:8px; border:1px solid var(--line);">
                </div>
            <?php endif; ?>
            <input type="file" name="qris_image" accept="image/*">
        </div>

        <div class="form-group">
            <label>Harga per sesi (Rp)</label>
            <input type="number" name="price" value="<?= htmlspecialchars($s->price) ?>" required>
        </div>

        <div class="form-group">
            <label>Durasi sesi (menit)</label>
            <input type="number" name="session_minutes" value="<?= htmlspecialchars($s->session_minutes) ?>" required>
        </div>
        <div class="form-group">
            <label>Jumlah cetak foto per sesi</label>
            <input type="number" name="print_copies" min="1" value="<?= (int)($s->print_copies ?? 2) ?>" required>
        </div>

        <a href="<?= base_url('index.php/AdminPayments/cards') ?>" class="btn btn-secondary btn-sm">Kelola Panel Landing</a>

        <button type="submit" class="btn btn-primary">Simpan perubahan</button>
    </form>
</div>