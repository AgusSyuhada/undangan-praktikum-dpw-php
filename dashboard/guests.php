<?php
session_start();
require '../koneksi.php';

// Proteksi halaman admin
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Logika Hapus Tamu
if (isset($_GET['delete_id'])) {
    $delete_id = (int) $_GET['delete_id'];
    $conn->query("DELETE FROM guests WHERE id = $delete_id");
    header("Location: guests.php");
    exit;
}

// Ambil data tamu dari database
$query = "SELECT * FROM guests ORDER BY id ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nama Tamu - Dashboard Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script> tailwind.config = { theme: { extend: { colors: { wedding: { primary: '#c19a6b' } } } } } </script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f3f4f6;
        }

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

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 flex flex-col min-w-0">
        <header class="md:hidden flex items-center justify-between p-4 bg-white shadow-sm border-b sticky top-0 z-30">
            <h1 class="text-lg font-bold text-wedding-primary">Nama Tamu</h1>
            <button onclick="toggleSidebar()" class="text-gray-600 p-2"><i class="fas fa-bars text-2xl"></i></button>
        </header>

        <div class="p-4 md:p-8 overflow-y-auto flex-1">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Daftar Tamu Undangan</h2>
                <a href="guest-form.php"
                    class="bg-wedding-primary text-white px-4 py-2 rounded-lg text-sm shadow hover:bg-yellow-700 transition w-full md:w-auto text-center">
                    <i class="fas fa-plus mr-1"></i> Tambah Tamu
                </a>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-x-auto custom-scrollbar w-full">
                <table class="w-full text-left border-collapse min-w-[650px]">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="p-4 text-sm font-semibold text-gray-600 w-16">No</th>
                            <th class="p-4 text-sm font-semibold text-gray-600">Nama Tamu</th>
                            <th class="p-4 text-sm font-semibold text-gray-600 text-center">Link Undangan</th>
                            <th class="p-4 text-sm font-semibold text-gray-600 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php if ($result->num_rows > 0): ?>
                            <?php $no = 1;
                            while ($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="p-4 text-gray-500">
                                        <?= $no++ ?>
                                    </td>
                                    <td class="p-4 font-medium text-gray-800">
                                        <?= htmlspecialchars($row['nama']) ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <button onclick="copyLink('<?= htmlspecialchars($row['nama']) ?>')"
                                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded text-sm transition border border-gray-300 shadow-sm whitespace-nowrap">
                                            <i class="fas fa-link mr-1"></i> Copy Link
                                        </button>
                                    </td>
                                    <td class="p-4 text-center">
                                        <div class="flex justify-center gap-2">
                                            <a href="guest-form.php?id=<?= $row['id'] ?>"
                                                class="bg-blue-50 text-blue-600 hover:bg-blue-100 px-3 py-1 rounded text-sm font-medium transition whitespace-nowrap">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <button onclick="openDeleteModal(<?= $row['id'] ?>)"
                                                class="bg-red-50 text-red-600 hover:bg-red-100 px-3 py-1 rounded text-sm font-medium transition whitespace-nowrap">
                                                <i class="fas fa-trash-alt"></i> Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="p-8 text-center text-gray-500 italic">Belum ada data tamu.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="copyModal"
        class="fixed inset-0 z-[70] hidden bg-gray-900 bg-opacity-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm transform scale-95 transition-transform"
            id="copyModalContent">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                    <i class="fas fa-check text-green-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Link Berhasil Disalin!</h3>
                <p class="text-sm text-gray-500 mb-6" id="copyModalMsg"></p>
                <button onclick="closeCopyModal()"
                    class="px-4 py-2 bg-wedding-primary text-white rounded-lg w-full font-medium transition hover:bg-yellow-700">Oke</button>
            </div>
        </div>
    </div>

    <div id="deleteModal"
        class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm transform scale-95 transition-transform"
            id="modalDeleteContent">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Hapus Tamu?</h3>
                <p class="text-sm text-gray-500 mb-6">Tautan undangan yang sudah dibagikan tidak akan menampilkan nama
                    mereka lagi.</p>
                <div class="flex gap-3">
                    <button onclick="closeDeleteModal()"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg w-full">Batal</button>
                    <button onclick="confirmDelete()" class="px-4 py-2 bg-red-600 text-white rounded-lg w-full">Ya,
                        Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let deleteTargetId = null;

        function copyLink(namaTamu) {
            const baseUrl = window.location.href.split('/dashboard/')[0];
            const finalUrl = `${baseUrl}/index.php?to=${encodeURIComponent(namaTamu)}`;

            navigator.clipboard.writeText(finalUrl).then(() => {
                // Tampilkan Modal alih-alih alert
                document.getElementById('copyModalMsg').innerText = `Link untuk "${namaTamu}" telah disalin ke clipboard.`;
                document.getElementById('copyModal').classList.remove('hidden');
                setTimeout(() => { document.getElementById('copyModalContent').classList.replace('scale-95', 'scale-100'); }, 10);
            });
        }

        function closeCopyModal() {
            document.getElementById('copyModalContent').classList.replace('scale-100', 'scale-95');
            setTimeout(() => { document.getElementById('copyModal').classList.add('hidden'); }, 150);
        }

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
            window.location.href = 'guests.php?delete_id=' + deleteTargetId;
        }
    </script>
</body>

</html>