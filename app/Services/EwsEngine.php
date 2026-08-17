<?php

namespace App\Services;

use App\Models\Balita;
use App\Models\Pengukuran;
use App\Models\Peringatan;

/**
 * Early Warning System Engine — SI-MANTAP
 *
 * Mendeteksi risiko pertumbuhan balita berdasarkan tiga kriteria
 * yang telah divalidasi terhadap standar WHO:
 *
 * Kriteria 1 — Penurunan BB:
 *   WHO (2008). Training Course on Child Growth Assessment. Geneva: WHO, p. C-4.
 *
 * Kriteria 2 — Stagnasi 2 bulan:
 *   WHO (2008). Training Course on Child Growth Assessment. Geneva: WHO.
 *   Kemenkes RI (2020). Permenkes No. 2 Tahun 2020 tentang Standar Antropometri Anak.
 *
 * Kriteria 3 — Penurunan Z-score:
 *   Anagnostou et al. (2020). Annals of Nutrition and Metabolism, 80(S1), 18-28.
 *   WHO MGRS Group (2009). WHO Child Growth Standards: Growth Velocity. Acta Paediatrica, 450(S).
 */
class EwsEngine
{
    public function run(Pengukuran $pengukuran): array
    {
        $peringatan = [];
        $balita     = $pengukuran->balita;

        // Ambil 3 pengukuran sebelumnya (lebih lama dari pengukuran ini)
        $riwayat = Pengukuran::where('balita_id', $balita->id)
                             ->where('tanggal_ukur', '<', $pengukuran->tanggal_ukur)
                             ->orderBy('tanggal_ukur', 'desc')
                             ->limit(3)
                             ->get();

        // Cek apakah ini pengukuran terakhir balita ini
        // Peringatan (antrean tindakan) hanya dibuat untuk pengukuran terakhir.
        // Pengukuran lama tetap mendapat flag_ews sebagai histori, tapi tidak masuk antrean.
        $adaYangLebihBaru = Pengukuran::where('balita_id', $balita->id)
                                      ->where('tanggal_ukur', '>', $pengukuran->tanggal_ukur)
                                      ->exists();
        $adalahTerakhir = !$adaYangLebihBaru;

        // ── BLOK 1: STATUS GIZI ABSOLUT ──────────────────────────────────
        $peringatan = array_merge($peringatan,
            $this->cekStatusGizi($pengukuran),
            $this->cekStatusStunting($pengukuran)
        );

        // ── BLOK 2: WEIGHT FALTERING (butuh minimal 1 data sebelumnya) ───
        if ($riwayat->count() >= 1) {
            $prev = $riwayat->first();
            $peringatan = array_merge($peringatan,
                $this->cekPenurunanBB($pengukuran, $prev),
                $this->cekPenurunanZScore1Kunjungan($pengukuran, $prev)
            );
        }

        if ($riwayat->count() >= 2) {
            $peringatan = array_merge($peringatan,
                $this->cekStagnasiBB($pengukuran, $riwayat->get(0), $riwayat->get(1)),
                $this->cekPenurunanZScore2Kunjungan($pengukuran, $riwayat->get(1))
            );
        }

        // ── BLOK 3: ABSENSI ──────────────────────────────────────────────
        $peringatan = array_merge($peringatan,
            $this->cekAbsensi($balita)
        );

        // Simpan flag_ews sebagai histori pada record pengukuran
        $pengukuran->flag_ews = $this->tentukan_flag($peringatan);
        $pengukuran->saveQuietly();

        // Peringatan (antrean tindakan) hanya disimpan untuk pengukuran terakhir
        if ($adalahTerakhir) {
            foreach ($peringatan as $p) {
                $p->save();
            }
            return $peringatan; // hanya kembalikan jika sudah tersimpan (ada id)
        }

        return []; // pengukuran lampau: flag_ews dicatat tapi tidak ada antrean baru
    }

    // ── Pengecekan Individual ─────────────────────────────────────────────

    private function cekStatusGizi(Pengukuran $p): array
    {
        $result = [];
        $z = $p->z_score_bb_u;
        if ($z === null) return $result;

        if ($z < -3) {
            $result[] = $this->buat($p, 'GIZI_BURUK', 'MERAH',
                "Gizi Buruk: Z-score BB/U = {$z} (< -3 SD)");
        } elseif ($z < -2) {
            $result[] = $this->buat($p, 'GIZI_KURANG', 'MERAH',
                "Gizi Kurang: Z-score BB/U = {$z} (< -2 SD)");
        } elseif ($z < -1) {
            $result[] = $this->buat($p, 'RISIKO_GIZI', 'KUNING',
                "Berisiko Gizi Kurang: Z-score BB/U = {$z} (antara -2 dan -1 SD)");
        }

        return $result;
    }

    private function cekStatusStunting(Pengukuran $p): array
    {
        $result = [];
        $z = $p->z_score_tb_u;
        if ($z === null) return $result;

        if ($z < -3) {
            $result[] = $this->buat($p, 'SANGAT_PENDEK', 'MERAH',
                "Sangat Pendek: Z-score TB/U = {$z} (< -3 SD)");
        } elseif ($z < -2) {
            $result[] = $this->buat($p, 'PENDEK_STUNTED', 'MERAH',
                "Pendek (Stunted): Z-score TB/U = {$z} (< -2 SD)");
        } elseif ($z < -1) {
            $result[] = $this->buat($p, 'RISIKO_PENDEK', 'KUNING',
                "Berisiko Pendek: Z-score TB/U = {$z} (antara -2 dan -1 SD)");
        }

        return $result;
    }

    /** Kriteria 1: Penurunan berat badan (WHO 2008) */
    private function cekPenurunanBB(Pengukuran $baru, Pengukuran $prev): array
    {
        if ($baru->berat_badan_kg >= $prev->berat_badan_kg) return [];

        $turun = round($prev->berat_badan_kg - $baru->berat_badan_kg, 2);
        return [$this->buat($baru, 'WEIGHT_LOSS', 'MERAH',
            "BB turun {$turun} kg ({$prev->berat_badan_kg} → {$baru->berat_badan_kg} kg)")];
    }

    /** Kriteria 2: Stagnasi BB 2 bulan berturut (WHO 2008, Permenkes 2/2020) */
    private function cekStagnasiBB(Pengukuran $baru, Pengukuran $prev1, Pengukuran $prev2): array
    {
        if ($baru->berat_badan_kg <= $prev1->berat_badan_kg
            && $prev1->berat_badan_kg <= $prev2->berat_badan_kg) {
            return [$this->buat($baru, 'WEIGHT_STAGNATION', 'KUNING',
                'BB tidak naik selama 2 bulan berturut-turut')];
        }
        return [];
    }

    /** Kriteria 3a: Penurunan Z-score dalam 1 kunjungan (Anagnostou et al., 2020) */
    private function cekPenurunanZScore1Kunjungan(Pengukuran $baru, Pengukuran $prev): array
    {
        if ($baru->z_score_bb_u === null || $prev->z_score_bb_u === null) return [];

        $delta = $baru->z_score_bb_u - $prev->z_score_bb_u;

        if ($delta <= -1.0) {
            return [$this->buat($baru, 'ZSCORE_DROP', 'MERAH',
                'Z-score BB/U turun ' . abs(round($delta, 2)) . ' SD dalam 1 kunjungan')];
        }

        if ($delta <= -0.67) {
            return [$this->buat($baru, 'ZSCORE_DROP_MILD', 'KUNING',
                'Z-score BB/U turun ' . abs(round($delta, 2)) . ' SD — perlu dipantau')];
        }

        return [];
    }

    /** Kriteria 3b: Penurunan Z-score progresif (WHO MGRS 2009) */
    private function cekPenurunanZScore2Kunjungan(Pengukuran $baru, Pengukuran $prev2): array
    {
        if ($baru->z_score_bb_u === null || $prev2->z_score_bb_u === null) return [];

        $delta = $baru->z_score_bb_u - $prev2->z_score_bb_u;

        if ($delta <= -2.0) {
            return [$this->buat($baru, 'ZSCORE_DROP_PROG', 'MERAH',
                'Z-score BB/U turun ' . abs(round($delta, 2)) . ' SD dalam 2 kunjungan')];
        }

        return [];
    }

    /** Deteksi absensi berdasarkan tanggal pengukuran terakhir */
    private function cekAbsensi(Balita $balita): array
    {
        $terakhir = Pengukuran::where('balita_id', $balita->id)
                              ->orderBy('tanggal_ukur', 'desc')
                              ->value('tanggal_ukur');

        if (!$terakhir) return [];

        // Menghitung selisih menggunakan hari aktual[cite: 1]
        $hariAbsen = (int) \Carbon\Carbon::parse($terakhir)->diffInDays(now());
        
        // Membulatkan hari ke bulan untuk teks pesan (misal: 95 hari = 3 bulan)
        $bulanHitungan = floor($hariAbsen / 30);

        // Jika tidak hadir lebih dari atau sama dengan 90 hari (3 bulan)[cite: 1]
        if ($hariAbsen >= 90) {
            $dummy = new Pengukuran(['balita_id' => $balita->id]);
            return [$this->buat($dummy, 'ABSEN_LAMA', 'MERAH',
                "Tidak hadir {$bulanHitungan} bulan — kunjungan rumah diperlukan")];
        }

        // Jika tidak hadir lebih dari atau sama dengan 60 hari (2 bulan)[cite: 1]
        if ($hariAbsen >= 60) {
            $dummy = new Pengukuran(['balita_id' => $balita->id]);
            return [$this->buat($dummy, 'ABSEN_2BULAN', 'KUNING',
                "Tidak hadir {$bulanHitungan} bulan — ingatkan untuk hadir")];
        }

        return [];
    }

    // ── Helper ───────────────────────────────────────────────────────────

    private function buat(Pengukuran $p, string $jenis, string $level, string $pesan): Peringatan
    {
        return new Peringatan([
            'pengukuran_id'    => $p->id,
            'balita_id'        => $p->balita_id,
            'jenis_peringatan' => $jenis,
            'level_risiko'     => $level,
            'pesan'            => $pesan,
            'status_tindak_lanjut' => 'BELUM',
        ]);
    }

    private function tentukan_flag(array $peringatan): string
    {
        foreach ($peringatan as $p) {
            if ($p->level_risiko === 'MERAH') return 'MERAH';
        }
        foreach ($peringatan as $p) {
            if ($p->level_risiko === 'KUNING') return 'KUNING';
        }
        return 'HIJAU';
    }
}
