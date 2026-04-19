<?php
session_start();
require '../koneksi.php';

// Proteksi halaman
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Logika Hapus Pengguna
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    // Cegah admin menghapus akunnya sendiri yang sedang aktif
    if ($delete_id != $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE id = $delete_id");
    }
    header("Location: users.php");
    exit;
}

// Ambil data users dari database
$query = "SELECT * FROM users ORDER BY id ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Admin - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script> tailwind.config = { theme: { extend: { colors: { wedding: { primary: '#c19a6b' } } } } } </script>
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f3f4f6; }
        .custom-scrollbar::-webkit-scrollbar { height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #c19a6b; border-radius: 10px; }
    </style>
</head>

<body class="flex h-screen overflow-hidden">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 flex flex-col min-w-0">
        <header class="md:hidden flex items-center justify-between p-4 bg-white shadow-sm border-b">
            <h1 class="text-lg font-bold text-wedding-primary">Data Admin</h1>
            <button onclick="toggleSidebar()" class="text-gray-600 p-2"><i class="fas fa-bars text-2xl"></i></button>
        </header>

        <div class="p-4 md:p-8 overflow-y-auto flex-1">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Data Pengguna</h2>
                <a href="user-form.php"
                    class="bg-wedding-primary text-white px-4 py-2 rounded-lg text-sm shadow hover:bg-yellow-700 transition w-full md:w-auto text-center">
                    <i class="fas fa-plus mr-1"></i> Tambah Pengguna
                </a>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse min-w-[700px]">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="p-4 text-sm font-semibold text-gray-600 w-12">No</th>
                            <th class="p-4 text-sm font-semibold text-gray-600">Nama Lengkap</th>
                            <th class="p-4 text-sm font-semibold text-gray-600">Username</th>
                            <th class="p-4 text-sm font-semibold text-gray-600">Email</th>
                            <th class="p-4 text-sm font-semibold text-gray-600 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php if ($result->num_rows > 0): ?>
                            <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="p-4 text-gray-500"><?= $no++ ?></td>
                                    <td class="p-4 font-medium text-gray-800"><?= htmlspecialchars($row['nama']) ?></td>
                                    <td class="p-4 text-gray-600">@<?= htmlspecialchars($row['username']) ?></td>
                                    <td class="p-4 text-gray-600"><?= htmlspecialchars($row['email']) ?></td>
                                    <td class="p-4 text-center">
                                        <div class="flex justify-center gap-2">
                                            <a href="user-form.php?id=<?= $row['id'] ?>" class="bg-blue-50 text-blue-600 px-3 py-1 rounded text-sm font-medium transition hover:bg-blue-100"><i class="fas fa-edit"></i> Edit</a>
                                            <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                                <button onclick="openDeleteModal(<?= $row['id'] ?>)" class="bg-red-50 text-red-600 px-3 py-1 rounded text-sm font-medium transition hover:bg-red-100"><i class="fas fa-trash-alt"></i> Hapus</button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="p-4 text-center text-gray-500">Belum ada data pengguna.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="deleteModal" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm transform scale-95 transition-transform" id="modalDeleteContent">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Hapus Pengguna?</h3>
                <p class="text-sm text-gray-500 mb-6">Tindakan ini tidak dapat dibatalkan.</p>
                <div class="flex gap-3">
                    <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg w-full">Batal</button>
                    <button onclick="confirmDelete()" class="px-4 py-2 bg-red-600 text-white rounded-lg w-full">Ya, Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let deleteTargetId = null;

        function openDeleteModal(id) {
            deleteTargetId = id;
            document.getElementById('deleteModal').classList.remove('hidden');
            setTimeout(() => { document.getElementById('modalDeleteContent').classList.replace('scale-95', 'scale-100'); }, 10);
        }

        function closeDeleteModal() {
            document.getElementById('modalDeleteContent').classList.replace('scale-100', 'scale-95');
            setTimeout(() => { document.getElementById('deleteModal').classList.add('hidden'); }, 150);
        }

        function confirmDelete() {
            window.location.href = 'users.php?delete_id=' + deleteTargetId;
        }
    </script>
</body>
</html>