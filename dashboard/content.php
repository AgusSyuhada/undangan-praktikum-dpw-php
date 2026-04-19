<?php
session_start();
require '../koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Ambil semua data konten
$query = "SELECT * FROM contents";
$result = $conn->query($query);
$all_data = [];
while ($row = $result->fetch_assoc()) {
    $all_data[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Isi Halaman - Dashboard Admin</title>
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

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>

<body class="flex h-screen overflow-hidden">

    <?php include 'sidebar.php'; ?>

    <main class="flex-1 flex flex-col min-w-0">
        <header class="md:hidden flex items-center justify-between p-4 bg-white shadow-sm border-b sticky top-0 z-30">
            <h1 class="text-lg font-bold text-wedding-primary">Isi Halaman</h1>
            <button onclick="toggleSidebar()" class="text-gray-600 p-2"><i class="fas fa-bars text-2xl"></i></button>
        </header>

        <div class="p-4 md:p-8 overflow-y-auto flex-1">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 hidden md:block">Manajemen Konten Halaman</h2>

            <div class="flex gap-2 md:gap-4 mb-6 border-b pb-2 overflow-x-auto no-scrollbar whitespace-nowrap">
                <button onclick="filterTab('cover')" id="tab-cover"
                    class="tab-btn px-4 py-2 text-sm font-semibold transition">Cover & Ayat</button>
                <button onclick="filterTab('mempelai')" id="tab-mempelai"
                    class="tab-btn px-4 py-2 text-sm font-semibold transition">Data Mempelai</button>
                <button onclick="filterTab('acara')" id="tab-acara"
                    class="tab-btn px-4 py-2 text-sm font-semibold transition">Jadwal & Lokasi</button>
                <button onclick="filterTab('lainnya')" id="tab-lainnya"
                    class="tab-btn px-4 py-2 text-sm font-semibold transition">Gift & Lainnya</button>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-x-auto custom-scrollbar w-full">
                <table class="w-full text-left border-collapse min-w-[700px]">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="p-4 text-sm font-semibold text-gray-600 w-1/3">Bagian (Tipe)</th>
                            <th class="p-4 text-sm font-semibold text-gray-600">Isi / Preview</th>
                            <th class="p-4 text-sm font-semibold text-gray-600 w-28 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="table-body" class="divide-y">
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        const contentData = <?= json_encode($all_data) ?>;

        function filterTab(kategori) {
            // Update UI Tab
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.className = "tab-btn px-4 py-2 text-sm font-semibold text-gray-500 hover:text-wedding-primary transition whitespace-nowrap";
            });
            const activeBtn = document.getElementById(`tab-${kategori}`);
            activeBtn.className = "tab-btn px-4 py-2 text-sm font-semibold text-wedding-primary border-b-2 border-wedding-primary transition whitespace-nowrap";

            // Filter Table
            const tbody = document.getElementById('table-body');
            tbody.innerHTML = '';

            const filtered = contentData.filter(item => item.kategori === kategori);
            filtered.forEach(item => {
                let isiDisplay = item.isi;
                if (item.tipe === 'gambar') {
                    // Cek apakah isi adalah path lokal atau URL luar
                    const imgSrc = item.isi.includes('http') ? item.isi : `../${item.isi}`;
                    isiDisplay = `
                        <div class="flex items-center gap-4">
                            <img src="${imgSrc}" class="h-12 w-12 object-cover rounded border bg-gray-100">
                            <span class="text-xs text-gray-400 truncate max-w-[200px]">${item.isi}</span>
                        </div>`;
                }

                tbody.innerHTML += `
                    <tr class="hover:bg-gray-50">
                        <td class="p-4 align-top">
                            <p class="font-medium text-gray-800">${item.bagian}</p>
                            <span class="text-[10px] bg-gray-200 text-gray-600 px-2 py-1 rounded uppercase tracking-wider mt-1 inline-block">${item.tipe}</span>
                        </td>
                        <td class="p-4 text-gray-700 text-sm align-middle whitespace-pre-line break-words max-w-xs">${isiDisplay}</td>
                        <td class="p-4 text-center align-middle">
                            <a href="content-form.php?id=${item.id_key}" class="bg-blue-50 text-blue-600 hover:bg-blue-100 px-3 py-2 rounded text-sm font-medium transition flex items-center justify-center gap-2 border border-blue-200">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        </td>
                    </tr>`;
            });
        }

        // Load tab pertama kali
        filterTab('cover');
    </script>
</body>

</html>