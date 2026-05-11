<?php

namespace App\Http\Controllers;

use App\Models\MessengerSetting;
use App\Services\SmsService;
use Illuminate\Http\Request;

class MessengerSettingController extends Controller
{
    public function index()
    {
        $settings = MessengerSetting::getInstance();
        return view('settings.messenger', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'driver'             => 'required|in:bulksms,whatsapp_cloud',
            'sms_api_url'        => 'nullable|url|max:255',
            'sms_api_auth'       => 'nullable|string|max:500',
            'wa_phone_number_id' => 'nullable|string|max:100',
            'wa_access_token'    => 'nullable|string|max:1000',
            'country_code'       => 'required|digits_between:1,5',
            'salary_template'    => 'required|string|max:500',
            'is_active'          => 'boolean',
        ]);

        $settings = MessengerSetting::getInstance();
        $settings->update([
            'driver'             => $request->driver,
            'sms_api_url'        => $request->sms_api_url,
            'sms_api_auth'       => $request->sms_api_auth ?: $settings->sms_api_auth,
            'wa_phone_number_id' => $request->wa_phone_number_id,
            'wa_access_token'    => $request->wa_access_token ?: $settings->wa_access_token,
            'country_code'       => $request->country_code,
            'salary_template'    => $request->salary_template,
            'is_active'          => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'تم حفظ الإعدادات بنجاح.');
    }

    public function test(Request $request)
    {
        $request->validate([
            'test_phone'   => 'required|string|max:20',
            'test_message' => 'required|string|max:500',
        ]);

        $sms = app(SmsService::class);
        $sent = $sms->send($request->test_phone, $request->test_message);

        if ($sent) {
            return back()->with('success', 'تم إرسال الرسالة التجريبية بنجاح.');
        }

        return back()->with('error', 'فشل إرسال الرسالة — راجع الـ Log للتفاصيل.');
    }
}
