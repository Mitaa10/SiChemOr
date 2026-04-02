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

    // =============================================
    // TUGAS / LKPD
    // =============================================

    case 'upload_tugas_guru':
        $judul      = mysqli_real_escape_string($conn, $_POST['judul'] ?? '');
        $deskripsi  = mysqli_real_escape_string($conn, $_POST['deskripsi'] ?? '');
        $deadline   = $_POST['deadline'] ?? '';
        $deadline_sql = (!empty($deadline)) ? "'" . mysqli_real_escape_string($conn, $deadline) . "'" : "NULL";

        if (empty($judul)) {
            echo json_encode(['success' => false, 'message' => 'Judul wajib diisi']); exit;
        }
        if (empty($_FILES['file']['name'])) {
            echo json_encode(['success' => false, 'message' => 'File tidak ditemukan']); exit;
        }

        $nama_file  = basename($_FILES['file']['name']);
        $upload_dir = __DIR__ . '/uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $unique_name = time() . '_' . $nama_file;
        $path_file   = 'uploads/' . $unique_name;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_dir . $unique_name)) {
            $ket = mysqli_real_escape_string($conn, "Soal Tugas - $judul");
            $sql = "INSERT INTO uploads (nama_file, path_file, tipe_user, keterangan, deskripsi, deadline)
                    VALUES ('$nama_file', '$path_file', 'guru', '$ket', '$deskripsi', $deadline_sql)";
            if (mysqli_query($conn, $sql)) {
                echo json_encode(['success' => true, 'id' => mysqli_insert_id($conn)]);
            } else {
                echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal upload file']);
        }
        break;

    case 'get_tugas_guru':
        $sql = "SELECT * FROM uploads WHERE tipe_user='guru' ORDER BY tanggal DESC";
        $result = mysqli_query($conn, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) $data[] = $row;
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    case 'hapus_tugas_guru':
        $id = intval($_POST['id'] ?? 0);
        if (!$id) { echo json_encode(['success' => false, 'message' => 'ID tidak valid']); exit; }
        $sql_siswa = "DELETE FROM uploads WHERE id_tugas_guru = $id";
        mysqli_query($conn, $sql_siswa);
        $res = mysqli_query($conn, "SELECT path_file FROM uploads WHERE id = $id");
        $row = mysqli_fetch_assoc($res);
        if ($row && file_exists(__DIR__ . '/' . $row['path_file'])) unlink(__DIR__ . '/' . $row['path_file']);
        if (mysqli_query($conn, "DELETE FROM uploads WHERE id = $id")) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        break;

    case 'upload_jawaban_siswa':
        $nama_siswa    = mysqli_real_escape_string($conn, $_POST['nama_siswa'] ?? '');
        $kelas         = mysqli_real_escape_string($conn, $_POST['kelas'] ?? '');
        $keterangan    = mysqli_real_escape_string($conn, $_POST['keterangan'] ?? '');
        $id_tugas_guru = intval($_POST['id_tugas_guru'] ?? 0);

        if (!$id_tugas_guru) { echo json_encode(['success' => false, 'message' => 'ID tugas tidak valid']); exit; }
        if (empty($_FILES['file']['name'])) { echo json_encode(['success' => false, 'message' => 'File tidak ditemukan']); exit; }

        // Cek deadline
        $res_deadline = mysqli_query($conn, "SELECT deadline FROM uploads WHERE id = $id_tugas_guru");
        $row_deadline = mysqli_fetch_assoc($res_deadline);
        if ($row_deadline && $row_deadline['deadline'] && strtotime($row_deadline['deadline']) < time()) {
            echo json_encode(['success' => false, 'message' => 'Deadline sudah lewat! Tidak bisa mengumpulkan tugas.']); exit;
        }

        $nama_file   = basename($_FILES['file']['name']);
        $upload_dir  = __DIR__ . '/uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $unique_name = time() . '_' . $nama_file;
        $path_file   = 'uploads/' . $unique_name;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_dir . $unique_name)) {
            $ket = mysqli_real_escape_string($conn, "$nama_siswa - $kelas - $keterangan");
            $sql = "INSERT INTO uploads (nama_file, path_file, tipe_user, keterangan, id_tugas_guru)
                    VALUES ('$nama_file', '$path_file', 'siswa', '$ket', $id_tugas_guru)";
            if (mysqli_query($conn, $sql)) {
                echo json_encode(['success' => true, 'message' => 'Tugas berhasil dikumpulkan']);
            } else {
                echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal upload file']);
        }
        break;

    case 'get_jawaban_siswa':
        $id_tugas_guru = intval($_GET['id_tugas_guru'] ?? 0);
        if (!$id_tugas_guru) { echo json_encode(['success' => false, 'data' => []]); exit; }
        $sql = "SELECT * FROM uploads WHERE tipe_user='siswa' AND id_tugas_guru=$id_tugas_guru ORDER BY tanggal DESC";
        $result = mysqli_query($conn, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) $data[] = $row;
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    case 'hapus_jawaban_siswa':
        $id = intval($_POST['id'] ?? 0);
        if (!$id) { echo json_encode(['success' => false, 'message' => 'ID tidak valid']); exit; }
        $res = mysqli_query($conn, "SELECT path_file FROM uploads WHERE id = $id AND tipe_user='siswa'");
        $row = mysqli_fetch_assoc($res);
        if ($row && file_exists(__DIR__ . '/' . $row['path_file'])) unlink(__DIR__ . '/' . $row['path_file']);
        if (mysqli_query($conn, "DELETE FROM uploads WHERE id = $id")) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        break;

    // =============================================
    // PRESENSI
    // =============================================

    case 'simpan_presensi':
        $nama       = mysqli_real_escape_string($conn, $_POST['nama'] ?? '');
        $kelas      = mysqli_real_escape_string($conn, $_POST['kelas'] ?? '');
        $status     = mysqli_real_escape_string($conn, $_POST['status'] ?? 'Hadir');
        $pertemuan  = intval($_POST['pertemuan'] ?? 1);
        $judul_pertemuan = mysqli_real_escape_string($conn, $_POST['judul_pertemuan'] ?? '');

        if (!$nama || !$kelas) { echo json_encode(['success' => false, 'message' => 'Nama dan kelas wajib diisi']); exit; }

        // Cek apakah sudah presensi di pertemuan ini
        $cek = mysqli_query($conn, "SELECT id FROM presensi WHERE nama='$nama' AND kelas='$kelas' AND pertemuan=$pertemuan");
        if (mysqli_num_rows($cek) > 0) {
            // Update status
            $row_cek = mysqli_fetch_assoc($cek);
            mysqli_query($conn, "UPDATE presensi SET status='$status', waktu=NOW() WHERE id={$row_cek['id']}");
            echo json_encode(['success' => true, 'message' => 'Presensi diperbarui']);
        } else {
            $sql = "INSERT INTO presensi (nama, kelas, status, pertemuan, judul_pertemuan) VALUES ('$nama','$kelas','$status',$pertemuan,'$judul_pertemuan')";
            if (mysqli_query($conn, $sql)) {
                echo json_encode(['success' => true, 'message' => 'Presensi berhasil']);
            } else {
                echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
            }
        }
        break;

    case 'get_presensi':
        $pertemuan = intval($_GET['pertemuan'] ?? 1);
        $sql = "SELECT * FROM presensi WHERE pertemuan=$pertemuan ORDER BY waktu ASC";
        $result = mysqli_query($conn, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) $data[] = $row;
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    case 'get_semua_presensi':
        $sql = "SELECT * FROM presensi ORDER BY pertemuan ASC, waktu ASC";
        $result = mysqli_query($conn, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) $data[] = $row;
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    case 'update_status_presensi':
        $id     = intval($_POST['id'] ?? 0);
        $status = mysqli_real_escape_string($conn, $_POST['status'] ?? 'Hadir');
        if (!$id) { echo json_encode(['success' => false, 'message' => 'ID tidak valid']); exit; }
        if (mysqli_query($conn, "UPDATE presensi SET status='$status' WHERE id=$id")) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        break;

    case 'hapus_presensi':
        $id = intval($_POST['id'] ?? 0);
        if (!$id) { echo json_encode(['success' => false, 'message' => 'ID tidak valid']); exit; }
        if (mysqli_query($conn, "DELETE FROM presensi WHERE id=$id")) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        break;

    // =============================================
    // KUIS
    // =============================================

    case 'simpan_kuis':
        $judul     = mysqli_real_escape_string($conn, $_POST['judul'] ?? '');
        $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi'] ?? '');
        $soal_raw  = $_POST['soal'] ?? '[]';
        // Validasi JSON
        $soal_decoded = json_decode($soal_raw, true);
        if (!$judul || !$soal_decoded) { echo json_encode(['success' => false, 'message' => 'Data tidak valid']); exit; }
        $soal = mysqli_real_escape_string($conn, json_encode($soal_decoded, JSON_UNESCAPED_UNICODE));
        $sql = "INSERT INTO kuis (judul, deskripsi, soal) VALUES ('$judul','$deskripsi','$soal')";
        if (mysqli_query($conn, $sql)) {
            echo json_encode(['success' => true, 'id' => mysqli_insert_id($conn)]);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        break;

    case 'get_kuis':
        $sql = "SELECT id, judul, deskripsi, dibuat FROM kuis ORDER BY dibuat DESC";
        $result = mysqli_query($conn, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) $data[] = $row;
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    case 'get_soal_kuis':
        $id = intval($_GET['id'] ?? 0);
        if (!$id) { echo json_encode(['success' => false]); exit; }
        $res = mysqli_query($conn, "SELECT * FROM kuis WHERE id=$id");
        $row = mysqli_fetch_assoc($res);
        if ($row) {
            $row['soal'] = json_decode($row['soal'], true);
            echo json_encode(['success' => true, 'data' => $row]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Kuis tidak ditemukan']);
        }
        break;

    case 'hapus_kuis':
        $id = intval($_POST['id'] ?? 0);
        if (!$id) { echo json_encode(['success' => false, 'message' => 'ID tidak valid']); exit; }
        if (mysqli_query($conn, "DELETE FROM kuis WHERE id=$id")) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        break;

    case 'simpan_nilai_kuis':
        $id_kuis    = intval($_POST['id_kuis'] ?? 0);
        $nama_siswa = mysqli_real_escape_string($conn, $_POST['nama_siswa'] ?? '');
        $kelas      = mysqli_real_escape_string($conn, $_POST['kelas'] ?? '');
        $nilai      = intval($_POST['nilai'] ?? 0);
        $jawaban    = mysqli_real_escape_string($conn, $_POST['jawaban'] ?? '[]');

        if (!$id_kuis || !$nama_siswa) { echo json_encode(['success' => false, 'message' => 'Data tidak valid']); exit; }

        // Cek apakah sudah pernah mengerjakan
        $cek = mysqli_query($conn, "SELECT id FROM nilai_kuis WHERE id_kuis=$id_kuis AND nama_siswa='$nama_siswa' AND kelas='$kelas'");
        if (mysqli_num_rows($cek) > 0) {
            echo json_encode(['success' => false, 'message' => 'Kamu sudah mengerjakan kuis ini!']); exit;
        }

        $sql = "INSERT INTO nilai_kuis (id_kuis, nama_siswa, kelas, nilai, jawaban) VALUES ($id_kuis,'$nama_siswa','$kelas',$nilai,'$jawaban')";
        if (mysqli_query($conn, $sql)) {
            echo json_encode(['success' => true, 'nilai' => $nilai]);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        break;

    case 'get_nilai_kuis':
        $id_kuis = intval($_GET['id_kuis'] ?? 0);
        if (!$id_kuis) { echo json_encode(['success' => false, 'data' => []]); exit; }
        $sql = "SELECT * FROM nilai_kuis WHERE id_kuis=$id_kuis ORDER BY nilai DESC, waktu ASC";
        $result = mysqli_query($conn, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) $data[] = $row;
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    case 'get_semua_nilai':
        $sql = "SELECT nk.*, k.judul as judul_kuis FROM nilai_kuis nk JOIN kuis k ON nk.id_kuis=k.id ORDER BY nk.waktu DESC";
        $result = mysqli_query($conn, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) $data[] = $row;
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    // =============================================
    // MODUL MATERI
    // =============================================

    case 'simpan_modul':
        $judul     = mysqli_real_escape_string($conn, $_POST['judul'] ?? '');
        $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi'] ?? '');
        $link      = mysqli_real_escape_string($conn, $_POST['link'] ?? '');

        if (!$judul) { echo json_encode(['success' => false, 'message' => 'Judul wajib diisi']); exit; }

        // Kalau ada file yang diupload
        if (!empty($_FILES['file']['name'])) {
            $nama_file  = basename($_FILES['file']['name']);
            $upload_dir = __DIR__ . '/uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $unique_name = time() . '_' . $nama_file;
            $path_file   = 'uploads/' . $unique_name;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_dir . $unique_name)) {
                $nama_esc = mysqli_real_escape_string($conn, $nama_file);
                $sql = "INSERT INTO modul (judul, deskripsi, link, nama_file, path_file) VALUES ('$judul','$deskripsi','$link','$nama_esc','$path_file')";
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal upload file']); exit;
            }
        } else {
            if (!$link) { echo json_encode(['success' => false, 'message' => 'File atau link wajib diisi']); exit; }
            $sql = "INSERT INTO modul (judul, deskripsi, link) VALUES ('$judul','$deskripsi','$link')";
        }

        if (mysqli_query($conn, $sql)) {
            echo json_encode(['success' => true, 'id' => mysqli_insert_id($conn)]);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        break;

    case 'get_modul':
        $sql = "SELECT * FROM modul ORDER BY dibuat DESC";
        $result = mysqli_query($conn, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) $data[] = $row;
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    case 'hapus_modul':
        $id = intval($_POST['id'] ?? 0);
        if (!$id) { echo json_encode(['success' => false, 'message' => 'ID tidak valid']); exit; }
        $res = mysqli_query($conn, "SELECT path_file FROM modul WHERE id=$id");
        $row = mysqli_fetch_assoc($res);
        if ($row && $row['path_file'] && file_exists(__DIR__ . '/' . $row['path_file'])) unlink(__DIR__ . '/' . $row['path_file']);
        if (mysqli_query($conn, "DELETE FROM modul WHERE id=$id")) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        break;

    // =============================================
    // MEDIA (VIDEO & ZOOM/VIRTUAL ROOM)
    // =============================================

    case 'simpan_media':
        $tipe      = mysqli_real_escape_string($conn, $_POST['tipe'] ?? 'video'); // 'video' atau 'zoom'
        $judul     = mysqli_real_escape_string($conn, $_POST['judul'] ?? '');
        $link      = mysqli_real_escape_string($conn, $_POST['link'] ?? '');
        $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi'] ?? '');
        $jadwal    = $_POST['jadwal'] ?? '';
        $jadwal_sql = (!empty($jadwal)) ? "'" . mysqli_real_escape_string($conn, $jadwal) . "'" : "NULL";

        if (!$judul || !$link) { echo json_encode(['success' => false, 'message' => 'Judul dan link wajib diisi']); exit; }

        $sql = "INSERT INTO media (tipe, judul, link, deskripsi, jadwal) VALUES ('$tipe','$judul','$link','$deskripsi',$jadwal_sql)";
        if (mysqli_query($conn, $sql)) {
            echo json_encode(['success' => true, 'id' => mysqli_insert_id($conn)]);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        break;

    case 'get_media':
        $tipe = mysqli_real_escape_string($conn, $_GET['tipe'] ?? 'video');
        $sql = "SELECT * FROM media WHERE tipe='$tipe' ORDER BY dibuat DESC";
        $result = mysqli_query($conn, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) $data[] = $row;
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    case 'hapus_media':
        $id = intval($_POST['id'] ?? 0);
        if (!$id) { echo json_encode(['success' => false, 'message' => 'ID tidak valid']); exit; }
        if (mysqli_query($conn, "DELETE FROM media WHERE id=$id")) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        break;

    // =============================================
    // DISKUSI
    // =============================================

    case 'kirim_pesan':
        $nama  = mysqli_real_escape_string($conn, $_POST['nama'] ?? '');
        $role  = mysqli_real_escape_string($conn, $_POST['role'] ?? 'siswa');
        $pesan = mysqli_real_escape_string($conn, $_POST['pesan'] ?? '');

        if (!$nama || !$pesan) { echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']); exit; }

        $sql = "INSERT INTO diskusi (nama, role, pesan) VALUES ('$nama','$role','$pesan')";
        if (mysqli_query($conn, $sql)) {
            echo json_encode(['success' => true, 'id' => mysqli_insert_id($conn)]);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        break;

    case 'get_diskusi':
        $sql = "SELECT * FROM diskusi ORDER BY waktu ASC";
        $result = mysqli_query($conn, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) $data[] = $row;
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    case 'hapus_pesan':
        $id = intval($_POST['id'] ?? 0);
        if (!$id) { echo json_encode(['success' => false, 'message' => 'ID tidak valid']); exit; }
        if (mysqli_query($conn, "DELETE FROM diskusi WHERE id=$id")) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        break;

    default:
        echo json_encode(['error' => 'Action tidak dikenal: ' . $action]);
}

mysqli_close($conn);
?>
