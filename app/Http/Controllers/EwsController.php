<?php

namespace App\Http\Controllers;

use App\Models\Peringatan;
use App\Models\TindakLanjut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class EwsController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $ids  = $user->accessiblePosyanduIds();

        $filterPosyandu = $request->filled('posyandu_id')
            ? array_intersect([(int) $request->posyandu_id], $ids)
            : $ids;

        $query = Peringatan::with(['balita.posyandu', 'pengukuran'])
            ->whereHas('balita', fn($q) => $q->whereIn('posyandu_id', $filterPosyandu));

        if ($request->filled('level')) {
            $query->where('level_risiko', strtoupper($request->level));
        }

        $isNakes = in_array($user->role, ['nakes']);

        if ($request->filled('status')) {
            $query->where('status_tindak_lanjut', strtoupper($request->status));
        } elseif ($isNakes) {
            // Nakes: antrean bersih — hanya yang sudah diteruskan Kader atau diinput Nakes sendiri,
            // plus pengecualian: MERAH yang belum diteruskan tetap tampil
            $query->where(function ($q) {
                $q->whereIn('status_tindak_lanjut', ['DILAPORKAN', 'DALAM_PROSES'])
                  ->orWhere(function ($q2) {
                      // MERAH belum diteruskan → tetap tampil sebagai peringatan darurat
                      $q2->where('status_tindak_lanjut', 'BELUM')
                         ->where('level_risiko', 'MERAH');
                  })
                  ->orWhere(function ($q3) {
                      // Input langsung oleh Nakes/admin → tidak perlu forwarding Kader
                      $q3->where('status_tindak_lanjut', 'BELUM')
                         ->whereHas('pengukuran', fn($q4) => $q4->whereHas(
                             'pencatat', fn($q5) => $q5->whereIn('role', ['nakes', 'admin'])
                         ));
                  });
            });
        } else {
            // Kader, petugas, admin: tampilkan semua yang belum selesai
            $query->whereIn('status_tindak_lanjut', ['BELUM', 'DILAPORKAN', 'DALAM_PROSES']);
        }

        $peringatan = $query
            ->orderByRaw("CASE WHEN level_risiko='MERAH' THEN 0 WHEN level_risiko='KUNING' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString()
            ->through(fn($p) => [
                'id'                   => $p->id,
                'level_risiko'         => $p->level_risiko,
                'jenis_peringatan'     => $p->label_jenis,
                'pesan'                => $p->pesan,
                'status_tindak_lanjut' => $p->status_tindak_lanjut,
                'belum_diteruskan'     => $p->status_tindak_lanjut === 'BELUM',
                'created_at'           => $p->created_at->format('d/m/Y H:i'),
                'created_at_human'     => $p->created_at->diffForHumans(),
                'bulan_label'          => ['', 'Januari','Februari','Maret','April','Mei','Juni',
                                            'Juli','Agustus','September','Oktober','November','Desember']
                                           [(int) $p->created_at->format('n')] . ' ' . $p->created_at->format('Y'),
                'is_menunggak'         => $p->created_at->format('Y-m') !== now()->format('Y-m'),
                'balita_nama'          => $p->balita->nama,
                'balita_id'            => $p->balita_id,
                'umur_lengkap'         => $p->balita->umur_lengkap,
                'posyandu_nama'        => $p->balita->posyandu->nama,
                'bb_terakhir'          => $p->pengukuran?->berat_badan_kg,
                'z_score_bb_u'         => $p->pengukuran?->z_score_bb_u,
            ]);

        $ringkasan = [
            'total_merah'      => Peringatan::whereHas('balita', fn($q) => $q->whereIn('posyandu_id', $ids))
                                    ->where('level_risiko', 'MERAH')
                                    ->whereIn('status_tindak_lanjut', ['BELUM','DILAPORKAN','DALAM_PROSES'])
                                    ->count(),
            'total_kuning'     => Peringatan::whereHas('balita', fn($q) => $q->whereIn('posyandu_id', $ids))
                                    ->where('level_risiko', 'KUNING')
                                    ->whereIn('status_tindak_lanjut', ['BELUM','DILAPORKAN','DALAM_PROSES'])
                                    ->count(),
            'menunggu_nakes'   => Peringatan::whereHas('balita', fn($q) => $q->whereIn('posyandu_id', $ids))
                                    ->where('status_tindak_lanjut', 'DILAPORKAN')
                                    ->count(),
        ];

        $posyandu = \App\Models\Posyandu::whereIn('id', $ids)->orderBy('nama')->get(['id', 'nama']);

        return Inertia::render('Ews/Index', [
            'peringatan' => $peringatan,
            'ringkasan'  => $ringkasan,
            'posyandu'   => $posyandu,
            'filters'    => [
                'posyandu_id' => $request->posyandu_id,
                'level'       => $request->level,
                'status'      => $request->status,
            ],
            'roleUser' => $user->role,
        ]);
    }

    public function show(Peringatan $peringatan): Response
    {
        $user = Auth::user();
        $ids  = $user->accessiblePosyanduIds();
        abort_unless(in_array($peringatan->balita->posyandu_id, $ids), 403);

        $p = $peringatan->load(['balita.posyandu', 'pengukuran', 'penanggungjawab', 'pelapor']);

        $riwayatTindakan = $peringatan->tindakLanjut()
            ->with('pencatat:id,nama')
            ->latest()
            ->get()
            ->map(fn($t) => [
                'id'                   => $t->id,
                'jenis_tindakan'       => $t->jenis_tindakan,
                'dilaporkan_ke_atasan' => $t->dilaporkan_ke_atasan,
                'catatan'              => $t->catatan,
                'status_akhir'         => $t->status_akhir,
                'pencatat'             => $t->pencatat?->nama,
                'created_at'           => $t->created_at->format('d/m/Y H:i'),
            ]);

        return Inertia::render('Ews/Show', [
            'peringatan'      => array_merge($p->toArray(), [
                'jenis_tindakan'            => $p->jenis_tindakan ?? [],
                'dilaporkan_ke_atasan'      => (bool) $p->dilaporkan_ke_atasan,
                'sudah_verifikasi_ulang'    => (bool) $p->sudah_verifikasi_ulang,
                'sudah_eskalasi_nakes'      => (bool) $p->sudah_eskalasi_nakes,
                'diverifikasi_pada'         => $p->diverifikasi_pada?->format('d/m/Y H:i'),
                'dilaporkan_pada'           => $p->dilaporkan_pada?->format('d/m/Y H:i'),
                'diverifikasi_oleh_nama'    => $p->diverifikasi_oleh
                    ? \App\Models\User::find($p->diverifikasi_oleh)?->nama
                    : null,
                'pelapor_nama'              => $p->pelapor?->nama,
                'pengukuran_is_validated'   => (bool) $p->pengukuran->is_validated,
            ]),
            'riwayatTindakan' => $riwayatTindakan,
            'roleUser'        => $user->role,
            'bisaVerifikasi'  => in_array($user->role, ['petugas', 'admin']),
        ]);
    }

    /** Aksi kader: laporan operasional (bukan intervensi klinis) */
    public function laporKader(Request $request, Peringatan $peringatan)
    {
        $ids = Auth::user()->accessiblePosyanduIds();
        abort_unless(in_array($peringatan->balita->posyandu_id, $ids), 403);
        abort_unless($peringatan->status_tindak_lanjut === 'BELUM', 422);

        $data = $request->validate([
            'sudah_verifikasi_ulang' => 'required|boolean',
            'sudah_eskalasi_nakes'   => 'required|boolean',
            'catatan_kader'          => 'nullable|string|max:1000',
        ]);

        $peringatan->update([
            'status_tindak_lanjut'   => 'DILAPORKAN',
            'sudah_verifikasi_ulang' => $data['sudah_verifikasi_ulang'],
            'sudah_eskalasi_nakes'   => $data['sudah_eskalasi_nakes'],
            'catatan_kader'          => $data['catatan_kader'] ?? null,
            'dilaporkan_oleh'        => Auth::id(),
            'dilaporkan_pada'        => now(),
        ]);

        return back()->with('success', 'Laporan kader berhasil dikirim ke Nakes.');
    }

    /** Aksi nakes: intervensi klinis */
    public function updateStatus(Request $request, Peringatan $peringatan)
    {
        $user = Auth::user();
        $ids  = $user->accessiblePosyanduIds();
        abort_unless(in_array($peringatan->balita->posyandu_id, $ids), 403);
        abort_unless(in_array($user->role, ['nakes', 'petugas', 'admin']), 403);

        $rules = [
            'status_tindak_lanjut' => 'required|in:DALAM_PROSES,SELESAI',
            'catatan_petugas'      => 'nullable|string|max:1000',
            'jenis_tindakan'       => 'nullable|array',
            'jenis_tindakan.*'     => 'string|in:tambahan_gizi,edukasi,rujuk,konsultasi,suplemen,lainnya',
            'dilaporkan_ke_atasan' => 'boolean',
        ];

        if ($request->status_tindak_lanjut === 'SELESAI') {
            $rules['jenis_tindakan'] = 'required|array|min:1';
        }

        $data = $request->validate($rules);

        // Selalu catat ke riwayat tindak lanjut agar tercatat di profil balita
        TindakLanjut::create([
            'peringatan_id'        => $peringatan->id,
            'balita_id'            => $peringatan->balita_id,
            'dicatat_oleh'         => Auth::id(),
            'jenis_tindakan'       => $data['jenis_tindakan'] ?? [],
            'dilaporkan_ke_atasan' => $data['dilaporkan_ke_atasan'] ?? false,
            'catatan'              => $data['catatan_petugas'] ?? null,
            'status_akhir'         => $data['status_tindak_lanjut'],
        ]);

        $peringatan->update([
            'status_tindak_lanjut' => $data['status_tindak_lanjut'],
            'catatan_petugas'      => $data['catatan_petugas'] ?? $peringatan->catatan_petugas,
            'jenis_tindakan'       => $data['jenis_tindakan'] ?? $peringatan->jenis_tindakan,
            'dilaporkan_ke_atasan' => $data['dilaporkan_ke_atasan'] ?? $peringatan->dilaporkan_ke_atasan,
            'ditindaklanjuti_oleh' => Auth::id(),
            'ditindaklanjuti_pada' => now(),
        ]);

        return back()->with('success', 'Tindak lanjut klinis berhasil disimpan.');
    }

    /** Aksi petugas/admin: verifikasi supervisi */
    public function verifikasi(Request $request, Peringatan $peringatan)
    {
        $ids = Auth::user()->accessiblePosyanduIds();
        abort_unless(in_array($peringatan->balita->posyandu_id, $ids), 403);
        abort_unless(in_array(Auth::user()->role, ['petugas', 'admin']), 403);
        abort_unless($peringatan->status_tindak_lanjut === 'SELESAI', 422);

        $data = $request->validate([
            'status_verifikasi'  => 'required|in:DISETUJUI,DITOLAK',
            'catatan_verifikasi' => 'nullable|string|max:1000',
        ]);

        $peringatan->update([
            'status_verifikasi'  => $data['status_verifikasi'],
            'catatan_verifikasi' => $data['catatan_verifikasi'] ?? null,
            'diverifikasi_oleh'  => Auth::id(),
            'diverifikasi_pada'  => now(),
        ]);

        $label = $data['status_verifikasi'] === 'DISETUJUI' ? 'disetujui' : 'ditolak — perlu tindak ulang';
        return back()->with('success', "Tindak lanjut nakes berhasil $label.");
    }
}
