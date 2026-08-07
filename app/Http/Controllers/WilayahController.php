<?php

namespace App\Http\Controllers;

use App\Models\Wilayah;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WilayahController extends Controller
{
    public function index(Request $request): Response
    {
        $search    = $request->input('search');
        $kecamatan = $request->input('kecamatan');

        $query = Wilayah::withCount('posyandu')->orderBy('kecamatan')->orderBy('nama');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('kecamatan', 'like', "%{$search}%")
                  ->orWhere('kabupaten', 'like', "%{$search}%");
            });
        }

        if ($kecamatan) {
            $query->where('kecamatan', $kecamatan);
        }

        $wilayah    = $query->paginate(20)->withQueryString();
        $kecamatans = Wilayah::select('kecamatan')->distinct()->orderBy('kecamatan')->pluck('kecamatan');

        return Inertia::render('Wilayah/Index', [
            'wilayah'    => $wilayah,
            'kecamatans' => $kecamatans,
            'filters'    => ['search' => $search, 'kecamatan' => $kecamatan],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'      => 'required|string|max:100',
            'kecamatan' => 'required|string|max:100',
            'kabupaten' => 'required|string|max:100',
            'provinsi'  => 'required|string|max:100',
            'kode_bps'  => 'nullable|string|max:20|unique:wilayah,kode_bps',
        ]);

        Wilayah::create($data);

        return back()->with('success', 'Wilayah berhasil ditambahkan.');
    }

    public function update(Request $request, Wilayah $wilayah)
    {
        $data = $request->validate([
            'nama'      => 'required|string|max:100',
            'kecamatan' => 'required|string|max:100',
            'kabupaten' => 'required|string|max:100',
            'provinsi'  => 'required|string|max:100',
            'kode_bps'  => 'nullable|string|max:20|unique:wilayah,kode_bps,' . $wilayah->id,
        ]);

        $wilayah->update($data);

        return back()->with('success', 'Wilayah berhasil diperbarui.');
    }

    public function destroy(Wilayah $wilayah)
    {
        if ($wilayah->posyandu()->exists()) {
            return back()->withErrors(['delete' => 'Wilayah tidak dapat dihapus karena masih memiliki posyandu.']);
        }

        $wilayah->delete();

        return back()->with('success', 'Wilayah berhasil dihapus.');
    }
}
