<?php
namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\DocumentShare;
use App\Models\Employee;
use App\Models\Department;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    // ── DASHBOARD ────────────────────────────────────────────
    public function index()
    {
        $companyId = auth()->user()->company_id;

        $stats = [
            'total'         => Document::where('company_id', $companyId)->count(),
            'active'        => Document::where('company_id', $companyId)->where('status', 'active')->count(),
            'expiring_soon' => Document::where('company_id', $companyId)
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '>=', today())
                ->where('expiry_date', '<=', today()->addDays(30))->count(),
            'expired'       => Document::where('company_id', $companyId)
                ->where('expiry_date', '<', today())->count(),
            'total_size'    => Document::where('company_id', $companyId)->sum('file_size'),
        ];

        $categories = DocumentCategory::where('company_id', $companyId)
            ->withCount('documents')
            ->orderBy('sort_order')
            ->get();

        $recentDocuments = Document::with(['category', 'uploader', 'employee'])
            ->where('company_id', $companyId)
            ->latest()->take(8)->get();

        $expiringDocuments = Document::with(['employee', 'category'])
            ->where('company_id', $companyId)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '>=', today())
            ->where('expiry_date', '<=', today()->addDays(60))
            ->orderBy('expiry_date')
            ->take(8)->get();

        // Type distribution
        $typeDist = Document::where('company_id', $companyId)
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type');

        return view('documents.index', compact(
            'stats', 'categories', 'recentDocuments',
            'expiringDocuments', 'typeDist'
        ));
    }

    // ── LIST DOCUMENTS ───────────────────────────────────────
    public function list(Request $request)
    {
        $companyId   = auth()->user()->company_id;
        $categories  = DocumentCategory::where('company_id', $companyId)->get();
        $employees   = Employee::where('company_id', $companyId)
            ->where('employment_status', 'active')
            ->orderBy('first_name')->get();

        $query = Document::with(['category', 'uploader', 'employee'])
            ->where('company_id', $companyId)
            ->where('is_latest_version', true);

        if ($request->filled('category'))
            $query->where('document_category_id', $request->category);
        if ($request->filled('type'))
            $query->where('type', $request->type);
        if ($request->filled('status'))
            $query->where('status', $request->status);
        if ($request->filled('employee'))
            $query->where('employee_id', $request->employee);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%$s%")
                  ->orWhere('document_number', 'like', "%$s%")
                  ->orWhere('tags', 'like', "%$s%");
            });
        }

        $documents = $query->latest()->paginate(15)->withQueryString();

        return view('documents.list', compact('documents', 'categories', 'employees'));
    }

    // ── UPLOAD DOCUMENT ──────────────────────────────────────
    public function upload(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:200',
            'file'        => 'required|file|max:20480', // 20MB
            'type'        => 'required|string',
            'access_level'=> 'required|string',
        ]);

        $companyId = auth()->user()->company_id;
        $file      = $request->file('file');
        $ext       = strtolower($file->getClientOriginalExtension());
        $fileName  = Str::slug($request->title) . '-v1-' . time() . '.' . $ext;
        $filePath  = $file->storeAs(
            "documents/{$companyId}",
            $fileName,
            'private'
        );

        $document = Document::create([
            'company_id'            => $companyId,
            'document_category_id'  => $request->document_category_id,
            'employee_id'           => $request->employee_id,
            'uploaded_by'           => auth()->id(),
            'title'                 => $request->title,
            'document_number'       => $request->document_number,
            'description'           => $request->description,
            'file_path'             => $filePath,
            'file_name'             => $file->getClientOriginalName(),
            'file_type'             => $ext,
            'file_size'             => $file->getSize(),
            'type'                  => $request->type,
            'access_level'          => $request->access_level,
            'status'                => 'active',
            'issue_date'            => $request->issue_date,
            'expiry_date'           => $request->expiry_date,
            'version'               => 1,
            'is_latest_version'     => true,
            'tags'                  => $request->tags,
        ]);

        AuditLog::log('document_uploaded', $document);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'document' => $document]);
        }

        return redirect()->route('documents.show', $document)
            ->with('success', "Document \"{$document->title}\" uploaded successfully.");
    }

    // ── SHOW DOCUMENT ────────────────────────────────────────
    public function show(Document $document)
    {
        $this->authorizeDocument($document);

        $document->increment('view_count');
        $document->load([
            'category', 'uploader', 'employee',
            'versions', 'shares.sharedWith',
        ]);

        $employees = Employee::where('company_id', auth()->user()->company_id)
            ->where('employment_status', 'active')->get();

        $allVersions = collect([$document])
            ->merge($document->versions)
            ->sortByDesc('version');

        return view('documents.show', compact('document', 'employees', 'allVersions'));
    }

    // ── DOWNLOAD DOCUMENT ────────────────────────────────────
    public function download(Document $document)
    {
        $this->authorizeDocument($document);
        $document->increment('download_count');
        AuditLog::log('document_downloaded', $document);

        return Storage::disk('private')->download(
            $document->file_path,
            $document->file_name
        );
    }

    // ── UPLOAD NEW VERSION ───────────────────────────────────
    public function uploadVersion(Request $request, Document $document)
    {
        $this->authorizeDocument($document);

        $request->validate([
            'file'        => 'required|file|max:20480',
            'description' => 'nullable|string',
        ]);

        $companyId  = auth()->user()->company_id;
        $file       = $request->file('file');
        $ext        = strtolower($file->getClientOriginalExtension());
        $newVersion = $document->version + $document->versions()->count() + 1;
        $fileName   = Str::slug($document->title)
                      . '-v' . $newVersion . '-' . time() . '.' . $ext;
        $filePath   = $file->storeAs(
            "documents/{$companyId}",
            $fileName,
            'private'
        );

        // Mark old as not latest
        $document->update(['is_latest_version' => false]);
        $document->versions()->update(['is_latest_version' => false]);

        $newDoc = Document::create([
            'company_id'           => $companyId,
            'document_category_id' => $document->document_category_id,
            'employee_id'          => $document->employee_id,
            'uploaded_by'          => auth()->id(),
            'title'                => $document->title,
            'document_number'      => $document->document_number,
            'description'          => $request->description ?? $document->description,
            'file_path'            => $filePath,
            'file_name'            => $file->getClientOriginalName(),
            'file_type'            => $ext,
            'file_size'            => $file->getSize(),
            'type'                 => $document->type,
            'access_level'         => $document->access_level,
            'status'               => 'active',
            'issue_date'           => $document->issue_date,
            'expiry_date'          => $document->expiry_date,
            'version'              => $newVersion,
            'parent_document_id'   => $document->parent_document_id ?? $document->id,
            'is_latest_version'    => true,
            'tags'                 => $document->tags,
        ]);

        AuditLog::log('document_version_uploaded', $newDoc);

        return back()->with('success', "Version {$newVersion} uploaded successfully.");
    }

    // ── UPDATE DOCUMENT ──────────────────────────────────────
    public function update(Request $request, Document $document)
    {
        $this->authorizeDocument($document);

        $request->validate([
            'title'       => 'required|string|max:200',
            'type'        => 'required|string',
            'access_level'=> 'required|string',
        ]);

        $document->update($request->only([
            'title', 'document_number', 'description', 'document_category_id',
            'employee_id', 'type', 'access_level', 'status',
            'issue_date', 'expiry_date', 'tags',
        ]));

        return back()->with('success', 'Document updated.');
    }

    // ── DELETE DOCUMENT ──────────────────────────────────────
    public function destroy(Document $document)
    {
        $this->authorizeDocument($document);
        Storage::disk('private')->delete($document->file_path);
        $document->delete();
        AuditLog::log('document_deleted', $document);

        return redirect()->route('documents.list')
            ->with('success', 'Document deleted.');
    }

    // ── CATEGORIES ───────────────────────────────────────────
    public function categories()
    {
        $companyId  = auth()->user()->company_id;
        $categories = DocumentCategory::where('company_id', $companyId)
            ->withCount('documents')
            ->orderBy('sort_order')
            ->get();

        return view('documents.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:100',
        ]);

        DocumentCategory::create([
            'company_id'       => auth()->user()->company_id,
            'name'             => $request->name,
            'slug'             => Str::slug($request->name),
            'description'      => $request->description,
            'icon'             => $request->icon ?? 'fa-folder',
            'color'            => $request->color ?? '#C49A3C',
            'requires_expiry'  => $request->boolean('requires_expiry'),
            'sort_order'       => DocumentCategory::where('company_id', auth()->user()->company_id)
                ->max('sort_order') + 1,
        ]);

        return back()->with('success', "Category \"{$request->name}\" created.");
    }

    // ── EMPLOYEE DOCUMENTS ───────────────────────────────────
    public function employeeDocuments(Employee $employee)
    {
        if ($employee->company_id !== auth()->user()->company_id) abort(403);

        $documents = Document::with(['category', 'uploader'])
            ->where('employee_id', $employee->id)
            ->where('is_latest_version', true)
            ->latest()->get();

        $categories = DocumentCategory::where('company_id', auth()->user()->company_id)->get();

        return view('documents.employee', compact('employee', 'documents', 'categories'));
    }

    // ── SEARCH ───────────────────────────────────────────────
    public function search(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $q         = $request->q;

        $results = Document::with(['category', 'employee'])
            ->where('company_id', $companyId)
            ->where('is_latest_version', true)
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%$q%")
                      ->orWhere('document_number', 'like', "%$q%")
                      ->orWhere('description', 'like', "%$q%")
                      ->orWhere('tags', 'like', "%$q%");
            })
            ->take(10)->get();

        return response()->json($results->map(fn($d) => [
            'id'    => $d->id,
            'title' => $d->title,
            'type'  => $d->type,
            'category' => $d->category?->name,
            'url'   => route('documents.show', $d),
        ]));
    }

    private function authorizeDocument(Document $document): void {
        if ($document->company_id !== auth()->user()->company_id) abort(403);
    }
}