<table class="data-table">
    <thead>
        <tr>
            <th>Document</th>
            <th>Type</th>
            <th>Employee</th>
            <th>Size</th>
            <th>Status</th>
            <th>Expiry</th>
            <th class="center">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($documents as $doc)
        @php
            $sBadge = $doc->status_badge;
            $tBadge = $doc->type_badge;
        @endphp
        <tr>
            <td>
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:36px;height:36px;flex-shrink:0;
                                background:{{ $doc->file_icon_color }}15;
                                border:1px solid {{ $doc->file_icon_color }}30;
                                border-radius:8px;display:flex;align-items:center;justify-content:center;">
                        <i class="fa-solid {{ $doc->file_icon }}"
                           style="font-size:15px;color:{{ $doc->file_icon_color }};"></i>
                    </div>
                    <div>
                        <a href="{{ route('documents.show', $doc) }}"
                           style="font-size:13px;font-weight:600;color:var(--text-primary);text-decoration:none;">
                            {{ $doc->title }}
                        </a>
                        <div style="font-size:10px;color:var(--text-muted);">
                            v{{ $doc->version }}
                            @if($doc->document_number) · {{ $doc->document_number }} @endif
                            · {{ $doc->file_size_formatted }}
                        </div>
                    </div>
                </div>
            </td>
            <td>
                <span class="badge" style="background:{{ $tBadge['bg'] }};color:{{ $tBadge['color'] }};border:1px solid {{ $tBadge['border'] }};font-size:10px;">
                    {{ ucfirst(str_replace('_', ' ', $doc->type)) }}
                </span>
            </td>
            <td>
                @if($doc->employee)
                <div style="display:flex;align-items:center;gap:7px;font-size:12px;color:var(--text-secondary);">
                    <img src="{{ $doc->employee->avatar_url }}"
                         style="width:22px;height:22px;border-radius:50%;object-fit:cover;border:1px solid var(--accent-border);">
                    {{ $doc->employee->full_name }}
                </div>
                @else
                <span style="font-size:12px;color:var(--text-muted);">Company</span>
                @endif
            </td>
            <td class="muted">{{ $doc->file_size_formatted }}</td>
            <td>
                <span class="badge" style="background:{{ $sBadge['bg'] }};color:{{ $sBadge['color'] }};border:1px solid {{ $sBadge['border'] }};">
                    {{ ucfirst($doc->status) }}
                </span>
            </td>
            <td>
                @if($doc->expiry_date)
                <span style="font-size:12px;font-weight:500;
                             color:{{ $doc->isExpired() ? 'var(--red)' : ($doc->isExpiringSoon() ? 'var(--yellow)' : 'var(--text-secondary)') }};">
                    {{ $doc->expiry_date->format('d M Y') }}
                    @if($doc->isExpired()) ⚠️
                    @elseif($doc->isExpiringSoon()) 🔔
                    @endif
                </span>
                @else
                <span class="text-muted" style="font-size:12px;">No Expiry</span>
                @endif
            </td>
            <td class="center">
                <div style="display:flex;align-items:center;justify-content:center;gap:5px;">
                    <a href="{{ route('documents.show', $doc) }}" class="action-btn" title="View">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                    <a href="{{ route('documents.download', $doc) }}" class="action-btn" title="Download">
                        <i class="fa-solid fa-download"></i>
                    </a>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7">
                <div class="empty-state">
                    <i class="fa-solid fa-file-lines"></i>
                    No documents found.
                </div>
            </td>
        </tr>
        @endforelse
    </tbody>
</table>