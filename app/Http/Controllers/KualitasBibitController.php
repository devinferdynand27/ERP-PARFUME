<?php

namespace App\Http\Controllers;

use App\Repositories\KualitasBibitRepository;
use Illuminate\Http\Request;

class KualitasBibitController extends Controller
{
    public function __construct(private KualitasBibitRepository $kualitasBibitRepository)
    {
    }

    public function index()
    {
        return view('kualitas-bibit.index');
    }

    public function data(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 10);
        $page = (int) $request->integer('page', 1);
        $search = $request->string('search')->toString();

        return response()->json(
            $this->kualitasBibitRepository->paginate($perPage, $page, $search !== '' ? $search : null)
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kualitas' => ['required', 'string', 'max:50'],
        ]);

        $kbid = $this->kualitasBibitRepository->create($validated);

        return response()->json($this->kualitasBibitRepository->find($kbid), 201);
    }

    public function update(Request $request, int $kbid)
    {
        $item = $this->kualitasBibitRepository->find($kbid);
        abort_if(! $item, 404);

        $validated = $request->validate([
            'kualitas' => ['required', 'string', 'max:50'],
        ]);

        $this->kualitasBibitRepository->update($kbid, $validated);

        return response()->json($this->kualitasBibitRepository->find($kbid));
    }

    public function toggleAktif(int $kbid)
    {
        $item = $this->kualitasBibitRepository->find($kbid);
        abort_if(! $item, 404);

        $this->kualitasBibitRepository->setAktif($kbid, ! (bool) $item->aktif);

        return response()->json($this->kualitasBibitRepository->find($kbid));
    }
}
