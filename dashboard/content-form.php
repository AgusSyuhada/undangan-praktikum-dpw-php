<?php
session_start();
require '../koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$id_key = isset($_GET['id']) ? $_GET['id'] : '';
$success = false;

$res = $conn->query("SELECT * FROM contents WHERE id_key = '$id_key'");
$data = $res->fetch_assoc();

if (!$data) {
    header("Location: content.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $final_value = "";

    if ($data['tipe'] === 'gambar' || $data['tipe'] === 'audio') {
        $source_type = $_POST['source_type'];

        if ($source_type === 'file' && isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] == 0) {

            // Konfigurasi dinamis berdasarkan tipe konten
            if ($data['tipe'] === 'gambar') {
                $allowed_mime = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
                $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $target_dir = "../assets/img/";
                $db_path = "assets/img/";
            } else { // audio
                $allowed_mime = [
                    'audio/mpeg', 'audio/ogg', 'audio/wav', 'audio/mp3', 
                    'audio/x-m4a', 'audio/mp4', 'audio/x-wav', 'video/mp4', 'application/octet-stream'
                ];
                $allowed_ext = ['mp3', 'ogg', 'wav', 'm4a'];
                $target_dir = "../assets/audio/";
                $db_path = "assets/audio/";
            }

            $file_mime_type = mime_content_type($_FILES['file_upload']['tmp_name']);
            $ext = strtolower(pathinfo($_FILES['file_upload']['name'], PATHINFO_EXTENSION));

            if (!in_array($file_mime_type, $allowed_mime) && !in_array($ext, $allowed_ext)) {
                echo "<script>alert('Gagal: Tipe file tidak diizinkan oleh sistem server! (MIME yang terdeteksi: " . $file_mime_type . ")'); window.history.back();</script>";
                exit;
            }

            if (!is_dir($target_dir))
                mkdir($target_dir, 0755, true);

            $ext = strtolower(pathinfo($_FILES['file_upload']['name'], PATHINFO_EXTENSION));
            $filename = $id_key . "." . $ext;
            $target_file = $target_dir . $filename;

            if (move_uploaded_file($_FILES['file_upload']['tmp_name'], $target_file)) {
                $final_value = $db_path . $filename;
            } else {
                $final_value = $_POST['current_isi'];
            }
        } else {
            // Jika memilih link
            $final_value = $conn->real_escape_string($_POST['content_link']);
        }
    } else {
        $final_value = $conn->real_escape_string($_POST['content_text']);
    }

    $update = "UPDATE contents SET isi = '$final_value' WHERE id_key = '$id_key'";
    if ($conn->query($update)) {
        $success = true;
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
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Edit Konten: <?= $data['bagian'] ?></h2>

            <div class="bg-white rounded-xl shadow-sm p-6 md:p-8 max-w-2xl mx-auto md:mx-0">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="current_isi" value="<?= $data['isi'] ?>">

                    <div class="mb-8">
                        <?php if ($data['tipe'] === 'gambar' || $data['tipe'] === 'audio'): ?>
                            <div class="flex gap-6 mb-6">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="source_type" value="link" checked
                                        onclick="toggleInput('link')" class="w-4 h-4 text-wedding-primary">
                                    <span class="text-sm font-medium">Gunakan Link (URL)</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="source_type" value="file" onclick="toggleInput('file')"
                                        class="w-4 h-4 text-wedding-primary">
                                    <span class="text-sm font-medium">Upload File</span>
                                </label>
                            </div>

                            <div id="input-link" class="block">
                                <input type="url" name="content_link" value="<?= $data['isi'] ?>"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-wedding-primary outline-none"
                                    placeholder="<?= $data['tipe'] === 'audio' ? 'https://link.com/musik.mp3' : 'https://link.com/foto.jpg' ?>">
                            </div>

                            <div id="input-file" class="hidden">
                                <input type="file" name="file_upload" id="fileInput"
                                    accept="<?= $data['tipe'] === 'audio' ? 'audio/*' : 'image/*' ?>"
                                    class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-wedding-primary file:text-white hover:file:bg-yellow-700">
                            </div>

                            <div class="mt-6 p-4 bg-gray-50 border rounded-lg text-center">
                                <p class="text-xs text-gray-500 mb-2">Preview Saat Ini:</p>
                                <?php $src = strpos($data['isi'], 'http') === 0 ? $data['isi'] : '../' . $data['isi']; ?>
                                <?php if ($data['tipe'] === 'gambar'): ?>
                                    <img src="<?= $src ?>" class="max-h-40 mx-auto rounded shadow-sm">
                                <?php else: ?>
                                    <audio controls class="mx-auto w-full max-w-xs">
                                        <source src="<?= $src ?>">
                                    </audio>
                                <?php endif; ?>
                            </div>

                        <?php elseif ($data['tipe'] === 'textarea'): ?>
                            <textarea name="content_text" rows="5" required
                                class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-wedding-primary outline-none"><?= $data['isi'] ?></textarea>
                        <?php else: ?>
                            <input type="text" name="content_text" value="<?= $data['isi'] ?>" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-wedding-primary outline-none">
                        <?php endif; ?>
                    </div>

                    <div class="flex gap-4 border-t pt-4">
                        <button type="submit"
                            class="px-6 py-2 bg-wedding-primary text-white rounded-lg shadow-md hover:bg-yellow-700 w-full md:w-auto">Simpan
                            Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <div id="errorModal"
        class="fixed inset-0 z-[60] hidden bg-gray-900 bg-opacity-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <i class="fas fa-times text-red-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Format File Salah!</h3>
                <p class="text-sm text-gray-500 mb-6" id="errorModalMsg">Pastikan format file sesuai.</p>
                <button onclick="closeErrorModal()"
                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg w-full font-medium">Mengerti</button>
            </div>
        </div>
    </div>

    <div id="successModal"
        class="fixed inset-0 z-50 <?= $success ? 'flex' : 'hidden' ?> bg-gray-900 bg-opacity-50 items-center justify-center p-4">
        <div class="bg-white rounded-xl p-6 w-full max-w-sm text-center">
            <h3 class="text-xl font-bold text-green-600 mb-2">Berhasil Diperbarui!</h3>
        </div>
    </div>

    <script>
        function toggleInput(type) {
            document.getElementById('input-link').classList.toggle('hidden', type === 'file');
            document.getElementById('input-file').classList.toggle('hidden', type === 'link');
        }

        function closeErrorModal() {
            document.getElementById('errorModal').classList.add('hidden');
        }

        const fileInput = document.getElementById('fileInput');
        if (fileInput) {
            fileInput.addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (!file) return;

                const isAudio = <?= $data['tipe'] === 'audio' ? 'true' : 'false' ?>;
                const imgTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];

                let isValid = false;
                
                if (isAudio) {
                    const validExt = ['.mp3', '.wav', '.ogg', '.m4a'];
                    if (file.type.startsWith('audio/') || validExt.some(ext => file.name.toLowerCase().endsWith(ext))) {
                        isValid = true;
                    }
                } else {
                    if (imgTypes.includes(file.type)) {
                        isValid = true;
                    }
                }

                if (!isValid) {
                    document.getElementById('errorModalMsg').innerText = isAudio ? 'Hanya file Audio (MP3, WAV, OGG) yang diperbolehkan.' : 'Hanya file Gambar (JPG, PNG) yang diperbolehkan.';
                    document.getElementById('errorModal').classList.remove('hidden');
                    e.target.value = ''; // RESET FILE AGAR KOSONG
                }
            });
        }

        <?php if ($success): ?>
            setTimeout(() => { window.location.href = 'content.php'; }, 1000);
        <?php endif; ?>
    </script>
</body>

</html>