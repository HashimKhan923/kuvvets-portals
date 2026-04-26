@extends('layouts.app')
@section('title', 'Add Employee')
@section('page-title', 'Add New Employee')
@section('breadcrumb', 'Employees · New')

@section('content')
<form method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data" id="empForm">
@csrf
@include('employees._form', ['employee' => null, 'nextId' => $nextId])
</form>
@endsection