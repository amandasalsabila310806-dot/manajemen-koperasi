<?php
include 'config.php';
$message = "";

// Handle Registrasi Anggota Baru + Input Simpanan Pokok Wajib Otomatis
if (isset($_POST['daftar_anggota'])) {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $id_jenis = $_POST['id_jenis'];
    $nomor_rekening = "REQ-" . date("Ymd") . "-" . rand(100, 999);

    try {
        $pdo->beginTransaction();

        // 1. Ambil nilai iuran/simpanan pokok wajib berdasarkan jenis keanggotaan
        $stmt_jenis = $pdo->prepare("SELECT iuran_wajib FROM Jenis_Keanggotaan WHERE id_jenis = ?");
        $stmt_jenis->execute([$id_jenis]);
        $iuran = $stmt_jenis->fetch(PDO::FETCH_ASSOC)['iuran_wajib'] ?? 0;

        // 2. Insert Anggota Baru
        $stmt_anggota = $pdo->prepare("INSERT INTO Anggota (nama, email, id_jenis) VALUES (?, ?, ?) RETURNING id_anggota");
        $stmt_anggota->execute([$nama, $email, $id_jenis]);
        $id_anggota = $stmt_anggota->fetch(PDO::FETCH_ASSOC)['id_anggota'];

        // 3. Buat Rekening Simpanan Pokok/Wajib Otomatis untuk Anggota Baru
        $stmt_rek = $pdo->prepare("INSERT INTO Rekening_Simpanan (id_rekening, id_anggota, jenis_simpanan, saldo) VALUES (?, ?, 'Pokok', ?)");
        $stmt_rek->execute([$nomor_rekening, $id_anggota, $iuran]);

        // 4. Catat Transaksi Setoran Awal (Memicu Trigger Double-Entry Jurnal Otomatis)
        $stmt_tx = $pdo->prepare("INSERT INTO Transaksi_Simpanan (id_rekening, jenis_transaksi, jumlah) VALUES (?, 'Setor', ?)");
        $stmt_tx->execute([$nomor_rekening, $iuran]);

        $pdo->commit();
        $message = "<div class='p-4 mb-4 text-sm text-emerald-700 bg-emerald-50 rounded-xl font-medium'>✓ Anggota Berhasil Registrasi! Rekening {$nomor_rekening} aktif dengan saldo awal Rp " . number_format($iuran, 0, ',', '.') . "</div>";
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "<div class='p-4 mb-4 text-sm text-rose-700 bg-rose-50 rounded-xl font-medium'>✗ Registrasi Gagal: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoreCoop — Keanggotaan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-slate-50 text-slate-800 font-['Inter']">
    <div class="flex min-h-screen">
        <!-- SIDEBAR -->
        <aside class="w-64 bg-slate-900 text-white p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 mb-8">
                    <div class="h-8 w-8 bg-indigo-500 rounded-lg flex items-center justify-center font-bold">C</div>
                    <span class="text-xl font-bold tracking-tight">CoreCoop</span>
                </div>
                <nav class="space-y-2">
                    <a href="index.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:bg-slate-800 hover:text-white rounded-xl transition-all">Dashboard</a>
                    <a href="#" class="flex items-center gap-3 px-4 py-3 bg-indigo-600 text-white rounded-xl font-medium transition-all">Anggota</a>
                    <a href="simpanan.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:bg-slate-800 hover:text-white rounded-xl transition-all">Simpanan</a>
                    <a href="pinjaman.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:bg-slate-800 hover:text-white rounded-xl transition-all">Pinjaman</a>
                    <a href="akuntansi.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:bg-slate-800 hover:text-white rounded-xl transition-all">Jurnal & Keuangan</a>
                </nav>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="flex-1 p-10">
            <header class="mb-10">
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Manajemen Anggota</h1>
                <p class="text-slate-500 text-sm mt-1">Registrasi anggota baru beserta otomatisasi pembuatan rekening simpanan wajib.</p>
            </header>

            <?= $message ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- FORM REGISTRASI -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 h-fit">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Pendaftaran Anggota Baru</h3>
                    <form action="" method="POST" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Nama Lengkap</label>
                            <input type="text" name="nama" placeholder="Nama sesuai KTP" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:outline-indigo-600" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Alamat Email</label>
                            <input type="email" name="email" placeholder="contoh@email.com" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:outline-indigo-600" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Jenis Keanggotaan</label>
                            <select name="id_jenis" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:outline-indigo-600" required>
                                <?php
                                $jenis_stmt = $pdo->query("SELECT * FROM Jenis_Keanggotaan");
                                while($jk = $jenis_stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='{$jk['id_jenis']}'>{$jk['nama_jenis']} (Wajib: Rp " . number_format($jk['iuran_wajib'], 0, ',', '.') . ")</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <button type="submit" name="daftar_anggota" class="w-full bg-indigo-600 text-white font-medium p-3 rounded-xl hover:bg-indigo-700 transition-all text-sm shadow-sm">
                            Daftarkan & Ambil Setoran Pokok
                        </button>
                    </form>
                </div>

                <!-- TABEL DATA ANGGOTA -->
                <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Database Anggota Koperasi</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 text-slate-400 text-xs font-semibold uppercase tracking-wider">
                                    <th class="pb-3">ID</th>
                                    <th class="pb-3">Nama Anggota</th>
                                    <th class="pb-3">Email</th>
                                    <th class="pb-3">Tipe</th>
                                    <th class="pb-3 text-center">Bergabung</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 text-sm text-slate-700">
                                <?php
                                $list_stmt = $pdo->query("SELECT a.id_anggota, a.nama, a.email, j.nama_jenis, a.tanggal_registrasi FROM Anggota a JOIN Jenis_Keanggotaan j ON a.id_jenis = j.id_jenis ORDER BY a.id_anggota DESC");
                                while($row = $list_stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<tr>";
                                    echo "<td class='py-4 font-mono font-bold text-slate-400'>#{$row['id_anggota']}</td>";
                                    echo "<td class='py-4 font-semibold text-slate-900'>{$row['nama']}</td>";
                                    echo "<td class='py-4 text-slate-500'>{$row['email']}</td>";
                                    echo "<td class='py-4'><span class='bg-indigo-50 text-indigo-700 text-xs px-2.5 py-1 rounded-md font-semibold'>{$row['nama_jenis']}</span></td>";
                                    echo "<td class='py-4 text-center text-slate-400'>{$row['tanggal_registrasi']}</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
<nav class="space-y-2">
    <a href="index.php" class="...">Dashboard</a>
    <a href="simpanan.php" class="...">Simpanan</a>
    <a href="pinjaman.php" class="...">Pinjaman</a>
    <a href="akuntansi.php" class="...">Jurnal & Keuangan</a>
</nav>
<!-- Bagian <aside> atau Sidebar Anda -->
<aside class="w-64 bg-slate-900 text-white p-6 flex flex-col justify-between">
    <div>
        <div class="flex items-center gap-3 mb-8">
            <span class="font-bold">CoreCoop</span>
        </div>

        <nav class="flex flex-col gap-2">
            <!-- Menu yang bisa dilihat semua role -->
            <a href="dashboard.php" class="block p-2 hover:bg-slate-800 rounded">Dashboard</a>
            <a href="profile.php" class="block p-2 hover:bg-slate-800 rounded">Profil</a>

            <!-- Batasan Akses Akun Demo sesuai file di VS Code Anda -->
            <?php if ($_SESSION['role'] === 'SUPER_ADMIN'): ?>
                <a href="manajemen-user.php" class="block p-2 hover:bg-slate-800 rounded text-red-400">Manajemen User</a>
                <a href="setting.php" class="block p-2 hover:bg-slate-800 rounded">Setting Aplikasi</a>

            <?php elseif ($_SESSION['role'] === 'BENDAHARA' || $_SESSION['role'] === 'KETUA'): ?>
                <a href="akuntansi.php" class="block p-2 hover:bg-slate-800 rounded text-green-400">Audit & Akuntansi</a>

            <?php elseif ($_SESSION['role'] === 'TELLER'): ?>
                <a href="anggota.php" class="block p-2 hover:bg-slate-800 rounded text-blue-400">Data Anggota</a>
                
            <?php elseif ($_SESSION['role'] === 'ANGGOTA'): ?>
                <a href="simpanan-saya.php" class="block p-2 hover:bg-slate-800 rounded">Simpanan Saya</a>
            <?php endif; ?>
        </nav>
    </div>
</aside>