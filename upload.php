<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$user = "root";
$pass = "";
$db = "database chemor";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

if (isset($_POST['upload'])) {
    $tipe_user = $_POST['tipe_user'];
    $keterangan = $_POST['keterangan'];
    $nama_file = basename($_FILES['file']['name']);
    $path_file = "uploads/" . $nama_file;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $path_file)) {
        $sql = "INSERT INTO uploads (nama_file, path_file, tipe_user, keterangan) 
                VALUES ('$nama_file', '$path_file', '$tipe_user', '$keterangan')";
        mysqli_query($conn, $sql);
        echo "Upload berhasil!";
    } else {
        echo "Upload gagal!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload File - ChemOrWebsite</title>
</head>
<body>
    <h2>Upload File</h2>
    <form method="POST" enctype="multipart/form-data">
        <label>Tipe User:</label><br>
        <select name="tipe_user">
            <option value="siswa">Siswa</option>
            <option value="admin">Admin</option>
        </select><br><br>

        <label>Keterangan:</label><br>
        <input type="text" name="keterangan" placeholder="Contoh: Tugas Bab 1"><br><br>

        <label>Pilih File:</label><br>
        <input type="file" name="file"><br><br>

        <button type="submit" name="upload">Upload</button>
    </form>
</body>
</html>