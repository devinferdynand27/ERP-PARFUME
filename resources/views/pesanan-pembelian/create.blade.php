@extends('layouts.app')

@section('title', 'Buat Pesanan Pembelian - CAVA Parfums')

@section('content')
    <div
        data-vue-component="PesananPembelianForm"
        data-vue-props="{{ json_encode([
            'formOptionsUrl' => route('pesanan-pembelian.form-options'),
            'dariPermintaanUrlTemplate' => route('pesanan-pembelian.dari-permintaan', ['pbid' => '__pbid__']),
            'storeUrl' => route('pesanan-pembelian.store'),
            'indexUrl' => route('pesanan-pembelian.index'),
            'permintaanBarangDataUrl' => route('permintaan-barang.data'),
        ]) }}"
    ></div>
@endsection
