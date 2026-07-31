<?php

namespace App\Services;

use Google_Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class NotificationService
{
    /**
     * Mengirim pesan Push Notification menggunakan Firebase Cloud Messaging (HTTP v1 API).
     *
     * @param string $fcmToken Token FCM dari perangkat (HP).
     * @param string $title Judul Notifikasi.
     * @param string $body Isi Notifikasi.
     * @param array $data Data tambahan (opsional).
     * @return bool True jika berhasil, false jika gagal.
     */
    public static function sendPushNotification(string $fcmToken, string $title, string $body, array $data = []): bool
    {
        try {
            $credentialsPath = base_path(env('FIREBASE_CREDENTIALS', 'firebase-auth.json'));

            if (!file_exists($credentialsPath)) {
                Log::error("FCM Error: File credentials tidak ditemukan di {$credentialsPath}");
                return false;
            }

            // Mendapatkan OAuth2 Access Token menggunakan Google API Client
            $client = new Google_Client();
            $client->setAuthConfig($credentialsPath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->useApplicationDefaultCredentials();

            $token = $client->fetchAccessTokenWithAssertion();
            
            if (!isset($token['access_token'])) {
                Log::error("FCM Error: Gagal mendapatkan Access Token.");
                return false;
            }

            $accessToken = $token['access_token'];

            // Mendapatkan Project ID dari file JSON
            $jsonContent = json_decode(file_get_contents($credentialsPath), true);
            $projectId = $jsonContent['project_id'] ?? null;

            if (!$projectId) {
                Log::error("FCM Error: Project ID tidak ditemukan di file JSON.");
                return false;
            }

            // Membangun payload FCM HTTP v1
            $message = [
                'message' => [
                    'token' => $fcmToken,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'data' => empty($data) ? null : $data,
                ]
            ];

            // Menembakkan request ke Firebase API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json',
            ])->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", $message);

            if ($response->successful()) {
                Log::info("FCM Success: Pesan berhasil dikirim ke token " . substr($fcmToken, 0, 10) . "...");
                return true;
            } else {
                Log::error("FCM Error API: " . $response->body());
                return false;
            }

        } catch (Exception $e) {
            Log::error("FCM Exception: " . $e->getMessage());
            return false;
        }
    }
}
