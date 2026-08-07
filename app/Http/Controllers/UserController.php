<?php

namespace App\Http\Controllers;

use App\Models\Posyandu;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Role yang boleh dibuat oleh masing-masing aktor:
     *   admin   → semua role
     *   petugas → nakes
     *   nakes   → kader (hanya posyandu binaannya)
     */
    private function roleBisaDibuat(): array
    {
        $user = Auth::user();
        return match($user->role) {
            'admin'   => ['kader', 'nakes', 'petugas', 'admin', 'dinas', 'orang_tua'],
            'petugas' => ['nakes'],
            'nakes'   => ['kader'],
            default   => [],
        };
    }

    private function posyanduBisaDiakses(): array
    {
        return Auth::user()->accessiblePosyanduIds();
    }

    public function index(): Response
    {
        $user  = Auth::user();
        $roles = $this->roleBisaDibuat();
        $ids   = $this->posyanduBisaDiakses();

        $query = User::with(['posyandu', 'posyandus'])
            ->whereIn('role', $roles);

        // Nakes hanya lihat kader di posyandu binaannya
        if ($user->role === 'nakes') {
            $query->whereIn('posyandu_id', $ids);
        }

        $users = $query->orderByRaw("CASE role
                WHEN 'admin' THEN 1 WHEN 'petugas' THEN 2
                WHEN 'nakes' THEN 3 WHEN 'kader' THEN 4 ELSE 5 END")
            ->orderBy('nama')
            ->get()
            ->map(fn($u) => [
                'id'           => $u->id,
                'nama'         => $u->nama,
                'email'        => $u->email,
                'role'         => $u->role,
                'posyandu'     => in_array($u->role, ['nakes', 'petugas'])
                    ? $u->posyandus->pluck('nama')->join(', ') ?: ($u->posyandu?->nama ?? '-')
                    : ($u->posyandu?->nama ?? '-'),
                'posyandu_id'  => $u->posyandu_id,
                'posyandu_ids' => in_array($u->role, ['nakes', 'petugas'])
                    ? $u->posyandus->pluck('id')->toArray()
                    : [],
                'aktif'        => true,
            ]);

        // Posyandu yang boleh dipilih
        $posyanduList = $user->role === 'nakes'
            ? Posyandu::with('wilayah')->whereIn('id', $ids)->orderBy('nama')->get(['id', 'nama', 'wilayah_id'])
                ->map(fn($p) => ['id' => $p->id, 'nama' => $p->nama, 'kelurahan' => $p->wilayah?->nama, 'kecamatan' => $p->wilayah?->kecamatan])
            : Posyandu::with('wilayah')->where('aktif', true)->orderBy('nama')->get(['id', 'nama', 'wilayah_id'])
                ->map(fn($p) => ['id' => $p->id, 'nama' => $p->nama, 'kelurahan' => $p->wilayah?->nama, 'kecamatan' => $p->wilayah?->kecamatan]);

        return Inertia::render('Users/Index', [
            'users'         => $users,
            'posyandu'      => $posyanduList,
            'roleBisaDibuat'=> $roles,
            'roleAktor'     => $user->role,
        ]);
    }

    public function create(): Response
    {
        $user = Auth::user();
        $ids  = $this->posyanduBisaDiakses();

        $posyanduList = in_array($user->role, ['nakes', 'petugas'])
            ? Posyandu::with('wilayah')->whereIn('id', $ids)->orderBy('nama')->get(['id', 'nama', 'wilayah_id'])
                ->map(fn($p) => ['id' => $p->id, 'nama' => $p->nama, 'kelurahan' => $p->wilayah?->nama, 'kecamatan' => $p->wilayah?->kecamatan])
            : Posyandu::with('wilayah')->where('aktif', true)->orderBy('nama')->get(['id', 'nama', 'wilayah_id'])
                ->map(fn($p) => ['id' => $p->id, 'nama' => $p->nama, 'kelurahan' => $p->wilayah?->nama, 'kecamatan' => $p->wilayah?->kecamatan]);

        return Inertia::render('Users/Create', [
            'posyandu'  => $posyanduList,
            'roles'     => $this->roleBisaDibuat(),
            'roleAktor' => $user->role,
        ]);
    }

    public function store(Request $request)
    {
        $user        = Auth::user();
        $rolesDiizin = $this->roleBisaDibuat();
        $ids         = $this->posyanduBisaDiakses();

        $data = $request->validate([
            'nama'           => 'required|string|max:100',
            'email'          => 'required|email|unique:users,email',
            'role'           => 'required|in:' . implode(',', $rolesDiizin),
            'posyandu_id'    => 'nullable|exists:posyandu,id',
            'posyandu_ids'   => 'nullable|array',
            'posyandu_ids.*' => 'exists:posyandu,id',
            'nomor_hp'       => 'nullable|string|max:20',
        ]);

        // Nakes hanya bisa buat kader untuk posyandu binaannya
        if ($user->role === 'nakes') {
            abort_unless(in_array($data['posyandu_id'], $ids), 403,
                'Anda hanya dapat membuat akun kader untuk posyandu binaan Anda.');
        }

        // Petugas hanya bisa assign Nakes ke posyandu wilayah kerjanya
        $posyanduIds = $data['posyandu_ids'] ?? [];
        if ($user->role === 'petugas' && !empty($posyanduIds)) {
            abort_unless(empty(array_diff($posyanduIds, $ids)), 403,
                'Anda hanya dapat menugaskan Nakes ke posyandu dalam wilayah kerja Anda.');
        }
        unset($data['posyandu_ids']);
        $data['password'] = Hash::make('123456'); // password default, user wajib ganti

        if (in_array($data['role'], ['nakes', 'petugas']) && !empty($posyanduIds)) {
            $data['posyandu_id'] = $posyanduIds[0];
        }

        $newUser = User::create($data);

        if (in_array($newUser->role, ['nakes', 'petugas']) && !empty($posyanduIds)) {
            $newUser->posyandus()->sync($posyanduIds);
        }

        return redirect()->route('users.index')
            ->with('success', "Akun {$newUser->nama} berhasil dibuat.");
    }

    public function edit(User $user): Response
    {
        $aktor = Auth::user();
        $this->otorisasiEdit($aktor, $user);

        $ids = $this->posyanduBisaDiakses();
        $posyanduList = in_array($aktor->role, ['nakes', 'petugas'])
            ? Posyandu::with('wilayah')->whereIn('id', $ids)->orderBy('nama')->get(['id', 'nama', 'wilayah_id'])
                ->map(fn($p) => ['id' => $p->id, 'nama' => $p->nama, 'kelurahan' => $p->wilayah?->nama, 'kecamatan' => $p->wilayah?->kecamatan])
            : Posyandu::with('wilayah')->where('aktif', true)->orderBy('nama')->get(['id', 'nama', 'wilayah_id'])
                ->map(fn($p) => ['id' => $p->id, 'nama' => $p->nama, 'kelurahan' => $p->wilayah?->nama, 'kecamatan' => $p->wilayah?->kecamatan]);

        return Inertia::render('Users/Edit', [
            'user'      => array_merge($user->toArray(), [
                'posyandu_ids' => in_array($user->role, ['nakes', 'petugas'])
                    ? $user->posyandus()->pluck('posyandu.id')->toArray()
                    : [],
            ]),
            'posyandu'  => $posyanduList,
            'roles'     => $this->roleBisaDibuat(),
            'roleAktor' => $aktor->role,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $aktor = Auth::user();
        $this->otorisasiEdit($aktor, $user);

        $rolesDiizin = $this->roleBisaDibuat();
        $ids         = $this->posyanduBisaDiakses();

        $data = $request->validate([
            'nama'           => 'required|string|max:100',
            'email'          => "required|email|unique:users,email,{$user->id}",
            'role'           => 'required|in:' . implode(',', $rolesDiizin),
            'posyandu_id'    => 'nullable|exists:posyandu,id',
            'posyandu_ids'   => 'nullable|array',
            'posyandu_ids.*' => 'exists:posyandu,id',
            'nomor_hp'       => 'nullable|string|max:20',
            'password'       => 'nullable|min:6',
        ]);

        if ($aktor->role === 'nakes') {
            abort_unless(in_array($data['posyandu_id'], $ids), 403);
        }

        $posyanduIds = $data['posyandu_ids'] ?? [];
        if ($aktor->role === 'petugas' && !empty($posyanduIds)) {
            abort_unless(empty(array_diff($posyanduIds, $ids)), 403,
                'Anda hanya dapat menugaskan Nakes ke posyandu dalam wilayah kerja Anda.');
        }

        unset($data['posyandu_ids']);

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        if (in_array($data['role'], ['nakes', 'petugas']) && !empty($posyanduIds)) {
            $data['posyandu_id'] = $posyanduIds[0];
        }

        $user->update($data);

        if (in_array($user->role, ['nakes', 'petugas'])) {
            $user->posyandus()->sync($posyanduIds);
        }

        return redirect()->route('users.index')
            ->with('success', "Akun {$user->nama} berhasil diperbarui.");
    }

    public function gantiPasswordSendiri(Request $request)
    {
        $request->validate([
            'password_lama'     => 'required|string',
            'password'          => 'required|string|min:6|confirmed',
        ], [
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min'       => 'Password minimal 6 karakter.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password_lama, $user->password)) {
            return back()->withErrors(['password_lama' => 'Password lama tidak sesuai.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password berhasil diubah.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $aktor = Auth::user();
        $this->otorisasiEdit($aktor, $user);

        $data = $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min'       => 'Password minimal 6 karakter.',
        ]);

        $user->update(['password' => Hash::make($data['password'])]);

        return back()->with('success', "Password {$user->nama} berhasil direset.");
    }

    public function destroy(User $user)
    {
        $aktor = Auth::user();

        if ($user->id === $aktor->id) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        $this->otorisasiEdit($aktor, $user);

        $nama = $user->nama;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "Akun {$nama} berhasil dihapus.");
    }

    /** Pastikan aktor boleh mengedit user target */
    private function otorisasiEdit(User $aktor, User $target): void
    {
        $rolesDiizin = $this->roleBisaDibuat();
        abort_unless(in_array($target->role, $rolesDiizin), 403,
            'Anda tidak berwenang mengelola akun dengan role ini.');

        // Nakes hanya bisa edit kader di posyandunyaa
        if ($aktor->role === 'nakes') {
            $ids = $aktor->accessiblePosyanduIds();
            abort_unless(in_array($target->posyandu_id, $ids), 403,
                'Kader ini bukan bagian dari posyandu binaan Anda.');
        }
    }
}
