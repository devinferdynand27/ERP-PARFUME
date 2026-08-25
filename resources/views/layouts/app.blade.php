<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ERP PARFUME')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-background text-foreground antialiased">
    <div class="flex h-screen overflow-hidden">
        <div class="lg:flex lg:h-screen lg:shrink-0"
            data-vue-component="AppSidebar"
            data-vue-props="{{ json_encode([
                'active' => explode('.', request()->route()?->getName() ?? '')[0] ?? '',
                'userRole' => auth()->user()?->role ?? 'guest',
                'urls' => [
                    'dashboard' => url('/'),
                    'supplier' => route('supplier.index'),
                    'masterBarang' => route('master-barang.index'),
                    'satuan' => route('satuan.index'),
                    'permintaanBarang' => route('permintaan-barang.index'),
                    'pesananPembelian' => route('pesanan-pembelian.index'),
                    'penerimaanBarang' => route('penerimaan-barang.index'),
                ],
            ]) }}"
        ></div>

        <div class="flex flex-1 flex-col h-screen overflow-hidden">
            <div 
                data-vue-component="AppHeader"
                data-vue-props="{{ json_encode([
                    'userName' => auth()->user()?->nama_admin ?? 'Pengguna',
                    'userRole' => auth()->user()?->role ?? 'guest',
                    'logoutUrl' => route('logout'),
                ]) }}"
            ></div>

            <main
                data-page-content
                data-active-menu="{{ explode('.', request()->route()?->getName() ?? '')[0] ?? '' }}"
                class="flex-1 overflow-y-auto p-8"
            >
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
