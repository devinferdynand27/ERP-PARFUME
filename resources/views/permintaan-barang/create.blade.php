@extends('layouts.app')

@section('title', 'Buat Permintaan Barang - CAVA Parfums')

@section('content')
    <div
        data-vue-component="PermintaanBarangForm"
        data-vue-props="{{ json_encode([
            'formOptionsUrl' => route('permintaan-barang.form-options'),
            'masterBarangDataUrl' => route('permintaan-barang.master-barang-data'),
            'storeUrl' => route('permintaan-barang.store'),
            'indexUrl' => route('permintaan-barang.index'),
        ]) }}"
    ></div>
@endsection
