@extends('layouts.app')
@section('title', 'All Documents')
@section('page-title', 'Document Library')
@section('breadcrumb', 'Documents · All Documents')

@section('content')

{{-- Toolbar --}}
<div class="card card-sm" style="margin-bottom:18px;">
    <div class="toolbar">
        <form method="GET" action="{{ route('documents.list') }}" class="toolbar" style="flex:1;">
            <div class="toolbar-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Title, number, tags…" class="form-input">
            </div>
            <select name="type" class="form-select">
                <option value="">All Types</option>
                @foreach(['policy'=>'Policy','procedure'=>'Procedure','contract'=>'Contract','certificate'=>'Certificate','compliance'=>'Compliance','hr_document'=>'HR Document','legal'=>'Legal','financial'=>'Financial','training'=>'Training','other'=>'Other'] as $v => $l)
                <option value="{{ $v }}" {{ request('type') === $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
            <select name="category" class="form-select" style="min-width:150px;">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select">
                <option value="">All Status</option>
                @foreach(['active'=>'Active','expired'=>'Expired','archived'=>'Archived','draft'=>'Draft'] as $v => $l)
                <option value="{{ $v }}" {{ request('status') === $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-filter"></i>
            </button>
            @if(request()->hasAny(['search','type','category','status','employee']))
            <a href="{{ route('documents.list') }}" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-xmark"></i> Clear
            </a>
            @endif
        </form>
        <button onclick="document.getElementById('uploadModal').classList.add('open')"
                class="btn btn-primary btn-sm">
            <i class="fa-solid fa-upload"></i> Upload
        </button>
    </div>
</div>

{{-- Table --}}
<div class="card card-flush">
    @include('documents._document_table', ['documents' => $documents])

    @if($documents->hasPages())
    <div class="pagination">
        <span class="pagination-info">
            Showing {{ $documents->firstItem() }}–{{ $documents->lastItem() }} of {{ $documents->total() }}
        </span>
        <div class="pagination-btns">
            @if($documents->onFirstPage())<span class="page-btn disabled">← Prev</span>
            @else<a href="{{ $documents->previousPageUrl() }}" class="page-btn">← Prev</a>@endif
            @if($documents->hasMorePages())<a href="{{ $documents->nextPageUrl() }}" class="page-btn active">Next →</a>
            @else<span class="page-btn disabled">Next →</span>@endif
        </div>
    </div>
    @endif
</div>

{{-- Upload Modal --}}
@include('documents._upload_modal')

@endsection