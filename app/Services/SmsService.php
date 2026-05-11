<?php

namespace App\Services;

use App\Models\MessengerSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    private ?MessengerSetting $settings = null;

    private function getSettings(): MessengerSetting
    {
        return $this->settings ??= MessengerSetting::getInstance();
    }

    /**
     * إرسال رسالة (SMS أو WhatsApp حسب الإعداد)
     */
    public function send(string $phone, string $message): bool
    {
        $cfg = $this->getSettings();

        if (!$cfg->is_active) {
            Log::info('SmsService: الإرسال معطّل من الإعدادات');
            return false;
        }

        $phone = $this->formatPhone($phone, $cfg->country_code ?? '970');

        return match ($cfg->driver) {
            'whatsapp_cloud' => $this->sendWhatsApp($phone, $message, $cfg),
            default          => $this->sendBulkSms($phone, $message, $cfg),
        };
    }

    /**
     * إرسال عبر SMS API (BulkSMS وما شابه)
     */
    private function sendBulkSms(string $phone, string $message, MessengerSetting $cfg): bool
    {
        if (!$cfg->sms_api_url || !$cfg->sms_api_auth) {
            Log::warning('SmsService: بيانات SMS API غير مكتملة');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $cfg->sms_api_auth,
                'Content-Type'  => 'application/json',
            ])->post($cfg->sms_api_url, [
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
     * إرسال عبر WhatsApp Cloud API (Meta)
     */
    private function sendWhatsApp(string $phone, string $message, MessengerSetting $cfg): bool
    {
        if (!$cfg->wa_phone_number_id || !$cfg->wa_access_token) {
            Log::warning('SmsService: بيانات WhatsApp غير مكتملة');
            return false;
        }

        $url = "https://graph.facebook.com/v19.0/{$cfg->wa_phone_number_id}/messages";

        // WhatsApp يتوقع الرقم بدون +
        $phone = ltrim($phone, '+');

        try {
            $response = Http::withToken($cfg->wa_access_token)
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'to'                => $phone,
                    'type'              => 'text',
                    'text'              => ['body' => $message],
                ]);

            if ($response->successful()) {
                Log::info("WhatsApp sent to {$phone}");
                return true;
            }

            Log::warning('WhatsApp failed', [
                'phone'    => $phone,
                'status'   => $response->status(),
                'response' => $response->json(),
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('WhatsApp exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * بناء رسالة الراتب من القالب المحفوظ
     */
    public function buildSalaryMessage(string $employeeName, int $amount, string $period, string $bankAccount = ''): string
    {
        $template = $this->getSettings()->salary_template
            ?? 'تم احتساب راتبك عن الفترة {period} بمبلغ {amount} ₪';

        return str_replace(
            ['{name}',        '{amount}', '{period}', '{account}'],
            [$employeeName,   $amount,    $period,    $bankAccount],
            $template
        );
    }

    /**
     * تنسيق رقم الهاتف
     */
    private function formatPhone(string $phone, string $countryCode = '970'): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = $countryCode . substr($phone, 1);
        } elseif (strlen($phone) <= 9) {
            $phone = $countryCode . $phone;
        }

        return '+' . $phone;
    }
}
