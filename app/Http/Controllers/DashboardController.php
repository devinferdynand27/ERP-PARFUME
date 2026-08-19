<?php

namespace App\Http\Controllers;

use App\Repositories\DashboardRepository;

class DashboardController extends Controller
{
    protected DashboardRepository $dashboardRepository;

    public function __construct(DashboardRepository $dashboardRepository)
    {
        $this->dashboardRepository = $dashboardRepository;
    }

    /**
     * Tampilkan halaman dashboard utama beserta ringkasan statistik.
     */
    public function index()
    {
        $data = $this->dashboardRepository->getDashboardData();

        return view('dashboard', [
            'dashboardData' => $data,
        ]);
    }
}
