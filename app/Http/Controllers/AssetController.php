<?php
namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetAssignment;
use App\Models\MaintenanceRecord;
use App\Models\RentalContract;
use App\Models\Employee;
use App\Models\Department;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AssetController extends Controller
{
    // ── DASHBOARD ────────────────────────────────────────────
    public function index()
    {
        $companyId = auth()->user()->company_id;

        $stats = [
            'total'             => Asset::where('company_id',$companyId)->whereNull('deleted_at')->count(),
            'available'         => Asset::where('company_id',$companyId)->where('status','available')->count(),
            'assigned'          => Asset::where('company_id',$companyId)->where('status','assigned')->count(),
            'under_maintenance' => Asset::where('company_id',$companyId)->where('status','under_maintenance')->count(),
            'total_value'       => Asset::where('company_id',$companyId)->sum('current_value'),
            'maintenance_due'   => MaintenanceRecord::where('company_id',$companyId)
                ->where('status','scheduled')
                ->where('scheduled_date','<=',today()->addDays(7))->count(),
            'insurance_expiring'=> Asset::where('company_id',$companyId)
                ->whereNotNull('insurance_expiry')
                ->where('insurance_expiry','>=',today())
                ->where('insurance_expiry','<=',today()->addDays(30))->count(),
            'overdue_returns'   => AssetAssignment::whereHas('asset',
                fn($q)=>$q->where('company_id',$companyId))
                ->where('status','active')
                ->where('expected_return_date','<',today())->count(),
        ];

        $recentAssets = Asset::with(['category','department','currentAssignment.employee'])
            ->where('company_id',$companyId)
            ->latest()->take(8)->get();

        $upcomingMaintenance = MaintenanceRecord::with(['asset'])
            ->where('company_id',$companyId)
            ->where('status','scheduled')
            ->orderBy('scheduled_date')
            ->take(5)->get();

        $activeRentals = RentalContract::with(['asset'])
            ->where('company_id',$companyId)
            ->where('status','active')
            ->orderBy('end_date')
            ->take(5)->get();

        // Type distribution
        $typeDist = Asset::where('company_id',$companyId)
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count','type');

        // Monthly maintenance cost (last 6 months)
        $maintenanceCost = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $maintenanceCost[] = [
                'month' => $d->format('M Y'),
                'cost'  => MaintenanceRecord::where('company_id',$companyId)
                    ->where('status','completed')
                    ->whereMonth('completed_date',$d->month)
                    ->whereYear('completed_date',$d->year)
                    ->sum('cost'),
            ];
        }

        return view('assets.index', compact(
            'stats','recentAssets','upcomingMaintenance',
            'activeRentals','typeDist','maintenanceCost'
        ));
    }

    // ── ASSET LIST ───────────────────────────────────────────
    public function list(Request $request)
    {
        $companyId   = auth()->user()->company_id;
        $departments = Department::where('company_id',$companyId)->get();
        $categories  = AssetCategory::where('company_id',$companyId)->get();

        $query = Asset::with(['category','department','currentAssignment.employee'])
            ->where('company_id',$companyId);

        if ($request->filled('status'))     $query->where('status',$request->status);
        if ($request->filled('type'))       $query->where('type',$request->type);
        if ($request->filled('department')) $query->where('department_id',$request->department);
        if ($request->filled('category'))   $query->where('asset_category_id',$request->category);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('name','like',"%$s%")
                  ->orWhere('asset_code','like',"%$s%")
                  ->orWhere('serial_number','like',"%$s%")
                  ->orWhere('registration_number','like',"%$s%");
            });
        }

        $assets = $query->latest()->paginate(15)->withQueryString();

        return view('assets.list', compact('assets','departments','categories'));
    }

    // ── CREATE ASSET ─────────────────────────────────────────
    public function create()
    {
        $companyId   = auth()->user()->company_id;
        $categories  = AssetCategory::where('company_id',$companyId)->where('is_active',true)->get();
        $departments = Department::where('company_id',$companyId)->get();

        return view('assets.create', compact('categories','departments'));
    }

    // ── STORE ASSET ──────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:200',
            'type'             => 'required|string',
            'condition'        => 'required|string',
            'ownership'        => 'required|string',
            'purchase_cost'    => 'nullable|numeric|min:0',
            'current_value'    => 'nullable|numeric|min:0',
            'depreciation_rate'=> 'nullable|numeric|min:0|max:100',
        ]);

        $companyId = auth()->user()->company_id;
        $prefix    = strtoupper(substr($request->type, 0, 3));
        $code      = $prefix . '-' . str_pad(
            Asset::where('company_id',$companyId)->count() + 1,
            4, '0', STR_PAD_LEFT
        );

        $image = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image')->store('assets', 'public');
        }

        $asset = Asset::create([
            'company_id'        => $companyId,
            'created_by'        => auth()->id(),
            'asset_code'        => $code,
            'image'             => $image,
            ...$request->only([
                'asset_category_id','department_id','name','brand','model',
                'serial_number','registration_number','type','condition',
                'ownership','purchase_date','purchase_cost','current_value',
                'depreciation_rate','vendor','vendor_contact','warranty_expiry',
                'insurance_expiry','insurance_policy','license_expiry',
                'odometer_reading','operating_hours','location','notes',
            ]),
            'status' => 'available',
        ]);

        AuditLog::log('asset_created', $asset);
        return redirect()->route('assets.show', $asset)
            ->with('success', "Asset \"{$asset->name}\" registered successfully.");
    }

    // ── SHOW ASSET ───────────────────────────────────────────
    public function show(Asset $asset)
    {
        $this->authorizeAsset($asset);
        $asset->load([
            'category','department','creator',
            'assignments.employee','assignments.assigner',
            'maintenance.creator',
            'rentals.creator',
            'currentAssignment.employee',
        ]);

        $employees = Employee::where('company_id', auth()->user()->company_id)
            ->where('employment_status','active')
            ->orderBy('first_name')->get();

        return view('assets.show', compact('asset','employees'));
    }

    // ── EDIT ASSET ───────────────────────────────────────────
    public function edit(Asset $asset)
    {
        $this->authorizeAsset($asset);
        $companyId   = auth()->user()->company_id;
        $categories  = AssetCategory::where('company_id',$companyId)->get();
        $departments = Department::where('company_id',$companyId)->get();
        return view('assets.edit', compact('asset','categories','departments'));
    }

    // ── UPDATE ASSET ─────────────────────────────────────────
    public function update(Request $request, Asset $asset)
    {
        $this->authorizeAsset($asset);
        $request->validate([
            'name'      => 'required|string|max:200',
            'type'      => 'required|string',
            'condition' => 'required|string',
            'ownership' => 'required|string',
        ]);

        if ($request->hasFile('image')) {
            $request->merge(['image' => $request->file('image')->store('assets','public')]);
        }

        $asset->update($request->only([
            'asset_category_id','department_id','name','brand','model',
            'serial_number','registration_number','type','condition','status',
            'ownership','purchase_date','purchase_cost','current_value',
            'depreciation_rate','vendor','vendor_contact','warranty_expiry',
            'insurance_expiry','insurance_policy','license_expiry',
            'odometer_reading','operating_hours','location','notes','image',
        ]));

        AuditLog::log('asset_updated', $asset);
        return redirect()->route('assets.show', $asset)
            ->with('success', 'Asset updated successfully.');
    }

    // ── ASSIGN ASSET ─────────────────────────────────────────
    public function assign(Request $request, Asset $asset)
    {
        $this->authorizeAsset($asset);

        $request->validate([
            'employee_id'          => 'required|exists:employees,id',
            'assigned_date'        => 'required|date',
            'expected_return_date' => 'nullable|date|after:assigned_date',
            'purpose'              => 'nullable|string|max:200',
            'condition_on_issue'   => 'nullable|string',
        ]);

        if ($asset->status === 'assigned') {
            return back()->with('error', 'Asset is already assigned. Return it first.');
        }

        AssetAssignment::create([
            'asset_id'             => $asset->id,
            'employee_id'          => $request->employee_id,
            'assigned_by'          => auth()->id(),
            'assigned_date'        => $request->assigned_date,
            'expected_return_date' => $request->expected_return_date,
            'purpose'              => $request->purpose,
            'condition_on_issue'   => $request->condition_on_issue,
            'status'               => 'active',
        ]);

        $asset->update(['status' => 'assigned']);

        AuditLog::log('asset_assigned', $asset);
        return back()->with('success', 'Asset assigned successfully.');
    }

    // ── RETURN ASSET ─────────────────────────────────────────
    public function returnAsset(Request $request, AssetAssignment $assignment)
    {
        $request->validate([
            'condition_on_return' => 'nullable|string',
            'notes'               => 'nullable|string',
        ]);

        $assignment->update([
            'actual_return_date'  => today(),
            'status'              => 'returned',
            'condition_on_return' => $request->condition_on_return,
            'notes'               => $request->notes,
            'returned_to'         => auth()->id(),
        ]);

        $assignment->asset->update(['status' => 'available']);

        AuditLog::log('asset_returned', $assignment->asset);
        return back()->with('success', 'Asset returned successfully.');
    }

    // ── ADD MAINTENANCE ──────────────────────────────────────
    public function storeMaintenance(Request $request, Asset $asset)
    {
        $this->authorizeAsset($asset);

        $request->validate([
            'type'           => 'required|string',
            'scheduled_date' => 'required|date',
            'description'    => 'required|string|max:500',
            'cost'           => 'nullable|numeric|min:0',
        ]);

        MaintenanceRecord::create([
            'asset_id'         => $asset->id,
            'company_id'       => auth()->user()->company_id,
            'created_by'       => auth()->id(),
            'reference_number' => 'MNT-' . strtoupper(Str::random(7)),
            'type'             => $request->type,
            'status'           => 'scheduled',
            'scheduled_date'   => $request->scheduled_date,
            'performed_by'     => $request->performed_by,
            'vendor'           => $request->vendor,
            'cost'             => $request->cost ?? 0,
            'downtime_hours'   => $request->downtime_hours ?? 0,
            'description'      => $request->description,
            'next_service_date'=> $request->next_service_date,
        ]);

        if (in_array($request->type, ['corrective','emergency'])) {
            $asset->update(['status' => 'under_maintenance']);
        }

        return back()->with('success', 'Maintenance record scheduled.');
    }

    // ── COMPLETE MAINTENANCE ─────────────────────────────────
    public function completeMaintenance(Request $request, MaintenanceRecord $record)
    {
        $request->validate([
            'work_done'        => 'nullable|string',
            'cost'             => 'nullable|numeric|min:0',
            'next_service_date'=> 'nullable|date|after:today',
        ]);

        $record->update([
            'status'            => 'completed',
            'completed_date'    => today(),
            'work_done'         => $request->work_done,
            'parts_replaced'    => $request->parts_replaced,
            'cost'              => $request->cost ?? $record->cost,
            'next_service_date' => $request->next_service_date,
            'odometer_reading'  => $request->odometer_reading,
            'operating_hours'   => $request->operating_hours,
        ]);

        // Set asset back to available after maintenance
        if ($record->asset->status === 'under_maintenance') {
            $record->asset->update(['status' => 'available']);
        }

        return back()->with('success', 'Maintenance marked as completed.');
    }

    // ── RENTAL CONTRACT ──────────────────────────────────────
    public function storeRental(Request $request, Asset $asset)
    {
        $this->authorizeAsset($asset);

        $request->validate([
            'rental_type'   => 'required|in:inbound,outbound',
            'party_name'    => 'required|string|max:200',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after:start_date',
            'rate_per_day'  => 'required|numeric|min:0',
        ]);

        $days  = Carbon::parse($request->start_date)->diffInDays($request->end_date) + 1;
        $total = $days * $request->rate_per_day;

        RentalContract::create([
            'asset_id'       => $asset->id,
            'company_id'     => auth()->user()->company_id,
            'created_by'     => auth()->id(),
            'contract_number'=> 'RNT-' . strtoupper(Str::random(7)),
            'rental_type'    => $request->rental_type,
            'party_name'     => $request->party_name,
            'party_contact'  => $request->party_contact,
            'start_date'     => $request->start_date,
            'end_date'       => $request->end_date,
            'rate_per_day'   => $request->rate_per_day,
            'total_amount'   => $total,
            'deposit_amount' => $request->deposit_amount ?? 0,
            'terms'          => $request->terms,
            'status'         => 'active',
        ]);

        if ($request->rental_type === 'outbound') {
            $asset->update(['status' => 'rented_out']);
        }

        return back()->with('success', 'Rental contract created.');
    }

    // ── CATEGORIES ───────────────────────────────────────────
    public function categories()
    {
        $companyId  = auth()->user()->company_id;
        $categories = AssetCategory::where('company_id',$companyId)
            ->withCount('assets')->get();
        return view('assets.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:100',
            'color' => 'nullable|string|max:7',
        ]);

        AssetCategory::create([
            'company_id'  => auth()->user()->company_id,
            'name'        => $request->name,
            'code'        => strtoupper(substr($request->name, 0, 4)) . rand(10,99),
            'description' => $request->description,
            'icon'        => $request->icon ?? 'fa-box',
            'color'       => $request->color ?? '#C49A3C',
        ]);

        return back()->with('success', "Category \"{$request->name}\" created.");
    }

    // ── MAINTENANCE LIST ─────────────────────────────────────
    public function maintenance(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = MaintenanceRecord::with(['asset','creator'])
            ->where('company_id',$companyId);

        if ($request->filled('status')) $query->where('status',$request->status);
        if ($request->filled('type'))   $query->where('type',$request->type);

        $records = $query->orderBy('scheduled_date')->paginate(15)->withQueryString();

        $stats = [
            'scheduled'   => MaintenanceRecord::where('company_id',$companyId)->where('status','scheduled')->count(),
            'in_progress' => MaintenanceRecord::where('company_id',$companyId)->where('status','in_progress')->count(),
            'completed'   => MaintenanceRecord::where('company_id',$companyId)->where('status','completed')->count(),
            'total_cost'  => MaintenanceRecord::where('company_id',$companyId)->where('status','completed')->sum('cost'),
        ];

        return view('assets.maintenance', compact('records','stats'));
    }

    // ── REPORT ───────────────────────────────────────────────
    public function report(Request $request)
    {
        $companyId   = auth()->user()->company_id;
        $departments = Department::where('company_id',$companyId)->get();

        $query = Asset::with(['category','department','assignments','maintenance'])
            ->where('company_id',$companyId);

        if ($request->filled('type'))       $query->where('type',$request->type);
        if ($request->filled('department')) $query->where('department_id',$request->department);

        $assets = $query->get();

        $summary = [
            'total_assets'    => $assets->count(),
            'total_value'     => $assets->sum('current_value'),
            'total_purchase'  => $assets->sum('purchase_cost'),
            'maintenance_cost'=> MaintenanceRecord::where('company_id',$companyId)
                ->where('status','completed')->sum('cost'),
            'rental_income'   => RentalContract::where('company_id',$companyId)
                ->where('rental_type','outbound')->where('status','completed')->sum('total_amount'),
            'rental_expense'  => RentalContract::where('company_id',$companyId)
                ->where('rental_type','inbound')->sum('total_amount'),
        ];

        return view('assets.report', compact('assets','summary','departments'));
    }

    private function authorizeAsset(Asset $asset): void {
        if ($asset->company_id !== auth()->user()->company_id) abort(403);
    }
}