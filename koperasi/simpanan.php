<?php
include 'config.php';

$message = "";

// Handle Input Transaksi Baru (Setor / Tarik)
if (isset($_POST['submit_transaksi'])) {
    $id_rekening = $_POST['id_rekening'];
    $jenis_transaksi = $_POST['jenis_transaksi'];
    $jumlah = $_POST['jumlah'];

    try {
        $pdo->beginTransaction();

        // 1. Insert ke tabel Transaksi_Simpanan (Memicu Trigger Otomatis)
        $stmt = $pdo->prepare("INSERT INTO Transaksi_Simpanan (id_rekening, jenis_transaksi, jumlah) VALUES (?, ?, ?)");
        $stmt->execute([$id_rekening, $jenis_transaksi, $jumlah]);

        // 2. Update saldo di Rekening_Simpanan
        if ($jenis_transaksi == 'Setor') {
            $stmt_update = $pdo->prepare("UPDATE Rekening_Simpanan SET saldo = saldo + ? WHERE id_rekening = ?");
        } else {
            $stmt_update = $pdo->prepare("UPDATE Rekening_Simpanan SET saldo = saldo - ? WHERE id_rekening = ?");
        }
        $stmt_update->execute([$jumlah, $id_rekening]);

        $pdo->commit();
        $message = "<div class='p-4 mb-4 text-sm text-emerald-700 bg-emerald-50 rounded-xl font-medium'>✓ Transaksi $jenis_transaksi berhasil disimpan & dicatat di Jurnal!</div>";
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "<div class='p-4 mb-4 text-sm text-rose-700 bg-rose-50 rounded-xl font-medium'>✗ Gagal: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoreCoop — Simpanan</title>
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
                    <a href="index.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:bg-slate-800 hover:text-white rounded-xl font-medium transition-all">Dashboard</a>
                    <a href="#" class="flex items-center gap-3 px-4 py-3 bg-indigo-600 text-white rounded-xl font-medium transition-all">Simpanan</a>
                    <a href="pinjaman.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:bg-slate-800 hover:text-white rounded-xl transition-all">Pinjaman</a>
                    <a href="akuntansi.php" class="flex items-center gap-3 px-4 py-3 text-slate-400 hover:bg-slate-800 hover:text-white rounded-xl transition-all">Jurnal & Keuangan</a>
                </nav>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="flex-1 p-10">
            <header class="mb-10">
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Manajemen Simpanan</h1>
                <p class="text-slate-500 text-sm mt-1">Kelola simpanan pokok, wajib, sukarela, dan pencatatan mutasi kas.</p>
            </header>

            <?= $message ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- FORM TRANSAKSI BARU -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 h-fit">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Form Setor / Tarik Tunai</h3>
                    <form action="" method="POST" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Pilih Rekening</label>
                            <select name="id_rekening" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:outline-indigo-600" required>
                                <?php
                                $rek_stmt = $pdo->query("SELECT r.id_rekening, a.nama, r.jenis_simpanan FROM Rekening_Simpanan r JOIN Anggota a ON r.id_anggota = a.id_anggota");
                                while($rek = $rek_stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='{$rek['id_rekening']}'>{$rek['id_rekening']} - {$rek['nama']} ({$rek['jenis_simpanan']})</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Jenis Transaksi</label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 text-sm text-slate-700 font-medium">
                                    <input type="radio" name="jenis_transaksi" value="Setor" checked class="text-indigo-600 focus:ring-indigo-500"> Setor Simpanan
                                </label>
                                <label class="flex items-center gap-2 text-sm text-slate-700 font-medium">
                                    <input type="radio" name="jenis_transaksi" value="Tarik" class="text-indigo-600 focus:ring-indigo-500"> Penarikan
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Nominal (Rp)</label>
                            <input type="number" name="jumlah" placeholder="Contoh: 50000" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:outline-indigo-600" required>
                        </div>
                        <button type="submit" name="submit_transaksi" class="w-full bg-indigo-600 text-white font-medium p-3 rounded-xl hover:bg-indigo-700 transition-all text-sm shadow-sm shadow-indigo-100">
                            Proses Transaksi
                        </button>
                    </form>
                </div>

                <!-- DAFTAR REKENING DAN SALDO -->
                <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Ikhtisar Saldo Rekening</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 text-slate-400 text-xs font-semibold uppercase tracking-wider">
                                    <th class="pb-3">No. Rekening</th>
                                    <th class="pb-3">Nama Anggota</th>
                                    <th class="pb-3">Jenis</th>
                                    <th class="pb-3 text-right">Total Saldo</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 text-sm text-slate-700">
                                <?php
                                $saldo_stmt = $pdo->query("SELECT r.id_rekening, a.nama, r.jenis_simpanan, r.saldo FROM Rekening_Simpanan r JOIN Anggota a ON r.id_anggota = a.id_anggota ORDER BY r.id_rekening ASC");
                                while($row = $saldo_stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<tr>";
                                    echo "<td class='py-4 font-mono font-medium text-slate-900'>{$row['id_rekening']}</td>";
                                    echo "<td class='py-4 font-medium'>{$row['nama']}</td>";
                                    echo "<td class='py-4'><span class='bg-slate-100 text-slate-700 text-xs px-2.5 py-1 rounded-md font-medium'>{$row['jenis_simpanan']}</span></td>";
                                    echo "<td class='py-4 text-right font-semibold text-slate-900'>Rp " . number_format($row['saldo'], 2, ',', '.') . "</td>";
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