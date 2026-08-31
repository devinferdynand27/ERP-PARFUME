<?php

namespace App\Http\Controllers;

use App\Repositories\KualitasRepository;
use Illuminate\Http\Request;

class KualitasController extends Controller
{
    public function __construct(private KualitasRepository $kualitasRepository)
    {
    }

    public function index()
    {
        return view('kualitas.index');
    }

    public function data(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 10);
        $page = (int) $request->integer('page', 1);
        $search = $request->string('search')->toString();

        return response()->json(
            $this->kualitasRepository->paginate($perPage, $page, $search !== '' ? $search : null)
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kualitas' => ['required', 'string', 'max:50'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $kuid = $this->kualitasRepository->create($validated, optional($request->user())->adid);

        return response()->json($this->kualitasRepository->find($kuid), 201);
    }

    public function update(Request $request, int $kuid)
    {
        $item = $this->kualitasRepository->find($kuid);
        abort_if(! $item, 404);

        $validated = $request->validate([
            'nama_kualitas' => ['required', 'string', 'max:50'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $this->kualitasRepository->update($kuid, $validated, optional($request->user())->adid);

        return response()->json($this->kualitasRepository->find($kuid));
    }

    public function toggleAktif(Request $request, int $kuid)
    {
        $item = $this->kualitasRepository->find($kuid);
        abort_if(! $item, 404);

        $this->kualitasRepository->setAktif($kuid, ! (bool) $item->aktif, optional($request->user())->adid);

        return response()->json($this->kualitasRepository->find($kuid));
    }
}
