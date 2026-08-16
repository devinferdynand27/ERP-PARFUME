@extends('layouts.app')

@section('title', 'Master Supplier - CAVA Parfums')

@section('content')
    <div
        data-vue-component="SupplierManager"
        data-vue-props="{{ json_encode([
            'dataUrl' => route('supplier.data'),
            'storeUrl' => route('supplier.store'),
            'updateUrlTemplate' => route('supplier.update', ['spid' => '__spid__']),
            'toggleUrlTemplate' => route('supplier.toggle-aktif', ['spid' => '__spid__']),
        ]) }}"
    ></div>
@endsection
