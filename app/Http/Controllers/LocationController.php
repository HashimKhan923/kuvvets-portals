<?php
namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\Location;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * List all locations.
     */
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = Location::where('company_id', $companyId)
            ->withCount('employees')
            ->orderBy('name');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('code', 'like', "%{$s}%")
                  ->orWhere('city', 'like', "%{$s}%");
            });
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $locations = $query->paginate(20)->withQueryString();

        $stats = [
            'total'     => Location::where('company_id', $companyId)->count(),
            'active'    => Location::where('company_id', $companyId)->where('is_active', true)->count(),
            'warehouse' => Location::where('company_id', $companyId)->where('type','warehouse')->count(),
            'office'    => Location::where('company_id', $companyId)->where('type','office')->count(),
        ];

        return view('locations.index', compact('locations','stats'));
    }

    /**
     * Create form.
     */
    public function create()
    {
        return view('locations.create');
    }

    /**
     * Store.
     */
    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        $data['company_id']  = auth()->user()->company_id;
        $data['qr_token']    = Location::generateQrToken();
        $data['is_active']   = $request->boolean('is_active', true);

        $location = Location::create($data);

        AuditLog::log('location_created', $location, [], $data);

        return redirect()->route('locations.show', $location)
            ->with('success', 'Location "' . $location->name . '" created successfully.');
    }

    /**
     * Show single.
     */
    public function show(Location $location)
    {
        $this->authorizeCompany($location);
        $location->load([
            'employees' => fn($q) => $q->with('department','designation')
                                        ->orderBy('first_name'),
        ]);

        // Recent attendance at this location
        $recentAttendance = \App\Models\Attendance::with('employee')
            ->where('location_id', $location->id)
            ->orderByDesc('date')
            ->orderByDesc('check_in')
            ->limit(10)
            ->get();

        return view('locations.show', compact('location','recentAttendance'));
    }

    /**
     * Edit.
     */
    public function edit(Location $location)
    {
        $this->authorizeCompany($location);
        return view('locations.edit', compact('location'));
    }

    /**
     * Update.
     */
    public function update(Request $request, Location $location)
    {
        $this->authorizeCompany($location);

        $data = $request->validate($this->rules($location->id));
        $data['is_active'] = $request->boolean('is_active', true);

        $before = $location->only(array_keys($data));
        $location->update($data);

        AuditLog::log('location_updated', $location, $before, $data);

        return redirect()->route('locations.show', $location)
            ->with('success', 'Location updated.');
    }

    /**
     * Delete.
     */
    public function destroy(Location $location)
    {
        $this->authorizeCompany($location);

        if ($location->employees()->count() > 0) {
            return back()->with('error', 'Cannot delete: this location has ' . $location->employees()->count() . ' employee(s) assigned. Unassign them first.');
        }

        $name = $location->name;
        AuditLog::log('location_deleted', $location);
        $location->delete();

        return redirect()->route('locations.index')
            ->with('success', "Location '{$name}' deleted.");
    }

    /**
     * Rotate QR token (invalidates old QR codes).
     */
    public function rotateQr(Location $location)
    {
        $this->authorizeCompany($location);

        $location->update([
            'qr_token'       => Location::generateQrToken(),
            'qr_rotated_at'  => now(),
        ]);

        AuditLog::log('location_qr_rotated', $location);

        return back()->with('success', 'QR token regenerated. Old printed QR codes will no longer work.');
    }

    /**
     * Stream QR as SVG (scannable — contains qr_token).
     */
    public function qrSvg(Location $location)
    {
        // $this->authorizeCompany($location);

        $renderer = new ImageRenderer(
            new RendererStyle(420, 1),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $svg = $writer->writeString($location->qr_token);

        return response($svg, 200, [
            'Content-Type'        => 'image/svg+xml',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate',
            'Content-Disposition' => 'inline; filename="qr-'.$location->code.'.svg"',
        ]);
    }

    /**
     * Printable QR page (A4-friendly).
     */
    public function print(Location $location)
    {
        $this->authorizeCompany($location);
        return view('locations.print', compact('location'));
    }

    /**
     * Assign / unassign employees.
     */
    public function assign(Request $request, Location $location)
    {
        $this->authorizeCompany($location);

        $data = $request->validate([
            'employee_ids'   => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
            'is_primary'     => 'nullable|boolean',
            'assigned_from'  => 'nullable|date',
            'assigned_until' => 'nullable|date|after_or_equal:assigned_from',
        ]);

        // Verify employees belong to same company
        $companyId = auth()->user()->company_id;
        $employees = Employee::whereIn('id', $data['employee_ids'])
            ->where('company_id', $companyId)
            ->pluck('id');

        $sync = [];
        foreach ($employees as $eid) {
            $sync[$eid] = [
                'is_primary'     => (bool) $request->boolean('is_primary'),
                'assigned_from'  => $data['assigned_from']  ?? null,
                'assigned_until' => $data['assigned_until'] ?? null,
            ];
        }

        $location->employees()->syncWithoutDetaching($sync);

        // If is_primary, unset primary for other locations of these employees
        if ($request->boolean('is_primary')) {
            foreach ($employees as $eid) {
                \DB::table('employee_locations')
                    ->where('employee_id', $eid)
                    ->where('location_id', '!=', $location->id)
                    ->update(['is_primary' => false]);
            }
        }

        AuditLog::log('location_employees_assigned', $location, [], [
            'count' => $employees->count(),
        ]);

        return back()->with('success', $employees->count() . ' employee(s) assigned to ' . $location->name);
    }

    public function unassign(Location $location, Employee $employee)
    {
        $this->authorizeCompany($location);
        if ($employee->company_id !== auth()->user()->company_id) abort(403);

        $location->employees()->detach($employee->id);
        AuditLog::log('location_employee_unassigned', $location, [], [
            'employee' => $employee->employee_id,
        ]);

        return back()->with('success', $employee->full_name . ' unassigned from ' . $location->name);
    }

    // ───────────────────────────────────────────────────────────
    protected function authorizeCompany(Location $location): void
    {
        if ($location->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized');
        }
    }

    protected function rules(?int $ignoreId = null): array
    {
        $codeRule = 'required|string|max:30|alpha_dash|unique:locations,code';
        if ($ignoreId) $codeRule .= ',' . $ignoreId;

        return [
            'code'          => $codeRule,
            'name'          => 'required|string|max:150',
            'type'          => 'required|in:warehouse,office,site,branch,other',
            'address'       => 'nullable|string|max:500',
            'city'          => 'nullable|string|max:100',
            'province'      => 'nullable|string|max:100',
            'latitude'      => 'required|numeric|between:-90,90',
            'longitude'     => 'required|numeric|between:-180,180',
            'radius_meters' => 'required|integer|min:20|max:5000',
            'notes'         => 'nullable|string|max:1000',
        ];
    }
}