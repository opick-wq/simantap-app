<?php

namespace App\Http\Controllers;

use App\Models\Posyandu;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class KaderController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();
        $ids  = $user->accessiblePosyanduIds();

        $kader = User::where('role', 'kader')
            ->whereIn('posyandu_id', $ids)
            ->with('posyandu')
            ->orderBy('nama')
            ->get()
            ->map(fn($u) => [
                'id'           => $u->id,
                'nama'         => $u->nama,
                'email'        => $u->email,
                'nomor_hp'     => $u->nomor_hp,
                'posyandu_id'  => $u->posyandu_id,
                'posyandu'     => $u->posyandu?->nama ?? '-',
                'aktif'        => $u->aktif,
            ]);

        $posyandu = Posyandu::whereIn('id', $ids)->orderBy('nama')->get(['id', 'nama']);

        return Inertia::render('Kader/Index', compact('kader', 'posyandu'));
    }

    public function store(Request $request)
    {
        $ids = Auth::user()->accessiblePosyanduIds();

        $data = $request->validate([
            'nama'        => 'required|string|max:100',
            'nomor_hp'    => 'required|string|max:20',
            'email'       => 'nullable|email|unique:users,email',
            'password'    => 'required|string|min:6',
            'posyandu_id' => 'required|exists:posyandu,id',
        ]);

        abort_unless(in_array($data['posyandu_id'], $ids), 403);

        $user = User::create([
            'nama'        => $data['nama'],
            'nomor_hp'    => $data['nomor_hp'],
            'email'       => $data['email'] ?? null,
            'password'    => Hash::make($data['password']),
            'role'        => 'kader',
            'posyandu_id' => $data['posyandu_id'],
            'aktif'       => true,
        ]);

        return back()->with('success', "Akun kader {$user->nama} berhasil dibuat.");
    }

    public function update(Request $request, User $user)
    {
        $ids = Auth::user()->accessiblePosyanduIds();
        abort_unless($user->role === 'kader' && in_array($user->posyandu_id, $ids), 403);

        $data = $request->validate([
            'nama'        => 'required|string|max:100',
            'nomor_hp'    => 'required|string|max:20',
            'email'       => "nullable|email|unique:users,email,{$user->id}",
            'posyandu_id' => 'required|exists:posyandu,id',
            'password'    => 'nullable|string|min:6',
            'aktif'       => 'boolean',
        ]);

        abort_unless(in_array($data['posyandu_id'], $ids), 403);

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return back()->with('success', "Akun {$user->nama} berhasil diperbarui.");
    }

    public function destroy(User $user)
    {
        $ids = Auth::user()->accessiblePosyanduIds();
        abort_unless($user->role === 'kader' && in_array($user->posyandu_id, $ids), 403);

        $nama = $user->nama;
        $user->delete();

        return back()->with('success', "Akun kader {$nama} berhasil dihapus.");
    }
}
