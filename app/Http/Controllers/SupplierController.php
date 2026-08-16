<?php

namespace App\Http\Controllers;

use App\Repositories\SupplierRepository;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct(private SupplierRepository $supplierRepository)
    {
    }

    public function index()
    {
        return view('supplier.index');
    }

    public function data(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 10);
        $page = (int) $request->integer('page', 1);
        $search = $request->string('search')->toString();

        return response()->json(
            $this->supplierRepository->paginate($perPage, $page, $search !== '' ? $search : null)
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_supplier' => ['required', 'string', 'max:150'],
            'kontak' => ['nullable', 'string', 'max:100'],
            'alamat' => ['nullable', 'string'],
        ]);

        $spid = $this->supplierRepository->create($validated);

        return response()->json($this->supplierRepository->find($spid), 201);
    }

    public function update(Request $request, int $spid)
    {
        $item = $this->supplierRepository->find($spid);
        abort_if(! $item, 404);

        $validated = $request->validate([
            'nama_supplier' => ['required', 'string', 'max:150'],
            'kontak' => ['nullable', 'string', 'max:100'],
            'alamat' => ['nullable', 'string'],
        ]);

        $this->supplierRepository->update($spid, $validated);

        return response()->json($this->supplierRepository->find($spid));
    }

    public function toggleAktif(int $spid)
    {
        $item = $this->supplierRepository->find($spid);
        abort_if(! $item, 404);

        $this->supplierRepository->setAktif($spid, ! (bool) $item->aktif);

        return response()->json($this->supplierRepository->find($spid));
    }
}
