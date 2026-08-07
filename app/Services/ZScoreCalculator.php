<?php

namespace App\Services;

use App\Models\Pengukuran;
use App\Models\WhoWeightIncrement;
use App\Models\WhoZscoreReference;

/**
 * Kalkulasi Z-score menggunakan metode LMS (Cole & Green, 1992)
 * sesuai WHO Child Growth Standards 2006.
 *
 * Referensi:
 * - WHO MGRS Group (2006). WHO Child Growth Standards. Geneva: WHO.
 * - WHO (2008). Training Course on Child Growth Assessment. Geneva: WHO.
 */
class ZScoreCalculator
{
    public function calculate(Pengukuran $pengukuran): Pengukuran
    {
        $balita = $pengukuran->balita;
        $umur   = $this->getUmurEfektif($pengukuran, $balita);

        $pengukuran->umur_bulan = $umur;

        // BB/U — Berat Badan menurut Umur
        $pengukuran->z_score_bb_u = $this->calcZ(
            $pengukuran->berat_badan_kg, $umur,
            $balita->jenis_kelamin, 'BB_U'
        );

        if ($pengukuran->tinggi_badan_cm) {
            // Koreksi posisi pengukuran — Permenkes No. 2 Tahun 2020, Lampiran Bab II:
            // Anak ≤24 bln seharusnya diukur terlentang (PB). Jika berdiri → +0.7 cm.
            // Anak >24 bln seharusnya diukur berdiri (TB). Jika terlentang → -0.7 cm.
            $tinggiKoreksi = $pengukuran->tinggi_badan_cm;
            if ($umur <= 24 && $pengukuran->posisi_ukur === 'berdiri') {
                $tinggiKoreksi += 0.7;
            } elseif ($umur > 24 && $pengukuran->posisi_ukur === 'terlentang') {
                $tinggiKoreksi -= 0.7;
            }

            // TB/U — Tinggi Badan menurut Umur
            $pengukuran->z_score_tb_u = $this->calcZ(
                $tinggiKoreksi, $umur,
                $balita->jenis_kelamin, 'TB_U'
            );

            // BB/TB — Berat Badan menurut Tinggi Badan
            $pengukuran->z_score_bb_tb = $this->calcZ(
                $pengukuran->berat_badan_kg,
                $tinggiKoreksi,
                $balita->jenis_kelamin, 'BB_TB'
            );

            // IMT/U — Indeks Massa Tubuh menurut Umur
            $tinggiM = $tinggiKoreksi / 100;
            $pengukuran->imt_u = round($pengukuran->berat_badan_kg / ($tinggiM * $tinggiM), 2);
            $pengukuran->z_score_imt_u = $this->calcZ(
                $pengukuran->imt_u, $umur,
                $balita->jenis_kelamin, 'IMT_U'
            );
        }

        $pengukuran->status_gizi    = $this->classifyGizi($pengukuran->z_score_bb_u);
        $pengukuran->status_stunting = $this->classifyStunting($pengukuran->z_score_tb_u);
        $pengukuran->status_wasting  = $this->classifyWasting($pengukuran->z_score_bb_tb);
        $pengukuran->status_imt_u    = $this->classifyWasting($pengukuran->z_score_imt_u);

        // Kenaikan BB dari pengukuran sebelumnya
        $this->calcKenaikanBb($pengukuran, $balita, $umur);

        $pengukuran->save();

        return $pengukuran;
    }

    /**
     * Hitung Z-score menggunakan metode LMS.
     *
     * Untuk BB/TB: $param = tinggi_badan_cm
     * Untuk BB/U dan TB/U: $param = umur_bulan
     */
    private function calcZ(float $nilai, float $param, string $gender, string $indikator): ?float
    {
        // Untuk BB/TB, parameter adalah tinggi badan
        // Bulatkan ke 0.5 terdekat untuk lookup tabel WHO
        $lookupParam = $indikator === 'BB_TB'
            ? round($param * 2) / 2  // Kelipatan 0.5
            : (int) $param;           // Bulatkan ke bulan penuh

        $ref = WhoZscoreReference::lookup($indikator, $gender, (float) $lookupParam);

        if (!$ref) return null;

        $L = $ref->l_value;
        $M = $ref->m_value;
        $S = $ref->s_value;

        // Formula LMS (Cole & Green, 1992)
        if (abs($L) < 0.0001) {
            $z = log($nilai / $M) / $S;
        } else {
            $z = (pow($nilai / $M, $L) - 1) / ($L * $S);
        }

        // Koreksi cutoff WHO untuk distribusi tidak simetris
        if ($z > 3) {
            $z = 3 + ($nilai - $ref->sd_plus3) / ($ref->sd_plus3 - $ref->sd_plus2);
        } elseif ($z < -3) {
            $z = -3 - ($ref->sd_minus3 - $nilai) / ($ref->sd_minus2 - $ref->sd_minus3);
        }

        return round($z, 3);
    }

    /**
     * Hitung umur efektif: kronologis atau adjusted (jika prematur < 24 bulan).
     * Sumber: WHO (2006). Training Course on Child Growth Assessment, p. C-3.
     */
    private function getUmurEfektif(Pengukuran $p, $balita): int
    {
        $umurKronologis = (int) $balita->tanggal_lahir->diffInMonths($p->tanggal_ukur);

        if ($balita->prematur && $umurKronologis < 24 && $balita->usia_gestasi_minggu) {
            $koreksiMinggu = 40 - $balita->usia_gestasi_minggu;
            $umurAdjusted  = max(0, $umurKronologis - (int) round($koreksiMinggu / 4.33));
            $p->umur_bulan_adjusted = $umurAdjusted;
            return min($umurAdjusted, 60);
        }

        return min($umurKronologis, 60);
    }

    // ── Klasifikasi BB/U — Permenkes No. 2 Tahun 2020 ───────────────────────
    // BB/U hanya 4 kategori. > +1 SD = Risiko Berat Badan Lebih (satu kategori).
    // Konfirmasi gizi lebih/obesitas dilakukan via BB/TB atau IMT/U, bukan BB/U.

    public function classifyGizi(?float $z): ?string
    {
        if ($z === null) return null;
        if ($z < -3) return 'GIZI_BURUK';   // Berat Badan Sangat Kurang
        if ($z < -2) return 'GIZI_KURANG';  // Berat Badan Kurang
        if ($z <= 1) return 'GIZI_BAIK';    // Berat Badan Normal
        return 'RISIKO_LEBIH';              // Risiko Berat Badan Lebih (> +1 SD)
    }

    // ── Klasifikasi TB/U — Permenkes No. 2 Tahun 2020 ───────────────────────

    public function classifyStunting(?float $z): ?string
    {
        if ($z === null) return null;
        if ($z < -3)  return 'SANGAT_PENDEK';
        if ($z < -2)  return 'PENDEK';
        if ($z <= 3)  return 'NORMAL';
        return 'TINGGI';
    }

    // ── Kenaikan Berat Badan (Weight Increment) ──────────────────────────────
    // N/T/O: KBM operasional (KMS Indonesia) — berlaku 0-60 bulan
    // is_weight_faltering: Tabel WHO Permenkes 2/2020 — hanya 0-24 bulan, interval ≥3 bulan

    private function calcKenaikanBb(Pengukuran $pengukuran, $balita, int $umur): void
    {
        $sebelumnya = $balita->pengukuran()
            ->where('tanggal_ukur', '<', $pengukuran->tanggal_ukur)
            ->where('id', '!=', $pengukuran->id)
            ->orderBy('tanggal_ukur', 'desc')
            ->first();

        if (!$sebelumnya) {
            $pengukuran->kenaikan_bb_gram    = null;
            $pengukuran->status_kbb          = null;
            $pengukuran->is_weight_faltering = null;
            return;
        }

        $intervalHari  = $sebelumnya->tanggal_ukur->diffInDays($pengukuran->tanggal_ukur);
        $intervalBulan = max($intervalHari / 30.44, 0.5);

        $kenaikanGram     = round(($pengukuran->berat_badan_kg - $sebelumnya->berat_badan_kg) * 1000);
        $kenaikanPerBulan = $kenaikanGram / $intervalBulan;

        $pengukuran->kenaikan_bb_gram = $kenaikanGram;

        // Klasifikasi N/T/O (KMS operasional)
        if ($kenaikanGram <= 0) {
            $pengukuran->status_kbb = 'O';
        } elseif ($kenaikanPerBulan >= $this->kbbMinimal($umur)) {
            $pengukuran->status_kbb = 'N';
        } else {
            $pengukuran->status_kbb = 'T';
        }

        // Weight faltering — Permenkes 2/2020 (hanya 0-24 bulan)
        // Gunakan lookback 3 bulan: cari pengukuran ≥3 bulan sebelum tanggal ukur saat ini.
        // Ini memungkinkan anak yang rutin hadir tiap bulan tetap bisa dinilai.
        if ($umur <= 24) {
            $tanggalLookback = $pengukuran->tanggal_ukur->copy()->subMonths(3)->subDays(7);
            $ref3bln = $balita->pengukuran()
                ->where('tanggal_ukur', '<=', $pengukuran->tanggal_ukur->copy()->subDays(75))
                ->where('tanggal_ukur', '>=', $tanggalLookback)
                ->where('id', '!=', $pengukuran->id)
                ->orderBy('tanggal_ukur', 'desc')
                ->first();

            if ($ref3bln) {
                $hari3bln    = $ref3bln->tanggal_ukur->diffInDays($pengukuran->tanggal_ukur);
                $interval3   = (int) round($hari3bln / 30.44);
                $usiaAwal    = (int) $balita->tanggal_lahir->diffInMonths($ref3bln->tanggal_ukur);
                $kenaikan3   = round(($pengukuran->berat_badan_kg - $ref3bln->berat_badan_kg) * 1000);
                $refRow      = WhoWeightIncrement::lookup($balita->jenis_kelamin, $interval3, $usiaAwal);

                $pengukuran->is_weight_faltering = $refRow ? ($kenaikan3 < $refRow->min_gram) : null;
            } else {
                $pengukuran->is_weight_faltering = null;
            }
        } else {
            $pengukuran->is_weight_faltering = null;
        }
    }

    private function kbbMinimal(int $umurBulan): int
    {
        if ($umurBulan <= 3)  return 700;
        if ($umurBulan <= 6)  return 500;
        if ($umurBulan <= 12) return 300;
        return 200;
    }

    // ── Klasifikasi BB/TB — Permenkes No. 2 Tahun 2020 ──────────────────────

    public function classifyWasting(?float $z): ?string
    {
        if ($z === null) return null;
        if ($z < -3)  return 'SANGAT_KURUS';
        if ($z < -2)  return 'KURUS';
        if ($z <= 1)  return 'NORMAL';
        if ($z <= 2)  return 'BERISIKO_GEMUK';
        if ($z <= 3)  return 'GEMUK';
        return 'OBESITAS';
    }
}
