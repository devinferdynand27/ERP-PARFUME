<?php

namespace App\Http\Controllers;

use App\Repositories\MasterBarangRepository;
use App\Repositories\PermintaanBarangRepository;
use App\Repositories\PesananPembelianRepository;
use App\Repositories\SatuanRepository;
use App\Repositories\SupplierRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PesananPembelianController extends Controller
{
    public function __construct(
        private PesananPembelianRepository $pesananPembelianRepository,
        private PermintaanBarangRepository $permintaanBarangRepository,
        private MasterBarangRepository $masterBarangRepository,
        private SatuanRepository $satuanRepository,
        private SupplierRepository $supplierRepository,
    ) {
    }

    public function index()
    {
        return view('pesanan-pembelian.index');
    }

    public function create()
    {
        return view('pesanan-pembelian.create');
    }

    public function data(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 10);
        $page = (int) $request->integer('page', 1);
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();

        return response()->json(
            $this->pesananPembelianRepository->paginate(
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
            'master_barang' => $this->masterBarangRepository->getAktif(),
            'satuan' => $this->satuanRepository->getAktif(),
            'supplier' => $this->supplierRepository->getAktif(),
            'nomor_po_berikutnya' => $this->pesananPembelianRepository->generateNomorPO(),
        ]);
    }

    public function dariPermintaan(int $pbid)
    {
        $header = $this->permintaanBarangRepository->find($pbid);
        abort_if(! $header, 404);
        abort_if($header->status !== 'disetujui', 422, 'Permintaan barang belum disetujui.');

        return response()->json([
            'header' => $header,
            'items' => $this->permintaanBarangRepository->getItems($pbid),
        ]);
    }

    public function show(int $ppid)
    {
        $header = $this->pesananPembelianRepository->find($ppid);
        abort_if(! $header, 404);

        return response()->json($this->showPayload($ppid));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pbid' => ['nullable', 'integer', 'exists:permintaan_barang,pbid'],
            'spid' => ['required', 'integer', 'exists:supplier,spid'],
            'tanggal' => ['required', 'date'],
            'catatan' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.pbdid' => ['nullable', 'integer', 'exists:permintaan_barang_detail,pbdid'],
            'items.*.mbid' => ['required', 'integer', 'exists:master_barang,mbid'],
            'items.*.stid' => ['required', 'integer', 'exists:satuan,stid'],
            'items.*.qty_dipesan' => ['required', 'numeric', 'min:0.01'],
            'items.*.harga_satuan' => ['required', 'numeric', 'min:0'],
        ]);
        $adid = optional($request->user())->adid;

        $ppid = DB::transaction(function () use ($validated, $adid) {
            return $this->pesananPembelianRepository->create(
                [
                    'nomor_po' => $this->pesananPembelianRepository->generateNomorPO(),
                    'pbid' => $validated['pbid'] ?? null,
                    'spid' => $validated['spid'],
                    'tanggal' => $validated['tanggal'],
                    'status' => 'diterbitkan',
                    'catatan' => $validated['catatan'] ?? null,
                ],
                $validated['items'],
                $adid
            );
        });

        return response()->json($this->showPayload($ppid), 201);
    }

    public function updateStatus(Request $request, int $ppid)
    {
        $header = $this->pesananPembelianRepository->find($ppid);
        abort_if(! $header, 404);

        $validated = $request->validate([
            'status' => ['required', 'in:draft,diterbitkan,dibatalkan'],
        ]);

        $this->pesananPembelianRepository->updateStatus($ppid, $validated['status'], optional($request->user())->adid);

        return response()->json($this->showPayload($ppid));
    }

    private function showPayload(int $ppid): array
    {
        return [
            'header' => $this->pesananPembelianRepository->find($ppid),
            'items' => $this->pesananPembelianRepository->getItems($ppid),
        ];
    }
}
