<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessengerSetting extends Model
{
    protected $fillable = [
        'driver',
        'sms_api_url',
        'sms_api_auth',
        'wa_phone_number_id',
        'wa_access_token',
        'country_code',
        'salary_template',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** دائماً سجل واحد في الجدول */
    public static function getInstance(): self
    {
        return static::firstOrCreate([], [
            'driver'          => 'bulksms',
            'sms_api_url'     => 'https://api.bulksms.com/v1/messages',
            'country_code'    => '970',
            'salary_template' => 'تم احتساب راتبك عن الفترة {period} بمبلغ {amount} ₪',
            'is_active'       => false,
        ]);
    }
}
