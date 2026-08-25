@extends('layouts.app')

@section('title', 'Edit Pesanan Pembelian - ERP PARFUME')

@section('content')
    <div
        data-vue-component="PesananPembelianForm"
        data-vue-props="{{ json_encode([
            'ppid' => $ppid,
            'formOptionsUrl' => route('pesanan-pembelian.form-options'),
            'dariPermintaanUrlTemplate' => route('pesanan-pembelian.dari-permintaan', ['pbid' => '__pbid__']),
            'showUrl' => route('pesanan-pembelian.show', ['ppid' => $ppid]),
            'storeUrl' => route('pesanan-pembelian.store'),
            'updateUrl' => route('pesanan-pembelian.update', ['ppid' => $ppid]),
            'indexUrl' => route('pesanan-pembelian.index'),
            'permintaanBarangDataUrl' => route('permintaan-barang.data'),
        ]) }}"
    ></div>
@endsection
