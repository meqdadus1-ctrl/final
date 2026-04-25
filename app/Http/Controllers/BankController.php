<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use Illuminate\Http\Request;

class BankController extends Controller
{
    public function index()
    {
        $banks = Bank::withCount('employees')->orderBy('name')->paginate(20);
        return view('banks.index', compact('banks'));
    }

    public function create()
    {
        return view('banks.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:150',
            'bank_name'  => 'nullable|string|max:150',
            'branch'     => 'nullable|string|max:150',
            'swift_code' => 'nullable|string|max:30',
            'notes'      => 'nullable|string|max:1000',
        ]);

        // إذا ما اتكتب bank_name نسخه من name
        if (empty($data['bank_name'])) {
            $data['bank_name'] = $data['name'];
        }

        Bank::create($data);

        return redirect()->route('banks.index')
            ->with('success', 'تم إضافة البنك بنجاح');
    }

    public function show(Bank $bank)
    {
        $bank->load(['employees' => fn($q) => $q->orderBy('name')]);
        return view('banks.show', compact('bank'));
    }

    public function edit(Bank $bank)
    {
        return view('banks.edit', compact('bank'));
    }

    public function update(Request $request, Bank $bank)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:150',
            'bank_name'  => 'nullable|string|max:150',
            'branch'     => 'nullable|string|max:150',
            'swift_code' => 'nullable|string|max:30',
            'notes'      => 'nullable|string|max:1000',
        ]);

        if (empty($data['bank_name'])) {
            $data['bank_name'] = $data['name'];
        }

        $bank->update($data);

        return redirect()->route('banks.index')
            ->with('success', 'تم تعديل البنك بنجاح');
    }

    public function destroy(Bank $bank)
    {
        if ($bank->employees()->count() > 0) {
            return redirect()->route('banks.index')
                ->with('error', 'لا يمكن حذف البنك لأنه مرتبط بموظفين');
        }

        $bank->delete();

        return redirect()->route('banks.index')
            ->with('success', 'تم حذف البنك بنجاح');
    }
}
