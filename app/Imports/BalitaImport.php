<?php

namespace App\Imports;

use App\Models\Balita;
use App\Models\Pengukuran;
use App\Services\ZScoreCalculator;
use Carbon\Carbon;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use DateTimeInterface;
use Exception;

class BalitaImport
{
    public array $errors         = [];
    public int   $berhasil       = 0;
    public int   $balitaBaru     = 0;
    public int   $pengukuranBaru = 0;

    public function __construct(
        private int $posyanduId,
        private int $dicatatOleh,
    ) {}

    public function import(string $path, string $ext): void
    {
        $reader = strtolower($ext) === 'csv'
            ? new CsvReader()
            : new XlsxReader();

        $reader->open($path);

        $baris = 0;
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $baris++;
                
                // Lewati baris 1 karena merupakan header
                if ($baris === 1) {
                    continue; 
                }

                $cells = $row->toArray();
                
                // Helper function untuk membaca kolom dengan aman (termasuk konversi tanggal)
                $col = function($i) use ($cells) {
                    $value = $cells[$i] ?? '';
                    if ($value instanceof DateTimeInterface) {
                        return $value->format('Y-m-d');
                    }
                    return trim((string) $value);
                };

                $nik            = $col(0);
                $nama           = $col(1);
                $tglLahirRaw    = $col(2);
                $jk             = strtoupper($col(3));
                $namaIbu        = $col(4);
                $alamat         = $col(5);
                $tglUkurRaw     = $col(6);
                $bb             = (float) str_replace(',', '.', $col(7));
                $tb             = (float) str_replace(',', '.', $col(8));
                $prematurRaw    = strtoupper($col(9));
                $prematur       = in_array($prematurRaw, ['Y', 'YA', '1', 'TRUE']);
                $usiaGestasi    = $col(10) !== '' ? (int) $col(10) : null;

                // Abaikan baris jika benar-benar kosong
                if (!$nik && !$nama && !$tglUkurRaw) {
                    continue; 
                }

                // --- 1. Validasi Wajib ---
                if (!$nik || !$nama || !$tglLahirRaw || !$tglUkurRaw || !$bb || !$tb) {
                    $this->errors[] = "Baris {$baris}: Data tidak lengkap (NIK, Nama, Tgl Lahir, Tgl Ukur, BB, TB wajib diisi).";
                    continue;
                }
                if (strlen($nik) !== 16 || !ctype_digit($nik)) {
                    $this->errors[] = "Baris {$baris}: NIK harus 16 digit angka (sekarang: {$nik}).";
                    continue;
                }
                if (!in_array($jk, ['L', 'P'])) {
                    $this->errors[] = "Baris {$baris}: Jenis kelamin harus L atau P (sekarang: {$jk}).";
                    continue;
                }
                if ($bb < 0.5 || $bb > 30) {
                    $this->errors[] = "Baris {$baris}: Berat badan tidak valid ({$bb} kg).";
                    continue;
                }
                if ($tb < 30 || $tb > 130) {
                    $this->errors[] = "Baris {$baris}: Tinggi badan tidak valid ({$tb} cm).";
                    continue;
                }

                try {
                    $tglLahir = Carbon::parse($tglLahirRaw)->startOfDay();
                    $tglUkur  = Carbon::parse($tglUkurRaw)->startOfDay();
                } catch (Exception) {
                    $this->errors[] = "Baris {$baris}: Format tanggal tidak valid.";
                    continue;
                }

                // --- 2. Cari atau Buat Profil Balita ---
                // (Menggunakan nik_balita sesuai dengan struktur kolom tabel Anda)
                $balita = Balita::where('nik_balita', $nik)->first();

                if ($balita) {
                    // Cek konsistensi nama jika NIK sudah ada
                    if (strtolower(trim($balita->nama)) !== strtolower(trim($nama))) {
                        $this->errors[] = "Baris {$baris}: NIK {$nik} sudah terdaftar atas nama '{$balita->nama}', tetapi di file Excel tertulis '{$nama}'. Data dilewati untuk mencegah salah input.";
                        continue;
                    }
                } else {
                    // Buat profil balita baru
                    $balita = Balita::create([
                        'nik_balita'           => $nik,
                        'posyandu_id'          => $this->posyanduId,
                        'nama'                 => $nama,
                        'jenis_kelamin'        => $jk,
                        'tanggal_lahir'        => $tglLahir,
                        'nama_ibu'             => $namaIbu ?: '-',
                        'alamat'               => $alamat ?: '-',
                        'prematur'             => $prematur,
                        'usia_gestasi_minggu'  => $prematur ? $usiaGestasi : null,
                        'aktif'                => true,
                    ]);
                    
                    $this->balitaBaru++;
                }

                // --- 3. Validasi Duplikasi Pengukuran ---
                $sudahAda = Pengukuran::where('balita_id', $balita->id)
                    ->whereDate('tanggal_ukur', $tglUkur)
                    ->exists();

                if ($sudahAda) {
                    $this->errors[] = "Baris {$baris}: Pengukuran {$nama} pada {$tglUkur->format('d/m/Y')} sudah ada, dilewati.";
                    continue;
                }

                // --- 4. Proses Simpan Pengukuran ---
                $umur = (int) $tglLahir->diffInMonths($tglUkur);

                $p = new Pengukuran([
                    'balita_id'       => $balita->id,
                    'dicatat_oleh'    => $this->dicatatOleh,
                    'tanggal_ukur'    => $tglUkur,
                    'umur_bulan'      => $umur,
                    'berat_badan_kg'  => $bb,
                    'tinggi_badan_cm' => $tb,
                ]);

                // Hitung rumus Z-Score dan Status Gizi
                app(ZScoreCalculator::class)->calculate($p);
                
                // Simpan ke database
                $p->save();

                $this->pengukuranBaru++;
                $this->berhasil++;
            }
            break; // Hanya baca sheet pertama
        }

        $reader->close();
    }
}