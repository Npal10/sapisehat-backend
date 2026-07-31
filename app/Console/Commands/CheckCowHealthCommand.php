<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cow;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckCowHealthCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-cow-health';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengecek kondisi kesehatan dan vaksinasi sapi, lalu mengirim peringatan (FCM) jika dibutuhkan.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Memulai pengecekan kesehatan sapi...");

        $cows = Cow::all();
        $notificationsSent = 0;

        foreach ($cows as $cow) {
            // Logika contoh 1: Peringatan Vaksinasi
            // Misalnya sapi harus divaksin setiap 6 bulan
            if ($cow->last_vaccinated_at) {
                $lastVaccine = Carbon::parse($cow->last_vaccinated_at);
                $monthsSinceVaccine = $lastVaccine->diffInMonths(Carbon::now());
                
                if ($monthsSinceVaccine >= 6) {
                    $this->sendNotificationToOwner(
                        $cow->user_id,
                        "Waktunya Vaksinasi! 💉",
                        "Sapi {$cow->breed} dengan tag {$cow->ear_tag} sudah lebih dari 6 bulan tidak divaksin."
                    );
                    $notificationsSent++;
                }
            } else {
                // Sapi belum pernah divaksin
                $this->sendNotificationToOwner(
                    $cow->user_id,
                    "Perhatian Kesehatan ⚠️",
                    "Sapi {$cow->breed} dengan tag {$cow->ear_tag} belum memiliki riwayat vaksinasi."
                );
                $notificationsSent++;
            }
        }

        $this->info("Pengecekan selesai! Total notifikasi dikirim: {$notificationsSent}");
        Log::info("Cron Job: app:check-cow-health dijalankan. Notifikasi dikirim: {$notificationsSent}");
    }

    private function sendNotificationToOwner($userId, $title, $body)
    {
        $user = User::find($userId);
        
        // Pastikan user ada dan memiliki fcm_token
        if ($user && $user->fcm_token) {
            NotificationService::sendPushNotification($user->fcm_token, $title, $body);
        }
    }
}
