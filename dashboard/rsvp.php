<?php
session_start();
require '../koneksi.php';

// Proteksi halaman admin
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Ambil data RSVP dari database (urutkan dari yang terbaru)
$query = "SELECT * FROM rsvps ORDER BY id DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data RSVP - Dashboard Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script> tailwind.config = { theme: { extend: { colors: { wedding: { primary: '#c19a6b' } } } } } </script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f3f4f6;
        }

        /* Custom scrollbar untuk tabel mobile */
        .custom-scrollbar::-webkit-scrollbar {
            height: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #c19a6b;
            border-radius: 10px;
        }
    </style>
</head>

<body class="flex h-screen overflow-hidden">

    <div id="sidebarOverlay" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40 hidden md:hidden transition-opacity"
        onclick="toggleSidebar()"></div>

    <aside id="sidebar"
        class="fixed inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 transition duration-300 ease-in-out z-50 w-64 bg-white shadow-md h-full flex flex-col">
        <div class="p-6 border-b text-center flex justify-between items-center md:block">
            <h2 class="text-xl font-bold text-wedding-primary">Admin Panel</h2>
            <button class="md:hidden text-gray-500" onclick="toggleSidebar()"><i
                    class="fas fa-times text-xl"></i></button>
        </div>

        <div class="px-6 py-4 border-b bg-gray-50">
            <p class="text-xs text-gray-500 mb-1">Login sebagai:</p>
            <p class="text-sm font-bold text-gray-800"><i class="fas fa-user-circle mr-1 text-wedding-primary"></i>
                <?= htmlspecialchars($_SESSION['nama']) ?></p>
        </div>

        <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
            <a href="rsvp.php"
                class="block w-full text-left px-4 py-3 rounded-lg bg-wedding-primary text-white font-medium transition"><i
                    class="fas fa-envelope-open-text w-6"></i> Data RSVP</a>
            <a href="users.html"
                class="block w-full text-left px-4 py-3 rounded-lg text-gray-600 hover:bg-gray-100 font-medium transition"><i
                    class="fas fa-users-cog w-6"></i> Data Admin</a>
            <a href="guests.html"
                class="block w-full text-left px-4 py-3 rounded-lg text-gray-600 hover:bg-gray-100 font-medium transition"><i
                    class="fas fa-address-book w-6"></i> Nama Tamu (Link)</a>
            <a href="content.html"
                class="block w-full text-left px-4 py-3 rounded-lg text-gray-600 hover:bg-gray-100 font-medium transition"><i
                    class="fas fa-file-alt w-6"></i> Data Isi Halaman</a>
        </nav>
        <div class="p-4 border-t">
            <button onclick="openLogoutModal()"
                class="block w-full text-center px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg transition"><i
                    class="fas fa-sign-out-alt mr-2"></i> Logout</button>
        </div>
    </aside>

    <main class="flex-1 flex flex-col min-w-0">
        <header class="md:hidden flex items-center justify-between p-4 bg-white shadow-sm border-b sticky top-0 z-30">
            <h1 class="text-lg font-bold text-wedding-primary">Data RSVP</h1>
            <button onclick="toggleSidebar()" class="text-gray-600 p-2"><i class="fas fa-bars text-2xl"></i></button>
        </header>

        <div class="p-4 md:p-8 overflow-y-auto flex-1">
            <h2 class="hidden md:block text-2xl font-bold text-gray-800 mb-6">Data Kehadiran (RSVP)</h2>
            <div class="bg-white rounded-xl shadow-sm overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse min-w-[600px]">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="p-4 text-sm font-semibold text-gray-600">Nama Lengkap</th>
                            <th class="p-4 text-sm font-semibold text-gray-600">Status Kehadiran</th>
                            <th class="p-4 text-sm font-semibold text-gray-600">Ucapan & Doa</th>
                        </tr>
                    </thead>
                    <tbody id="table-body" class="divide-y">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="p-4 font-medium"><?= htmlspecialchars($row['nama']) ?></td>
                                    <td class="p-4">
                                        <span
                                            class="px-3 py-1 rounded-full text-xs font-semibold <?= $row['hadir'] == 'Hadir' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                                            <?= htmlspecialchars($row['hadir']) ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-gray-600 text-sm italic">
                                        "<?= nl2br(htmlspecialchars($row['ucapan'])) ?>"</td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="p-8 text-center text-gray-500 italic">Belum ada data RSVP yang masuk.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="logoutModal"
        class="fixed inset-0 z-[60] hidden bg-gray-900 bg-opacity-50 flex items-center justify-center p-4 transition-opacity">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm transform scale-95 transition-transform"
            id="logoutModalContent">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4"><i
                        class="fas fa-sign-out-alt text-red-600 text-xl"></i></div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Konfirmasi Logout</h3>
                <p class="text-sm text-gray-500 mb-6">Apakah Anda yakin ingin keluar?</p>
                <div class="flex gap-3">
                    <button onclick="closeLogoutModal()"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg w-full">Batal</button>
                    <a href="logout.php"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg w-full text-center">Keluar</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
            document.getElementById('sidebarOverlay').classList.toggle('hidden');
        }

        function openLogoutModal() {
            document.getElementById('logoutModal').classList.remove('hidden');
            setTimeout(() => { document.getElementById('logoutModalContent').classList.replace('scale-95', 'scale-100'); }, 10);
        }

        function closeLogoutModal() {
            document.getElementById('logoutModalContent').classList.replace('scale-100', 'scale-95');
            setTimeout(() => { document.getElementById('logoutModal').classList.add('hidden'); }, 150);
        }
    </script>
</body>

</html>