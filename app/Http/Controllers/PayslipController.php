<?php

namespace App\Http\Controllers;

/**
 * PayslipController — تم إيقافه.
 * نظام الرواتب الشهرية حُذف والنظام الأسبوعي مع Ledger هو المعتمد.
 * جميع الطلبات تُعاد توجيهها لصفحة الرواتب.
 */
class PayslipController extends Controller
{
    public function __call($method, $args)
    {
        return redirect()->route('salary.index')
            ->with('info', 'تم استبدال نظام الرواتب الشهرية بنظام الرواتب الأسبوعي الجديد.');
    }
}
