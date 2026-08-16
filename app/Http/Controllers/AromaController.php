<?php

namespace App\Http\Controllers;

use App\Repositories\AromaRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AromaController extends Controller
{
    public function __construct(private AromaRepository $aromaRepository)
    {
    }

    public function index()
    {
        return view('aroma.index');
    }

    public function data(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 10);
        $page = (int) $request->integer('page', 1);
        $search = $request->string('search')->toString();

        return response()->json(
            $this->aromaRepository->paginate($perPage, $page, $search !== '' ? $search : null)
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_aroma' => ['required', 'string', 'max:100'],
            'kategori' => ['required', 'string', 'max:50'],
        ]);

        $arid = $this->aromaRepository->create($validated);

        return response()->json($this->aromaRepository->find($arid), 201);
    }

    public function update(Request $request, int $arid)
    {
        $aroma = $this->aromaRepository->find($arid);
        abort_if(! $aroma, 404);

        $validated = $request->validate([
            'nama_aroma' => ['required', 'string', 'max:100'],
            'kategori' => ['required', 'string', 'max:50'],
        ]);

        $this->aromaRepository->update($arid, $validated);

        return response()->json($this->aromaRepository->find($arid));
    }

    public function toggleAktif(int $arid)
    {
        $aroma = $this->aromaRepository->find($arid);
        abort_if(! $aroma, 404);

        $aktifBaru = ! (bool) $aroma->aktif;

        if (! $aktifBaru && $this->aromaRepository->isUsedByActiveProduk($arid)) {
            return response()->json([
                'message' => 'Aroma masih dipakai produk aktif, tidak bisa dinonaktifkan.',
            ], 422);
        }

        $this->aromaRepository->setAktif($arid, $aktifBaru);

        return response()->json($this->aromaRepository->find($arid));
    }
}
