@extends('layouts.app')
@section('title', 'Register Employee')
@section('page-title', 'Register New Employee')
@section('breadcrumb', 'HRMS › Employees › Register')

@section('content')
    @livewire('employees.create')
@endsection
