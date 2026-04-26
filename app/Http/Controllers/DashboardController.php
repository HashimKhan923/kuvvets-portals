<?php
namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;

class DashboardController extends Controller {
    public function index() {
        $stats = [
            'total_employees'  => Employee::where('employment_status', 'active')->count(),
            'departments'      => Department::where('is_active', true)->count(),
            'on_probation'     => Employee::where('probation_status', 'on_probation')->count(),
            'new_this_month'   => Employee::whereMonth('joining_date', now()->month)
                                    ->whereYear('joining_date', now()->year)->count(),
        ];
        return view('dashboard', compact('stats'));
    }
}