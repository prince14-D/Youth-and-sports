@extends('layouts.app')
@section('title', $employee->full_name . ' — Profile')
@section('page-title', $employee->full_name)
@section('breadcrumb', 'HRMS › Employees › ' . $employee->employee_no)

@section('content')
    @livewire('employees.show', ['employee' => $employee])
@endsection
