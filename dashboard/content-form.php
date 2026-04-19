<?php
session_start();
require '../koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$id_key = isset($_GET['id']) ? $_GET['id'] : '';
$success = false;

// Ambil data konten berdasarkan id_key
$res = $conn->query("SELECT * FROM contents WHERE id_key = '$id_key'");
$data = $res->fetch_assoc();

if (!$data) {
    header("Location: content.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $final_value = "";

    if ($data['tipe'] === 'gambar') {
        $source_type = $_POST['source_type']; // 'link' atau 'file'

        if ($source_type === 'file' && isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
            $target_dir = "../assets/img/";
            if (!is_dir($target_dir))
                mkdir($target_dir, 0755, true);

            // Nama file tetap berdasarkan id_key agar menimpa file lama
            $ext = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
            $filename = $id_key . "." . $ext;
            $target_file = $target_dir . $filename;

            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $target_file)) {
                $final_value = "assets/img/" . $filename; // Simpan path relatif ke DB
            } else {
                $final_value = $_POST['current_isi']; // Fallback jika gagal
            }
        } else {
            // Jika pilih link
            $final_value = $conn->real_escape_string($_POST['content_link']);
        }
    } else {
        // Untuk tipe teks atau textarea
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
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f3f4f6;
        }
    </style>
</head>

<body class="flex h-screen overflow-hidden bg-gray-100">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <header class="md:hidden flex items-center justify-between p-4 bg-white shadow-sm border-b sticky top-0 z-30">
            <h1 class="text-lg font-bold text-wedding-primary">Edit Konten</h1>
            <button onclick="toggleSidebar()" class="text-gray-600 p-2"><i class="fas fa-bars text-2xl"></i></button>
        </header>

        <div class="p-4 md:p-8">
            <div class="mb-6 flex items-center gap-3">
                <a href="content.php" class="text-gray-500 hover:text-wedding-primary transition"><i
                        class="fas fa-arrow-left text-xl"></i></a>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Edit Konten</h2>
                    <p class="text-sm text-gray-500">Bagian: <?= $data['bagian'] ?></p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 md:p-8 max-w-2xl mx-auto md:mx-0">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="current_isi" value="<?= $data['isi'] ?>">

                    <div class="mb-8">
                        <label class="block text-sm font-semibold text-gray-800 mb-4">Isi Konten Untuk
                            <?= $data['bagian'] ?></label>

                        <?php if ($data['tipe'] === 'gambar'): ?>
                            <div class="flex gap-6 mb-6">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="source_type" value="link" checked
                                        onclick="toggleInput('link')"
                                        class="w-4 h-4 text-wedding-primary focus:ring-wedding-primary">
                                    <span class="text-sm font-medium text-gray-700">Gunakan Link (URL)</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="source_type" value="file" onclick="toggleInput('file')"
                                        class="w-4 h-4 text-wedding-primary focus:ring-wedding-primary">
                                    <span class="text-sm font-medium text-gray-700">Upload File</span>
                                </label>
                            </div>

                            <div id="input-link" class="block">
                                <input type="url" name="content_link" value="<?= $data['isi'] ?>"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-wedding-primary outline-none"
                                    placeholder="https://example.com/foto.jpg">
                            </div>

                            <div id="input-file" class="hidden">
                                <input type="file" name="image_file" accept="image/*"
                                    class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-wedding-primary file:text-white hover:file:bg-yellow-700">
                                <p class="text-[10px] text-red-500 mt-2">*File baru akan otomatis menggantikan file lama.
                                </p>
                            </div>

                            <div class="mt-6 p-4 bg-gray-50 border rounded-lg text-center">
                                <p class="text-xs text-gray-500 mb-2 font-medium">Preview Saat Ini:</p>
                                <img src="<?= strpos($data['isi'], 'http') === 0 ? $data['isi'] : '../' . $data['isi'] ?>"
                                    class="max-h-40 mx-auto rounded shadow-sm">
                            </div>

                        <?php elseif ($data['tipe'] === 'textarea'): ?>
                            <textarea name="content_text" rows="5" required
                                class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-wedding-primary outline-none"><?= $data['isi'] ?></textarea>

                        <?php else: ?>
                            <input type="text" name="content_text" value="<?= $data['isi'] ?>" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-wedding-primary outline-none">
                        <?php endif; ?>
                    </div>

                    <div class="flex flex-col md:flex-row items-center gap-4 pt-4 border-t">
                        <a href="content.php"
                            class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 w-full md:w-auto text-center">Batal</a>
                        <button type="submit"
                            class="px-6 py-2 bg-wedding-primary text-white rounded-lg font-medium shadow-md hover:bg-yellow-700 w-full md:w-auto">
                            <i class="fas fa-save mr-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <div id="successModal"
        class="fixed inset-0 z-50 <?= $success ? 'flex' : 'hidden' ?> bg-gray-900 bg-opacity-50 items-center justify-center p-4">
        <div
            class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm transform scale-100 animate-[bounce_0.5s_ease-in-out]">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                    <i class="fas fa-check text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Berhasil Diperbarui!</h3>
                <p class="text-sm text-gray-500 mb-4">Konten "<?= $data['bagian'] ?>" telah berhasil disimpan.</p>
                <div class="w-full bg-gray-200 rounded-full h-1.5 mb-4 overflow-hidden">
                    <div class="bg-wedding-primary h-1.5 rounded-full w-full animate-[progress_1s_linear]"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleInput(type) {
            document.getElementById('input-link').classList.toggle('hidden', type === 'file');
            document.getElementById('input-file').classList.toggle('hidden', type === 'link');
        }

        <?php if ($success): ?>
            setTimeout(() => { window.location.href = 'content.php'; }, 1200);
        <?php endif; ?>
    </script>
</body>

</html>