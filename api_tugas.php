<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$conn = mysqli_connect("localhost", "root", "", "database chemor");
if (!$conn) {
    echo json_encode(['error' => 'Koneksi gagal: ' . mysqli_connect_error()]);
    exit;
}
mysqli_set_charset($conn, 'utf8mb4');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

    // =====================
    // GURU: Upload tugas baru
    // =====================
    case 'upload_guru':
        $jenis      = mysqli_real_escape_string($conn, $_POST['jenis'] ?? '');
        $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan'] ?? '');

        if (empty($_FILES['file']['name'])) {
            echo json_encode(['success' => false, 'message' => 'File tidak ditemukan']);
            exit;
        }

        $nama_file = basename($_FILES['file']['name']);
        $upload_dir = __DIR__ . '/uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $path_file = 'uploads/' . $nama_file;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_dir . $nama_file)) {
            $sql = "INSERT INTO uploads (nama_file, path_file, tipe_user, keterangan)
                    VALUES ('$nama_file', '$path_file', 'guru', '$jenis - $keterangan')";
            if (mysqli_query($conn, $sql)) {
                echo json_encode(['success' => true, 'id' => mysqli_insert_id($conn), 'message' => 'Upload berhasil']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal simpan ke DB: ' . mysqli_error($conn)]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal upload file']);
        }
        break;

    // =====================
    // GURU: Ambil semua tugas guru
    // =====================
    case 'get_tugas_guru':
        $sql = "SELECT * FROM uploads WHERE tipe_user='guru' ORDER BY tanggal DESC";
        $result = mysqli_query($conn, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    // =====================
    // GURU: Hapus tugas guru (+ semua jawaban siswa yang terkait)
    // =====================
    case 'hapus_tugas_guru':
        $id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
        if (!$id) { echo json_encode(['success' => false, 'message' => 'ID tidak valid']); exit; }

        // Hapus jawaban siswa yang terkait dulu
        $sql_siswa = "DELETE FROM uploads WHERE id_tugas_guru = $id";
        mysqli_query($conn, $sql_siswa);

        // Ambil path file guru untuk hapus file fisik
        $res = mysqli_query($conn, "SELECT path_file FROM uploads WHERE id = $id");
        $row = mysqli_fetch_assoc($res);
        if ($row && file_exists(__DIR__ . '/' . $row['path_file'])) {
            unlink(__DIR__ . '/' . $row['path_file']);
        }

        // Hapus tugas guru
        if (mysqli_query($conn, "DELETE FROM uploads WHERE id = $id")) {
            echo json_encode(['success' => true, 'message' => 'Tugas berhasil dihapus']);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        break;

    // =====================
    // SISWA: Upload pengerjaan tugas (terkait ke tugas guru)
    // =====================
    case 'upload_siswa':
        $nama_siswa    = mysqli_real_escape_string($conn, $_POST['nama_siswa'] ?? '');
        $kelas         = mysqli_real_escape_string($conn, $_POST['kelas'] ?? '');
        $keterangan    = mysqli_real_escape_string($conn, $_POST['keterangan'] ?? '');
        $id_tugas_guru = intval($_POST['id_tugas_guru'] ?? 0);

        if (!$id_tugas_guru) {
            echo json_encode(['success' => false, 'message' => 'ID tugas tidak valid']);
            exit;
        }
        if (empty($_FILES['file']['name'])) {
            echo json_encode(['success' => false, 'message' => 'File tidak ditemukan']);
            exit;
        }

        $nama_file = basename($_FILES['file']['name']);
        $upload_dir = __DIR__ . '/uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        // Tambah prefix unik supaya tidak tumpang tindih
        $unique_name = time() . '_' . $nama_file;
        $path_file = 'uploads/' . $unique_name;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_dir . $unique_name)) {
            $ket = mysqli_real_escape_string($conn, "$nama_siswa - $kelas - $keterangan");
            $sql = "INSERT INTO uploads (nama_file, path_file, tipe_user, keterangan, id_tugas_guru)
                    VALUES ('$nama_file', '$path_file', 'siswa', '$ket', $id_tugas_guru)";
            if (mysqli_query($conn, $sql)) {
                echo json_encode(['success' => true, 'message' => 'Tugas berhasil dikumpulkan']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal simpan: ' . mysqli_error($conn)]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal upload file']);
        }
        break;

    // =====================
    // GURU: Ambil semua jawaban siswa untuk satu tugas
    // =====================
    case 'get_jawaban_siswa':
        $id_tugas_guru = intval($_GET['id_tugas_guru'] ?? 0);
        if (!$id_tugas_guru) { echo json_encode(['success' => false, 'data' => []]); exit; }

        $sql = "SELECT * FROM uploads WHERE tipe_user='siswa' AND id_tugas_guru=$id_tugas_guru ORDER BY tanggal DESC";
        $result = mysqli_query($conn, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    // =====================
    // GURU: Hapus satu jawaban siswa
    // =====================
    case 'hapus_jawaban_siswa':
        $id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
        if (!$id) { echo json_encode(['success' => false, 'message' => 'ID tidak valid']); exit; }

        $res = mysqli_query($conn, "SELECT path_file FROM uploads WHERE id = $id AND tipe_user='siswa'");
        $row = mysqli_fetch_assoc($res);
        if ($row && file_exists(__DIR__ . '/' . $row['path_file'])) {
            unlink(__DIR__ . '/' . $row['path_file']);
        }

        if (mysqli_query($conn, "DELETE FROM uploads WHERE id = $id")) {
            echo json_encode(['success' => true, 'message' => 'Jawaban dihapus']);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        break;

    default:
        echo json_encode(['error' => 'Action tidak dikenal: ' . $action]);
}

mysqli_close($conn);
?>
