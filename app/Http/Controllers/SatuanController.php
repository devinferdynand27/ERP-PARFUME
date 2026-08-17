<?php

namespace App\Http\Controllers;

use App\Repositories\SatuanRepository;
use Illuminate\Http\Request;

class SatuanController extends Controller
{
    public function __construct(private SatuanRepository $satuanRepository)
    {
    }

    public function index()
    {
        return view('satuan.index');
    }

    public function data(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 10);
        $page = (int) $request->integer('page', 1);
        $search = $request->string('search')->toString();

        return response()->json(
            $this->satuanRepository->paginate($perPage, $page, $search !== '' ? $search : null)
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_satuan' => ['required', 'string', 'max:50'],
            'tipe' => ['required', 'string', 'max:50'],
            'isi' => ['required', 'numeric', 'min:0'],
        ]);

        $stid = $this->satuanRepository->create($validated, optional($request->user())->adid);

        return response()->json($this->satuanRepository->find($stid), 201);
    }

    public function update(Request $request, int $stid)
    {
        $item = $this->satuanRepository->find($stid);
        abort_if(! $item, 404);

        $validated = $request->validate([
            'nama_satuan' => ['required', 'string', 'max:50'],
            'tipe' => ['required', 'string', 'max:50'],
            'isi' => ['required', 'numeric', 'min:0'],
        ]);

        $this->satuanRepository->update($stid, $validated, optional($request->user())->adid);

        return response()->json($this->satuanRepository->find($stid));
    }

    public function toggleAktif(Request $request, int $stid)
    {
        $item = $this->satuanRepository->find($stid);
        abort_if(! $item, 404);

        $this->satuanRepository->setAktif($stid, ! (bool) $item->aktif, optional($request->user())->adid);

        return response()->json($this->satuanRepository->find($stid));
    }
}
