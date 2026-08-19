@extends('layouts.app')

@section('title', 'Penerimaan Barang - CAVA Parfums')

@section('content')
    <div
        data-vue-component="PenerimaanBarangManager"
        data-vue-props="{{ json_encode([
            'dataUrl' => route('penerimaan-barang.data'),
            'createUrl' => route('penerimaan-barang.create'),
        ]) }}"
    ></div>
@endsection
