@extends('layouts.app')
@section('title', 'Bulk Import Employees')
@section('page-title', 'Bulk Employee Migration')
@section('breadcrumb', 'HRMS › Employees › Import')

@section('content')
    @livewire('employees.import')
@endsection
