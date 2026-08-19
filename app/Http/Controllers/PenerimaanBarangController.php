<?php

namespace App\Http\Controllers;

use App\Repositories\PenerimaanBarangRepository;
use App\Repositories\PesananPembelianRepository;
use App\Repositories\ProdukRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class PenerimaanBarangController extends Controller
{
    public function __construct(
        private PenerimaanBarangRepository $penerimaanBarangRepository,
        private PesananPembelianRepository $pesananPembelianRepository,
        private ProdukRepository $produkRepository,
    ) {
    }

    public function index()
    {
        return view('penerimaan-barang.index');
    }

    public function create()
    {
        return view('penerimaan-barang.create');
    }

    public function data(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 10);
        $page = (int) $request->integer('page', 1);
        $search = $request->string('search')->toString();

        return response()->json(
            $this->penerimaanBarangRepository->paginate($perPage, $page, $search !== '' ? $search : null)
        );
    }

    public function formOptions(Request $request)
    {
        $data = [
            'pesanan_pembelian' => DB::select("
                SELECT pp.ppid, pp.nomor_po, sp.nama_supplier
                FROM pesanan_pembelian pp
                JOIN supplier sp ON sp.spid = pp.spid
                WHERE pp.status IN ('diterbitkan', 'diterima_sebagian')
                ORDER BY pp.tanggal DESC
            "),
            'nomor_penerimaan_berikutnya' => $this->penerimaanBarangRepository->generateNomorPenerimaan(),
        ];

        if ($request->filled('ppid')) {
            $data['items'] = $this->pesananPembelianRepository->getItemsWithSisa((int) $request->integer('ppid'));
        }

        return response()->json($data);
    }

    public function show(int $pnid)
    {
        $header = $this->penerimaanBarangRepository->find($pnid);
        abort_if(! $header, 404);

        return response()->json([
            'header' => $header,
            'items' => $this->penerimaanBarangRepository->getItems($pnid),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ppid' => ['required', 'integer', 'exists:pesanan_pembelian,ppid'],
            'nomor_faktur_supplier' => ['nullable', 'string', 'max:100'],
            'tanggal' => ['required', 'date'],
            'catatan' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.ppdid' => ['required', 'integer', 'exists:pesanan_pembelian_detail,ppdid'],
            'items.*.qty_diterima' => ['required', 'numeric', 'min:0.01'],
        ]);
        $adid = optional($request->user())->adid;

        $pnid = DB::transaction(function () use ($validated, $adid) {
            $ppid = $validated['ppid'];

            $pnid = $this->penerimaanBarangRepository->createHeader([
                'nomor_penerimaan' => $this->penerimaanBarangRepository->generateNomorPenerimaan(),
                'ppid' => $ppid,
                'nomor_faktur_supplier' => $validated['nomor_faktur_supplier'] ?? null,
                'tanggal' => $validated['tanggal'],
                'catatan' => $validated['catatan'] ?? null,
            ], $adid);

            foreach ($validated['items'] as $item) {
                // Row-lock detail PO supaya aman dari race condition saat dua
                // penerimaan parsial disimpan bersamaan (lihat getStokForUpdate
                // pattern di ProdukRepository).
                $detail = $this->pesananPembelianRepository->getDetailForUpdate($item['ppdid']);

                abort_if(! $detail || $detail->ppid !== $ppid, 422, 'Item PO tidak valid.');

                $sisa = $detail->qty_dipesan - $detail->qty_diterima;
                if ($item['qty_diterima'] > $sisa) {
                    throw ValidationException::withMessages([
                        'items' => "Qty diterima untuk salah satu item melebihi sisa pesanan ({$sisa}).",
                    ]);
                }

                $ppDetailFull = DB::selectOne('
                    SELECT stid, harga_satuan FROM pesanan_pembelian_detail WHERE ppdid = ?
                ', [$item['ppdid']]);

                $this->penerimaanBarangRepository->insertItem($pnid, [
                    'ppdid' => $item['ppdid'],
                    'prid' => $detail->prid,
                    'stid' => $ppDetailFull->stid,
                    'qty_diterima' => $item['qty_diterima'],
                    'harga_beli' => $ppDetailFull->harga_satuan,
                ]);

                $this->pesananPembelianRepository->addQtyDiterima($item['ppdid'], $item['qty_diterima']);
                $this->produkRepository->adjustStok($detail->prid, (int) $item['qty_diterima']);
            }

            $this->pesananPembelianRepository->recomputeStatus($ppid, $adid);

            return $pnid;
        });

        return response()->json($this->showPayload($pnid), 201);
    }

    private function showPayload(int $pnid): array
    {
        return [
            'header' => $this->penerimaanBarangRepository->find($pnid),
            'items' => $this->penerimaanBarangRepository->getItems($pnid),
        ];
    }
}
