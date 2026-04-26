<x-emails.layout>
    <div class="status-banner {{ $document->isExpired() ? 'red' : 'yellow' }}">
        <span class="status-icon">{{ $document->isExpired() ? '🚨' : '⏰' }}</span>
        <div class="status-title">
            {{ $document->isExpired() ? 'Document Expired' : 'Document Expiring Soon' }}
        </div>
        <div class="status-sub">
            {{ $document->isExpired()
                ? 'Action required: update your document immediately.'
                : 'Please renew your document before it expires.' }}
        </div>
    </div>

    <p class="greeting">Hi <strong>{{ $document->employee->first_name }}</strong>,</p>

    <p class="body-text">
        @if($document->isExpired())
            Your document <strong>{{ $document->title }}</strong> has <strong style="color:#DC2626;">expired</strong>. Please upload a renewed copy as soon as possible.
        @else
            Your document <strong>{{ $document->title }}</strong> will expire on
            <strong>{{ $document->expiry_date->format('F j, Y') }}</strong>
            — that's <strong>{{ $document->expiry_date->diffInDays(now()) }} days away</strong>.
            Please arrange for renewal.
        @endif
    </p>

    <div class="detail-box">
        <div class="detail-box-title">Document Details</div>
        <div class="detail-row">
            <span class="detail-label">Document</span>
            <span class="detail-value">{{ $document->title }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Type</span>
            <span class="detail-value">{{ ucfirst(str_replace('_',' ',$document->type)) }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Expiry Date</span>
            <span class="detail-value" style="color:{{ $document->isExpired() ? '#DC2626' : '#D97706' }};">
                {{ $document->expiry_date->format('F j, Y') }}
                @if($document->isExpired()) (Expired) @else (In {{ $document->expiry_date->diffInDays(now()) }} days) @endif
            </span>
        </div>
    </div>

    <div class="cta-wrap">
        <a href="{{ url('/employee/profile?tab=documents') }}" class="cta-btn">Upload New Document →</a>
    </div>
</x-emails.layout>