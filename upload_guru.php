<?php
session_start();
$host = "localhost";
$user = "root";
$pass = "";
$db = "database chemor";
$conn = mysqli_connect($host, $user, $pass, $db);

$PASSWORD_GURU = "guru2024";
$pesan = "";
if (!isset($_SESSION['guru_login'])) $_SESSION['guru_login'] = false;

if (isset($_POST['login'])) {
    if ($_POST['password'] === $PASSWORD_GURU) {
        $_SESSION['guru_login'] = true;
    } else {
        $pesan = "password_salah";
    }
}

if (isset($_GET['logout'])) {
    $_SESSION['guru_login'] = false;
    session_destroy();
    header("Location: upload_guru.php");
    exit;
}

if (isset($_POST['upload']) && $_SESSION['guru_login']) {
    $jenis = $_POST['jenis'];
    $keterangan = $_POST['keterangan'];
    $nama_file = basename($_FILES['file']['name']);
    $path_file = "uploads/" . $nama_file;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $path_file)) {
        $sql = "INSERT INTO uploads (nama_file, path_file, tipe_user, keterangan) 
                VALUES ('$nama_file', '$path_file', 'guru', '$jenis - $keterangan')";
        mysqli_query($conn, $sql);
        $pesan = "success";
    } else {
        $pesan = "gagal";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Guru — SiChemOr</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:sans-serif; background:#f0f4fc; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:2rem; }
        .card { background:#fff; border-radius:14px; padding:2rem; width:100%; max-width:480px; box-shadow:0 8px 40px rgba(30,58,138,0.13); }
        .logo { text-align:center; margin-bottom:1.5rem; }
        .logo h1 { font-size:1.4rem; font-weight:700; color:#1e3a8a; }
        .logo p { font-size:0.85rem; color:#4a5a80; margin-top:0.3rem; }
        .form-group { margin-bottom:1rem; }
        label { font-size:0.82rem; font-weight:600; color:#1a2340; display:block; margin-bottom:0.4rem; }
        input, select { width:100%; padding:0.6rem 0.9rem; border:1.5px solid rgba(30,58,138,0.18); border-radius:8px; font-size:0.9rem; color:#1a2340; outline:none; }
        input:focus, select:focus { border-color:#1e3a8a; }
        .upload-zone { border:1.5px dashed rgba(30,58,138,0.25); border-radius:8px; padding:1.5rem; text-align:center; cursor:pointer; background:#f8faff; position:relative; min-height:120px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:0.3rem; }
        .upload-zone:hover { border-color:#1e3a8a; background:#eef2ff; }
        .upload-zone input[type=file] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
        .upload-zone p { font-size:0.82rem; color:#4a5a80; }
        .file-name { font-size:0.85rem; color:#1e3a8a; font-weight:600; margin-top:0.3rem; word-break:break-all; }
        .btn { width:100%; padding:0.75rem; background:#1e3a8a; color:#fff; border:none; border-radius:8px; font-size:1rem; font-weight:600; cursor:pointer; margin-top:1rem; }
        .btn:hover { background:#1e40af; }
        .btn-logout { background:#dc2626; margin-top:0.5rem; }
        .btn-logout:hover { background:#b91c1c; }
        .success { background:#d1fae5; border:1px solid #059669; color:#065f46; padding:0.9rem; border-radius:8px; text-align:center; margin-bottom:1rem; font-weight:600; }
        .error { background:#fee2e2; border:1px solid #dc2626; color:#7f1d1d; padding:0.9rem; border-radius:8px; text-align:center; margin-bottom:1rem; }
        .back-link { text-align:center; margin-top:1rem; font-size:0.85rem; }
        .back-link a { color:#1e3a8a; text-decoration:none; font-weight:600; }
        .guru-badge { background:#1e3a8a; color:#fff; padding:0.3rem 0.8rem; border-radius:99px; font-size:0.75rem; font-weight:600; display:inline-block; margin-bottom:1rem; }
    </style>
</head>
<body>
<div class="card">
    <div class="logo">
        <h1>🧪 SiChemOr</h1>
        <p>Panel Upload Guru</p>
    </div>

    <?php if (!$_SESSION['guru_login']): ?>
        <?php if ($pesan == "password_salah"): ?>
            <div class="error">❌ Password salah, coba lagi.</div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Password Guru</label>
                <input type="password" name="password" placeholder="Masukkan password guru" required>
            </div>
            <button type="submit" name="login" class="btn">🔑 Masuk</button>
        </form>

    <?php else: ?>
        <div style="text-align:center">
            <span class="guru-badge">👨‍🏫 Mode Guru</span>
        </div>

        <?php if ($pesan == "success"): ?>
            <div class="success">✅ File berhasil diupload!</div>
        <?php elseif ($pesan == "gagal"): ?>
            <div class="error">❌ Upload gagal, coba lagi.</div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Jenis File</label>
                <select name="jenis">
                    <option value="Soal Tugas">📝 Soal Tugas / LKPD</option>
                    <option value="Materi">📚 Materi / Modul</option>
                    <option value="Kuis">📋 Kuis</option>
                </select>
            </div>
            <div class="form-group">
                <label>Keterangan</label>
                <input type="text" name="keterangan" placeholder="Contoh: LKPD 2 - Reaksi Esterifikasi" required>
            </div>
            <div class="form-group">
                <label>Pilih File</label>
                <div class="upload-zone" id="upload-zone">
                    <div style="font-size:1.8rem">📁</div>
                    <p>Klik untuk pilih file</p>
                    <p style="font-size:0.72rem;color:#8fa3c8">Semua format diterima</p>
                    <p class="file-name" id="nama-file"></p>
                    <input type="file" name="file" required onchange="
                        var nama = this.files[0].name;
                        document.getElementById('nama-file').innerText = '📄 ' + nama;
                        document.getElementById('upload-zone').style.borderColor = '#1e3a8a';
                        document.getElementById('upload-zone').style.background = '#eef2ff';
                    ">
                </div>
            </div>
            <button type="submit" name="upload" class="btn">📤 Upload Sekarang</button>
        </form>
        <form method="GET">
            <button type="submit" name="logout" class="btn btn-logout">🚪 Keluar</button>
        </form>

    <?php endif; ?>

    <div class="back-link">
        <a href="ChemOr.php">← Kembali ke Halaman Utama</a>
    </div>
</div>
</body>
</html>