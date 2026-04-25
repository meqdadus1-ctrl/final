<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FcmService
{
    private string $projectId = 'hr-system-b38cb';
    private string $credentialsPath;

    public function __construct()
    {
        $this->credentialsPath = storage_path('firebase-credentials.json');
    }

    /**
     * إرسال إشعار لموظف واحد
     */
    public function sendToEmployee(string $fcmToken, string $title, string $body, array $data = []): bool
    {
        try {
            $accessToken = $this->getAccessToken();

            $response = Http::withToken($accessToken)
                ->post("https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send", [
                    'message' => [
                        'token' => $fcmToken,
                        'notification' => [
                            'title' => $title,
                            'body'  => $body,
                        ],
                        'android' => [
                            'notification' => [
                                'sound'        => 'default',
                                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            ],
                        ],
                        'apns' => [
                            'payload' => [
                                'aps' => [
                                    'sound' => 'default',
                                    'badge' => 1,
                                ],
                            ],
                        ],
                        'data' => array_map('strval', $data),
                    ],
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::warning('FCM send failed', [
                'status'   => $response->status(),
                'response' => $response->json(),
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('FCM exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * الحصول على Access Token من Google OAuth2
     * يُخزَّن في Cache لمدة 55 دقيقة
     */
    private function getAccessToken(): string
    {
        return Cache::remember('fcm_access_token', 3300, function () {
            $credentials = json_decode(file_get_contents($this->credentialsPath), true);

            $now = time();
            $claim = [
                'iss'   => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud'   => $credentials['token_uri'],
                'iat'   => $now,
                'exp'   => $now + 3600,
            ];

            $jwt = $this->createJwt($claim, $credentials['private_key']);

            $response = Http::asForm()->post($credentials['token_uri'], [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            return $response->json('access_token');
        });
    }

    /**
     * إنشاء JWT للمصادقة مع Google
     */
    private function createJwt(array $claim, string $privateKey): string
    {
        $header  = $this->base64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = $this->base64url(json_encode($claim));

        $data = "{$header}.{$payload}";

        openssl_sign($data, $signature, $privateKey, 'SHA256');

        return "{$data}." . $this->base64url($signature);
    }

    private function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
