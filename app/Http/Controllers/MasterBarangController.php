<?php

namespace App\Http\Controllers;

use App\Repositories\MasterBarangRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MasterBarangController extends Controller
{
    public function __construct(private MasterBarangRepository $masterBarangRepository)
    {
    }

    public function index()
    {
        return view('master-barang.index');
    }

    public function data(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 10);
        $page = (int) $request->integer('page', 1);
        $search = $request->string('search')->toString();

        return response()->json(
            $this->masterBarangRepository->paginate($perPage, $page, $search !== '' ? $search : null)
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_barang' => ['required', 'string', 'max:100'],
            'kategori' => ['required', 'string', 'max:50'],
        ]);

        $mbid = $this->masterBarangRepository->create($validated);

        return response()->json($this->masterBarangRepository->find($mbid), 201);
    }

    public function update(Request $request, int $mbid)
    {
        $masterBarang = $this->masterBarangRepository->find($mbid);
        abort_if(! $masterBarang, 404);

        $validated = $request->validate([
            'nama_barang' => ['required', 'string', 'max:100'],
            'kategori' => ['required', 'string', 'max:50'],
        ]);

        $this->masterBarangRepository->update($mbid, $validated);

        return response()->json($this->masterBarangRepository->find($mbid));
    }

    public function toggleAktif(int $mbid)
    {
        $masterBarang = $this->masterBarangRepository->find($mbid);
        abort_if(! $masterBarang, 404);

        $aktifBaru = ! (bool) $masterBarang->aktif;

        if (! $aktifBaru && $this->masterBarangRepository->isUsedByActiveProduk($mbid)) {
            return response()->json([
                'message' => 'Master barang masih dipakai produk aktif, tidak bisa dinonaktifkan.',
            ], 422);
        }

        $this->masterBarangRepository->setAktif($mbid, $aktifBaru);

        return response()->json($this->masterBarangRepository->find($mbid));
    }
}
