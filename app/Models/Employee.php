<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model {
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id','user_id','department_id','designation_id','manager_id',
        'employee_id','first_name','last_name','father_name','cnic','cnic_expiry',
        'date_of_birth','gender','marital_status','religion','nationality',
        'personal_email','work_email','personal_phone','work_phone','whatsapp',
        'current_address','current_city','permanent_address','permanent_city','province',
        'joining_date','confirmation_date','resignation_date','termination_date',
        'last_working_day','employment_type','employment_status','probation_status',
        'probation_end_date','bank_name','bank_account_no','bank_iban','bank_branch',
        'eobi_number','pessi_number','nssf_number','basic_salary',
        'avatar','emergency_contact_name','emergency_contact_relation',
        'emergency_contact_phone','notes',
    ];

    protected $casts = [
        'date_of_birth'     => 'date',
        'joining_date'      => 'date',
        'confirmation_date' => 'date',
        'probation_end_date'=> 'date',
        'resignation_date'  => 'date',
        'termination_date'  => 'date',
        'last_working_day'  => 'date',
        'basic_salary'      => 'decimal:2',
    ];

    // Relationships
    public function company()     { return $this->belongsTo(Company::class); }
    public function user()        { return $this->belongsTo(User::class); }
    public function department()  { return $this->belongsTo(Department::class); }
    public function designation() { return $this->belongsTo(Designation::class); }
    public function manager()     { return $this->belongsTo(Employee::class, 'manager_id'); }
    public function subordinates(){ return $this->hasMany(Employee::class, 'manager_id'); }

    // Accessors
    public function getFullNameAttribute(): string {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getAvatarUrlAttribute(): string {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->full_name) . '&background=BA7517&color=0d1117&bold=true';
    }

    public function getFormattedCnicAttribute(): string {
        $c = preg_replace('/\D/', '', $this->cnic ?? '');
        return strlen($c) === 13
            ? substr($c,0,5).'-'.substr($c,5,7).'-'.substr($c,12,1)
            : $this->cnic;
    }

    public function isOnProbation(): bool {
        return $this->probation_status === 'on_probation';
    }

    public function getServiceLengthAttribute(): string {
        if (!$this->joining_date) return 'N/A';
        $diff = $this->joining_date->diff(now());
        return $diff->y . 'y ' . $diff->m . 'm';
    }

    public function documents() 
    { 
        return $this->hasMany(EmployeeDocument::class); 
    }

    public function notes()
    { return $this->hasMany(EmployeeNote::class); 

    }

    public function employeeShifts()
    { 
        return $this->hasMany(EmployeeShift::class);
    }
    
    public function currentShift() {
        return $this->hasOneThrough(Shift::class, EmployeeShift::class, 'employee_id', 'id', 'id', 'shift_id')
            ->where('employee_shifts.is_current', true);
    }

    public function salaryStructure() 
    {
        return $this->hasOne(SalaryStructure::class)->where('is_current', true);
    }
    
    public function payslips()
    { 
        return $this->hasMany(Payslip::class); 
    }

    public function certifications()
    { 
        return $this->hasMany(EmployeeCertification::class); 
    }
    public function skills()
    { 
        return $this->hasMany(EmployeeSkill::class);
    }
    public function trainingEnrollments()
    { 
        return $this->hasMany(TrainingEnrollment::class); 
    }

    public function hrDocuments() { return $this->hasMany(Document::class); }


    public function locations()
    {
        return $this->belongsToMany(Location::class, 'employee_locations')
            ->withPivot('is_primary','assigned_from','assigned_until')
            ->withTimestamps();
    }

    public function primaryLocation()
    {
        return $this->belongsToMany(Location::class, 'employee_locations')
            ->wherePivot('is_primary', true)
            ->withPivot('is_primary','assigned_from','assigned_until');
    }

    /** Locations that are currently valid (within from/until window + active). */
    public function activeLocations()
    {
        $today = now()->toDateString();
        return $this->locations()
            ->where('locations.is_active', true)
            ->where(function($q) use ($today) {
                $q->whereNull('employee_locations.assigned_from')
                  ->orWhere('employee_locations.assigned_from', '<=', $today);
            })
            ->where(function($q) use ($today) {
                $q->whereNull('employee_locations.assigned_until')
                  ->orWhere('employee_locations.assigned_until', '>=', $today);
            });
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

}