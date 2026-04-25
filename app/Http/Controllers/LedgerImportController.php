<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeLedger;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Shuchkin\SimpleXLSX;
use Carbon\Carbon;

class LedgerImportController extends Controller
{
    public function __construct(private LedgerService $ledger) {}

    /* =====================================================
     *  GET /ledger/import — نموذج الاستيراد
     * ===================================================== */
    public function showImportForm()
    {
        $employees = Employee::active()->orderBy('name')->get(['id', 'name']);
        return view('ledger.import', compact('employees'));
    }

    /* =====================================================
     *  POST /ledger/import/preview — معاينة الملف
     * ===================================================== */
    public function preview(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'file'        => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        $rows     = $this->parseFile($request->file('file'));

        // تحقق من التكرار
        $existingRefs = EmployeeLedger::where('employee_id', $employee->id)
            ->whereNotNull('reference_id')
            ->where('reference_type', 'ExcelImport')
            ->pluck('reference_id')
            ->map(fn($v) => (string) $v)
            ->toArray();

        foreach ($rows as &$row) {
            $row['duplicate'] = in_array((string) $row['ref_number'], $existingRefs);
        }
        unset($row);

        $newCount  = collect($rows)->where('duplicate', false)->count();
        $skipCount = collect($rows)->where('duplicate', true)->count();

        // نحفظ البيانات في session للاستخدام في store
        session([
            'import_employee_id' => $employee->id,
            'import_rows'        => $rows,
        ]);

        $employees = Employee::active()->orderBy('name')->get(['id', 'name']);

        return view('ledger.import', compact('employees', 'employee', 'rows', 'newCount', 'skipCount'));
    }

    /* =====================================================
     *  POST /ledger/import/store — حفظ القيود
     * ===================================================== */
    public function store(Request $request)
    {
        $employeeId = session('import_employee_id');
        $rows       = session('import_rows', []);

        if (!$employeeId || empty($rows)) {
            return redirect()->route('ledger.import')
                ->with('error', 'انتهت صلاحية الجلسة، يرجى إعادة رفع الملف.');
        }

        $employee = Employee::findOrFail($employeeId);
        $imported = 0;
        $skipped  = 0;

        foreach ($rows as $row) {
            if ($row['duplicate'] || $row['skip']) {
                $skipped++;
                continue;
            }

            $this->ledger->addEntry(
                $employee->id,
                $row['entry_type'],
                (float) $row['credit'],
                (float) $row['debit'],
                $row['description'],
                $row['date'],
                [
                    'reference_type' => 'ExcelImport',
                    'reference_id'   => $row['ref_number'],
                    'created_by'     => auth()->id(),
                ]
            );

            $imported++;
        }

        session()->forget(['import_employee_id', 'import_rows']);

        return redirect()->route('ledger.show', $employee)
            ->with('success', "✅ تم استيراد {$imported} قيد بنجاح" . ($skipped > 0 ? "، وتم تخطي {$skipped} قيد مكرر." : '.'));
    }

    /* =====================================================
     *  قراءة وتحليل ملف الـ Excel
     * ===================================================== */
    private function parseFile($file): array
    {
        $xlsx = SimpleXLSX::parse($file->getPathname());

        if (!$xlsx) {
            throw new \Exception('تعذّر قراءة الملف: ' . SimpleXLSX::parseError());
        }

        $allRows = $xlsx->rows(0); // أول sheet
        $rows    = [];

        // نوع القيد حسب التفصيل
        $typeMap = [
            'ايصال القبض'         => ['entry_type' => 'payment',          'side' => 'credit'],
            'إيصال القبض'         => ['entry_type' => 'payment',          'side' => 'credit'],
            'سند الصرف'           => ['entry_type' => 'payment',          'side' => 'debit'],
            'فاتورة البيع'        => ['entry_type' => 'deduction_manual', 'side' => 'debit'],
            'سند القيد'           => ['entry_type' => 'adjustment',       'side' => 'auto'],
            'كشف رواتب الموظفين' => ['entry_type' => 'skip',             'side' => 'skip'],
        ];

        foreach ($allRows as $index => $cells) {
            // تخطي أول صفين (عنوان + header)
            if ($index < 2) continue;

            // تخطي الصفوف الفارغة
            if (empty(array_filter($cells, fn($v) => $v !== null && $v !== ''))) continue;

            // الأعمدة: A=0, B=1, C=2, D=3, E=4(مدين), F=5(دائن), L=11(رقم القيد)
            $detail  = trim((string) ($cells[0] ?? ''));
            $refNum  = trim((string) ($cells[11] ?? '0'));
            $date    = $cells[2] ?? null;
            $debit   = $cells[4] ?? 0;
            $credit  = $cells[5] ?? 0;

            if (empty($detail)) continue;

            // البحث عن نوع القيد
            $map = null;
            foreach ($typeMap as $keyword => $config) {
                if (str_contains($detail, $keyword)) {
                    $map = $config;
                    break;
                }
            }

            if (!$map || $map['side'] === 'skip') continue;

            // تحليل التاريخ
            $parsedDate = $this->parseDate($date);
            if (!$parsedDate) continue;

            // تحديد المبالغ
            $creditVal = (float) ($credit ?? 0);
            $debitVal  = (float) ($debit  ?? 0);

            if ($map['side'] === 'credit') {
                // ايصال القبض → دائن دائماً (خذ القيمة من أي عمود فيه رقم)
                $creditVal = $debitVal > 0 ? $debitVal : $creditVal;
                $debitVal  = 0;
            } elseif ($map['side'] === 'debit') {
                // سند الصرف / فاتورة البيع → مدين دائماً
                $debitVal  = $creditVal > 0 ? $creditVal : $debitVal;
                $creditVal = 0;
            }
            // auto (سند القيد): يبقى كما هو حسب العمود

            if ($creditVal == 0 && $debitVal == 0) continue;

            $rows[] = [
                'detail'      => $detail,
                'ref_number'  => $refNum ?: '0',
                'date'        => $parsedDate,
                'credit'      => $creditVal,
                'debit'       => $debitVal,
                'entry_type'  => $map['entry_type'],
                'description' => $detail . ($refNum ? " — رقم القيد: {$refNum}" : ''),
                'duplicate'   => false,
                'skip'        => false,
            ];
        }

        return $rows;
    }

    /* =====================================================
     *  تحليل التاريخ — يدعم نص عربي ورقم Excel
     * ===================================================== */
    private function parseDate($value): ?string
    {
        if ($value === null || $value === '') return null;

        $str = trim((string) $value);

        // شكل: 05/02/2026 أو 5/2/2026 (مع مسافة في البداية أحياناً)
        $str = ltrim($str);
        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $str, $m)) {
            try {
                return Carbon::createFromDate((int)$m[3], (int)$m[2], (int)$m[1])->toDateString();
            } catch (\Exception $e) {
                return null;
            }
        }

        // رقم Excel serial date
        if (is_numeric($str) && (float)$str > 40000) {
            try {
                // Excel serial: الأيام منذ 1900-01-01
                $ts = \DateTime::createFromFormat('U', (string) round(((float)$str - 25569) * 86400));
                return $ts ? $ts->format('Y-m-d') : null;
            } catch (\Exception $e) {
                return null;
            }
        }

        try {
            return Carbon::parse($str)->toDateString();
        } catch (\Exception $e) {
            return null;
        }
    }
}
