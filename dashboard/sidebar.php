<?php
// Mendapatkan nama file yang sedang diakses untuk efek "active" pada menu
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div id="sidebarOverlay" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40 hidden md:hidden transition-opacity"
    onclick="toggleSidebar()"></div>

<aside id="sidebar"
    class="fixed inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 transition duration-300 ease-in-out z-50 w-64 bg-white shadow-md h-full flex flex-col">
    <div class="p-6 border-b text-center flex justify-between items-center md:block">
        <h2 class="text-xl font-bold text-wedding-primary">Admin Panel</h2>
        <button class="md:hidden text-gray-500" onclick="toggleSidebar()">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>

    <div class="px-6 py-4 border-b bg-gray-50">
        <p class="text-xs text-gray-500 mb-1">Login sebagai:</p>
        <p class="text-sm font-bold text-gray-800">
            <i class="fas fa-user-circle mr-1 text-wedding-primary"></i>
            <?= htmlspecialchars($_SESSION['nama'] ?? 'Admin') ?>
        </p>
    </div>

    <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
        <a href="rsvp.php"
            class="block w-full text-left px-4 py-3 rounded-lg <?= $current_page == 'rsvp.php' ? 'bg-wedding-primary text-white' : 'text-gray-600 hover:bg-gray-100' ?> font-medium transition">
            <i class="fas fa-envelope-open-text w-6"></i> Data RSVP
        </a>
        <a href="users.php"
            class="block w-full text-left px-4 py-3 rounded-lg <?= ($current_page == 'users.php' || $current_page == 'user-form.php') ? 'bg-wedding-primary text-white' : 'text-gray-600 hover:bg-gray-100' ?> font-medium transition">
            <i class="fas fa-users-cog w-6"></i> Data Admin
        </a>
        <a href="guests.php"
            class="block w-full text-left px-4 py-3 rounded-lg <?= ($current_page == 'guests.php' || $current_page == 'guest-form.php') ? 'bg-wedding-primary text-white' : 'text-gray-600 hover:bg-gray-100' ?> font-medium transition">
            <i class="fas fa-address-book w-6"></i> Nama Tamu (Link)
        </a>
        <a href="content.php"
            class="block w-full text-left px-4 py-3 rounded-lg <?= ($current_page == 'content.php' || $current_page == 'content-form.php') ? 'bg-wedding-primary text-white' : 'text-gray-600 hover:bg-gray-100' ?> font-medium transition">
            <i class="fas fa-file-alt w-6"></i> Data Isi Halaman
        </a>
    </nav>
    <div class="p-4 border-t">
        <button onclick="openLogoutModal()"
            class="block w-full text-center px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg transition">
            <i class="fas fa-sign-out-alt mr-2"></i> Logout
        </button>
    </div>
</aside>

<div id="logoutModal"
    class="fixed inset-0 z-[60] hidden bg-gray-900 bg-opacity-50 flex items-center justify-center p-4 transition-opacity">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm transform scale-95 transition-transform"
        id="logoutModalContent">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <i class="fas fa-sign-out-alt text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Konfirmasi Logout</h3>
            <p class="text-sm text-gray-500 mb-6">Apakah Anda yakin ingin keluar?</p>
            <div class="flex gap-3">
                <button onclick="closeLogoutModal()"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg w-full">Batal</button>
                <a href="logout.php" class="px-4 py-2 bg-red-600 text-white rounded-lg w-full text-center">Keluar</a>
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