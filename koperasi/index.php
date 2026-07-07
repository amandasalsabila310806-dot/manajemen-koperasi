
<?php
include 'config.php';

// 1. Total outstanding pinjaman
$stmt_outstanding = $pdo->query("SELECT SUM(sisa_pokok) as total FROM Pinjaman_Aktif");
$outstanding = $stmt_outstanding->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// 2. Total Anggota
$stmt_anggota = $pdo->query("SELECT COUNT(*) as total FROM Anggota");
$total_anggota = $stmt_anggota->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoreCoop — Manajemen Keuangan</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800">

    <div class="flex min-h-screen">
        <!-- SIDEBAR -->
        <aside class="w-64 bg-slate-900 text-white p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 mb-8">
                    <div class="h-8 w-8 bg-indigo-500 rounded-lg flex items-center justify-center font-bold text-white">C</div>
                    <span class="text-xl font-bold tracking-tight">CoreCoop</span>
                </div>
                <nav class="space-y-2">
                    <a href="#" class="flex items-center gap-3 px-4 py-3 bg-indigo-600 text-white rounded-xl font-medium transition-all">
                        <span>Dashboard</span>
                    </a>
                    <a href="anggota.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:bg-slate-800 hover:text-white rounded-xl transition-all">
                        <span>Anggota</span>
                    </a>
                    <a href="simpanan.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:bg-slate-800 hover:text-white rounded-xl transition-all">
                        <span>Simpanan</span>
                    </a>
                    <a href="pinjaman.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:bg-slate-800 hover:text-white rounded-xl transition-all">
                        <span>Pinjaman</span>
                    </a>
                    <a href="akuntansi.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:bg-slate-800 hover:text-white rounded-xl transition-all">
                        <span>Jurnal & Keuangan</span>
                    </a>
                </nav>
            </div>
            <div class="text-xs text-slate-500 border-t border-slate-800 pt-4">
                Sistem Akuntansi v1.0
            </div>
        </aside>
<!-- Tombol Keluar Sistem di Paling Bawah Sidebar -->
<div class="mt-auto pt-6 border-t border-slate-800">
    <a href="logout.php" class="block text-center p-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg text-sm transition shadow-md">
        🚪 Keluar Sistem
    </a>
</div>
        <!-- MAIN CONTENT -->
        <main class="flex-1 p-10 overflow-y-auto">
            <!-- Header -->
            <header class="flex justify-between items-center mb-10">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Ringkasan Eksekutif</h1>
                    <p class="text-slate-500 text-sm mt-1">Data keuangan koperasi simpan pinjam ter-update hari ini.</p>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm font-medium text-slate-600">Administrator</span>
                    <div class="h-10 w-10 bg-slate-200 rounded-full border border-slate-300"></div>
                </div>
            </header>

            <!-- METRIC CARDS -->
            <section class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col justify-between">
                    <span class="text-sm font-medium text-slate-400 uppercase tracking-wider">Total Anggota</span>
                    <h3 class="text-4xl font-bold text-slate-900 mt-2"><?= number_format($total_anggota) ?></h3>
                    <span class="text-xs text-emerald-600 font-medium mt-4">✓ Terverifikasi Aktif</span>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col justify-between">
                    <span class="text-sm font-medium text-slate-400 uppercase tracking-wider">Outstanding Pinjaman</span>
                    <h3 class="text-4xl font-bold text-slate-900 mt-2">Rp <?= number_format($outstanding, 2, ',', '.') ?></h3>
                    <span class="text-xs text-indigo-600 font-medium mt-4">Dana berputar di anggota</span>
                </div>
                <div class="bg-gradient-to-br from-indigo-600 to-violet-700 p-6 rounded-2xl shadow-sm text-white flex flex-col justify-between">
                    <span class="text-sm font-medium text-indigo-200 uppercase tracking-wider">Status Neraca</span>
                    <h3 class="text-4xl font-bold mt-2">Balanced</h3>
                    <span class="text-xs text-indigo-200 mt-4">Automated Trigger Double-Entry Aktif</span>
                </div>
            </section>

            <!-- DATA TABLE / RECENT TRANSACTION -->
            <section class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-slate-900">Aktivitas Simpanan Terbaru</h3>
                    <button class="bg-slate-900 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-slate-800 transition-all">+ Transaksi Baru</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-slate-400 text-sm font-medium">
                                <th class="pb-3">ID Rekening</th>
                                <th class="pb-3">Jenis</th>
                                <th class="pb-3 text-right">Jumlah</th>
                                <th class="pb-3 text-center">Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 text-sm text-slate-700">
                            <?php
                            $stmt_tx = $pdo->query("SELECT * FROM Transaksi_Simpanan ORDER BY tanggal_transaksi DESC LIMIT 5");
                            while ($row = $stmt_tx->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>";
                                echo "<td class='py-4 font-semibold text-slate-900'>{$row['id_rekening']}</td>";
                                echo "<td class='py-4'><span class='px-2 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700'>{$row['jenis_transaksi']}</span></td>";
                                echo "<td class='py-4 text-right font-medium text-slate-900'>Rp " . number_format($row['jumlah'], 2, ',', '.') . "</td>";
                                echo "<td class='py-4 text-center text-slate-400'>{$row['tanggal_transaksi']}</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

</body>
</html>
