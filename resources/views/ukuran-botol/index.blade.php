@extends('layouts.app')

@section('title', 'Master Ukuran Botol - CAVA Parfums')

@section('content')
    <div
        data-vue-component="UkuranBotolManager"
        data-vue-props="{{ json_encode([
            'dataUrl' => route('ukuran-botol.data'),
            'storeUrl' => route('ukuran-botol.store'),
            'updateUrlTemplate' => route('ukuran-botol.update', ['ubid' => '__ubid__']),
            'toggleUrlTemplate' => route('ukuran-botol.toggle-aktif', ['ubid' => '__ubid__']),
        ]) }}"
    ></div>
@endsection
