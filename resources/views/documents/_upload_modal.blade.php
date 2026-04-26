<div id="uploadModal" class="modal-overlay">
    <div class="modal-box" style="width:600px;max-height:90vh;overflow-y:auto;">

        <div class="modal-title">
            <i class="fa-solid fa-upload"></i> Upload Document
        </div>

        <form method="POST" action="{{ route('documents.upload') }}" enctype="multipart/form-data">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">

                {{-- Drop Zone --}}
                <div style="grid-column:span 2;">
                    <label class="form-label">File <span style="color:var(--red);">*</span></label>
                    <div id="dropZone"
                         style="border:2px dashed var(--accent-border);border-radius:10px;
                                padding:28px;text-align:center;cursor:pointer;
                                background:var(--accent-bg);transition:border-color .2s;"
                         onclick="document.getElementById('fileInput').click()"
                         ondragover="event.preventDefault();this.style.borderColor='var(--accent)'"
                         ondragleave="this.style.borderColor='var(--accent-border)'"
                         ondrop="handleFileDrop(event)">
                        <i class="fa-solid fa-cloud-arrow-up"
                           style="font-size:32px;color:var(--accent);display:block;margin-bottom:8px;"></i>
                        <div style="font-size:13px;color:var(--text-secondary);margin-bottom:4px;">
                            Drag & drop or click to browse
                        </div>
                        <div style="font-size:11px;color:var(--text-muted);">
                            PDF, DOCX, XLSX, PNG, JPG · Max 20MB
                        </div>
                        <div id="fileSelectedLabel"
                             style="display:none;margin-top:10px;font-size:12px;font-weight:600;color:var(--green);"></div>
                    </div>
                    <input type="file" id="fileInput" name="file" required style="display:none;"
                           onchange="showFileSelected(this)">
                </div>

                {{-- Title --}}
                <div style="grid-column:span 2;">
                    <label class="form-label">Title <span style="color:var(--red);">*</span></label>
                    <input type="text" name="title" required placeholder="Document title" class="form-input">
                </div>

                {{-- Doc Number --}}
                <div>
                    <label class="form-label">Document Number</label>
                    <input type="text" name="document_number" placeholder="e.g. POL-2024-001" class="form-input">
                </div>

                {{-- Type --}}
                <div>
                    <label class="form-label">Type <span style="color:var(--red);">*</span></label>
                    <select name="type" required class="form-select">
                        @foreach(['policy'=>'Policy','procedure'=>'Procedure','contract'=>'Contract','certificate'=>'Certificate','compliance'=>'Compliance','hr_document'=>'HR Document','legal'=>'Legal','financial'=>'Financial','training'=>'Training','other'=>'Other'] as $v => $l)
                        <option value="{{ $v }}">{{ $l }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Category --}}
                <div>
                    <label class="form-label">Category</label>
                    <select name="document_category_id" class="form-select">
                        <option value="">No Category</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Access Level --}}
                <div>
                    <label class="form-label">Access Level</label>
                    <select name="access_level" class="form-select">
                        <option value="hr_only">HR Only</option>
                        <option value="management">Management</option>
                        <option value="public">All Staff</option>
                        <option value="private">Private</option>
                    </select>
                </div>

                {{-- Employee --}}
                <div>
                    <label class="form-label">Link to Employee</label>
                    <select name="employee_id" id="emp-select" class="form-select">
                        <option value="">Company Document</option>
                        @php
                            $empList = \App\Models\Employee::where('company_id', auth()->user()->company_id)
                                ->where('employment_status', 'active')
                                ->orderBy('first_name')->get();
                        @endphp
                        @foreach($empList as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Issue + Expiry --}}
                <div>
                    <label class="form-label">Issue Date</label>
                    <input type="date" name="issue_date" class="form-input">
                </div>
                <div>
                    <label class="form-label">Expiry Date</label>
                    <input type="date" name="expiry_date" class="form-input">
                </div>

                {{-- Tags --}}
                <div style="grid-column:span 2;">
                    <label class="form-label">Tags</label>
                    <input type="text" name="tags" placeholder="Comma separated: policy, HR, 2024" class="form-input">
                </div>

                {{-- Description --}}
                <div style="grid-column:span 2;">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="2" class="form-textarea"></textarea>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('uploadModal').classList.remove('open')">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-upload"></i> Upload Document
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showFileSelected(input) {
    if (input.files && input.files[0]) {
        var file = input.files[0];
        var size = file.size > 1048576
            ? (file.size / 1048576).toFixed(2) + ' MB'
            : (file.size / 1024).toFixed(1) + ' KB';
        var label = document.getElementById('fileSelectedLabel');
        label.style.display = 'block';
        label.textContent   = '✓ ' + file.name + ' (' + size + ')';
        document.getElementById('dropZone').style.borderColor = 'var(--accent)';
    }
}

function handleFileDrop(e) {
    e.preventDefault();
    var dt = e.dataTransfer;
    if (dt.files.length) {
        document.getElementById('fileInput').files = dt.files;
        showFileSelected(document.getElementById('fileInput'));
    }
    e.currentTarget.style.borderColor = 'var(--accent-border)';
}

document.getElementById('uploadModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('open');
});
</script>