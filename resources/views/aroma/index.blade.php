@extends('layouts.app')

@section('title', 'Master Aroma - CAVA Parfums')

@section('content')
    <div
        data-vue-component="AromaManager"
        data-vue-props="{{ json_encode([
            'dataUrl' => route('aroma.data'),
            'storeUrl' => route('aroma.store'),
            'updateUrlTemplate' => route('aroma.update', ['arid' => '__arid__']),
            'toggleUrlTemplate' => route('aroma.toggle-aktif', ['arid' => '__arid__']),
        ]) }}"
    ></div>
@endsection
