@extends('layouts.app')
@section('title', 'Employees')
@section('page-title', 'Employee Management')
@section('breadcrumb', 'HRMS › Employees')

@section('content')
    <livewire:employees.index />
@endsection
