<?php

namespace App\Http\Controllers;

use App\Models\Balita;
use App\Models\Posyandu;
use App\Models\User;
use App\Models\UsulanNonaktif;
use App\Models\WhoZscoreReference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class BalitaController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $ids  = $user->accessiblePosyanduIds();

        // Filter posyandu (petugas/admin bisa pilih satu posyandu)
        $filterIds = $ids;
        if ($request->filled('posyandu_id') && in_array((int) $request->posyandu_id, $ids)) {
            $filterIds = [(int) $request->posyandu_id];
        }

        $query = Balita::with(['posyandu', 'pengukuran' => fn($q) => $q->orderBy('tanggal_ukur', 'desc')->limit(1)])
                       ->whereIn('posyandu_id', $filterIds)
                       ->where('aktif', true);

        if ($request->filled('search')) {
            $query->search($request->search);
        }
        // Helper: ambil ID pengukuran terakhir per balita
        $latestPengukuranIds = \App\Models\Pengukuran::selectRaw('MAX(id) as id')
            ->groupBy('balita_id')
            ->pluck('id');

        if ($request->filled('status_gizi')) {
            $vals = (array) $request->input('status_gizi');
            $balitaIds = \App\Models\Pengukuran::whereIn('id', $latestPengukuranIds)
                ->whereIn('status_gizi', $vals)
                ->pluck('balita_id');
            $query->whereIn('id', $balitaIds);
        }
        if ($request->filled('flag_ews')) {
            $vals = (array) $request->input('flag_ews');
            $balitaIds = \App\Models\Pengukuran::whereIn('id', $latestPengukuranIds)
                ->whereIn('flag_ews', $vals)
                ->pluck('balita_id');
            $query->whereIn('id', $balitaIds);
        }
        if ($request->boolean('belum_diukur')) {
            $sudahDiukur = \App\Models\Pengukuran::whereMonth('tanggal_ukur', now()->month)
                ->whereYear('tanggal_ukur', now()->year)
                ->pluck('balita_id');
            $query->whereNotIn('id', $sudahDiukur);
        }
        if ($request->boolean('double_burden')) {
            $query->whereHas('pengukuran', fn($q) => $q
                ->whereIn('status_wasting', ['SANGAT_KURUS', 'KURUS'])
                ->whereIn('status_stunting', ['PENDEK', 'SANGAT_PENDEK'])
                ->orderBy('tanggal_ukur', 'desc')
                ->limit(1));
        }

        if ($request->filled('status_wasting')) {
            $val = $request->status_wasting;
            $balitaIds = \App\Models\Pengukuran::whereIn('id', $latestPengukuranIds)
                ->when($val === 'WASTING',
                    fn($q) => $q->whereIn('status_wasting', ['SANGAT_KURUS', 'KURUS']),
                    fn($q) => $q->where('status_wasting', $val)
                )
                ->pluck('balita_id');
            $query->whereIn('id', $balitaIds);
        }
        if ($request->filled('status_stunting')) {
            $vals = (array) $request->input('status_stunting');
            if (in_array('STUNTING', $vals)) {
                $vals = array_merge(
                    array_diff($vals, ['STUNTING']),
                    ['PENDEK', 'SANGAT_PENDEK']
                );
            }
            $balitaIds = \App\Models\Pengukuran::whereIn('id', $latestPengukuranIds)
                ->whereIn('status_stunting', $vals)
                ->pluck('balita_id');
            $query->whereIn('id', $balitaIds);
        }

        if ($request->filled('status_imt_u')) {
            $vals = (array) $request->input('status_imt_u');
            $balitaIds = \App\Models\Pengukuran::whereIn('id', $latestPengukuranIds)
                ->whereIn('status_imt_u', $vals)
                ->pluck('balita_id');
            $query->whereIn('id', $balitaIds);
        }

        if ($request->filled('status_kbb')) {
            $vals = (array) $request->input('status_kbb');
            $balitaIds = \App\Models\Pengukuran::whereIn('id', $latestPengukuranIds)
                ->whereIn('status_kbb', $vals)
                ->pluck('balita_id');
            $query->whereIn('id', $balitaIds);
        }

        // Sort
        $sortCol = $request->input('sort', 'nama');
        $sortDir = $request->input('dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $sortableOnBalita = ['nama', 'tanggal_lahir'];
        if (in_array($sortCol, $sortableOnBalita)) {
            $query->orderBy($sortCol, $sortDir);
        } else {
            // Sort berdasarkan kolom pengukuran terakhir
            $pengukuranCol = match($sortCol) {
                'bb'          => 'berat_badan_kg',
                'tb'          => 'tinggi_badan_cm',
                'tanggal_ukur'=> 'tanggal_ukur',
                default       => null,
            };
            if ($pengukuranCol) {
                $query->orderBy(
                    \App\Models\Pengukuran::select($pengukuranCol)
                        ->whereColumn('balita_id', 'balita.id')
                        ->latest('tanggal_ukur')
                        ->limit(1),
                    $sortDir
                );
            } else {
                $query->orderBy('nama', 'asc');
            }
        }

        $balita = $query->paginate(20)->withQueryString()
                        ->through(fn($b) => [
                            'id'             => $b->id,
                            'nik_balita'     => $b->nik_balita,
                            'nama'           => $b->nama,
                            'jenis_kelamin'  => $b->jenis_kelamin,
                            'umur_lengkap'   => $b->umur_lengkap,
                            'umur_bulan'     => $b->pengukuran->first()?->umur_bulan,
                            'nama_ibu'       => $b->nama_ibu,
                            'posyandu_nama'  => $b->posyandu->nama,
                            'status_gizi'    => $b->pengukuran->first()?->status_gizi,
                            'status_stunting'=> $b->pengukuran->first()?->status_stunting,
                            'status_wasting'  => $b->pengukuran->first()?->status_wasting,
                            'status_imt_u'   => $b->pengukuran->first()?->status_imt_u,
                            'flag_ews'       => $b->pengukuran->first()?->flag_ews ?? 'HIJAU',
                            'bb_terakhir'    => $b->pengukuran->first()?->berat_badan_kg,
                            'tb_terakhir'    => $b->pengukuran->first()?->tinggi_badan_cm,
                            'tanggal_ukur'   => $b->pengukuran->first()?->tanggal_ukur?->format('d/m/Y'),
                        ]);

        return Inertia::render('Balita/Index', [
            'balita'   => $balita,
            'filters'  => [
                'search'          => $request->search,
                'posyandu_id'     => $request->posyandu_id,
                'flag_ews'        => $request->input('flag_ews', []),
                'status_gizi'     => $request->input('status_gizi', []),
                'status_stunting' => $request->input('status_stunting', []),
                'status_wasting'  => $request->input('status_wasting'),
                'belum_diukur'    => $request->belum_diukur,
                'double_burden'   => $request->double_burden,
                'sort'            => $sortCol,
                'dir'             => $sortDir,
            ],
            'posyandu' => Posyandu::whereIn('id', $ids)->get(['id', 'nama']),
        ]);
    }

    public function create(): Response
    {
        $ids = Auth::user()->accessiblePosyanduIds();
        return Inertia::render('Balita/Create', [
            'posyandu' => Posyandu::whereIn('id', $ids)->get(['id', 'nama']),
        ]);
    }

    public function store(Request $request)
    {
        $accessibleIds = Auth::user()->accessiblePosyanduIds();

        $data = $request->validate([
            'posyandu_id'          => ['required', 'exists:posyandu,id', function ($attr, $value, $fail) use ($accessibleIds) {
                if (!in_array((int) $value, $accessibleIds)) $fail('Posyandu tidak dalam wewenang Anda.');
            }],
            'nik_balita'           => ['required', 'string', 'digits:16', function ($attr, $value, $fail) use ($request) {
                // Cek duplikat NIK dalam kabupaten/kota yang sama
                $posyandu = \App\Models\Posyandu::with('wilayah')->find($request->posyandu_id);
                if (!$posyandu?->wilayah) return;
                $kabupaten = $posyandu->wilayah->kabupaten;
                $exists = \App\Models\Balita::where('nik_balita', $value)
                    ->whereHas('posyandu.wilayah', fn($q) => $q->where('kabupaten', $kabupaten))
                    ->exists();
                if ($exists) $fail('NIK sudah terdaftar.');
            }],
            'nama'                 => 'required|string|max:100',
            'tanggal_lahir'        => 'required|date|before:today',
            'jenis_kelamin'        => 'required|in:L,P',
            'nik_ibu'              => 'nullable|string|max:20',
            'nama_ibu'             => 'required|string|max:100',
            'nomor_hp_ibu'         => 'nullable|string|max:20',
            'nama_ayah'            => 'nullable|string|max:100',
            'alamat'               => 'required|string',
            'rt_rw'                => 'nullable|string|max:10',
            'berat_lahir_gram'     => 'nullable|numeric|min:0.5|max:6',
            'panjang_lahir_cm'     => 'nullable|numeric|min:30|max:70',
            'prematur'             => 'boolean',
            'usia_gestasi_minggu'  => 'nullable|integer|min:24|max:36',
        ]);

        $balita = Balita::create($data);

        return redirect()->route('balita.show', $balita)
                         ->with('success', "Data balita {$balita->nama} berhasil disimpan.");
    }

    public function show(Balita $balita): Response
    {
        $this->authorizeBalita($balita);

        $pengukuran = $balita->pengukuran()
                             ->with('peringatan')
                             ->orderBy('tanggal_ukur', 'desc')
                             ->get()
                             ->map(fn($p) => array_merge($p->toArray(), [
                                 'tanggal_ukur' => $p->tanggal_ukur->format('d/m/Y'),
                             ]));

        // Data kurva WHO untuk grafik
        $gender    = $balita->jenis_kelamin;
        $curveBbU   = WhoZscoreReference::getCurveData('BB_U', $gender);
        $curveTbU   = WhoZscoreReference::getCurveData('TB_U', $gender);
        $curveBbTb  = WhoZscoreReference::getCurveData('BB_TB', $gender);
        $curveImtU  = WhoZscoreReference::getCurveData('IMT_U', $gender);

        $riwayatIntervensi = $balita->tindakLanjut()
            ->with(['pencatat:id,nama', 'peringatan:id,jenis_peringatan,level_risiko,pesan'])
            ->latest()
            ->get()
            ->map(fn($t) => [
                'id'                   => $t->id,
                'tanggal'              => $t->created_at->format('d/m/Y H:i'),
                'jenis_tindakan'       => $t->jenis_tindakan,
                'dilaporkan_ke_atasan' => $t->dilaporkan_ke_atasan,
                'catatan'              => $t->catatan,
                'status_akhir'         => $t->status_akhir,
                'pencatat'             => $t->pencatat?->nama,
                'peringatan_jenis'     => $t->peringatan?->jenis_peringatan,
                'peringatan_level'     => $t->peringatan?->level_risiko,
                'peringatan_pesan'     => $t->peringatan?->pesan,
                'peringatan_id'        => $t->peringatan_id,
            ]);

        $akunOrtu = $balita->user_id_ortu
            ? User::find($balita->user_id_ortu, ['id', 'nama', 'email', 'nomor_hp', 'aktif'])
            : null;

        $usulanAktif = $balita->usulanNonaktif()
            ->whereIn('status', ['DIUSULKAN', 'DITERUSKAN'])
            ->with(['pengusul:id,nama', 'nakes:id,nama'])
            ->first();

        $riwayatUsulan = $balita->usulanNonaktif()
            ->whereIn('status', ['DISETUJUI', 'DITOLAK'])
            ->with(['pengusul:id,nama', 'nakes:id,nama', 'petugas:id,nama'])
            ->latest()
            ->limit(3)
            ->get()
            ->map(fn($u) => [
                'id'               => $u->id,
                'status'           => $u->status,
                'label_status'     => $u->labelStatus(),
                'alasan'           => $u->alasan,
                'catatan_nakes'    => $u->catatan_nakes,
                'catatan_petugas'  => $u->catatan_petugas,
                'tindakan_akhir'   => $u->tindakan_akhir,
                'pengusul'         => $u->pengusul?->nama,
                'nakes'            => $u->nakes?->nama,
                'petugas'          => $u->petugas?->nama,
                'tanggal'          => $u->created_at->format('d/m/Y'),
            ]);

        $roleUser = Auth::user()->role;
        $adaUsulanAktif = (bool) $usulanAktif;

        return Inertia::render('Balita/Show', [
            'balita'            => $balita->load('posyandu'),
            'pengukuran'        => $pengukuran,
            'curveBbU'          => $curveBbU,
            'curveTbU'          => $curveTbU,
            'curveBbTb'         => $curveBbTb,
            'curveImtU'         => $curveImtU,
            'riwayatIntervensi' => $riwayatIntervensi,
            'akunOrtu'          => $akunOrtu,
            'bisaValidasi'      => Auth::user()->canValidate(),
            'roleUser'          => $roleUser,
            'usulanAktif'       => $usulanAktif ? [
                'id'           => $usulanAktif->id,
                'status'       => $usulanAktif->status,
                'label_status' => $usulanAktif->labelStatus(),
                'alasan'       => $usulanAktif->alasan,
                'catatan_nakes'=> $usulanAktif->catatan_nakes,
                'pengusul'     => $usulanAktif->pengusul?->nama,
                'nakes'        => $usulanAktif->nakes?->nama,
            ] : null,
            'riwayatUsulan'     => $riwayatUsulan,
            'adaUsulanAktif'    => $adaUsulanAktif,
        ]);
    }

    public function edit(Balita $balita): Response
    {
        $this->authorizeBalita($balita);
        $ids = Auth::user()->accessiblePosyanduIds();
        return Inertia::render('Balita/Edit', [
            'balita'   => $balita,
            'posyandu' => Posyandu::whereIn('id', $ids)->get(['id', 'nama']),
        ]);
    }

    public function update(Request $request, Balita $balita)
    {
        $this->authorizeBalita($balita);
        $data = $request->validate([
            'nik_balita'   => ['required', 'string', 'digits:16', function ($attr, $value, $fail) use ($balita) {
                $kabupaten = $balita->posyandu->wilayah->kabupaten ?? null;
                if (!$kabupaten) return;
                $exists = \App\Models\Balita::where('nik_balita', $value)
                    ->where('id', '!=', $balita->id)
                    ->whereHas('posyandu.wilayah', fn($q) => $q->where('kabupaten', $kabupaten))
                    ->exists();
                if ($exists) $fail('NIK sudah terdaftar.');
            }],
            'nama'          => 'required|string|max:100',
            'tanggal_lahir' => 'required|date|before:today',
            'nama_ibu'      => 'required|string|max:100',
            'nomor_hp_ibu'  => 'nullable|string|max:20',
            'alamat'        => 'required|string',
        ]);
        $balita->update($data);
        return back()->with('success', 'Data balita berhasil diperbarui.');
    }

    public function buatAkunOrtu(Request $request, Balita $balita)
    {
        $this->authorizeBalita($balita);

        if ($balita->user_id_ortu) {
            return back()->withErrors(['akun' => 'Balita ini sudah memiliki akun orang tua.']);
        }

        $data = $request->validate([
            'nama'      => 'required|string|max:100',
            'nomor_hp'  => 'required|string|max:20',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6',
        ]);

        $user = User::create([
            'nama'        => $data['nama'],
            'nomor_hp'    => $data['nomor_hp'],
            'email'       => $data['email'],
            'password'    => Hash::make($data['password']),
            'role'        => 'orang_tua',
            'posyandu_id' => $balita->posyandu_id,
            'aktif'       => true,
        ]);

        // Sinkronkan nomor HP dan nama ibu ke data balita
        $balita->update([
            'user_id_ortu'  => $user->id,
            'nama_ibu'      => $data['nama'],
            'nomor_hp_ibu'  => $data['nomor_hp'],
        ]);

        return back()->with('success', "Akun orang tua untuk {$balita->nama} berhasil dibuat.");
    }

    public function hapusAkunOrtu(Balita $balita)
    {
        $this->authorizeBalita($balita);

        if ($balita->user_id_ortu) {
            User::destroy($balita->user_id_ortu);
            $balita->update(['user_id_ortu' => null]);
        }

        return back()->with('success', 'Akun orang tua berhasil dihapus.');
    }

    private function authorizeBalita(Balita $balita): void
    {
        $ids = Auth::user()->accessiblePosyanduIds();
        abort_unless(in_array($balita->posyandu_id, $ids), 403);
    }
}
