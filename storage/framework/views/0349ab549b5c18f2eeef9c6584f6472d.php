<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    font-family: 'DejaVu Sans', sans-serif;
    font-size: 13px;
    color: #1f2937;
    margin: 30px 30px 30px 40px;
  }

  .header { margin-bottom: 18px; border-bottom: 2.5px solid #1d4ed8; padding-bottom: 12px; }
  .header h1 { font-size: 22px; font-weight: bold; color: #1d4ed8; }
  .header p  { font-size: 12px; color: #6b7280; margin-top: 5px; }

  .section { margin-bottom: 18px; }
  .section-title {
    font-size: 13px; font-weight: bold; color: #1d4ed8;
    border-left: 4px solid #1d4ed8; padding-left: 8px;
    margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.3px;
  }

  /* ── Ringkasan tabel ── */
  .ring-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
  .ring-table th {
    background: #eff6ff; color: #1d4ed8; font-size: 11px; font-weight: bold;
    padding: 8px 12px; text-align: left; border: 1px solid #dbeafe;
    text-transform: uppercase; letter-spacing: 0.3px;
  }
  .ring-table td { padding: 8px 12px; border: 1px solid #e5e7eb; font-size: 13px; vertical-align: middle; }
  .ring-table tr:nth-child(even) { background: #f9fafb; }
  .num { font-weight: bold; font-size: 17px; text-align: right; width: 50px; }
  .pct { font-size: 12px; color: #6b7280; text-align: right; width: 55px; }
  .bar-wrap { width: 100px; padding: 0 4px; }
  .bar-bg { background: #e5e7eb; border-radius: 4px; height: 10px; overflow: hidden; }
  .bar-fill { height: 10px; border-radius: 4px; }
  .c-blue   { color: #1d4ed8; } .f-blue   { background: #1d4ed8; }
  .c-red    { color: #dc2626; } .f-red    { background: #dc2626; }
  .c-orange { color: #ea580c; } .f-orange { background: #ea580c; }
  .c-green  { color: #16a34a; } .f-green  { background: #16a34a; }
  .c-yellow { color: #ca8a04; } .f-yellow { background: #ca8a04; }
  .c-purple { color: #7c3aed; } .f-purple { background: #7c3aed; }
  .c-gray   { color: #6b7280; } .f-gray   { background: #9ca3af; }

  /* ── Tabel data ── */
  .data-table { width: 100%; border-collapse: collapse; font-size: 8px; }
  .data-table thead tr { background: #1d4ed8; color: white; }
  .data-table thead th { padding: 5px 4px; text-align: left; }
  .data-table tbody tr:nth-child(even) { background: #f0f9ff; }
  .data-table tbody td { padding: 3.5px 4px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }

  .badge { display: inline-block; padding: 1px 5px; border-radius: 9px; font-size: 7px; font-weight: bold; }
  .badge-merah   { background: #fee2e2; color: #dc2626; }
  .badge-kuning  { background: #fef9c3; color: #a16207; }
  .badge-hijau   { background: #dcfce7; color: #16a34a; }
  .badge-selesai { background: #dcfce7; color: #15803d; }
  .badge-proses  { background: #fef9c3; color: #a16207; }
  .tag { display: inline-block; padding: 1px 4px; border-radius: 3px;
         font-size: 7px; background: #dbeafe; color: #1d4ed8; margin: 1px 1px 0 0; }

  .page-break { page-break-after: always; }
  .empty-note { text-align: center; padding: 14px; color: #9ca3af; font-style: italic; font-size: 8.5px; }
  .footer { font-size: 7px; color: #9ca3af; border-top: 1px solid #e5e7eb;
            padding-top: 6px; margin-top: 14px; text-align: center; }

  @page { margin: 30px 30px 30px 40px; }
</style>
</head>
<body>

<?php
  $hadir      = $ringkasan['hadir'];
  $total      = $ringkasan['total_balita'];
  $pct = fn($n, $d = null) => ($d ?? $hadir) > 0 ? round($n / ($d ?? $hadir) * 100, 1) : 0;
?>

<!-- ── Header ── -->
<div class="header">
  <p style="font-size:12px;color:#6b7280;margin-bottom:5px;text-transform:uppercase;letter-spacing:0.5px">
    Laporan Monitoring Pertumbuhan Balita
  </p>
  <h1><?php echo e(implode(' · ', $posyanduList)); ?></h1>
  <p style="margin-top:4px">
    Periode: <strong><?php echo e($bulanLabel); ?></strong>
    &nbsp;·&nbsp; Dicetak: <?php echo e($dicetak); ?>

    &nbsp;·&nbsp; Oleh: <?php echo e($operator); ?>

  </p>
</div>

<!-- ══════════════════════════════════════════ -->
<!-- HALAMAN 1 — RINGKASAN STATISTIK           -->
<!-- ══════════════════════════════════════════ -->

<!-- Kehadiran -->
<div class="section">
  <div class="section-title">1. Kehadiran &amp; Cakupan Pengukuran</div>
  <table class="ring-table">
    <tr>
      <th style="width:40%">Indikator</th>
      <th style="width:12%" style="text-align:right">Jumlah</th>
      <th style="width:14%" style="text-align:right">% dari Total</th>
      <th style="width:34%">Proporsi</th>
    </tr>
    <tr>
      <td>Total Balita Terdaftar (aktif)</td>
      <td class="num c-blue"><?php echo e($total); ?></td>
      <td class="pct">—</td>
      <td class="bar-wrap"></td>
    </tr>
    <tr>
      <td>Hadir / Diukur Bulan Ini</td>
      <td class="num c-green"><?php echo e($hadir); ?></td>
      <td class="pct c-green"><?php echo e($pct($hadir, $total)); ?>%</td>
      <td class="bar-wrap">
        <div class="bar-bg"><div class="bar-fill f-green" style="width:<?php echo e($pct($hadir,$total)); ?>%"></div></div>
      </td>
    </tr>
    <tr>
      <td>Belum Diukur Bulan Ini</td>
      <td class="num c-gray"><?php echo e($ringkasan['belum_diukur']); ?></td>
      <td class="pct c-gray"><?php echo e($pct($ringkasan['belum_diukur'], $total)); ?>%</td>
      <td class="bar-wrap">
        <div class="bar-bg"><div class="bar-fill f-gray" style="width:<?php echo e($pct($ringkasan['belum_diukur'],$total)); ?>%"></div></div>
      </td>
    </tr>
  </table>
</div>

<!-- Status Gizi -->
<div class="section">
  <div class="section-title">2. Status Gizi Berdasarkan BB/Umur (dari <?php echo e($hadir); ?> balita diukur)</div>
  <table class="ring-table">
    <tr>
      <th style="width:40%">Kategori</th>
      <th style="width:12%">Jumlah</th>
      <th style="width:14%">% dari Diukur</th>
      <th style="width:34%">Proporsi</th>
    </tr>
    <tr>
      <td>Gizi Buruk <span style="font-size:11px;color:#6b7280">(BB/U &lt; -3 SD)</span></td>
      <td class="num c-red"><?php echo e($ringkasan['gizi_buruk']); ?></td>
      <td class="pct c-red"><?php echo e($pct($ringkasan['gizi_buruk'])); ?>%</td>
      <td class="bar-wrap"><div class="bar-bg"><div class="bar-fill f-red" style="width:<?php echo e($pct($ringkasan['gizi_buruk'])); ?>%"></div></div></td>
    </tr>
    <tr>
      <td>Gizi Kurang <span style="font-size:11px;color:#6b7280">(-3 s/d -2 SD)</span></td>
      <td class="num c-orange"><?php echo e($ringkasan['gizi_kurang']); ?></td>
      <td class="pct c-orange"><?php echo e($pct($ringkasan['gizi_kurang'])); ?>%</td>
      <td class="bar-wrap"><div class="bar-bg"><div class="bar-fill f-orange" style="width:<?php echo e($pct($ringkasan['gizi_kurang'])); ?>%"></div></div></td>
    </tr>
    <tr>
      <td>Gizi Baik <span style="font-size:11px;color:#6b7280">(-2 s/d +2 SD)</span></td>
      <td class="num c-green"><?php echo e($ringkasan['gizi_baik']); ?></td>
      <td class="pct c-green"><?php echo e($pct($ringkasan['gizi_baik'])); ?>%</td>
      <td class="bar-wrap"><div class="bar-bg"><div class="bar-fill f-green" style="width:<?php echo e($pct($ringkasan['gizi_baik'])); ?>%"></div></div></td>
    </tr>
    <tr>
      <td>Berisiko Lebih <span style="font-size:11px;color:#6b7280">(+2 s/d +3 SD)</span></td>
      <td class="num c-yellow"><?php echo e($ringkasan['berisiko_lebih']); ?></td>
      <td class="pct c-yellow"><?php echo e($pct($ringkasan['berisiko_lebih'])); ?>%</td>
      <td class="bar-wrap"><div class="bar-bg"><div class="bar-fill f-yellow" style="width:<?php echo e($pct($ringkasan['berisiko_lebih'])); ?>%"></div></div></td>
    </tr>
    <tr>
      <td>Gizi Lebih <span style="font-size:11px;color:#6b7280">(&gt; +3 SD)</span></td>
      <td class="num c-purple"><?php echo e($ringkasan['gizi_lebih']); ?></td>
      <td class="pct c-purple"><?php echo e($pct($ringkasan['gizi_lebih'])); ?>%</td>
      <td class="bar-wrap"><div class="bar-bg"><div class="bar-fill f-purple" style="width:<?php echo e($pct($ringkasan['gizi_lebih'])); ?>%"></div></div></td>
    </tr>
  </table>
</div>

<!-- Status Stunting -->
<div class="section">
  <div class="section-title">3. Status Stunting Berdasarkan TB/Umur (dari <?php echo e($hadir); ?> balita diukur)</div>
  <table class="ring-table">
    <tr>
      <th style="width:40%">Kategori</th>
      <th style="width:12%">Jumlah</th>
      <th style="width:14%">% dari Diukur</th>
      <th style="width:34%">Proporsi</th>
    </tr>
    <tr>
      <td>Sangat Pendek <span style="font-size:11px;color:#6b7280">(TB/U &lt; -3 SD)</span></td>
      <td class="num c-red"><?php echo e($ringkasan['stunting_sangat_pendek']); ?></td>
      <td class="pct c-red"><?php echo e($pct($ringkasan['stunting_sangat_pendek'])); ?>%</td>
      <td class="bar-wrap"><div class="bar-bg"><div class="bar-fill f-red" style="width:<?php echo e($pct($ringkasan['stunting_sangat_pendek'])); ?>%"></div></div></td>
    </tr>
    <tr>
      <td>Pendek <span style="font-size:11px;color:#6b7280">(-3 s/d -2 SD)</span></td>
      <td class="num c-orange"><?php echo e($ringkasan['stunting_pendek']); ?></td>
      <td class="pct c-orange"><?php echo e($pct($ringkasan['stunting_pendek'])); ?>%</td>
      <td class="bar-wrap"><div class="bar-bg"><div class="bar-fill f-orange" style="width:<?php echo e($pct($ringkasan['stunting_pendek'])); ?>%"></div></div></td>
    </tr>
    <tr>
      <td>Normal <span style="font-size:11px;color:#6b7280">(-2 s/d +3 SD)</span></td>
      <td class="num c-green"><?php echo e($ringkasan['stunting_normal']); ?></td>
      <td class="pct c-green"><?php echo e($pct($ringkasan['stunting_normal'])); ?>%</td>
      <td class="bar-wrap"><div class="bar-bg"><div class="bar-fill f-green" style="width:<?php echo e($pct($ringkasan['stunting_normal'])); ?>%"></div></div></td>
    </tr>
    <tr>
      <td>Tinggi <span style="font-size:11px;color:#6b7280">(&gt; +3 SD)</span></td>
      <td class="num c-blue"><?php echo e($ringkasan['stunting_tinggi']); ?></td>
      <td class="pct c-blue"><?php echo e($pct($ringkasan['stunting_tinggi'])); ?>%</td>
      <td class="bar-wrap"><div class="bar-bg"><div class="bar-fill f-blue" style="width:<?php echo e($pct($ringkasan['stunting_tinggi'])); ?>%"></div></div></td>
    </tr>
  </table>
</div>

<!-- Double Burden & EWS -->
<div class="section">
  <div class="section-title">4. Double Burden &amp; Early Warning System (EWS)</div>
  <table class="ring-table">
    <tr>
      <th style="width:40%">Indikator</th>
      <th style="width:12%">Jumlah</th>
      <th style="width:14%">% dari Diukur</th>
      <th style="width:34%">Proporsi</th>
    </tr>
    <tr>
      <td>Double Burden <span style="font-size:11px;color:#6b7280">(Wasting BB/TB &lt; -2 SD + Stunting TB/U &lt; -2 SD)</span></td>
      <td class="num c-red"><?php echo e($ringkasan['double_burden']); ?></td>
      <td class="pct c-red"><?php echo e($pct($ringkasan['double_burden'])); ?>%</td>
      <td class="bar-wrap"><div class="bar-bg"><div class="bar-fill f-red" style="width:<?php echo e($pct($ringkasan['double_burden'])); ?>%"></div></div></td>
    </tr>
    <tr>
      <td>EWS Merah — Risiko Tinggi (belum ditangani)</td>
      <td class="num c-red"><?php echo e($ringkasan['ews_merah']); ?></td>
      <td class="pct c-red"><?php echo e($pct($ringkasan['ews_merah'])); ?>%</td>
      <td class="bar-wrap"><div class="bar-bg"><div class="bar-fill f-red" style="width:<?php echo e($pct($ringkasan['ews_merah'])); ?>%"></div></div></td>
    </tr>
    <tr>
      <td>EWS Kuning — Perlu Perhatian (belum ditangani)</td>
      <td class="num c-yellow"><?php echo e($ringkasan['ews_kuning']); ?></td>
      <td class="pct c-yellow"><?php echo e($pct($ringkasan['ews_kuning'])); ?>%</td>
      <td class="bar-wrap"><div class="bar-bg"><div class="bar-fill f-yellow" style="width:<?php echo e($pct($ringkasan['ews_kuning'])); ?>%"></div></div></td>
    </tr>
  </table>
</div>

<div class="footer">
  SI-MANTAP — Sistem Informasi Monitoring Pertumbuhan Balita &nbsp;|&nbsp;
  Z-score: WHO Child Growth Standards 2006 (LMS) &nbsp;|&nbsp; Klasifikasi: Permenkes No. 2/2020
</div>

<div class="page-break"></div>

<!-- ══════════════════════════════════════════ -->
<!-- HALAMAN 2 — DATA PENGUKURAN               -->
<!-- ══════════════════════════════════════════ -->

<div class="header">
  <p style="font-size:12px;color:#6b7280;margin-bottom:5px;text-transform:uppercase;letter-spacing:0.5px">
    Data Pengukuran — <?php echo e(implode(' · ', $posyanduList)); ?>

  </p>
  <h1>Periode: <?php echo e($bulanLabel); ?></h1>
  <p style="margin-top:4px">Dicetak: <?php echo e($dicetak); ?> &nbsp;·&nbsp; Oleh: <?php echo e($operator); ?></p>
</div>

<div class="section">
  <table class="data-table">
    <thead>
      <tr>
        <th style="width:3%">#</th>
        <th style="width:16%">Nama Balita</th>
        <th style="width:4%">JK</th>
        <th style="width:6%">Umur</th>
        <th style="width:12%">Posyandu</th>
        <th style="width:7%">Tgl Ukur</th>
        <th style="width:5%">BB (kg)</th>
        <th style="width:5%">TB (cm)</th>
        <th style="width:6%">Z BB/U</th>
        <th style="width:6%">Z TB/U</th>
        <th style="width:12%">Status Gizi</th>
        <th style="width:10%">Stunting</th>
        <th style="width:8%">EWS</th>
      </tr>
    </thead>
    <tbody>
      <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <tr>
        <td><?php echo e($i + 1); ?></td>
        <td><?php echo e($row['nama_balita']); ?></td>
        <td><?php echo e($row['jenis_kelamin']); ?></td>
        <td><?php echo e($row['umur_bulan']); ?> bln</td>
        <td><?php echo e($row['posyandu']); ?></td>
        <td><?php echo e($row['tanggal_ukur']); ?></td>
        <td><?php echo e($row['berat_badan_kg']); ?></td>
        <td><?php echo e($row['tinggi_badan_cm']); ?></td>
        <td><?php echo e($row['z_score_bb_u'] ?? '-'); ?></td>
        <td><?php echo e($row['z_score_tb_u'] ?? '-'); ?></td>
        <td><?php echo e(str_replace('_', ' ', $row['status_gizi'] ?? '-')); ?></td>
        <td><?php echo e(str_replace('_', ' ', $row['status_stunting'] ?? '-')); ?></td>
        <td><span class="badge badge-<?php echo e(strtolower($row['flag_ews'])); ?>"><?php echo e($row['flag_ews']); ?></span></td>
      </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <tr><td colspan="13" class="empty-note">Tidak ada data pengukuran pada periode ini</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php if(count($peringatan) > 0): ?>
<div class="section" style="margin-top:12px;">
  <div class="section-title">Peringatan EWS Belum Ditangani</div>
  <table class="data-table">
    <thead>
      <tr>
        <th style="width:3%">#</th>
        <th style="width:17%">Nama Balita</th>
        <th style="width:14%">Posyandu</th>
        <th style="width:8%">Level</th>
        <th style="width:18%">Jenis</th>
        <th style="width:40%">Keterangan</th>
      </tr>
    </thead>
    <tbody>
      <?php $__currentLoopData = $peringatan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <tr>
        <td><?php echo e($i + 1); ?></td>
        <td><?php echo e($p['balita_nama']); ?></td>
        <td><?php echo e($p['posyandu_nama']); ?></td>
        <td><span class="badge badge-<?php echo e(strtolower($p['level'])); ?>"><?php echo e($p['level']); ?></span></td>
        <td><?php echo e($p['jenis']); ?></td>
        <td><?php echo e($p['pesan']); ?></td>
      </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<div class="footer">
  SI-MANTAP &nbsp;|&nbsp; Z-score: WHO 2006 &nbsp;|&nbsp; Klasifikasi: Permenkes No. 2/2020
</div>

<div class="page-break"></div>

<!-- ══════════════════════════════════════════ -->
<!-- HALAMAN 3 — RIWAYAT INTERVENSI KADER      -->
<!-- ══════════════════════════════════════════ -->

<div class="header">
  <p style="font-size:12px;color:#6b7280;margin-bottom:5px;text-transform:uppercase;letter-spacing:0.5px">
    Riwayat Intervensi Kader — <?php echo e(implode(' · ', $posyanduList)); ?>

  </p>
  <h1>Periode: <?php echo e($bulanLabel); ?></h1>
  <p style="margin-top:4px">Dicetak: <?php echo e($dicetak); ?> &nbsp;·&nbsp; Oleh: <?php echo e($operator); ?></p>
</div>

<div class="section">
  <?php if(count($intervensi) > 0): ?>
  <table class="data-table">
    <thead>
      <tr>
        <th style="width:3%">#</th>
        <th style="width:14%">Nama Balita</th>
        <th style="width:7%">Tanggal</th>
        <th style="width:9%">EWS Pemicu</th>
        <th style="width:27%">Intervensi Dilakukan</th>
        <th style="width:7%">Lapor Atasan</th>
        <th style="width:7%">Status</th>
        <th style="width:15%">Catatan</th>
        <th style="width:11%">Kader</th>
      </tr>
    </thead>
    <tbody>
      <?php $__currentLoopData = $intervensi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <tr>
        <td><?php echo e($i + 1); ?></td>
        <td><?php echo e($t['balita_nama']); ?></td>
        <td><?php echo e($t['tanggal']); ?></td>
        <td>
          <?php if($t['peringatan_level']): ?>
            <span class="badge badge-<?php echo e(strtolower($t['peringatan_level'])); ?>"><?php echo e($t['peringatan_level']); ?></span>
          <?php else: ?>
            -
          <?php endif; ?>
        </td>
        <td>
          <?php $__currentLoopData = $t['jenis_tindakan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <span class="tag"><?php echo e($labelTindakan[$jt] ?? $jt); ?></span>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </td>
        <td style="text-align:center"><?php echo e($t['dilaporkan_ke_atasan'] ? '✓ Ya' : '—'); ?></td>
        <td>
          <span class="badge <?php echo e($t['status_akhir'] === 'SELESAI' ? 'badge-selesai' : 'badge-proses'); ?>">
            <?php echo e($t['status_akhir'] === 'SELESAI' ? 'Selesai' : 'Proses'); ?>

          </span>
        </td>
        <td style="font-size:7.5px"><?php echo e($t['catatan'] ?? '—'); ?></td>
        <td><?php echo e($t['pencatat']); ?></td>
      </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
  </table>
  <?php else: ?>
  <p class="empty-note">Tidak ada intervensi yang tercatat pada periode ini</p>
  <?php endif; ?>
</div>

<div class="footer">
  SI-MANTAP — Sistem Informasi Monitoring Pertumbuhan Balita &nbsp;|&nbsp;
  Z-score: WHO Child Growth Standards 2006 (LMS) &nbsp;|&nbsp; Klasifikasi: Permenkes No. 2/2020
</div>

</body>
</html>
<?php /**PATH /Users/jayadin/simantap/simantap-app/resources/views/pdf/laporan.blade.php ENDPATH**/ ?>