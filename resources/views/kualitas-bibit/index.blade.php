@extends('layouts.app')

@section('title', 'Master Kualitas Bibit - CAVA Parfums')

@section('content')
    <div
        data-vue-component="KualitasBibitManager"
        data-vue-props="{{ json_encode([
            'dataUrl' => route('kualitas-bibit.data'),
            'storeUrl' => route('kualitas-bibit.store'),
            'updateUrlTemplate' => route('kualitas-bibit.update', ['kbid' => '__kbid__']),
            'toggleUrlTemplate' => route('kualitas-bibit.toggle-aktif', ['kbid' => '__kbid__']),
        ]) }}"
    ></div>
@endsection
