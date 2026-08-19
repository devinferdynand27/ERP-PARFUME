@extends('layouts.app')

@section('title', 'Edit Permintaan Barang - CAVA Parfums')

@section('content')
    <div
        data-vue-component="PermintaanBarangForm"
        data-vue-props="{{ json_encode([
            'pbid' => $pbid,
            'formOptionsUrl' => route('permintaan-barang.form-options'),
            'showUrl' => route('permintaan-barang.show', ['pbid' => $pbid]),
            'updateUrl' => route('permintaan-barang.update', ['pbid' => $pbid]),
            'indexUrl' => route('permintaan-barang.index'),
        ]) }}"
    ></div>
@endsection
