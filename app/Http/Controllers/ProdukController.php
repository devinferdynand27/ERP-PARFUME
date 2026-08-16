<?php

namespace App\Http\Controllers;

use App\Repositories\AromaRepository;
use App\Repositories\ProdukRepository;
use App\Repositories\UkuranBotolRepository;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function __construct(
        private ProdukRepository $produkRepository,
        private AromaRepository $aromaRepository,
        private UkuranBotolRepository $ukuranBotolRepository,
    ) {
    }

    public function index()
    {
        return view('produk.index');
    }

    public function data(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 10);
        $page = (int) $request->integer('page', 1);
        $search = $request->string('search')->toString();
        $arid = $request->filled('arid') ? (int) $request->integer('arid') : null;

        return response()->json(
            $this->produkRepository->paginate($perPage, $page, $search !== '' ? $search : null, $arid)
        );
    }

    public function formOptions()
    {
        return response()->json([
            'aroma' => $this->aromaRepository->getAktif(),
            'ukuran_botol' => $this->ukuranBotolRepository->getAktif(),
            'kode_produk_berikutnya' => $this->produkRepository->generateKodeProduk(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_produk' => ['required', 'string', 'max:200'],
            'arid' => ['required', 'integer', 'exists:aroma,arid'],
            'harga_beli_default' => ['required', 'numeric', 'min:0'],
            'harga_jual_default' => ['required', 'numeric', 'min:0'],
            'stok' => ['nullable', 'integer', 'min:0'],
            'stok_minimum' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['kode_produk'] = $this->produkRepository->generateKodeProduk();

        $prid = $this->produkRepository->create($validated);

        return response()->json($this->produkRepository->find($prid), 201);
    }

    public function update(Request $request, int $prid)
    {
        $produk = $this->produkRepository->find($prid);
        abort_if(! $produk, 404);

        $validated = $request->validate([
            'nama_produk' => ['required', 'string', 'max:200'],
            'arid' => ['required', 'integer', 'exists:aroma,arid'],
            'harga_beli_default' => ['required', 'numeric', 'min:0'],
            'harga_jual_default' => ['required', 'numeric', 'min:0'],
            'stok_minimum' => ['nullable', 'integer', 'min:0'],
        ]);

        $this->produkRepository->update($prid, $validated);

        return response()->json($this->produkRepository->find($prid));
    }

    public function toggleAktif(int $prid)
    {
        $produk = $this->produkRepository->find($prid);
        abort_if(! $produk, 404);

        $this->produkRepository->setAktif($prid, ! (bool) $produk->aktif);

        return response()->json($this->produkRepository->find($prid));
    }
}
