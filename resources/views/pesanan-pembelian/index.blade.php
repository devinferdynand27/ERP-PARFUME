@extends('layouts.app')

@section('title', 'Pesanan Pembelian - ERP PARFUME')

@section('content')
    <div
        data-vue-component="PesananPembelianManager"
        data-vue-props="{{ json_encode([
            'dataUrl' => route('pesanan-pembelian.data'),
            'createUrl' => route('pesanan-pembelian.create'),
            'statusUrlTemplate' => route('pesanan-pembelian.status', ['ppid' => '__ppid__']),
            'printUrlTemplate' => route('pesanan-pembelian.print', ['ppid' => '__ppid__']),
            'editUrlTemplate' => route('pesanan-pembelian.edit', ['ppid' => '__ppid__']),
        ]) }}"
    ></div>
@endsection
