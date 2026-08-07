<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Models\Peringatan;
use App\Models\User;

class NotificationDispatcher
{
    public function dispatch(array $peringatan, int $posyanduId): void
    {
        foreach ($peringatan as $p) {
            if (!$p->id) continue; // skip peringatan yang belum tersimpan

            $this->kirimKeKader($p, $posyanduId);

            if ($p->level_risiko === 'MERAH') {
                $this->kirimKePetugas($p);
            }
        }
    }

    private function kirimKeKader(Peringatan $p, int $posyanduId): void
    {
        User::where('role', 'kader')
            ->where('posyandu_id', $posyanduId)
            ->where('aktif', true)
            ->each(function (User $user) use ($p) {
                Notifikasi::create([
                    'peringatan_id' => $p->id,
                    'user_id'       => $user->id,
                    'tipe_penerima' => 'kader',
                    'sudah_dibaca'  => false,
                    'created_at'    => now(),
                ]);
            });
    }

    private function kirimKePetugas(Peringatan $p): void
    {
        User::where('role', 'petugas')
            ->where('aktif', true)
            ->each(function (User $user) use ($p) {
                Notifikasi::create([
                    'peringatan_id' => $p->id,
                    'user_id'       => $user->id,
                    'tipe_penerima' => 'petugas',
                    'sudah_dibaca'  => false,
                    'created_at'    => now(),
                ]);
            });
    }
}
