@extends('layouts.app')

@section('title', 'Pesanan Pembelian - CAVA Parfums')

@section('content')
    <div
        data-vue-component="PesananPembelianManager"
        data-vue-props="{{ json_encode([
            'dataUrl' => route('pesanan-pembelian.data'),
            'createUrl' => route('pesanan-pembelian.create'),
            'statusUrlTemplate' => route('pesanan-pembelian.status', ['ppid' => '__ppid__']),
        ]) }}"
    ></div>
@endsection
