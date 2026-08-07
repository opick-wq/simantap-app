<?php

namespace App\Http\Controllers;

use App\Models\SesiPosyandu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SesiPosyanduController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();
        $ids  = $user->accessiblePosyanduIds();

        $sesi = SesiPosyandu::whereIn('posyandu_id', $ids)
            ->with('posyandu')
            ->orderBy('tanggal', 'desc')
            ->paginate(20)
            ->through(fn($s) => [
                'id'             => $s->id,
                'tanggal'        => $s->tanggal->format('d/m/Y'),
                'tanggal_raw'    => $s->tanggal->format('Y-m-d'),
                'tema'           => $s->tema,
                'posyandu_nama'  => $s->posyandu->nama,
                'jumlah_hadir'   => $s->jumlah_hadir,
                'status'         => $s->status,
            ]);

        $posyandu = \App\Models\Posyandu::whereIn('id', $ids)->get(['id', 'nama']);

        $bisaBuatJadwal = in_array($user->role, ['nakes', 'admin']);

        return Inertia::render('Sesi/Index', compact('sesi', 'posyandu', 'bisaBuatJadwal'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        // Hanya nakes dan admin yang boleh membuat jadwal
        abort_unless(in_array($user->role, ['nakes', 'admin']), 403);

        $data = $request->validate([
            'posyandu_id' => 'required|exists:posyandu,id',
            'tanggal'     => 'required|date',
            'tema'        => 'nullable|string|max:200',
            'catatan'     => 'nullable|string|max:500',
        ]);

        $ids = $user->accessiblePosyanduIds();
        abort_unless(in_array($data['posyandu_id'], $ids), 403);

        SesiPosyandu::create($data + [
            'dibuat_oleh'    => Auth::id(),
            'dipimpin_oleh'  => Auth::id(),
        ]);

        return back()->with('success', 'Sesi posyandu berhasil dibuat.');
    }

    public function update(Request $request, SesiPosyandu $sesi)
    {
        $user = Auth::user();
        $ids  = $user->accessiblePosyanduIds();
        abort_unless(in_array($sesi->posyandu_id, $ids), 403);

        // Edit jadwal lengkap (nakes/admin)
        if ($request->has('tanggal')) {
            abort_unless(in_array($user->role, ['nakes', 'admin']), 403);
            $data = $request->validate([
                'tanggal' => 'required|date',
                'tema'    => 'nullable|string|max:200',
                'catatan' => 'nullable|string|max:500',
            ]);
            $sesi->update($data);
            return back()->with('success', 'Jadwal sesi berhasil diperbarui.');
        }

        // Ubah status saja
        $data = $request->validate([
            'status' => 'required|in:TERJADWAL,BERLANGSUNG,SELESAI',
        ]);
        $sesi->update($data);
        return back()->with('success', 'Status sesi diperbarui.');
    }

    public function destroy(SesiPosyandu $sesi)
    {
        $user = Auth::user();
        abort_unless(in_array($user->role, ['nakes', 'admin']), 403);
        $ids  = $user->accessiblePosyanduIds();
        abort_unless(in_array($sesi->posyandu_id, $ids), 403);
        abort_if($sesi->status === 'SELESAI', 403, 'Sesi yang sudah selesai tidak dapat dihapus.');

        $sesi->delete();
        return back()->with('success', 'Sesi posyandu berhasil dihapus.');
    }
}
