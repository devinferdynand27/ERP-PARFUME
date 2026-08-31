@extends('layouts.app')

@section('title', 'Master Kualitas - CAVA Parfums')

@section('content')
    <div
        data-vue-component="KualitasManager"
        data-vue-props="{{ json_encode([
            'dataUrl' => route('kualitas.data'),
            'storeUrl' => route('kualitas.store'),
            'updateUrlTemplate' => route('kualitas.update', ['kuid' => '__kuid__']),
            'toggleUrlTemplate' => route('kualitas.toggle-aktif', ['kuid' => '__kuid__']),
        ]) }}"
    ></div>
@endsection
