@extends('layouts.app')

@section('title', 'Master Satuan - CAVA Parfums')

@section('content')
    <div
        data-vue-component="SatuanManager"
        data-vue-props="{{ json_encode([
            'dataUrl' => route('satuan.data'),
            'storeUrl' => route('satuan.store'),
            'updateUrlTemplate' => route('satuan.update', ['stid' => '__stid__']),
            'toggleUrlTemplate' => route('satuan.toggle-aktif', ['stid' => '__stid__']),
        ]) }}"
    ></div>
@endsection
