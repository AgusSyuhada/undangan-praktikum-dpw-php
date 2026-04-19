<?php
session_start();
require 'koneksi.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard/rsvp.php"); // Pastikan redirect ke .php sekarang
    exit;
}

$error_msg = '';
$login_success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $loginId = $conn->real_escape_string($_POST['loginId']);
    $password = md5($_POST['password']);

    $sql = "SELECT * FROM users WHERE (username = '$loginId' OR email = '$loginId') AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['nama'] = $row['nama'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];

        $login_success = true;
    } else {
        $error_msg = 'Username/Email atau password salah!';
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Admin Undangan</title>
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

<body class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md border border-gray-100 relative">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Panel Admin</h1>
            <p class="text-sm text-gray-500">Silakan login untuk mengelola undangan</p>
        </div>

        <?php if ($error_msg): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-6 text-sm rounded">
                <p><i class="fas fa-exclamation-circle mr-2"></i><?= $error_msg ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Username / Email</label>
                <input type="text" name="loginId"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-wedding-primary outline-none"
                    placeholder="admin / admin@undangan.com" required>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <div class="relative">
                    <input type="password" name="password" id="password"
                        class="w-full px-4 py-2 pr-10 border rounded-lg focus:ring-2 focus:ring-wedding-primary outline-none"
                        required>
                    <button type="button" onclick="togglePassword('password', 'eyeIcon')"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-wedding-primary">
                        <i id="eyeIcon" class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <button type="submit"
                class="w-full bg-wedding-primary hover:bg-yellow-700 text-white font-medium py-2 rounded-lg transition shadow-md">Masuk
                Dashboard</button>
        </form>
    </div>

    <div id="loginSuccessModal"
        class="fixed inset-0 z-50 <?= $login_success ? 'flex' : 'hidden' ?> bg-gray-900 bg-opacity-50 items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm transform scale-100 transition-transform">
            <div class="text-center">
                <div
                    class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4 animate-bounce">
                    <i class="fas fa-check text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Login Berhasil!</h3>
                <p class="text-sm text-gray-500 mb-4">Selamat datang, <?= htmlspecialchars($_SESSION['nama'] ?? '') ?>.
                </p>
                <div class="w-full bg-gray-200 rounded-full h-1.5 mb-4 overflow-hidden">
                    <div class="bg-wedding-primary h-1.5 rounded-full w-full animate-[progress_1.5s_ease-in-out]"></div>
                </div>
                <p class="text-xs text-gray-400">Mengarahkan ke dashboard...</p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === "password") { input.type = "text"; icon.classList.replace("fa-eye", "fa-eye-slash"); }
            else { input.type = "password"; icon.classList.replace("fa-eye-slash", "fa-eye"); }
        }

        <?php if ($login_success): ?>
            // Otomatis redirect ke halaman RSVP (Dashboard) setelah 1.5 detik
            setTimeout(() => {
                window.location.href = 'dashboard/rsvp.php';
            }, 1500);
        <?php endif; ?>
    </script>
</body>

</html>