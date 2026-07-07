<?php
include 'config.php';

// Validasi Double Entry Check (Total Debit == Total Kredit)
$check_stmt = $pdo->query("SELECT SUM(debit) as total_debit, SUM(kredit) as total_kredit FROM Detail_Jurnal");
$summary = $check_stmt->fetch(PDO::FETCH_ASSOC);
$is_balanced = ($summary['total_debit'] == $summary['total_kredit']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoreCoop — Audit & Akuntansi</title>
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
                    <a href="simpanan.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:bg-slate-800 hover:text-white rounded-xl transition-all">Simpanan</a>
                    <a href="pinjaman.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:bg-slate-800 hover:text-white rounded-xl transition-all">Pinjaman</a>
                    <a href="#" class="flex items-center gap-3 px-4 py-3 bg-indigo-600 text-white rounded-xl font-medium transition-all">Jurnal & Keuangan</a>
                </nav>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="flex-1 p-10">
            <header class="mb-10 flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Jurnal Umum & Integritas Data</h1>
                    <p class="text-slate-500 text-sm mt-1">Audit double-entry bookkeeping otomatis hasil dari post-trigger database.</p>
                </div>
                
                <!-- INDIKATOR BALANCE INTEGRITAS -->
                <div>
                    <?php if($is_balanced): ?>
                        <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            ● Sistem Seimbang (Balanced)
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                            ▲ Peringatan: Selisih (Unbalanced)
                        </span>
                    <?php endif; ?>
                </div>
            </header>

            <!-- CARD REKAP JURNAL -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <h3 class="text-lg font-bold text-slate-900 mb-6">Buku Jurnal Umum Mutasi Kas</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-slate-400 text-xs font-semibold uppercase tracking-wider">
                                <th class="pb-3">Tanggal / No. Jurnal</th>
                                <th class="pb-3">Akun Anggaran</th>
                                <th class="pb-3">Keterangan Transaksi</th>
                                <th class="pb-3 text-right">Debit (Rp)</th>
                                <th class="pb-3 text-right">Kredit (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                            <?php
                            $jurnal_query = "
                                SELECT j.no_jurnal, j.tanggal_jurnal, j.keterangan, d.id_akun, d.debit, d.kredit 
                                FROM Jurnal_Umum j 
                                JOIN Detail_Jurnal d ON j.no_jurnal = d.no_jurnal 
                                ORDER BY j.no_jurnal DESC, d.debit DESC";
                            $jurnal_stmt = $pdo->query($jurnal_query);
                            
                            while($row = $jurnal_stmt->fetch(PDO::FETCH_ASSOC)) {
                                $is_debit = $row['debit'] > 0;
                                echo "<tr>";
                                echo "<td class='py-4 text-xs font-mono text-slate-400'>{$row['tanggal_jurnal']} <span class='text-indigo-600 font-bold'>#{$row['no_jurnal']}</span></td>";
                                echo "<td class='py-4 font-mono font-medium " . ($is_debit ? 'text-slate-900' : 'text-slate-500 pl-6') . "'>{$row['id_akun']}</td>";
                                echo "<td class='py-4 text-slate-600'>{$row['keterangan']}</td>";
                                echo "<td class='py-4 text-right font-semibold text-emerald-600'>" . ($row['debit'] > 0 ? number_format($row['debit'], 2, ',', '.') : '-') . "</td>";
                                echo "<td class='py-4 text-right font-semibold text-indigo-600'>" . ($row['kredit'] > 0 ? number_format($row['kredit'], 2, ',', '.') : '-') . "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
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