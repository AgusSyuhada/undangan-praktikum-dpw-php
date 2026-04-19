<?php
require 'koneksi.php';

// 1. Simpan Data RSVP jika form disubmit
if (isset($_POST['action']) && $_POST['action'] == 'submit_rsvp') {
    $nama = $conn->real_escape_string($_POST['nama']);
    $hadir = $conn->real_escape_string($_POST['hadir']);
    $ucapan = $conn->real_escape_string($_POST['ucapan']);

    $conn->query("INSERT INTO rsvps (nama, hadir, ucapan) VALUES ('$nama', '$hadir', '$ucapan')");

    // Ambil ulang data RSVP terbaru untuk dikembalikan sebagai HTML HTML
    $result_rsvp = $conn->query("SELECT * FROM rsvps ORDER BY id DESC");
    while ($row_rsvp = $result_rsvp->fetch_assoc()) {
        $badge_bg = $row_rsvp['hadir'] == 'Hadir' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
        $icon = $row_rsvp['hadir'] == 'Hadir' ? 'fa-check-circle' : 'fa-times-circle';

        echo "<div class='p-4 bg-gray-50 rounded-xl border border-gray-100'>
                <div class='flex justify-between items-center mb-1'>
                    <h4 class='font-semibold text-sm'>" . htmlspecialchars($row_rsvp['nama']) . "</h4>
                    <span class='text-[10px] $badge_bg px-2 py-1 rounded font-medium'><i class='fas $icon mr-1'></i>" . $row_rsvp['hadir'] . "</span>
                </div>
                <p class='text-sm text-gray-600'>\"" . nl2br(htmlspecialchars($row_rsvp['ucapan'])) . "\"</p>
              </div>";
    }
    exit; // HENTIKAN EKSEKUSI AGAR TIDAK MERENDER SELURUH HALAMAN LAGI
}

// 2. Ambil Data Konten Dinamis
$query_konten = "SELECT id_key, isi FROM contents";
$result_konten = $conn->query($query_konten);
$konten = [];
while ($row = $result_konten->fetch_assoc()) {
    $konten[$row['id_key']] = $row['isi'];
}

// 3. Ambil Data RSVP untuk ditampilkan di daftar ucapan
$query_rsvp = "SELECT * FROM rsvps ORDER BY id DESC";
$result_rsvp = $conn->query($query_rsvp);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Undangan Pernikahan - <?= htmlspecialchars($konten['c_judul']) ?></title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Poppins:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: { colors: { wedding: { light: '#fdfbf7', primary: '#c19a6b', dark: '#4a4a4a' } } }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fdfbf7;
            color: #4a4a4a;
        }

        .font-wedding {
            font-family: 'Great Vibes', cursive;
        }

        html {
            scroll-behavior: smooth;
        }

        /* Background diambil secara dinamis dari database */
        #cover-page {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('<?= htmlspecialchars($konten['c_bg']) ?>') center/cover;
        }

        .spin {
            animation: spin 4s linear infinite;
        }

        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #c19a6b;
            border-radius: 10px;
        }
    </style>
</head>

<body class="antialiased overflow-hidden" id="body">

    <audio id="bg-music" loop>
        <source src="https://upload.wikimedia.org/wikipedia/commons/2/20/Canon_in_D_Major.ogg" type="audio/ogg">
    </audio>

    <section id="cover-page"
        class="fixed inset-0 z-50 flex flex-col justify-center items-center text-white text-center px-4">
        <p class="text-sm tracking-widest uppercase mb-4">Pernikahan dari</p>
        <h1 class="font-wedding text-6xl md:text-7xl mb-8"><?= htmlspecialchars($konten['c_judul']) ?></h1>
        <div class="mt-8 mb-10 bg-white/20 backdrop-blur-sm p-6 rounded-2xl border border-white/30 w-full max-w-sm">
            <p class="text-sm mb-2">Kepada Yth. Bapak/Ibu/Saudara/i:</p>
            <p id="guest-name" class="text-2xl font-semibold mb-2">Tamu Undangan</p>
            <p class="text-xs italic text-gray-200">*Mohon maaf bila ada kesalahan penulisan nama/gelar</p>
        </div>
        <button onclick="openInvitation()"
            class="bg-wedding-primary hover:bg-yellow-700 text-white px-8 py-3 rounded-full font-medium transition duration-300 shadow-lg flex items-center gap-2 transform hover:scale-105">
            <i class="fas fa-envelope-open text-sm"></i> Buka Undangan
        </button>
    </section>

    <main id="main-content"
        class="hidden opacity-0 transition-opacity duration-1000 max-w-xl mx-auto bg-white shadow-2xl min-h-screen pb-20">

        <header class="text-center py-20 px-6 bg-wedding-light">
            <h2 class="font-wedding text-5xl text-wedding-primary mb-4"><?= htmlspecialchars($konten['c_judul']) ?></h2>
            <p class="text-sm text-gray-500 max-w-md mx-auto">
                <?= nl2br(htmlspecialchars($konten['c_ayat'])) ?>
            </p>
        </header>

        <section class="py-16 px-6">
            <div class="flex flex-col items-center gap-8">
                <div class="text-center">
                    <img src="<?= htmlspecialchars($konten['m_foto_pria']) ?>" alt="Mempelai Pria"
                        class="w-48 h-48 object-cover rounded-full shadow-lg mb-4 mx-auto border-4 border-wedding-primary">
                    <h3 class="font-wedding text-3xl font-bold"><?= htmlspecialchars($konten['m_nama_pria']) ?></h3>
                    <p class="text-sm text-gray-600 mt-2"><?= htmlspecialchars($konten['m_ortu_pria']) ?></p>
                </div>
                <div class="font-wedding text-5xl text-wedding-primary">&</div>
                <div class="text-center">
                    <img src="<?= htmlspecialchars($konten['m_foto_wanita']) ?>" alt="Mempelai Wanita"
                        class="w-48 h-48 object-cover rounded-full shadow-lg mb-4 mx-auto border-4 border-wedding-primary">
                    <h3 class="font-wedding text-3xl font-bold"><?= htmlspecialchars($konten['m_nama_wanita']) ?></h3>
                    <p class="text-sm text-gray-600 mt-2"><?= htmlspecialchars($konten['m_ortu_wanita']) ?></p>
                </div>
            </div>
        </section>

        <section class="py-16 px-6 bg-gray-50 text-center">
            <h2 class="font-wedding text-4xl mb-10 text-wedding-primary">Jadwal Acara</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <i class="fas fa-ring text-3xl text-wedding-primary mb-4"></i>
                    <h4 class="font-semibold text-xl mb-2">Akad Nikah</h4>
                    <p class="text-sm text-gray-600"><?= htmlspecialchars($konten['a_tgl_akad']) ?></p>
                    <p class="text-sm text-gray-600 mb-2"><?= htmlspecialchars($konten['a_jam_akad']) ?></p>
                    <p class="text-sm font-medium"><?= htmlspecialchars($konten['a_lokasi_akad']) ?></p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <i class="fas fa-glass-cheers text-3xl text-wedding-primary mb-4"></i>
                    <h4 class="font-semibold text-xl mb-2">Resepsi</h4>
                    <p class="text-sm text-gray-600"><?= htmlspecialchars($konten['a_tgl_resepsi']) ?></p>
                    <p class="text-sm text-gray-600 mb-2"><?= htmlspecialchars($konten['a_jam_resepsi']) ?></p>
                    <p class="text-sm font-medium"><?= htmlspecialchars($konten['a_lokasi_resepsi']) ?></p>
                </div>
            </div>
        </section>

        <section class="py-16 px-6 text-center">
            <h2 class="font-wedding text-4xl mb-6 text-wedding-primary">Lokasi Acara</h2>
            <div class="rounded-xl overflow-hidden shadow-lg border-2 border-gray-100">
                <iframe src="<?= htmlspecialchars($konten['a_map_resepsi']) ?>" width="100%" height="300"
                    style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </section>

        <section class="py-16 px-6 bg-gray-50 text-center">
            <h2 class="font-wedding text-4xl mb-6 text-wedding-primary">Turut Mengundang</h2>
            <p class="text-sm text-gray-600 mb-6">Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila
                Bapak/Ibu/Saudara/i berkenan hadir memberikan doa restu.</p>
            <div class="space-y-2 text-sm font-medium">
                <?= nl2br(htmlspecialchars($konten['l_mengundang'])) ?>
            </div>
        </section>

        <section class="py-16 px-6 text-center">
            <h2 class="font-wedding text-4xl mb-6 text-wedding-primary">Wedding Gift</h2>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 max-w-sm mx-auto">
                <img src="<?= htmlspecialchars($konten['l_icon_gift']) ?>" alt="Bank Icon" class="h-8 mx-auto mb-4">
                <p class="font-bold text-xl tracking-widest mb-1" id="rek-bca">
                    <?= htmlspecialchars($konten['l_rek_gift']) ?>
                </p>
                <p class="text-sm text-gray-500 mb-4"><?= htmlspecialchars($konten['l_an_gift']) ?></p>
                <button onclick="copyRekening('rek-bca')"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm transition">
                    <i class="fas fa-copy mr-1"></i> Salin Nomor Rekening
                </button>
            </div>
        </section>

        <section class="py-16 px-6 bg-wedding-light">
            <h2 class="font-wedding text-4xl mb-8 text-center text-wedding-primary">RSVP & Ucapan</h2>

            <form id="formRsvp" onsubmit="submitRsvpAjax(event)"
                class="bg-white p-6 rounded-2xl shadow-md border border-gray-100 mb-8">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                    <input type="text" name="nama" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-wedding-primary outline-none"
                        placeholder="Nama Anda">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kehadiran</label>
                    <select name="hadir" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-wedding-primary outline-none">
                        <option value="" disabled selected>Apakah akan hadir?</option>
                        <option value="Hadir">Ya, Saya akan hadir</option>
                        <option value="Tidak Hadir">Maaf, saya tidak bisa hadir</option>
                    </select>
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ucapan & Doa</label>
                    <textarea name="ucapan" required rows="4"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-wedding-primary outline-none"
                        placeholder="Tuliskan ucapan dan doa untuk mempelai..."></textarea>
                </div>
                <button type="submit"
                    class="w-full bg-wedding-primary hover:bg-yellow-700 text-white font-medium py-3 rounded-lg transition"
                    id="btnSubmitRsvp">
                    Kirim Ucapan
                </button>
            </form>

            <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-100">
                <h3 class="font-semibold text-lg text-gray-800 mb-4 border-b pb-2">
                    <i class="fas fa-comments text-wedding-primary mr-2"></i>Daftar Ucapan (<span
                        id="rsvp-count"><?= $result_rsvp->num_rows ?></span>)
                </h3>

                <div id="rsvp-list" class="max-h-72 overflow-y-auto custom-scrollbar pr-2 space-y-4">
                    <?php while ($row_rsvp = $result_rsvp->fetch_assoc()): ?>
                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="flex justify-between items-center mb-1">
                                <h4 class="font-semibold text-sm"><?= htmlspecialchars($row_rsvp['nama']) ?></h4>
                                <?php if ($row_rsvp['hadir'] == 'Hadir'): ?>
                                    <span class="text-[10px] bg-green-100 text-green-700 px-2 py-1 rounded font-medium"><i
                                            class="fas fa-check-circle mr-1"></i>Hadir</span>
                                <?php else: ?>
                                    <span class="text-[10px] bg-red-100 text-red-700 px-2 py-1 rounded font-medium"><i
                                            class="fas fa-times-circle mr-1"></i>Tidak Hadir</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-sm text-gray-600">"<?= nl2br(htmlspecialchars($row_rsvp['ucapan'])) ?>"</p>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </section>

        <footer class="text-center py-6 text-xs text-gray-400">
            <p>Dibuat dengan ❤️ untuk <?= htmlspecialchars($konten['c_judul']) ?></p>
        </footer>
    </main>

    <div id="successModal"
        class="fixed inset-0 z-[60] hidden bg-gray-900 bg-opacity-50 flex items-center justify-center p-4 transition-opacity">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm transform scale-95 transition-transform"
            id="successModalContent">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                    <i class="fas fa-check text-green-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Terima Kasih!</h3>
                <p class="text-sm text-gray-500 mb-6">Kehadiran dan doa Anda telah berhasil dikirim.</p>
                <button onclick="closeSuccessModal()"
                    class="px-4 py-2 bg-wedding-primary text-white rounded-lg w-full font-medium transition hover:bg-yellow-700">Tutup</button>
            </div>
        </div>
    </div>

    <button id="fab-music" onclick="toggleMusic()"
        class="hidden fixed bottom-6 right-6 z-50 bg-wedding-primary text-white w-12 h-12 rounded-full shadow-xl flex justify-center items-center transform hover:scale-110 transition duration-300">
        <i id="music-icon" class="fas fa-compact-disc text-2xl spin"></i>
    </button>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const guestName = urlParams.get('to');
        if (guestName) { document.getElementById('guest-name').textContent = guestName; }

        const audio = document.getElementById('bg-music');
        const fabMusic = document.getElementById('fab-music');
        const musicIcon = document.getElementById('music-icon');

        function openInvitation() {
            document.getElementById('cover-page').classList.add('-translate-y-full', 'duration-1000', 'ease-in-out');
            document.getElementById('body').classList.remove('overflow-hidden');
            const mainContent = document.getElementById('main-content');
            mainContent.classList.remove('hidden');
            setTimeout(() => { mainContent.classList.remove('opacity-0'); }, 100);
            audio.play().catch(error => console.log("Autoplay dicegah browser"));
            fabMusic.classList.remove('hidden');
        }

        function toggleMusic() {
            if (audio.paused) { audio.play(); musicIcon.classList.add('spin'); }
            else { audio.pause(); musicIcon.classList.remove('spin'); }
        }

        function copyRekening(elementId) {
            const rekText = document.getElementById(elementId).innerText;
            navigator.clipboard.writeText(rekText).then(() => { alert("Nomor rekening berhasil disalin: " + rekText); });
        }

        function submitRsvpAjax(event) {
            event.preventDefault(); // Mencegah reload halaman
            const form = event.target;
            const btn = document.getElementById('btnSubmitRsvp');

            // Ubah text tombol saat loading
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Mengirim...';
            btn.disabled = true;

            const formData = new FormData(form);
            formData.append('action', 'submit_rsvp'); // Menandakan request ini untuk AJAX PHP

            fetch('index.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(htmlString => {
                    // Perbarui daftar list RSVP
                    const rsvpList = document.getElementById('rsvp-list');
                    rsvpList.innerHTML = htmlString;

                    // Perbarui jumlah (counter) ucapan
                    const totalUcapan = rsvpList.children.length;
                    document.getElementById('rsvp-count').innerText = totalUcapan;

                    // Tampilkan Modal Sukses
                    document.getElementById('successModal').classList.remove('hidden');
                    setTimeout(() => {
                        document.getElementById('successModalContent').classList.replace('scale-95', 'scale-100');
                    }, 10);

                    // Reset form & tombol
                    form.reset();
                    btn.innerHTML = 'Kirim Ucapan';
                    btn.disabled = false;
                })
                .catch(error => {
                    alert("Terjadi kesalahan sistem.");
                    btn.innerHTML = 'Kirim Ucapan';
                    btn.disabled = false;
                });
        }

        function closeSuccessModal() {
            document.getElementById('successModalContent').classList.replace('scale-100', 'scale-95');
            setTimeout(() => { document.getElementById('successModal').classList.add('hidden'); }, 150);
        }
    </script>
</body>

</html>