<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "database chemor";
$conn = mysqli_connect($host, $user, $pass, $db);
if ($conn) mysqli_set_charset($conn, 'utf8mb4');

// Ambil daftar tugas guru dari DB (untuk dropdown)
$daftar_tugas = [];
if ($conn) {
    $r = mysqli_query($conn, "SELECT id, keterangan, tanggal FROM uploads WHERE tipe_user='guru' ORDER BY tanggal DESC");
    while ($row = mysqli_fetch_assoc($r)) $daftar_tugas[] = $row;
}

$pesan = "";
if (isset($_POST['upload'])) {
    $nama_siswa    = mysqli_real_escape_string($conn, $_POST['nama_siswa'] ?? '');
    $kelas         = mysqli_real_escape_string($conn, $_POST['kelas'] ?? '');
    $keterangan    = mysqli_real_escape_string($conn, $_POST['keterangan'] ?? '');
    $id_tugas_guru = intval($_POST['id_tugas_guru'] ?? 0);

    if (!$id_tugas_guru) {
        $pesan = "no_tugas";
    } elseif (empty($_FILES['file']['name'])) {
        $pesan = "gagal";
    } else {
        $nama_file   = basename($_FILES['file']['name']);
        $upload_dir  = __DIR__ . '/uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        // Tambah prefix unik supaya nama file tidak tumpang tindih
        $unique_name = time() . '_' . $nama_file;
        $path_file   = 'uploads/' . $unique_name;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_dir . $unique_name)) {
            $ket = mysqli_real_escape_string($conn, "$nama_siswa - $kelas - $keterangan");
            $sql = "INSERT INTO uploads (nama_file, path_file, tipe_user, keterangan, id_tugas_guru)
                    VALUES ('$nama_file', '$path_file', 'siswa', '$ket', $id_tugas_guru)";
            if (mysqli_query($conn, $sql)) {
                $pesan = "success";
            } else {
                $pesan = "gagal";
            }
        } else {
            $pesan = "gagal";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Tugas — SiChemOr</title>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Source Sans 3',sans-serif; background:#f0f4fc; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:2rem; }
        .card { background:#fff; border-radius:14px; padding:2rem; width:100%; max-width:480px; box-shadow:0 8px 40px rgba(30,58,138,0.13); }
        .logo { text-align:center; margin-bottom:1.5rem; }
        .logo h1 { font-size:1.4rem; font-weight:700; color:#1e3a8a; }
        .logo p { font-size:0.85rem; color:#4a5a80; margin-top:0.3rem; }
        .form-group { margin-bottom:1rem; }
        label { font-size:0.82rem; font-weight:600; color:#1a2340; display:block; margin-bottom:0.4rem; }
        input, select, textarea { width:100%; padding:0.6rem 0.9rem; border:1.5px solid rgba(30,58,138,0.18); border-radius:8px; font-family:inherit; font-size:0.9rem; color:#1a2340; outline:none; transition:border 0.2s; }
        input:focus, select:focus { border-color:#1e3a8a; }
        .upload-zone { border:1.5px dashed rgba(30,58,138,0.25); border-radius:8px; padding:1.5rem; text-align:center; cursor:pointer; background:#f8faff; position:relative; transition:all 0.2s; }
        .upload-zone:hover { border-color:#1e3a8a; background:#eef2ff; }
        .upload-zone input[type=file] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
        .upload-zone p { font-size:0.82rem; color:#4a5a80; margin-top:0.4rem; }
        .btn { width:100%; padding:0.75rem; background:#1e3a8a; color:#fff; border:none; border-radius:8px; font-size:1rem; font-weight:600; cursor:pointer; margin-top:1rem; transition:background 0.2s; }
        .btn:hover { background:#1e40af; }
        .success { background:#d1fae5; border:1px solid #059669; color:#065f46; padding:0.9rem; border-radius:8px; text-align:center; margin-bottom:1rem; font-weight:600; }
        .error   { background:#fee2e2; border:1px solid #dc2626; color:#7f1d1d; padding:0.9rem; border-radius:8px; text-align:center; margin-bottom:1rem; }
        .warning { background:#fef9c3; border:1px solid #ca8a04; color:#713f12; padding:0.9rem; border-radius:8px; text-align:center; margin-bottom:1rem; }
        .back-link { text-align:center; margin-top:1rem; font-size:0.85rem; }
        .back-link a { color:#1e3a8a; text-decoration:none; font-weight:600; }
        .no-tugas { text-align:center; padding:1.5rem; background:#f8faff; border-radius:8px; color:#4a5a80; font-size:0.85rem; border:1.5px dashed rgba(30,58,138,0.18); }
    </style>
</head>
<body>
<div class="card">
    <div class="logo">
        <h1>🧪 SiChemOr</h1>
        <p>Upload Tugas / LKPD</p>
    </div>

    <?php if ($pesan == "success"): ?>
        <div class="success">✅ Tugas berhasil dikumpulkan!</div>
    <?php elseif ($pesan == "gagal"): ?>
        <div class="error">❌ Upload gagal, coba lagi.</div>
    <?php elseif ($pesan == "no_tugas"): ?>
        <div class="warning">⚠️ Pilih tugas yang ingin dikumpulkan terlebih dahulu.</div>
    <?php endif; ?>

    <?php if (empty($daftar_tugas)): ?>
        <div class="no-tugas">
            📋 Belum ada tugas dari guru.<br>
            <small>Silakan kembali lagi nanti.</small>
        </div>
    <?php else: ?>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Pilih Tugas / LKPD</label>
            <select name="id_tugas_guru" required>
                <option value="">-- Pilih Tugas --</option>
                <?php foreach ($daftar_tugas as $t): ?>
                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['keterangan']) ?> (<?= $t['tanggal'] ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="nama_siswa" placeholder="Contoh: Budi Santoso" required>
        </div>
        <div class="form-group">
            <label>Kelas</label>
            <input type="text" name="kelas" placeholder="Contoh: XII MIPA 2" required>
        </div>
        <div class="form-group">
            <label>Keterangan Tambahan <span style="font-weight:400;color:#8fa3c8">(opsional)</span></label>
            <input type="text" name="keterangan" placeholder="Contoh: Pengerjaan susulan">
        </div>
        <div class="form-group">
            <label>File Tugas</label>
            <div class="upload-zone">
                <div style="font-size:1.8rem">📄</div>
                <p>Klik untuk pilih file</p>
                <p style="font-size:0.72rem;color:#8fa3c8">Semua format diterima</p>
                <p id="nama-file" style="font-size:0.85rem;color:#1e3a8a;font-weight:600;margin-top:0.5rem"></p>
                <input type="file" name="file" required onchange="document.getElementById('nama-file').innerText = this.files[0].name">
            </div>
        </div>
        <button type="submit" name="upload" class="btn">📤 Kumpulkan Tugas</button>
    </form>
    <?php endif; ?>

    <div class="back-link">
        <a href="ChemOr.php">← Kembali ke Halaman Utama</a>
    </div>
</div>
</body>
</html>
