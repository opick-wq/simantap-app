<?php

namespace App\Http\Controllers;

use App\Models\Posyandu;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PosyanduController extends Controller
{
    public function index(Request $request): Response
    {
        $search     = $request->input('search');
        $kecamatan  = $request->input('kecamatan');
        $kelurahan  = $request->input('kelurahan');
        $sortBy     = in_array($request->input('sort'), ['nama','total_balita','kelurahan','kecamatan','jadwal','aktif']) ? $request->input('sort') : 'nama';
        $sortDir    = $request->input('dir') === 'desc' ? 'desc' : 'asc';

        $query = Posyandu::with('wilayah')
            ->withCount(['balita' => fn($q) => $q->where('aktif', true)]);

        if ($search) {
            $query->where(fn($q) => $q
                ->where('nama', 'like', "%{$search}%")
                ->orWhereHas('wilayah', fn($w) => $w
                    ->where('nama', 'like', "%{$search}%")
                    ->orWhere('kecamatan', 'like', "%{$search}%")));
        }

        if ($kecamatan) {
            $query->whereHas('wilayah', fn($w) => $w->where('kecamatan', $kecamatan));
        }

        if ($kelurahan) {
            $query->where('wilayah_id', $kelurahan);
        }

        $wilayahCol = '(SELECT %s FROM wilayah WHERE wilayah.id = posyandu.wilayah_id)';
        match ($sortBy) {
            'total_balita' => $query->orderBy('balita_count', $sortDir),
            'kelurahan'    => $query->orderByRaw(sprintf($wilayahCol, 'nama') . " $sortDir"),
            'kecamatan'    => $query->orderByRaw(sprintf($wilayahCol, 'kecamatan') . " $sortDir"),
            'jadwal'       => $query->orderBy('jadwal_minggu', $sortDir)->orderBy('jadwal_hari', $sortDir),
            'aktif'        => $query->orderBy('aktif', $sortDir)->orderBy('nama'),
            default        => $query->orderByRaw(sprintf($wilayahCol, 'kecamatan'))->orderBy('nama', $sortDir),
        };

        $posyandu = $query->get()
            ->map(fn($p) => [
                'id'           => $p->id,
                'nama'         => $p->nama,
                'alamat'       => $p->alamat,
                'wilayah_id'   => $p->wilayah_id,
                'wilayah'      => $p->wilayah?->nama ?? '-',
                'kecamatan'    => $p->wilayah?->kecamatan ?? '-',
                'kabupaten'    => $p->wilayah?->kabupaten ?? '-',
                'jadwal_minggu' => $p->jadwal_minggu,
                'jadwal_hari'  => $p->jadwal_hari,
                'jadwal_jam'   => $p->jadwal_jam,
                'aktif'        => $p->aktif,
                'total_balita' => $p->balita_count,
            ]);

        // Opsi filter
        $kecamatans = Wilayah::select('kecamatan')->distinct()->orderBy('kecamatan')->pluck('kecamatan');
        $kelurahans = Wilayah::orderBy('nama')
            ->when($kecamatan, fn($q) => $q->where('kecamatan', $kecamatan))
            ->get(['id','nama','kecamatan']);

        return Inertia::render('Posyandu/Index', [
            'posyandu'   => $posyandu,
            'kecamatans' => $kecamatans,
            'kelurahans' => $kelurahans,
            'filters'    => compact('search','kecamatan','kelurahan','sortBy','sortDir'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Posyandu/Create', [
            'wilayah'     => Wilayah::orderBy('kecamatan')->orderBy('nama')->get(['id', 'nama', 'kecamatan']),
            'hariOptions' => ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'],
            'mingguOptions' => [1, 2, 3, 4],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'wilayah_id'    => 'nullable|exists:wilayah,id',
            'nama'          => 'required|string|max:100|unique:posyandu,nama',
            'alamat'        => 'required|string',
            'jadwal_minggu' => 'nullable|integer|between:1,4',
            'jadwal_hari'   => 'nullable|string|max:20',
            'jadwal_jam'    => 'nullable|date_format:H:i,H:i:s',
        ]);

        $posyandu = Posyandu::create($data);

        return redirect()->route('posyandu.index')
            ->with('success', "Posyandu {$posyandu->nama} berhasil ditambahkan.");
    }

    public function edit(Posyandu $posyandu): Response
    {
        return Inertia::render('Posyandu/Edit', [
            'posyandu'      => $posyandu,
            'wilayah'       => Wilayah::orderBy('kecamatan')->orderBy('nama')->get(['id', 'nama', 'kecamatan']),
            'hariOptions'   => ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'],
            'mingguOptions' => [1, 2, 3, 4],
        ]);
    }

    public function update(Request $request, Posyandu $posyandu)
    {
        $data = $request->validate([
            'wilayah_id'    => 'nullable|exists:wilayah,id',
            'nama'          => "required|string|max:100|unique:posyandu,nama,{$posyandu->id}",
            'alamat'        => 'required|string',
            'jadwal_minggu' => 'nullable|integer|between:1,4',
            'jadwal_hari'   => 'nullable|string|max:20',
            'jadwal_jam'    => 'nullable|date_format:H:i,H:i:s',
            'aktif'         => 'boolean',
        ]);

        $posyandu->update($data);

        return redirect()->route('posyandu.index')
            ->with('success', "Data Posyandu {$posyandu->nama} berhasil diperbarui.");
    }
}
