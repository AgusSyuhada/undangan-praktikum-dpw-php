<?php
session_start();
require '../koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$id_key = isset($_GET['id']) ? $_GET['id'] : '';
$success = false;
$error_msg = "";

$res = $conn->query("SELECT * FROM contents WHERE id_key = '$id_key'");
$data = $res->fetch_assoc();

if (!$data) {
    header("Location: content.php");
    exit;
}

// Tentukan default source berdasarkan nilai di database
// Jika isi bukan URL http dan tidak kosong, berarti sebelumnya pakai file upload
$default_source = (!empty($data['isi']) && strpos($data['isi'], 'http') !== 0) ? 'file' : 'link';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $final_value = "";
    $proceed = true;
    $current_isi = $_POST['current_isi']; // Simpan referensi nilai lama

    // PROSES GAMBAR & AUDIO
    if ($data['tipe'] === 'gambar' || $data['tipe'] === 'audio') {
        $source_type = isset($_POST['source_type']) ? $_POST['source_type'] : 'link';

        if ($source_type === 'file') {
            // Cek apakah ada file yang benar-benar diunggah (error 0)
            if (isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] === 0) {

                $ext = strtolower(pathinfo($_FILES['file_upload']['name'], PATHINFO_EXTENSION));

                if ($data['tipe'] === 'gambar') {
                    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    $target_dir = "../assets/img/";
                    $db_path = "assets/img/";
                } else { // audio
                    $allowed_ext = ['mp3', 'ogg', 'wav', 'm4a'];
                    $target_dir = "../assets/audio/";
                    $db_path = "assets/audio/";
                }

                // Validasi berdasarkan ekstensi saja (Lebih aman untuk semua server/localhost)
                if (!in_array($ext, $allowed_ext)) {
                    $error_msg = "Format file tidak didukung! Pastikan format sesuai.";
                    $proceed = false;
                }

                if ($proceed) {
                    if (!is_dir($target_dir)) {
                        mkdir($target_dir, 0755, true);
                    }

                    // Trik: Tambahkan timestamp agar nama file UNIK & browser langsung mereload gambar baru
                    $filename = $id_key . "_" . time() . "." . $ext;
                    $target_file = $target_dir . $filename;

                    if (move_uploaded_file($_FILES['file_upload']['tmp_name'], $target_file)) {
                        $final_value = $db_path . $filename;

                        // PROSES HAPUS FILE LAMA
                        // Pastikan isi lama bukan link http dan bukan file kosong
                        if (strpos($current_isi, 'http') !== 0 && !empty($current_isi)) {
                            $old_path = "../" . $current_isi;
                            if (file_exists($old_path) && is_file($old_path)) {
                                @unlink($old_path); // Hapus permanen dari server
                            }
                        }
                    } else {
                        $error_msg = "Sistem gagal memindahkan file ke direktori.";
                        $proceed = false;
                    }
                }
            }
            // Jika memilih "Upload File" tapi lupa memilih file (error 4)
            elseif (isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] === 4) {
                $final_value = $current_isi; // Tetap gunakan yang lama
            }
            // Jika ada error upload lainnya (misal ukuran kebesaran)
            else {
                $error_msg = "Gagal upload. Pastikan ukuran file tidak melebihi batas server.";
                $proceed = false;
            }
        } else {
            // PROSES JIKA MENGGUNAKAN LINK
            $link_input = trim($_POST['content_link']);
            if (!empty($link_input)) {
                $final_value = $conn->real_escape_string($link_input);

                // Jika dulunya file upload lalu sekarang diganti jadi link, Hapus file lama di server!
                if (strpos($current_isi, 'http') !== 0 && $current_isi !== $final_value && !empty($current_isi)) {
                    $old_path = "../" . $current_isi;
                    if (file_exists($old_path) && is_file($old_path)) {
                        @unlink($old_path);
                    }
                }
            } else {
                $final_value = $current_isi;
            }
        }
    }
    // PROSES TEXT & TEXTAREA
    else {
        if (isset($_POST['content_text'])) {
            $final_value = $conn->real_escape_string($_POST['content_text']);
        } else {
            $final_value = $current_isi;
        }
    }

    // UPDATE DATABASE
    if ($proceed) {
        $update = "UPDATE contents SET isi = '$final_value' WHERE id_key = '$id_key'";
        if ($conn->query($update)) {
            $success = true;
        } else {
            $error_msg = "Gagal menyimpan data ke database.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Konten - Dashboard Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script> tailwind.config = { theme: { extend: { colors: { wedding: { primary: '#c19a6b' } } } } } </script>
</head>

<body class="flex h-screen overflow-hidden bg-gray-100">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <div class="p-4 md:p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Edit Konten: <?= htmlspecialchars($data['bagian']) ?></h2>

            <div class="bg-white rounded-xl shadow-sm p-6 md:p-8 max-w-2xl mx-auto md:mx-0">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="current_isi" value="<?= htmlspecialchars($data['isi']) ?>">

                    <div class="mb-8">
                        <?php if ($data['tipe'] === 'gambar' || $data['tipe'] === 'audio'): ?>
                            <div class="flex gap-6 mb-6">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="source_type" value="link"
                                        <?= $default_source === 'link' ? 'checked' : '' ?>
                                        onclick="toggleInput('link')"
                                        class="w-4 h-4 text-wedding-primary focus:ring-wedding-primary">
                                    <span class="text-sm font-medium">Gunakan Link (URL)</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="source_type" value="file"
                                        <?= $default_source === 'file' ? 'checked' : '' ?>
                                        onclick="toggleInput('file')"
                                        class="w-4 h-4 text-wedding-primary focus:ring-wedding-primary">
                                    <span class="text-sm font-medium">Upload File</span>
                                </label>
                            </div>

                            <div id="input-link" class="<?= $default_source === 'link' ? 'block' : 'hidden' ?>">
                                <input type="text" name="content_link" id="content_link"
                                    value="<?= htmlspecialchars($data['isi']) ?>"
                                    <?= $default_source === 'file' ? 'disabled' : '' ?>
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-wedding-primary outline-none"
                                    placeholder="<?= $data['tipe'] === 'audio' ? 'https://link.com/musik.mp3' : 'https://link.com/foto.jpg' ?>">
                            </div>

                            <div id="input-file" class="<?= $default_source === 'file' ? 'block' : 'hidden' ?>">
                                <input type="file" name="file_upload" id="fileInput"
                                    accept="<?= $data['tipe'] === 'audio' ? '.mp3,.wav,.ogg,.m4a' : '.jpg,.jpeg,.png,.gif,.webp' ?>"
                                    class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-wedding-primary file:text-white hover:file:bg-yellow-700">
                            </div>

                            <div class="mt-6 p-4 bg-gray-50 border rounded-lg text-center">
                                <p class="text-xs text-gray-500 mb-2">Preview Saat Ini:</p>
                                <?php $src = strpos($data['isi'], 'http') === 0 ? $data['isi'] : '../' . $data['isi']; ?>
                                <?php if ($data['tipe'] === 'gambar'): ?>
                                    <img src="<?= htmlspecialchars($src) ?>" class="max-h-40 mx-auto rounded shadow-sm border">
                                <?php else: ?>
                                    <audio controls class="mx-auto w-full max-w-xs outline-none">
                                        <source src="<?= htmlspecialchars($src) ?>">
                                    </audio>
                                <?php endif; ?>
                            </div>

                        <?php elseif ($data['tipe'] === 'textarea'): ?>
                            <textarea name="content_text" rows="5" required
                                class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-wedding-primary outline-none"><?= htmlspecialchars($data['isi']) ?></textarea>
                        <?php else: ?>
                            <input type="text" name="content_text" value="<?= htmlspecialchars($data['isi']) ?>" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-wedding-primary outline-none">
                        <?php endif; ?>
                    </div>

                    <div class="flex flex-col md:flex-row items-center gap-4 pt-4 border-t">
                        <a href="content.php"
                            class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg w-full md:w-auto text-center font-medium hover:bg-gray-200 transition">Batal</a>
                        <button type="submit"
                            class="px-6 py-2 bg-wedding-primary text-white rounded-lg w-full md:w-auto font-medium shadow-md hover:bg-yellow-700 transition">
                            <i class="fas fa-save mr-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <div id="errorModal"
        class="fixed inset-0 z-[60] <?= !empty($error_msg) ? 'flex' : 'hidden' ?> bg-gray-900 bg-opacity-50 items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <i class="fas fa-times text-red-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Gagal!</h3>
                <p class="text-sm text-gray-500 mb-6" id="errorModalMsg">
                    <?= !empty($error_msg) ? $error_msg : 'Pastikan format file sesuai.' ?></p>
                <button onclick="closeErrorModal()"
                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg w-full font-medium hover:bg-gray-300 transition">Mengerti</button>
            </div>
        </div>
    </div>

    <div id="actionSuccessModal"
        class="fixed inset-0 z-50 <?= $success ? 'flex' : 'hidden' ?> bg-gray-900 bg-opacity-50 items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm transform scale-100 transition-transform">
            <div class="text-center">
                <div
                    class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4 animate-bounce">
                    <i class="fas fa-check text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Berhasil!</h3>
                <p class="text-sm text-gray-500 mb-4">Data konten berhasil diperbarui!</p>
                <div class="w-full bg-gray-200 rounded-full h-1.5 mb-4 overflow-hidden">
                    <div class="bg-wedding-primary h-1.5 rounded-full w-full animate-[progress_1.5s_ease-in-out]"></div>
                </div>
                <p class="text-xs text-gray-400">Kembali ke tabel...</p>
            </div>
        </div>
    </div>

    <script>
        function toggleInput(type) {
            const linkDiv = document.getElementById('input-link');
            const fileDiv = document.getElementById('input-file');
            const linkInput = document.getElementById('content_link');

            linkDiv.classList.toggle('hidden', type === 'file');
            fileDiv.classList.toggle('hidden', type === 'link');

            // Disable field yang tersembunyi agar tidak ikut validasi browser saat submit
            linkInput.disabled = (type === 'file');
        }

        function closeErrorModal() {
            document.getElementById('errorModal').classList.add('hidden');
            document.getElementById('errorModal').classList.remove('flex');
        }

        const fileInput = document.getElementById('fileInput');
        if (fileInput) {
            fileInput.addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (!file) return;

                const isAudio = <?= $data['tipe'] === 'audio' ? 'true' : 'false' ?>;
                const ext = file.name.split('.').pop().toLowerCase();

                let isValid = false;

                if (isAudio) {
                    const validExt = ['mp3', 'wav', 'ogg', 'm4a'];
                    if (validExt.includes(ext)) isValid = true;
                } else {
                    const validExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    if (validExt.includes(ext)) isValid = true;
                }

                if (!isValid) {
                    document.getElementById('errorModalMsg').innerText = isAudio ? 'Hanya file Audio (MP3, WAV, OGG) yang diperbolehkan.' : 'Hanya file Gambar (JPG, PNG, WEBP) yang diperbolehkan.';
                    document.getElementById('errorModal').classList.remove('hidden');
                    document.getElementById('errorModal').classList.add('flex');
                    e.target.value = ''; // Kosongkan form file
                }
            });
        }

        <?php if ($success): ?>
            // Otomatis redirect ke halaman Content setelah 1.5 detik
            setTimeout(() => { window.location.href = 'content.php'; }, 1500);
        <?php endif; ?>
    </script>
</body>

</html>