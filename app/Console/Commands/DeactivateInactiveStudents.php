<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;

class DeactivateInactiveStudents extends Command
{
    /**
     * Threshold default: 5 menit tanpa aktivitas = offline
     * Angka ini harus lebih besar dari interval heartbeat frontend (2-3 menit),
     * dengan buffer agar koneksi lambat tidak menyebabkan false-offline.
     */
    protected $signature = 'students:deactivate-inactive
                            {--minutes=5 : Jumlah menit tidak aktif sebelum dianggap offline}';

    protected $description = 'Set is_active = 0 untuk siswa yang tidak ada request/heartbeat dalam beberapa menit terakhir';

    public function handle(): void
    {
        $minutes  = (int) $this->option('minutes');
        $threshold = now()->subMinutes($minutes);

        // Ambil semua siswa yang masih tercatat online
        $activeStudentIds = User::where('is_active', true)
            ->role('siswa')
            ->pluck('id');

        if ($activeStudentIds->isEmpty()) {
            $this->info('Tidak ada siswa aktif, tidak ada yang perlu dinonaktifkan.');
            return;
        }

        // Dari siswa aktif tersebut, cari yang sudah tidak ada aktivitas token
        // sejak threshold (token terakhir dipakai sudah lama / tidak ada token)
        $inactiveIds = collect();

        foreach ($activeStudentIds as $userId) {
            $hasRecentActivity = PersonalAccessToken::where('tokenable_type', User::class)
                ->where('tokenable_id', $userId)
                ->where(function ($query) use ($threshold) {
                    // Masih aktif jika: last_used_at masih baru
                    $query->where('last_used_at', '>=', $threshold)
                        // Atau token baru dibuat (belum pernah dipakai sama sekali)
                        ->orWhere(function ($q) use ($threshold) {
                            $q->whereNull('last_used_at')
                                ->where('created_at', '>=', $threshold);
                        });
                })
                ->exists();

            if (!$hasRecentActivity) {
                $inactiveIds->push($userId);
            }
        }

        if ($inactiveIds->isEmpty()) {
            $this->info('Semua siswa aktif masih terdeteksi online.');
            return;
        }

        $count = User::whereIn('id', $inactiveIds)->update(['is_active' => false]);

        $this->info("Berhasil menonaktifkan {$count} siswa yang tidak aktif lebih dari {$minutes} menit.");
    }
}
