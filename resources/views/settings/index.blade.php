@extends('layouts.app')
@section('title','Settings')
@section('page-title','Settings')
@section('breadcrumb','System · Settings')

@section('content')

{{-- Company Banner --}}
<div style="background:linear-gradient(135deg,var(--navy) 0%,var(--navy-light) 100%);
            border-radius:14px;padding:24px 28px;margin-bottom:24px;
            display:flex;align-items:center;justify-content:space-between;
            box-shadow:0 4px 20px rgba(28,35,49,.15);">
    <div style="display:flex;align-items:center;gap:16px;">
        <div style="width:60px;height:60px;background:rgba(196,154,60,.15);
                    border:2px solid var(--gold);border-radius:12px;
                    display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            @if($company->logo)
            <img src="{{ asset('storage/'.$company->logo) }}"
                 style="width:100%;height:100%;object-fit:cover;border-radius:10px;">
            @else
            <span style="font-size:28px;color:var(--gold);">⬡</span>
            @endif
        </div>
        <div>
            <div style="font-family:'Space Grotesk',sans-serif;font-size:20px;font-weight:700;
                        color:#F0D080;">{{ $company->name }}</div>
            <div style="font-size:12px;color:#94A3B8;margin-top:2px;">
                {{ $company->city }}, {{ $company->country }}
                @if($company->ntn) · NTN: {{ $company->ntn }} @endif
            </div>
        </div>
    </div>
    <a href="{{ route('settings.company') }}"
       style="padding:9px 18px;background:rgba(196,154,60,.2);
              border:1px solid rgba(196,154,60,.4);border-radius:8px;
              color:#F0D080;font-size:12px;font-weight:600;text-decoration:none;">
        <i class="fa-solid fa-pen" style="margin-right:5px;"></i> Edit Profile
    </a>
</div>

{{-- Settings Grid --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:18px;">

    @php
    $settingCards = [
        [
            'title' => 'Company Profile',
            'desc'  => 'Company name, logo, address, NTN, STRN, contact information.',
            'icon'  => 'fa-building',
            'color' => '#2B6CB0',
            'bg'    => '#EBF8FF',
            'border'=> '#BEE3F8',
            'url'   => route('settings.company'),
            'items' => ['Logo & Branding','Address','Tax Numbers (NTN/STRN)','Contact Info'],
        ],
        [
            'title' => 'HR Policies',
            'desc'  => 'Leave quotas, probation period, notice period, working hours.',
            'icon'  => 'fa-file-lines',
            'color' => '#2D7A4F',
            'bg'    => '#F0FBF4',
            'border'=> '#B8E4CA',
            'url'   => route('settings.hr'),
            'items' => ['Leave Days','Probation Period','Notice Period','Working Hours'],
        ],
        [
            'title' => 'Payroll Settings',
            'desc'  => 'Payroll cycle, EOBI, PESSI, minimum wage, bank details.',
            'icon'  => 'fa-money-check-dollar',
            'color' => '#C49A3C',
            'bg'    => '#FBF5E6',
            'border'=> '#E8D5A3',
            'url'   => route('settings.payroll'),
            'items' => ['Payroll Cycle','EOBI/PESSI Toggle','Minimum Wage','Bank Details'],
        ],
        [
            'title' => 'Users & Access',
            'desc'  => 'Admin users, portal access management, account activation.',
            'icon'  => 'fa-users-gear',
            'color' => '#6B46C1',
            'bg'    => '#FAF5FF',
            'border'=> '#D6BCFA',
            'url'   => route('settings.users'),
            'items' => ['Create Admin Users','Assign Roles','Activate/Deactivate','Reset Passwords'],
        ],
        [
            'title' => 'Roles & Permissions',
            'desc'  => 'View role capabilities and permission matrix.',
            'icon'  => 'fa-shield-halved',
            'color' => '#B7791F',
            'bg'    => '#FFFBEB',
            'border'=> '#F6E05E',
            'url'   => route('settings.roles'),
            'items' => ['Role Overview','Permission Matrix','Spatie Roles','Access Control'],
        ],
        [
            'title' => 'Audit Log',
            'desc'  => 'Track all system actions — logins, updates, deletions.',
            'icon'  => 'fa-shield-check',
            'color' => '#C53030',
            'bg'    => '#FFF5F5',
            'border'=> '#FEB2B2',
            'url'   => route('settings.audit-log'),
            'items' => ['Login History','Data Changes','Exports','User Actions'],
        ],
        [
            'title' => 'My Profile',
            'desc'  => 'Update your personal details, avatar and password.',
            'icon'  => 'fa-user-circle',
            'color' => '#2C7A7B',
            'bg'    => '#E6FFFA',
            'border'=> '#81E6D9',
            'url'   => route('settings.profile'),
            'items' => ['Name & Email','Avatar','Change Password','Username'],
        ],
    ];
    @endphp

    @foreach($settingCards as $card)
    <div class="card card-gold" style="padding:24px;cursor:pointer;transition:all .25s;"
         onclick="window.location='{{ $card['url'] }}'"
         onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 24px rgba(196,154,60,.15)'"
         onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none'">
        <div style="display:flex;align-items:flex-start;gap:14px;margin-bottom:14px;">
            <div style="width:46px;height:46px;flex-shrink:0;border-radius:11px;
                        background:{{ $card['bg'] }};border:1px solid {{ $card['border'] }};
                        display:flex;align-items:center;justify-content:center;">
                <i class="fa-solid {{ $card['icon'] }}"
                   style="font-size:19px;color:{{ $card['color'] }};"></i>
            </div>
            <div>
                <div style="font-family:'Space Grotesk',sans-serif;font-size:15px;font-weight:700;
                            color:var(--text-primary);margin-bottom:4px;">
                    {{ $card['title'] }}
                </div>
                <div style="font-size:12px;color:var(--text-secondary);line-height:1.5;">
                    {{ $card['desc'] }}
                </div>
            </div>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:5px;margin-bottom:16px;">
            @foreach($card['items'] as $item)
            <span style="font-size:10px;background:{{ $card['bg'] }};
                         color:{{ $card['color'] }};border:1px solid {{ $card['border'] }};
                         border-radius:20px;padding:2px 9px;font-weight:500;">
                {{ $item }}
            </span>
            @endforeach
        </div>
        <a href="{{ $card['url'] }}"
           style="display:block;padding:9px;background:{{ $card['bg'] }};
                  border:1px solid {{ $card['border'] }};border-radius:8px;
                  color:{{ $card['color'] }};font-size:12px;font-weight:600;
                  text-decoration:none;text-align:center;"
           onclick="event.stopPropagation()">
            Configure →
        </a>
    </div>
    @endforeach
</div>
@endsection