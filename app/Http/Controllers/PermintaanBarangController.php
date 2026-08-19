<?php

namespace App\Http\Controllers;

use App\Repositories\AromaRepository;
use App\Repositories\PermintaanBarangRepository;
use App\Repositories\SatuanRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermintaanBarangController extends Controller
{
    public function __construct(
        private PermintaanBarangRepository $permintaanBarangRepository,
        private AromaRepository $aromaRepository,
        private SatuanRepository $satuanRepository,
    ) {
    }

    public function index()
    {
        return view('permintaan-barang.index');
    }

    public function create()
    {
        return view('permintaan-barang.create');
    }

    public function edit(int $pbid)
    {
        $header = $this->permintaanBarangRepository->find($pbid);
        abort_if(! $header, 404);
        abort_if($header->status !== 'draft', 404);

        return view('permintaan-barang.edit', ['pbid' => $pbid]);
    }

    public function data(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 10);
        $page = (int) $request->integer('page', 1);
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();

        return response()->json(
            $this->permintaanBarangRepository->paginate(
                $perPage,
                $page,
                $search !== '' ? $search : null,
                $status !== '' ? $status : null
            )
        );
    }

    public function formOptions()
    {
        return response()->json([
            'aroma' => $this->aromaRepository->getAktif(),
            'satuan' => $this->satuanRepository->getAktif(),
            'nomor_permintaan_berikutnya' => $this->permintaanBarangRepository->generateNomorPermintaan(),
        ]);
    }

    public function aromaData(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 20);
        $page = (int) $request->integer('page', 1);
        $search = $request->string('search')->toString();

        return response()->json(
            $this->aromaRepository->paginateAktif($perPage, $page, $search !== '' ? $search : null)
        );
    }

    public function show(int $pbid)
    {
        $header = $this->permintaanBarangRepository->find($pbid);
        abort_if(! $header, 404);

        return response()->json([
            'header' => $header,
            'items' => $this->permintaanBarangRepository->getItems($pbid),
        ]);
    }

    public function print(int $pbid)
    {
        $header = $this->permintaanBarangRepository->find($pbid);
        abort_if(! $header, 404);

        return view('permintaan-barang.print', [
            'header' => $header,
            'items' => $this->permintaanBarangRepository->getItems($pbid),
        ]);
    }

    private function validatedPayload(Request $request): array
    {
        return $request->validate([
            'tanggal' => ['required', 'date'],
            'catatan' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.arid' => ['required', 'integer', 'exists:aroma,arid'],
            'items.*.stid' => ['required', 'integer', 'exists:satuan,stid'],
            'items.*.qty_diminta' => ['required', 'numeric', 'min:0.01'],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatedPayload($request);
        $adid = optional($request->user())->adid;

        $pbid = DB::transaction(function () use ($validated, $adid) {
            return $this->permintaanBarangRepository->create(
                [
                    'nomor_permintaan' => $this->permintaanBarangRepository->generateNomorPermintaan(),
                    'tanggal' => $validated['tanggal'],
                    'catatan' => $validated['catatan'] ?? null,
                ],
                $validated['items'],
                $adid
            );
        });

        return response()->json($this->showPayload($pbid), 201);
    }

    public function update(Request $request, int $pbid)
    {
        $header = $this->permintaanBarangRepository->find($pbid);
        abort_if(! $header, 404);
        abort_if($header->status !== 'draft', 422, 'Hanya permintaan berstatus draft yang bisa diubah.');

        $validated = $this->validatedPayload($request);
        $adid = optional($request->user())->adid;

        DB::transaction(function () use ($pbid, $validated, $adid) {
            $this->permintaanBarangRepository->update(
                $pbid,
                [
                    'tanggal' => $validated['tanggal'],
                    'catatan' => $validated['catatan'] ?? null,
                ],
                $validated['items'],
                $adid
            );
        });

        return response()->json($this->showPayload($pbid));
    }

    public function updateStatus(Request $request, int $pbid)
    {
        $header = $this->permintaanBarangRepository->find($pbid);
        abort_if(! $header, 404);

        $validated = $request->validate([
            'status' => ['required', 'in:draft,diajukan,disetujui,ditolak,ditutup'],
        ]);

        $this->permintaanBarangRepository->updateStatus($pbid, $validated['status'], optional($request->user())->adid);

        return response()->json($this->showPayload($pbid));
    }

    public function destroy(int $pbid)
    {
        $header = $this->permintaanBarangRepository->find($pbid);
        abort_if(! $header, 404);
        abort_if($header->status !== 'draft', 422, 'Hanya permintaan berstatus draft yang bisa dihapus.');

        $this->permintaanBarangRepository->delete($pbid);

        return response()->json(['message' => 'Permintaan barang berhasil dihapus.']);
    }

    private function showPayload(int $pbid): array
    {
        return [
            'header' => $this->permintaanBarangRepository->find($pbid),
            'items' => $this->permintaanBarangRepository->getItems($pbid),
        ];
    }
}
