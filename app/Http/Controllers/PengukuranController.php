<?php

namespace App\Http\Controllers;

use App\Models\Balita;
use App\Models\Pengukuran;
use App\Models\Peringatan;
use App\Models\SesiPosyandu;
use App\Models\WhoZscoreReference;
use App\Services\EwsEngine;
use App\Services\ZScoreCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PengukuranController extends Controller
{
    public function create(Request $request): Response
    {
        $ids = Auth::user()->accessiblePosyanduIds();

        // Jika belum ada balita_id, tampilkan halaman pilih balita
        if (!$request->balita_id) {
            $daftarBalita = Balita::whereIn('posyandu_id', $ids)
                ->where('aktif', true)
                ->with('posyandu:id,nama')
                ->orderBy('nama')
                ->get(['id', 'nama', 'nik_balita', 'nama_ibu', 'jenis_kelamin', 'tanggal_lahir', 'posyandu_id']);

            return Inertia::render('Pengukuran/Create', [
                'balita'      => null,
                'daftarBalita'=> $daftarBalita,
                'sesi'        => null,
                'tanggal'     => today()->format('Y-m-d'),
                'riwayat'     => [],
            ]);
        }

        $balita = Balita::findOrFail($request->balita_id);
        $this->authorizeBalita($balita);

        $sesiHariIni = SesiPosyandu::where('posyandu_id', $balita->posyandu_id)
                                   ->whereDate('tanggal', today())
                                   ->first();

        return Inertia::render('Pengukuran/Create', [
            'balita'       => array_merge($balita->load('posyandu')->toArray(), [
                'tanggal_lahir_format' => $balita->tanggal_lahir->translatedFormat('d F Y'),
            ]),
            'daftarBalita' => [],
            'sesi'         => $sesiHariIni,
            'tanggal'      => today()->format('Y-m-d'),
            'riwayat'      => $balita->pengukuran()->latest('tanggal_ukur')->limit(3)->get()
                                ->map(fn($p) => array_merge($p->toArray(), [
                                    'tanggal_ukur' => $p->tanggal_ukur->format('d/m/Y'),
                                ])),
            'semuaRiwayat' => $balita->pengukuran()->orderBy('tanggal_ukur')->get()
                                ->map(fn($p) => [
                                    'umur_bulan'      => $p->umur_bulan,
                                    'berat_badan_kg'  => $p->berat_badan_kg,
                                    'tinggi_badan_cm' => $p->tinggi_badan_cm,
                                    'tanggal_ukur'    => $p->tanggal_ukur->format('Y-m-d'),
                                    'flag_ews'        => $p->flag_ews,
                                    'status_gizi'     => $p->status_gizi,
                                    'status_stunting' => $p->status_stunting,
                                    'label_status_gizi' => $p->status_gizi,
                                    'z_score_bb_u'    => $p->z_score_bb_u,
                                    'z_score_tb_u'    => $p->z_score_tb_u,
                                ]),
            'curveBbU'     => WhoZscoreReference::getCurveData('BB_U', $balita->jenis_kelamin),
            'curveTbU'     => WhoZscoreReference::getCurveData('TB_U', $balita->jenis_kelamin),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'balita_id'             => 'required|exists:balita,id',
            'sesi_id'               => 'nullable|exists:sesi_posyandu,id',
            'tanggal_ukur'          => 'required|date|before_or_equal:today',
            'berat_badan_kg'        => 'required|numeric|min:1|max:50',
            'tinggi_badan_cm'       => 'required|numeric|min:40|max:130',
            'posisi_ukur'           => 'nullable|in:terlentang,berdiri',
            'lingkar_lengan_atas_cm'=> 'nullable|numeric|min:8|max:25',
            'lingkar_kepala_cm'     => 'nullable|numeric|min:25|max:60',
            'catatan'               => 'nullable|string|max:500',
        ]);

        $balita = Balita::findOrFail($data['balita_id']);
        $this->authorizeBalita($balita);

        // umur_bulan dihitung sementara — Observer akan hitung ulang via ZScoreCalculator
        $data['umur_bulan']  = (int) $balita->tanggal_lahir->diffInMonths($data['tanggal_ukur']);
        $data['dicatat_oleh']= Auth::id();

        // Observer PengukuranObserver::created() otomatis terpicu setelah create()
        $pengukuran = Pengukuran::create($data);

        return redirect()->route('balita.show', $balita)
                         ->with('success', 'Pengukuran berhasil disimpan dan EWS telah dijalankan.');
    }

    public function show(Pengukuran $pengukuran): Response
    {
        $this->authorizeBalita($pengukuran->balita);

        $p = $pengukuran->load(['balita.posyandu', 'peringatan', 'pencatat', 'validator']);

        return Inertia::render('Pengukuran/Show', [
            'pengukuran'   => array_merge($p->toArray(), [
                'tanggal_ukur'      => $p->tanggal_ukur->format('d/m/Y'),
                'validated_at_label'=> $p->validated_at?->format('d/m/Y H:i'),
            ]),
            'bisaValidasi' => Auth::user()->canValidate(),
            'bisaEdit'     => !$pengukuran->is_validated && Auth::user()->canInputData(),
        ]);
    }

    public function edit(Pengukuran $pengukuran): Response
    {
        $this->authorizeBalita($pengukuran->balita);
        abort_if($pengukuran->is_validated, 403, 'Data sudah divalidasi Nakes dan tidak dapat diedit.');

        return Inertia::render('Pengukuran/Edit', [
            'pengukuran' => array_merge($pengukuran->load('balita.posyandu')->toArray(), [
                'tanggal_ukur' => $pengukuran->tanggal_ukur->format('Y-m-d'),
            ]),
        ]);
    }

    public function update(Request $request, Pengukuran $pengukuran)
    {
        $this->authorizeBalita($pengukuran->balita);
        abort_if($pengukuran->is_validated, 403, 'Data sudah divalidasi Nakes dan tidak dapat diedit.');

        $data = $request->validate([
            'tanggal_ukur'          => 'required|date|before_or_equal:today',
            'berat_badan_kg'        => 'required|numeric|min:1|max:50',
            'tinggi_badan_cm'       => 'required|numeric|min:40|max:130',
            'posisi_ukur'           => 'nullable|in:terlentang,berdiri',
            'lingkar_lengan_atas_cm'=> 'nullable|numeric|min:8|max:25',
            'lingkar_kepala_cm'     => 'nullable|numeric|min:25|max:60',
            'catatan'               => 'nullable|string|max:500',
        ]);

        $balita = $pengukuran->balita;
        $data['umur_bulan'] = (int) $balita->tanggal_lahir->diffInMonths($data['tanggal_ukur']);

        $pengukuran->update($data);

        // Hitung ulang Z-score
        $calculator = app(ZScoreCalculator::class);
        $calculator->calculate($pengukuran->fresh());

        // Hapus peringatan lama dari pengukuran ini, jalankan EWS ulang
        Peringatan::where('pengukuran_id', $pengukuran->id)->delete();
        $engine = app(EwsEngine::class);
        $engine->run($pengukuran->fresh());

        return redirect()->route('pengukuran.show', $pengukuran)
                         ->with('success', 'Data pengukuran berhasil diperbarui dan EWS dijalankan ulang.');
    }

    public function destroy(Pengukuran $pengukuran)
    {
        $user = Auth::user();
        $this->authorizeBalita($pengukuran->balita);

        abort_if($pengukuran->is_validated, 403, 'Data yang sudah divalidasi tidak dapat dihapus.');

        if ($user->role === 'kader') {
            abort_if($pengukuran->dicatat_oleh !== $user->id, 403,
                'Anda hanya dapat menghapus pengukuran yang Anda catat sendiri.');
        }

        $balitaId = $pengukuran->balita_id;
        $pengukuran->delete();

        return redirect()->route('balita.show', $balitaId)
            ->with('success', 'Data pengukuran berhasil dihapus.');
    }

    public function validasi(Request $request, Pengukuran $pengukuran)
    {
        $this->authorizeBalita($pengukuran->balita);
        abort_unless(Auth::user()->canValidate(), 403);

        $data = $request->validate([
            'catatan_validasi' => 'nullable|string|max:500',
        ]);

        $pengukuran->update([
            'is_validated'     => true,
            'validated_by'     => Auth::id(),
            'validated_at'     => now(),
            'catatan_validasi' => $data['catatan_validasi'] ?? null,
        ]);

        return back()->with('success', 'Pengukuran berhasil divalidasi.');
    }

    private function authorizeBalita(Balita $balita): void
    {
        $ids = Auth::user()->accessiblePosyanduIds();
        abort_unless(in_array($balita->posyandu_id, $ids), 403);
    }
}
