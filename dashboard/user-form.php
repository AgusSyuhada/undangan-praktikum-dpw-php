<?php
session_start();
require '../koneksi.php';

// Proteksi halaman
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Cek apakah mode Edit (terdapat ID di URL)
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$is_edit = $id > 0;
$success = false;
$pesan_sukses = "";

// Inisialisasi variabel input
$nama = '';
$username = '';
$email = '';

// Jika mode edit, ambil data lama dari database
if ($is_edit) {
    $result = $conn->query("SELECT * FROM users WHERE id = $id");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nama = $row['nama'];
        $username = $row['username'];
        $email = $row['email'];
    } else {
        // Jika ID tidak ditemukan, kembali ke halaman users
        header("Location: users.php");
        exit;
    }
}

// Logika pemrosesan form saat di-submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $conn->real_escape_string($_POST['nama']);
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password_input = $_POST['password']; // Password plain

    if ($is_edit) {
        // Mode Edit: Update password HANYA jika form password diisi
        if (!empty($password_input)) {
            $password_md5 = md5($password_input);
            $query = "UPDATE users SET nama='$nama', username='$username', email='$email', password='$password_md5' WHERE id=$id";
        } else {
            $query = "UPDATE users SET nama='$nama', username='$username', email='$email' WHERE id=$id";
        }
        $pesan_sukses = "Data pengguna berhasil diperbarui!";
    } else {
        // Mode Tambah: Insert data baru
        $password_md5 = md5($password_input);
        $query = "INSERT INTO users (nama, username, email, password) VALUES ('$nama', '$username', '$email', '$password_md5')";
        $pesan_sukses = "Data pengguna baru berhasil ditambahkan!";
    }

    if ($conn->query($query)) {
        $success = true; // Akan memicu Modal Success
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $is_edit ? 'Edit' : 'Tambah' ?> Pengguna - Dashboard Admin</title>
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

<body class="flex h-screen overflow-hidden">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <header class="md:hidden flex items-center justify-between p-4 bg-white shadow-sm border-b sticky top-0 z-30">
            <h1 class="text-lg font-bold text-wedding-primary">Form Pengguna</h1>
            <button onclick="toggleSidebar()" class="text-gray-600 p-2"><i class="fas fa-bars text-2xl"></i></button>
        </header>

        <div class="p-4 md:p-8">
            <div class="mb-6 flex items-center gap-3">
                <a href="users.php" class="text-gray-500 hover:text-wedding-primary transition"><i
                        class="fas fa-arrow-left text-xl"></i></a>
                <h2 class="text-2xl font-bold text-gray-800">
                    <?= $is_edit ? 'Edit Data Pengguna' : 'Tambah Pengguna Baru' ?></h2>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 md:p-8 max-w-2xl mx-auto md:mx-0">
                <form method="POST" action="">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                            <input type="text" name="nama" required value="<?= htmlspecialchars($nama) ?>"
                                class="w-full px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-wedding-primary transition"
                                placeholder="Masukkan nama">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">@</span>
                                <input type="text" name="username" required value="<?= htmlspecialchars($username) ?>"
                                    class="w-full pl-8 pr-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-wedding-primary transition"
                                    placeholder="username">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mb-8">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" name="email" required value="<?= htmlspecialchars($email) ?>"
                                class="w-full px-4 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-wedding-primary transition"
                                placeholder="email@contoh.com">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                            <div class="relative">
                                <input type="password" id="password" name="password" <?= $is_edit ? '' : 'required' ?>
                                    class="w-full pl-4 pr-10 py-2 border rounded-lg outline-none focus:ring-2 focus:ring-wedding-primary transition"
                                    placeholder="<?= $is_edit ? 'Kosongkan jika tidak diubah' : '••••••••' ?>">
                                <button type="button" onclick="togglePassword('password', 'eyeIcon')"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-wedding-primary">
                                    <i id="eyeIcon" class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row items-center gap-4 pt-4 border-t">
                        <a href="users.php"
                            class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg w-full md:w-auto text-center font-medium hover:bg-gray-200">Batal</a>
                        <button type="submit"
                            class="px-6 py-2 bg-wedding-primary text-white rounded-lg w-full md:w-auto font-medium shadow-md hover:bg-yellow-700 transition">
                            <i class="fas fa-save mr-1"></i> Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <div id="actionSuccessModal"
        class="fixed inset-0 z-50 <?= $success ? 'flex' : 'hidden' ?> bg-gray-900 bg-opacity-50 items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm transform scale-100 transition-transform">
            <div class="text-center">
                <div
                    class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4 animate-bounce">
                    <i class="fas fa-check text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Berhasil!</h3>
                <p class="text-sm text-gray-500 mb-4"><?= $pesan_sukses ?></p>
                <div class="w-full bg-gray-200 rounded-full h-1.5 mb-4 overflow-hidden">
                    <div class="bg-wedding-primary h-1.5 rounded-full w-full animate-[progress_1.5s_ease-in-out]"></div>
                </div>
                <p class="text-xs text-gray-400">Kembali ke tabel...</p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === "password") {
                input.type = "text"; icon.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                input.type = "password"; icon.classList.replace("fa-eye-slash", "fa-eye");
            }
        }

        <?php if ($success): ?>
            // Otomatis redirect ke halaman Users (Tabel Admin) setelah 1.5 detik
            setTimeout(() => {
                window.location.href = 'users.php';
            }, 1500);
        <?php endif; ?>
    </script>
</body>

</html>