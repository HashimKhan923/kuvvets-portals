<div x-data="{ uploadOpen: false }">

    {{-- Upload box --}}
    <div class="doc-upload-box" @click="uploadOpen = true">
        <div class="doc-upload-ico"><i class="fa-solid fa-cloud-arrow-up"></i></div>
        <div class="doc-upload-hd">Upload Document</div>
        <div class="doc-upload-sub">CNIC, degrees, certificates, contracts, and more • PDF/JPG/PNG/DOC up to 10MB</div>
    </div>

    {{-- Documents grid --}}
    @if($documents->count())
        <div class="doc-grid">
            @foreach($documents as $doc)
                @php
                    $ext = strtolower(pathinfo($doc->file_name, PATHINFO_EXTENSION));
                    $icoClass = in_array($ext, ['pdf'])
                        ? ['pdf','fa-file-pdf']
                        : (in_array($ext, ['jpg','jpeg','png','webp'])
                            ? ['img','fa-file-image']
                            : (in_array($ext, ['doc','docx'])
                                ? ['doc','fa-file-word']
                                : ['other','fa-file']));

                    $typeLabel = match($doc->type) {
                        'cnic'               => 'CNIC',
                        'passport'           => 'Passport',
                        'contract'           => 'Contract',
                        'offer_letter'       => 'Offer Letter',
                        'experience_letter'  => 'Experience',
                        'degree'             => 'Degree',
                        'certificate'        => 'Certificate',
                        'noc'                => 'NOC',
                        default              => 'Other',
                    };
                @endphp
                <div class="doc-item">
                    <div class="doc-ico-wrap {{ $icoClass[0] }}">
                        <i class="fa-solid {{ $icoClass[1] }}"></i>
                    </div>
                    <div class="doc-body">
                        <div class="doc-title" title="{{ $doc->title }}">{{ $doc->title }}</div>
                        <div class="doc-meta">
                            <span>{{ $typeLabel }}</span>
                            <span class="sep">•</span>
                            <span>{{ $doc->file_size }}</span>
                            <span class="sep">•</span>
                            <span>{{ $doc->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="doc-status">
                            @if($doc->is_verified)
                                <span class="badge badge-green" style="font-size:10px;">
                                    <i class="fa-solid fa-shield-check"></i> Verified
                                </span>
                            @else
                                <span class="badge badge-yellow" style="font-size:10px;">
                                    <i class="fa-solid fa-hourglass-half"></i> Pending
                                </span>
                            @endif
                            @if($doc->isExpired())
                                <span class="badge badge-red" style="font-size:10px;">
                                    <i class="fa-solid fa-circle-xmark"></i> Expired
                                </span>
                            @elseif($doc->isExpiringSoon())
                                <span class="badge badge-yellow" style="font-size:10px;">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    Expires {{ $doc->expiry_date->diffForHumans() }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="doc-actions">
                        <a href="{{ route('employee.profile.documents.download', $doc) }}" class="doc-act-btn" title="Download">
                            <i class="fa-solid fa-download"></i>
                        </a>
                        @if(!$doc->is_verified)
                            <form method="POST" action="{{ route('employee.profile.documents.destroy', $doc) }}"
                                  onsubmit="return confirm('Delete this document? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button class="doc-act-btn danger" title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div style="text-align:center;padding:60px 20px;color:var(--text-muted);background:var(--bg-card);border:1px solid var(--border);border-radius:16px;">
            <i class="fa-solid fa-folder-open" style="font-size:42px;opacity:.25;margin-bottom:12px;display:block;color:var(--accent);"></i>
            <div style="font-size:14px;font-weight:700;color:var(--text-secondary);margin-bottom:4px;">No documents uploaded yet</div>
            <div style="font-size:12px;">Upload your CNIC, degrees, certificates, and other important documents.</div>
        </div>
    @endif

    {{-- Upload modal --}}
    <template x-teleport="body">
        <div x-show="uploadOpen" style="display:none;">
            <div class="modal-overlay" @click.self="uploadOpen = false">
                <div class="modal-box">
                    <div class="modal-hd">
                        <div class="modal-title"><i class="fa-solid fa-cloud-arrow-up" style="color:var(--accent);margin-right:6px;"></i>Upload Document</div>
                        <button class="modal-close" @click="uploadOpen = false"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <form method="POST" action="{{ route('employee.profile.documents.upload') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="form-row full">
                                <div class="field">
                                    <label>Title <span style="color:var(--red);">*</span></label>
                                    <input type="text" name="title" required maxlength="150" placeholder="e.g. CNIC Copy, Bachelor's Degree">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="field">
                                    <label>Type <span style="color:var(--red);">*</span></label>
                                    <select name="type" required>
                                        <option value="cnic">CNIC</option>
                                        <option value="passport">Passport</option>
                                        <option value="degree">Degree</option>
                                        <option value="certificate">Certificate</option>
                                        <option value="contract">Contract</option>
                                        <option value="offer_letter">Offer Letter</option>
                                        <option value="experience_letter">Experience Letter</option>
                                        <option value="noc">NOC</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="field">
                                    <label>File <span style="color:var(--red);">*</span></label>
                                    <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="field">
                                    <label>Issue Date</label>
                                    <input type="date" name="issue_date">
                                </div>
                                <div class="field">
                                    <label>Expiry Date</label>
                                    <input type="date" name="expiry_date">
                                </div>
                            </div>
                            <div class="form-row full">
                                <div class="field">
                                    <label>Notes <span class="hint">(optional)</span></label>
                                    <textarea name="notes" maxlength="500" placeholder="Any extra info..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div style="padding:14px 22px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end;">
                            <button type="button" class="btn btn-secondary" @click="uploadOpen = false">Cancel</button>
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-upload"></i> Upload</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>