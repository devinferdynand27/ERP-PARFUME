@extends('layouts.app')

@section('title', 'Master Produk - CAVA Parfums')

@section('content')
    <div
        data-vue-component="ProdukManager"
        data-vue-props="{{ json_encode([
            'dataUrl' => route('produk.data'),
            'formOptionsUrl' => route('produk.form-options'),
            'storeUrl' => route('produk.store'),
            'updateUrlTemplate' => route('produk.update', ['prid' => '__prid__']),
            'toggleUrlTemplate' => route('produk.toggle-aktif', ['prid' => '__prid__']),
        ]) }}"
    ></div>
@endsection
