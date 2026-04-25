<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    private string $basicAuth = 'QTc3REZGMUFFRDYwNEE5NkE5NDc3RTQ5OTdFMDM0RDMtZm94Oipod2pjZ0tUQWkxcG1YYW9UeU5PMHl5MnhaMlE=';
    private string $apiUrl    = 'https://api.bulksms.com/v1/messages';

    /**
     * إرسال SMS
     */
    public function send(string $phone, string $message): bool
    {
        try {
            // تنظيف رقم الهاتف وإضافة كود فلسطين
            $phone = $this->formatPhone($phone);

            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $this->basicAuth,
                'Content-Type'  => 'application/json',
            ])->post($this->apiUrl, [
                'to'   => $phone,
                'body' => $message,
            ]);

            if ($response->successful()) {
                Log::info("SMS sent to {$phone}");
                return true;
            }

            Log::warning('SMS failed', [
                'phone'    => $phone,
                'status'   => $response->status(),
                'response' => $response->json(),
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('SMS exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * إرسال SMS تأكيد الراتب
     */
    public function sendSalaryNotification(string $phone, string $employeeName, float $amount, string $bankAccount): bool
    {
        $message = "تم ايداع الراتب {$amount} شيكل الى حسابك ({$bankAccount})";
        return $this->send($phone, $message);
    }

    /**
     * تنسيق رقم الهاتف
     */
    private function formatPhone(string $phone): string
    {
        // حذف كل شي غير أرقام
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // إذا يبدأ بـ 0 → استبدلها بـ 970 (فلسطين)
        if (str_starts_with($phone, '0')) {
            $phone = '970' . substr($phone, 1);
        }

        // إذا ما فيه كود دولة
        if (strlen($phone) <= 9) {
            $phone = '970' . $phone;
        }

        return '+' . $phone;
    }
}
