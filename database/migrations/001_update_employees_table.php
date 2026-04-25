<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {

            if (!Schema::hasColumn('employees', 'photo'))
                $table->string('photo')->nullable()->after('name');

            if (!Schema::hasColumn('employees', 'national_id'))
                $table->string('national_id')->nullable()->unique()->after('photo');

            if (!Schema::hasColumn('employees', 'birth_date'))
                $table->date('birth_date')->nullable()->after('national_id');

            if (!Schema::hasColumn('employees', 'gender'))
                $table->enum('gender', ['male','female'])->nullable()->after('birth_date');

            if (!Schema::hasColumn('employees', 'marital_status'))
                $table->enum('marital_status', ['single','married','divorced','widowed'])->nullable()->after('gender');

            if (!Schema::hasColumn('employees', 'nationality'))
                $table->string('nationality')->nullable()->after('marital_status');

            if (!Schema::hasColumn('employees', 'religion'))
                $table->string('religion')->nullable()->after('nationality');

            if (!Schema::hasColumn('employees', 'personal_email'))
                $table->string('personal_email')->nullable()->after('email');

            if (!Schema::hasColumn('employees', 'phone'))
                $table->string('phone')->nullable()->after('personal_email');

            if (!Schema::hasColumn('employees', 'phone2'))
                $table->string('phone2')->nullable()->after('phone');

            if (!Schema::hasColumn('employees', 'address'))
                $table->text('address')->nullable()->after('phone2');

            if (!Schema::hasColumn('employees', 'city'))
                $table->string('city')->nullable()->after('address');

            if (!Schema::hasColumn('employees', 'emergency_contact_name'))
                $table->string('emergency_contact_name')->nullable()->after('city');

            if (!Schema::hasColumn('employees', 'emergency_contact_phone'))
                $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');

            if (!Schema::hasColumn('employees', 'emergency_contact_relation'))
                $table->string('emergency_contact_relation')->nullable()->after('emergency_contact_phone');

            if (!Schema::hasColumn('employees', 'employee_number'))
                $table->string('employee_number')->nullable()->unique()->after('emergency_contact_relation');

            if (!Schema::hasColumn('employees', 'job_title'))
                $table->string('job_title')->nullable()->after('employee_number');

            if (!Schema::hasColumn('employees', 'work_location'))
                $table->string('work_location')->nullable()->after('job_title');

            if (!Schema::hasColumn('employees', 'contract_start'))
                $table->date('contract_start')->nullable()->after('work_location');

            if (!Schema::hasColumn('employees', 'contract_end'))
                $table->date('contract_end')->nullable()->after('contract_start');

            if (!Schema::hasColumn('employees', 'contract_type'))
                $table->enum('contract_type', ['permanent','temporary','part_time','freelance'])->default('permanent')->after('contract_end');

            if (!Schema::hasColumn('employees', 'work_email'))
                $table->string('work_email')->nullable()->after('contract_type');

            if (!Schema::hasColumn('employees', 'work_phone'))
                $table->string('work_phone')->nullable()->after('work_email');

            if (!Schema::hasColumn('employees', 'manager_id'))
                $table->foreignId('manager_id')->nullable()->constrained('employees')->nullOnDelete()->after('work_phone');

            if (!Schema::hasColumn('employees', 'education_level'))
                $table->string('education_level')->nullable()->after('manager_id');

            if (!Schema::hasColumn('employees', 'education_major'))
                $table->string('education_major')->nullable()->after('education_level');

            if (!Schema::hasColumn('employees', 'university'))
                $table->string('university')->nullable()->after('education_major');

            if (!Schema::hasColumn('employees', 'graduation_year'))
                $table->year('graduation_year')->nullable()->after('university');

            if (!Schema::hasColumn('employees', 'notes'))
                $table->text('notes')->nullable()->after('graduation_year');

        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'national_id','birth_date','gender','marital_status',
                'nationality','religion','personal_email','phone','phone2','address',
                'city','emergency_contact_name','emergency_contact_phone','emergency_contact_relation',
                'employee_number','job_title','work_location','contract_start','contract_end',
                'contract_type','work_email','work_phone','manager_id',
                'education_level','education_major','university','graduation_year','notes',
            ]);
        });
    }
};