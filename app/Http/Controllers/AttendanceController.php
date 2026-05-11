<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Shuchkin\SimpleXLSX;
// SimpleXLS مكتبة منفصلة للـ XLS القديم — إن وُجدت
// use Shuchkin\SimpleXLS;

class AttendanceController extends Controller
{
    // عرض قائمة الحضور
    public function index(Request $request)
    {
        $query = Attendance::with('employee');

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        // فلتر بالموظف
        if ($request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }

        // فلتر بالحالة
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $attendances = $query->orderByDesc('date')->paginate(20);
        $employees   = Employee::active()->get();

        return view('attendance.index', compact('attendances', 'employees'));
    }

    // صفحة الإضافة اليدوية
    public function create()
    {
        $employees = Employee::active()->get();
        return view('attendance.create', compact('employees'));
    }

    // حفظ سجل يدوي
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date'        => 'required|date',
            'check_in'    => 'nullable|date_format:H:i',
            'check_out'   => 'nullable|date_format:H:i',
            'status'      => 'required|in:present,absent,late,leave,holiday',
        ]);

        // إذا يوجد سجل لهذا الموظف في نفس اليوم → وجّهه لصفحة التعديل مباشرة
        $existing = Attendance::where('employee_id', $request->employee_id)
            ->whereDate('date', $request->date)
            ->first();

        if ($existing) {
            return redirect()->route('attendance.edit', $existing)
                ->with('info', 'يوجد سجل حضور لهذا الموظف في هذا التاريخ — يمكنك تعديله من هنا.');
        }

        // حساب ساعات العمل تلقائياً
        $workHours     = 0;
        $overtimeHours = 0;
        $lateMinutes   = 0;
        $employee      = Employee::find($request->employee_id);

        if ($request->check_in && $request->check_out) {
            [$workHours, $overtimeHours] = $this->calcWorkStats(
                $request->date, $request->check_in, $request->check_out,
                $employee?->shift_start, $employee?->shift_end
            );
        }

        if ($request->check_in && $employee) {
            $lateMinutes = $this->calcLateMinutes($request->date, $request->check_in, $employee->shift_start);
        }

        Attendance::create([
            'employee_id'    => $request->employee_id,
            'date'           => $request->date,
            'check_in'       => $request->check_in,
            'check_out'      => $request->check_out,
            'work_hours'     => $workHours,
            'overtime_hours' => $overtimeHours,
            'late_minutes'   => $lateMinutes,
            'status'         => $request->status,
            'leave_approved' => $request->leave_approved ? 1 : 0,
            'leave_reason'   => $request->leave_reason,
            'is_manual'      => 1,
            'updated_by'     => Auth::id(),
        ]);

        return redirect()->route('attendance.index')
            ->with('success', 'تم إضافة سجل الحضور بنجاح');
    }

    // صفحة عرض سجل واحد
    public function show(Attendance $attendance)
    {
        $attendance->load(['employee.department', 'updatedBy']);
        return view('attendance.show', compact('attendance'));
    }

    // صفحة التعديل
    public function edit(Attendance $attendance)
    {
        $employees = Employee::active()->get();
        return view('attendance.edit', compact('attendance', 'employees'));
    }

    // حفظ التعديل
    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date'        => 'required|date',
            'check_in'    => 'nullable|date_format:H:i',
            'check_out'   => 'nullable|date_format:H:i',
            'status'      => 'required|in:present,absent,late,leave,holiday',
        ]);

        $workHours     = 0;
        $overtimeHours = 0;
        $lateMinutes   = 0;
        $employee      = Employee::find($request->employee_id);

        if ($request->check_in && $request->check_out) {
            [$workHours, $overtimeHours] = $this->calcWorkStats(
                $request->date, $request->check_in, $request->check_out,
                $employee?->shift_start, $employee?->shift_end
            );
        }

        if ($request->check_in && $employee) {
            $lateMinutes = $this->calcLateMinutes($request->date, $request->check_in, $employee->shift_start);
        }

        $attendance->update([
            'employee_id'    => $request->employee_id,
            'date'           => $request->date,
            'check_in'       => $request->check_in,
            'check_out'      => $request->check_out,
            'work_hours'     => $workHours,
            'overtime_hours' => $overtimeHours,
            'late_minutes'   => $lateMinutes,
            'status'         => $request->status,
            'leave_approved' => $request->leave_approved ? 1 : 0,
            'leave_reason'   => $request->leave_reason,
            'updated_by'     => Auth::id(),
        ]);

        return redirect()->route('attendance.index')
            ->with('success', 'تم تعديل سجل الحضور بنجاح');
    }

    // حذف سجل
    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        return redirect()->route('attendance.index')
            ->with('success', 'تم حذف السجل بنجاح');
    }

    /**
     * قراءة صفوف ملف Excel (XLS القديم أو XLSX)
     * يدعم ثلاث طرق حسب ما هو متاح على الخادم:
     * 1. PhpSpreadsheet (إن كانت مثبتة)
     * 2. SimpleXLSX (للـ xlsx فقط)
     * 3. COM object على Windows (Laragon)
     */
    private function readExcelRows(string $path, string $ext): array
    {
        // الطريقة 1: PhpSpreadsheet — تدعم كل التنسيقات XLS/XLSX
        if (class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($path);
            $reader->setReadDataOnly(false); // نحتاج التنسيق لتحديد الخلايا التاريخية
            $spreadsheet = $reader->load($path);
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = [];

            foreach ($sheet->getRowIterator() as $row) {
                $rowData = [];
                $cellIter = $row->getCellIterator();
                $cellIter->setIterateOnlyExistingCells(false);

                foreach ($cellIter as $cell) {
                    $val = $cell->getValue();

                    if ($val === null || $val === '') {
                        $rowData[] = '';
                        continue;
                    }

                    // تحويل خلايا التاريخ/الوقت
                    if (is_numeric($val)
                        && \PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)) {
                        $dt  = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$val);
                        $val = $dt->format('Y-m-d H:i:s');
                    } elseif ($val instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
                        $val = $val->getPlainText();
                    }

                    $rowData[] = (string)$val;
                }

                // تجاهل الصفوف الفارغة كلياً
                if (array_filter($rowData, fn($v) => trim($v) !== '')) {
                    $rows[] = $rowData;
                }
            }
            return $rows;
        }

        // الطريقة 2: SimpleXLSX — للـ xlsx فقط
        if ($ext === 'xlsx' && class_exists(\Shuchkin\SimpleXLSX::class)) {
            $xlsx = \Shuchkin\SimpleXLSX::parse($path);
            if ($xlsx) {
                return $xlsx->rows();
            }
        }

        // الطريقة 3: COM على Windows (Laragon/WAMP)
        if ($ext === 'xls' && class_exists('COM')) {
            return $this->readXlsViaCom($path);
        }

        // الطريقة 4: Python fallback (إن كان متاحاً)
        $pythonCmd = $this->findPython();
        if ($pythonCmd) {
            return $this->readXlsViaPython($path, $pythonCmd);
        }

        throw new \RuntimeException(
            'لا يمكن قراءة ملف XLS القديم. يرجى تشغيل الأمر التالي في مجلد المشروع ثم إعادة الرفع: ' .
            "\n<code>composer require phpoffice/phpspreadsheet</code>"
        );
    }

    /**
     * قراءة XLS عبر COM على Windows
     */
    private function readXlsViaCom(string $path): array
    {
        $excel = new \COM('Excel.Application');
        $excel->Visible = false;
        $excel->DisplayAlerts = false;

        try {
            $wb = $excel->Workbooks->Open(realpath($path));
            $ws = $wb->Worksheets(1);

            $usedRange = $ws->UsedRange;
            $rowCount  = $usedRange->Rows->Count;
            $colCount  = $usedRange->Columns->Count;

            $rows = [];
            for ($r = 1; $r <= $rowCount; $r++) {
                $row = [];
                for ($c = 1; $c <= $colCount; $c++) {
                    $cell = $ws->Cells($r, $c);
                    $row[] = $cell->Text; // Text يعطي القيمة كما تُعرض
                }
                if (array_filter($row, fn($v) => trim((string)$v) !== '')) {
                    $rows[] = $row;
                }
            }

            $wb->Close(false);
            return $rows;
        } finally {
            $excel->Quit();
            $excel = null;
        }
    }

    /**
     * قراءة XLS عبر Python (xlrd أو openpyxl)
     */
    private function readXlsViaPython(string $path, string $python): array
    {
        $script = tempnam(sys_get_temp_dir(), 'xlsread_') . '.py';
        $output = tempnam(sys_get_temp_dir(), 'xlsout_') . '.json';

        file_put_contents($script, <<<PYEOF
import sys, json
path = sys.argv[1]
out  = sys.argv[2]

try:
    import xlrd
    wb = xlrd.open_workbook(path, encoding_override='utf-8')
    ws = wb.sheet_by_index(0)
    rows = []
    for r in range(ws.nrows):
        row = []
        for c in range(ws.ncols):
            cell = ws.cell(r, c)
            if cell.ctype == 3:  # XL_CELL_DATE
                import datetime
                dt = xlrd.xldate_as_datetime(cell.value, wb.datemode)
                row.append(dt.strftime('%Y-%m-%d %H:%M:%S'))
            else:
                row.append(str(cell.value).strip())
        if any(v.strip() for v in row):
            rows.append(row)
    with open(out, 'w', encoding='utf-8') as f:
        json.dump(rows, f, ensure_ascii=False)
except Exception as e:
    with open(out, 'w') as f:
        json.dump({'error': str(e)}, f)
PYEOF
        );

        $cmd = escapeshellcmd($python) . ' ' . escapeshellarg($script)
             . ' ' . escapeshellarg($path)
             . ' ' . escapeshellarg($output)
             . ' 2>/dev/null';

        exec($cmd, $out, $code);

        if (!file_exists($output)) {
            @unlink($script);
            throw new \RuntimeException('فشل تشغيل Python لقراءة الملف');
        }

        $data = json_decode(file_get_contents($output), true);
        @unlink($script);
        @unlink($output);

        if (isset($data['error'])) {
            throw new \RuntimeException('Python error: ' . $data['error']);
        }

        return is_array($data) ? $data : [];
    }

    private function findPython(): ?string
    {
        foreach (['python3', 'python', 'py'] as $cmd) {
            exec("where $cmd 2>/dev/null || which $cmd 2>/dev/null", $out, $code);
            if ($code === 0 && !empty($out)) return $cmd;
        }
        return null;
    }

    /**
     * صفحة سحب البيانات من جهاز البصمة (GET)
     */
    public function pullDevicePage()
    {
        return view('attendance.pull-device');
    }

    /**
     * استيراد الحضور من ملف Excel (من جهاز البصمة)
     * أعمدة الملف: رقم البصمه | الإسم | التاريخ والوقت
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|max:10240',
        ], [
            'excel_file.required' => 'يرجى اختيار ملف Excel',
        ]);

        $ext = strtolower($request->file('excel_file')->getClientOriginalExtension());
        if (!in_array($ext, ['xls', 'xlsx', 'csv'])) {
            return back()->with('error', 'يجب أن يكون الملف بصيغة xls أو xlsx');
        }

        try {
            $file = $request->file('excel_file');
            $path = $file->getPathname();

            $rows = $this->readExcelRows($path, $ext);

            if (empty($rows) || count($rows) < 2) {
                return back()->with('error', 'الملف فارغ أو لا يحتوي على بيانات كافية — تأكد أن الملف يحتوي على صف ترويسة وصفوف بيانات');
            }

            // تحديد أعمدة البيانات — نبحث في الصف الأول عن الترويسات
            $headerRow = array_values($rows[0]);
            $colFingerprint = null;
            $colName        = null;
            $colDateTime    = null;

            foreach ($headerRow as $idx => $cell) {
                $cell = trim((string)$cell);
                if (str_contains($cell, 'رقم') || str_contains($cell, 'بصمه') || str_contains($cell, 'بصمة') || strtolower($cell) === 'id') {
                    $colFingerprint = $idx;
                } elseif (str_contains($cell, 'اسم') || str_contains($cell, 'الإسم') || str_contains($cell, 'الاسم') || strtolower($cell) === 'name') {
                    $colName = $idx;
                } elseif (str_contains($cell, 'تاريخ') || str_contains($cell, 'وقت') || stripos($cell, 'time') !== false || stripos($cell, 'date') !== false) {
                    $colDateTime = $idx;
                }
            }

            // إذا لم نجد الترويسات نفترض الترتيب الافتراضي: A=رقم البصمة، B=الاسم، C=التاريخ والوقت
            $colFingerprint = $colFingerprint ?? 0;
            $colName        = $colName        ?? 1;
            $colDateTime    = $colDateTime    ?? 2;

            $grouped  = [];  // [fingerprint_id => [date => [times]]]
            $notFound = 0;
            $invalid  = 0;

            // تجاهل الصف الأول (ترويسات)
            foreach (array_slice($rows, 1) as $row) {
                $fingerprintId = trim((string)($row[$colFingerprint] ?? ''));
                $rawDateTime   = trim((string)($row[$colDateTime]    ?? ''));

                if (empty($fingerprintId) || empty($rawDateTime)) {
                    $invalid++;
                    continue;
                }

                // تحليل التاريخ والوقت
                // SimpleXLSX قد يعيد التاريخ كنص أو كرقم serial
                try {
                    if (is_numeric($rawDateTime) && $rawDateTime > 1000) {
                        // Excel serial date: تحويل يدوي
                        $unixTs = ($rawDateTime - 25569) * 86400;
                        $carbon  = \Carbon\Carbon::createFromTimestampUTC($unixTs);
                    } else {
                        $rawDateTime = str_replace(['/', '\\'], '-', $rawDateTime);
                        $carbon = \Carbon\Carbon::parse($rawDateTime);
                    }
                } catch (\Exception $e) {
                    $invalid++;
                    continue;
                }

                $date = $carbon->toDateString();
                $time = $carbon->format('H:i:s');

                // الجمعة = إجازة
                if ($carbon->dayOfWeek === \Carbon\Carbon::FRIDAY) continue;

                if (!isset($grouped[$fingerprintId][$date])) {
                    $grouped[$fingerprintId][$date] = [];
                }
                $grouped[$fingerprintId][$date][] = $time;
            }

            $imported = 0;
            $skipped  = 0;
            $errors   = [];

            foreach ($grouped as $fingerprintId => $days) {
                $employee = Employee::where('fingerprint_id', $fingerprintId)->first();

                if (!$employee) {
                    $notFound++;
                    $errors[] = "رقم بصمة غير مسجل: {$fingerprintId}";
                    continue;
                }

                foreach ($days as $date => $times) {
                    sort($times);

                    $checkIn  = $times[0];
                    $checkOut = count($times) > 1 ? end($times) : null;

                    // حساب ساعات العمل
                    $workHours     = 0;
                    $overtimeHours = 0;
                    $lateMinutes   = 0;
                    $status        = 'present';

                    if ($checkIn && $checkOut) {
                        [$workHours, $overtimeHours] = $this->calcWorkStats(
                            $date, $checkIn, $checkOut,
                            $employee->shift_start, $employee->shift_end
                        );
                    }

                    // تحديد التأخير وحساب دقائقه
                    if ($checkIn) {
                        $lateMinutes = $this->calcLateMinutes($date, $checkIn, $employee->shift_start);
                        if ($lateMinutes > 0) $status = 'late';
                    }

                    // حفظ أو تحديث — لا نكتب فوق السجلات اليدوية
                    $record = Attendance::firstOrNew([
                        'employee_id' => $employee->id,
                        'date'        => $date,
                    ]);

                    if ($record->exists && $record->is_manual) {
                        $skipped++;
                        continue;
                    }

                    $record->check_in       = $checkIn;
                    $record->check_out      = $checkOut;
                    $record->work_hours     = $workHours;
                    $record->overtime_hours = $overtimeHours;
                    $record->late_minutes   = $lateMinutes;
                    $record->status         = $status;
                    $record->is_manual      = false;
                    $record->updated_by     = Auth::id();
                    $record->save();

                    $imported++;
                }
            }

            $msg = "✅ تم استيراد {$imported} سجل بنجاح.";
            if ($skipped  > 0) $msg .= " تم تخطي {$skipped} سجل يدوي.";
            if ($notFound > 0) $msg .= " {$notFound} رقم بصمة غير مسجل في النظام.";
            if ($invalid  > 0) $msg .= " {$invalid} صف تم تجاهله (بيانات غير صالحة).";

            $session = $notFound > 0 ? 'warning' : 'success';
            return back()->with($session, $msg);

        } catch (\Exception $e) {
            \Log::error('Excel Attendance Import Error: ' . $e->getMessage());
            return back()->with('error', 'خطأ أثناء قراءة الملف: ' . $e->getMessage());
        }
    }

    /**
     * اختبار الاتصال بجهاز البصمة (POST) — بدون سحب بيانات
     */
    public function pingDevice(Request $request)
    {
        $request->validate([
            'ip'   => 'required|ip',
            'port' => 'required|integer|min:1|max:65535',
        ]);

        if (!extension_loaded('sockets')) {
            return back()->with('error', '❌ امتداد PHP Sockets (ext-sockets) غير مُفعَّل في الخادم. يرجى تفعيله في php.ini.');
        }

        try {
            $zk      = new \Rats\Zkteco\Lib\ZKTeco($request->ip, (int) $request->port);
            $connect = $zk->connect();

            if (!$connect) {
                return back()->with('error', '❌ فشل الاتصال بالجهاز. تأكد من: IP صحيح، البورت مفتوح (4370 UDP)، الجهاز مُشغَّل ومتصل بالشبكة.');
            }

            $deviceName = $zk->deviceName() ?: 'غير معروف';
            $serialNum  = $zk->serialNumber() ?: 'غير معروف';
            $time       = $zk->getTime() ?: 'غير معروف';
            $zk->disconnect();

            return back()->with('success',
                "✅ تم الاتصال بالجهاز بنجاح! | الاسم: {$deviceName} | السيريال: {$serialNum} | وقت الجهاز: {$time}"
            );
        } catch (\Exception $e) {
            \Log::error('ZKTeco ping error: ' . $e->getMessage());
            return back()->with('error', '❌ خطأ أثناء الاتصال: ' . $e->getMessage());
        }
    }

    /**
     * سحب بيانات الحضور من جهاز البصمة ZKTeco (POST)
     * يستخدم حزمة rats/zkteco المثبتة عبر Composer (بدون require_once يدوي)
     */
    public function pullFromDevice(Request $request)
    {
        $request->validate([
            'ip'        => 'required|ip',
            'port'      => 'required|integer|min:1|max:65535',
            'date_from' => 'required|date',
            'date_to'   => 'required|date|after_or_equal:date_from',
        ]);

        // فحص امتداد الـ sockets قبل أي عملية
        if (!extension_loaded('sockets')) {
            return back()->with('error',
                '❌ امتداد PHP Sockets (ext-sockets) غير مُفعَّل. افتح php.ini وفعّل السطر: extension=sockets'
            );
        }

        try {
            // استخدام rats/zkteco المثبتة عبر Composer — لا تحتاج require_once
            $zk      = new \Rats\Zkteco\Lib\ZKTeco($request->ip, (int) $request->port);
            $connect = $zk->connect();

            if (!$connect) {
                return back()->with('error', 'تعذر الاتصال بجهاز البصمة. تأكد من IP والبورت، وأن الجهاز متاح على الشبكة.');
            }

            $logs = $zk->getAttendance();
            $zk->disconnect();

            if (empty($logs)) {
                return back()->with('info', 'الجهاز متصل لكن لا توجد سجلات حضور. قد تكون فارغة أو تمت مسحها مسبقاً.');
            }

            $dateFrom  = \Carbon\Carbon::parse($request->date_from)->startOfDay();
            $dateTo    = \Carbon\Carbon::parse($request->date_to)->endOfDay();
            $imported  = 0;
            $skipped   = 0;
            $notFound  = 0;

            // نجمع أولاً كل السجلات مرتبة حسب timestamp
            // ونجمّعها: [employee_id => [date => [check_in_time, check_out_time]]]
            $grouped = [];

            foreach ($logs as $log) {
                if (empty($log['timestamp']) || empty($log['id'])) continue;

                $logTime = \Carbon\Carbon::parse($log['timestamp']);

                // فلتر الفترة الزمنية
                if ($logTime->lt($dateFrom) || $logTime->gt($dateTo)) continue;

                $fingerprintId = trim((string) $log['id']);
                if (empty($fingerprintId)) continue;

                $employee = Employee::where('fingerprint_id', $fingerprintId)->first();
                if (!$employee) {
                    $notFound++;
                    continue;
                }

                $date = $logTime->toDateString();
                $time = $logTime->format('H:i:s');
                $type = isset($log['type']) ? (int) $log['type'] : -1;

                // الجمعة = إجازة، نتجاهل سجلاتها
                if ($logTime->dayOfWeek === \Carbon\Carbon::FRIDAY) {
                    continue;
                }

                if (!isset($grouped[$employee->id][$date])) {
                    $grouped[$employee->id][$date] = [
                        'employee'   => $employee,
                        'check_in'   => null,
                        'check_out'  => null,
                        'all_times'  => [],
                    ];
                }

                $grouped[$employee->id][$date]['all_times'][] = [
                    'time' => $time,
                    'type' => $type,
                ];
            }

            // الآن نعالج كل موظف / يوم
            foreach ($grouped as $employeeId => $days) {
                foreach ($days as $date => $dayData) {
                    $employee  = $dayData['employee'];
                    $allTimes  = $dayData['all_times'];

                    if (empty($allTimes)) continue;

                    // ترتيب السجلات حسب الوقت
                    usort($allTimes, fn($a, $b) => strcmp($a['time'], $b['time']));

                    // جلب السجل الموجود مسبقاً (إن وُجد)
                    $record = Attendance::firstOrNew([
                        'employee_id' => $employee->id,
                        'date'        => $date,
                    ]);

                    // لا نكتب فوق السجلات اليدوية
                    if ($record->exists && $record->is_manual) {
                        $skipped++;
                        continue;
                    }

                    $checkIn  = null;
                    $checkOut = null;

                    if (count($allTimes) === 1 && $record->exists && $record->check_in) {
                        // الجهاز يرسل البصمات تدريجياً — هذه البصمة الوحيدة هي خروج
                        // لأن الدخول محفوظ مسبقاً في قاعدة البيانات
                        $newTime  = $allTimes[0]['time'];
                        $checkIn  = $record->check_in;

                        // نضع الجديدة كخروج فقط إذا كانت بعد وقت الدخول
                        if ($newTime > $checkIn) {
                            $checkOut = $newTime;
                        } else {
                            // البصمة قبل الدخول المحفوظ — إذن هي دخول أقدم، نتجاهلها
                            $skipped++;
                            continue;
                        }
                    } else {
                        // أول بصمة = دخول، آخر بصمة = خروج
                        $checkIn  = $allTimes[0]['time'];
                        $checkOut = count($allTimes) > 1
                            ? $allTimes[count($allTimes) - 1]['time']
                            : null;
                    }

                    // حساب ساعات العمل
                    $workHours     = 0;
                    $overtimeHours = 0;
                    $lateMinutes   = 0;
                    $status        = 'present';

                    if ($checkIn && $checkOut) {
                        [$workHours, $overtimeHours] = $this->calcWorkStats(
                            $date, $checkIn, $checkOut,
                            $employee->shift_start, $employee->shift_end
                        );
                    }

                    // تحديد التأخير وحساب دقائقه
                    if ($checkIn) {
                        $lateMinutes = $this->calcLateMinutes($date, $checkIn, $employee->shift_start);
                        if ($lateMinutes > 0) $status = 'late';
                    }

                    $record->check_in       = $checkIn;
                    $record->check_out      = $checkOut;
                    $record->work_hours     = $workHours;
                    $record->overtime_hours = $overtimeHours;
                    $record->late_minutes   = $lateMinutes;
                    $record->status         = $status;
                    $record->is_manual      = false;
                    $record->updated_by     = Auth::id();
                    $record->save();

                    $imported++;
                }
            }

            $msg = "✅ تم استيراد {$imported} سجل للفترة من {$request->date_from} إلى {$request->date_to}.";
            if ($skipped > 0)  $msg .= " تم تخطي {$skipped} سجل يدوي.";
            if ($notFound > 0) $msg .= " {$notFound} بصمة غير مسجلة في النظام.";

            return back()->with('success', $msg);

        } catch (\Exception $e) {
            \Log::error('ZKTeco sync error: ' . $e->getMessage(), [
                'ip'   => $request->ip,
                'port' => $request->port,
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'خطأ أثناء الاتصال: ' . $e->getMessage());
        }
    }

    public function exportByEmployee(Request $request)
    {
        $query = Attendance::with('employee')
            ->whereHas('employee')
            ->orderBy('employee_id');

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $records = $query->get()->groupBy('employee_id')
            ->sortBy(fn($group) => $group->first()->employee->name ?? '');

        $statusLabels = [
            'present' => 'حاضر',
            'absent'  => 'غائب',
            'late'    => 'متأخر',
            'leave'   => 'إجازة',
            'holiday' => 'عطلة',
        ];

        $boldStyle  = new \OpenSpout\Common\Entity\Style\Style(fontBold: true);
        $totalStyle = new \OpenSpout\Common\Entity\Style\Style(fontBold: true, backgroundColor: 'C6EFCE');

        $writer = new \OpenSpout\Writer\XLSX\Writer();
        $writer->openToBrowser('attendance_by_employee.xlsx');

        $writer->addRow(\OpenSpout\Common\Entity\Row::fromValuesWithStyle(
            ['اسم الموظف', 'التاريخ', 'وقت الدخول', 'وقت الخروج', 'ساعات العمل', 'أوفرتايم', 'تأخير (دقيقة)', 'الحالة'],
            $boldStyle
        ));

        foreach ($records as $employeeId => $group) {
            $employeeName  = $group->first()->employee->name ?? '—';
            $totalDays     = $group->count();
            $totalPresent  = $group->whereIn('status', ['present', 'late'])->count();
            $totalHours    = round($group->sum('work_hours'), 2);
            $totalOvertime = round($group->sum('overtime_hours'), 2);

            foreach ($group->sortBy('date') as $rec) {
                $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                    $employeeName,
                    $rec->date?->format('Y-m-d') ?? '',
                    $rec->check_in  ?? '—',
                    $rec->check_out ?? '—',
                    $rec->work_hours     ?? 0,
                    $rec->overtime_hours ?? 0,
                    $rec->late_minutes   ?? 0,
                    $statusLabels[$rec->status] ?? $rec->status,
                ]));
            }

            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValuesWithStyle([
                "إجمالي: {$employeeName}",
                "أيام: {$totalDays}",
                "حضور: {$totalPresent}",
                '',
                "ساعات: {$totalHours}",
                "أوفرتايم: {$totalOvertime}",
                '',
                '',
            ], $totalStyle));

            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues(['']));
        }

        $writer->close();
    }

    // يحسب ساعات العمل والأوفرتايم مع دعم وردية الليل (checkout < checkin)
    private function calcWorkStats(string $date, string $checkIn, ?string $checkOut, ?string $shiftStart, ?string $shiftEnd): array
    {
        if (!$checkOut) return [0, 0];

        $inTs  = strtotime("$date $checkIn");
        $outTs = strtotime("$date $checkOut");
        if ($outTs <= $inTs) $outTs += 86400;
        $workHours = round(($outTs - $inTs) / 3600, 2);

        $shiftHours = 8.0;
        if ($shiftStart && $shiftEnd) {
            $ssTs = strtotime("$date $shiftStart");
            $seTs = strtotime("$date $shiftEnd");
            if ($seTs <= $ssTs) $seTs += 86400;
            $shiftHours = max(1, round(($seTs - $ssTs) / 3600, 2));
        }

        $overtimeHours = $workHours > $shiftHours ? round($workHours - $shiftHours, 2) : 0;
        return [$workHours, $overtimeHours];
    }

    // يحسب دقائق التأخير (صفر إذا لا يوجد وردية أو جاء في الوقت)
    private function calcLateMinutes(string $date, string $checkIn, ?string $shiftStart): int
    {
        if (!$shiftStart) return 0;
        $scheduledIn = strtotime("$date $shiftStart");
        $actualIn    = strtotime("$date $checkIn");
        if ($actualIn > $scheduledIn + 300) {
            return (int) round(($actualIn - $scheduledIn) / 60);
        }
        return 0;
    }
}