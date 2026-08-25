@extends('layouts.app')

@section('title', 'Terima Barang - ERP PARFUME')

@section('content')
    <div
        data-vue-component="PenerimaanBarangForm"
        data-vue-props="{{ json_encode([
            'formOptionsUrl' => route('penerimaan-barang.form-options'),
            'storeUrl' => route('penerimaan-barang.store'),
            'indexUrl' => route('penerimaan-barang.index'),
        ]) }}"
    ></div>
@endsection
