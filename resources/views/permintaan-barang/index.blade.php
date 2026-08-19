@extends('layouts.app')

@section('title', 'Permintaan Barang - CAVA Parfums')

@section('content')
    <div
        data-vue-component="PermintaanBarangManager"
        data-vue-props="{{ json_encode([
            'dataUrl' => route('permintaan-barang.data'),
            'createUrl' => route('permintaan-barang.create'),
            'editUrlTemplate' => route('permintaan-barang.edit', ['pbid' => '__pbid__']),
            'statusUrlTemplate' => route('permintaan-barang.status', ['pbid' => '__pbid__']),
        ]) }}"
    ></div>
@endsection
