<?php
session_start();
require '../koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$is_edit = $id > 0;
$nama_tamu = '';
$success = false;

if ($is_edit) {
    $result = $conn->query("SELECT * FROM guests WHERE id = $id");
    if ($row = $result->fetch_assoc()) {
        $nama_tamu = $row['nama'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $conn->real_escape_string($_POST['nama']);

    if ($is_edit) {
        $query = "UPDATE guests SET nama = '$nama' WHERE id = $id";
    } else {
        $query = "INSERT INTO guests (nama) VALUES ('$nama')";
    }

    if ($conn->query($query)) {
        $success = true;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Tamu - Dashboard Admin</title>
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
            <h1 class="text-lg font-bold text-wedding-primary">Form Tamu</h1>
            <button onclick="toggleSidebar()" class="text-gray-600 p-2"><i class="fas fa-bars text-2xl"></i></button>
        </header>

        <div class="p-4 md:p-8">
            <div class="mb-6 flex items-center gap-3">
                <a href="guests.php" class="text-gray-500 hover:text-wedding-primary transition"><i
                        class="fas fa-arrow-left text-xl"></i></a>
                <h2 class="text-2xl font-bold text-gray-800"><?= $is_edit ? "Edit Data Tamu" : "Tambah Tamu Baru" ?>
                </h2>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden p-6 md:p-8 max-w-xl mx-auto md:mx-0">
                <form method="POST" action="">
                    <div class="mb-8">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Tamu Undangan</label>
                        <input type="text" name="nama" required value="<?= htmlspecialchars($nama_tamu) ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-wedding-primary outline-none transition"
                            placeholder="Contoh: Bapak Budi Santoso & Keluarga">
                        <p class="text-xs text-gray-500 mt-2">*Nama ini akan ditampilkan pada halaman Cover Undangan.
                        </p>
                    </div>

                    <div class="flex flex-col md:flex-row items-center gap-4 pt-4 border-t border-gray-100">
                        <a href="guests.php"
                            class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium transition text-center w-full md:w-auto">Batal</a>
                        <button type="submit"
                            class="px-6 py-2 bg-wedding-primary text-white rounded-lg font-medium transition w-full md:w-auto shadow-md">
                            <i class="fas fa-save mr-1"></i> Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <div id="actionSuccessModal"
        class="fixed inset-0 z-50 <?= $success ? 'flex' : 'hidden' ?> bg-gray-900 bg-opacity-50 items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm transform scale-100">
            <div class="text-center">
                <div
                    class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4 animate-bounce">
                    <i class="fas fa-check text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Berhasil!</h3>
                <p class="text-sm text-gray-500 mb-4">Data tamu berhasil disimpan.</p>
                <p class="text-xs text-gray-400">Kembali ke tabel...</p>
            </div>
        </div>
    </div>

    <script>
        <?php if ($success): ?>
            setTimeout(() => { window.location.href = 'guests.php'; }, 1500);
        <?php endif; ?>
    </script>
</body>

</html>