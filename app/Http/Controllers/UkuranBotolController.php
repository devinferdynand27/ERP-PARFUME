<?php

namespace App\Http\Controllers;

use App\Repositories\UkuranBotolRepository;
use Illuminate\Http\Request;

class UkuranBotolController extends Controller
{
    public function __construct(private UkuranBotolRepository $ukuranBotolRepository)
    {
    }

    public function index()
    {
        return view('ukuran-botol.index');
    }

    public function data(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 10);
        $page = (int) $request->integer('page', 1);
        $search = $request->string('search')->toString();

        return response()->json(
            $this->ukuranBotolRepository->paginate($perPage, $page, $search !== '' ? $search : null)
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ukuran' => ['required', 'string', 'max:50'],
        ]);

        $ubid = $this->ukuranBotolRepository->create($validated);

        return response()->json($this->ukuranBotolRepository->find($ubid), 201);
    }

    public function update(Request $request, int $ubid)
    {
        $item = $this->ukuranBotolRepository->find($ubid);
        abort_if(! $item, 404);

        $validated = $request->validate([
            'ukuran' => ['required', 'string', 'max:50'],
        ]);

        $this->ukuranBotolRepository->update($ubid, $validated);

        return response()->json($this->ukuranBotolRepository->find($ubid));
    }

    public function toggleAktif(int $ubid)
    {
        $item = $this->ukuranBotolRepository->find($ubid);
        abort_if(! $item, 404);

        $this->ukuranBotolRepository->setAktif($ubid, ! (bool) $item->aktif);

        return response()->json($this->ukuranBotolRepository->find($ubid));
    }
}
