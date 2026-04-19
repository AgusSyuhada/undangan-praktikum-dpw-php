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

    <?php include 'sidebar.php'; ?>

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

</body>

</html>