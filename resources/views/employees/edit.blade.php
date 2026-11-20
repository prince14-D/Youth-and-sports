@extends('layouts.app')
@section('title', 'Edit ' . $employee->full_name)
@section('page-title', 'Edit Employee Profile')
@section('breadcrumb', 'HRMS › Employees › ' . $employee->employee_no . ' › Edit')

@section('content')
    @livewire('employees.edit', ['employee' => $employee])
@endsection
