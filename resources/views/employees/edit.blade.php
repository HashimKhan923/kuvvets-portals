@extends('layouts.app')
@section('title', 'Edit Employee')
@section('page-title', 'Edit Employee Profile')
@section('breadcrumb', 'Employees · ' . $employee->full_name . ' · Edit')

@section('content')
<form method="POST" action="{{ route('employees.update', $employee) }}" enctype="multipart/form-data" id="empForm">
@csrf @method('PUT')
@include('employees._form', ['employee' => $employee, 'nextId' => $employee->employee_id])
</form>
@endsection