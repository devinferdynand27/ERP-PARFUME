@extends('layouts.app')

@section('title', 'Master Barang - CAVA Parfums')

@section('content')
    <div
        data-vue-component="MasterBarangManager"
        data-vue-props="{{ json_encode([
            'dataUrl' => route('master-barang.data'),
            'storeUrl' => route('master-barang.store'),
            'updateUrlTemplate' => route('master-barang.update', ['mbid' => '__mbid__']),
            'toggleUrlTemplate' => route('master-barang.toggle-aktif', ['mbid' => '__mbid__']),
        ]) }}"
    ></div>
@endsection
