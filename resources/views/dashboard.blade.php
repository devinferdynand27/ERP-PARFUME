@extends('layouts.app')

@section('title', 'Dashboard - ERP PARFUME')

@section('content')
<div 
    data-vue-component="DashboardManager"
    data-vue-props="{{ json_encode($dashboardData) }}"
></div>
@endsection
