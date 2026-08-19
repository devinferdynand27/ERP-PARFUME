<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class AdminRepository
{
    /**
     * Cari admin berdasarkan ID (adid).
     *
     * @param int $adid
     * @return object|null
     */
    public function find(int $adid): ?object
    {
        return DB::selectOne('
            SELECT adid, username, password, nama_admin, role, email, aktif
            FROM admin
            WHERE adid = ?
        ', [$adid]);
    }

    /**
     * Cari admin berdasarkan email.
     *
     * @param string $email
     * @return object|null
     */
    public function findByEmail(string $email): ?object
    {
        return DB::selectOne('
            SELECT adid, username, password, nama_admin, role, email, aktif
            FROM admin
            WHERE email = ?
        ', [$email]);
    }

    /**
     * Cari admin berdasarkan username.
     *
     * @param string $username
     * @return object|null
     */
    public function findByUsername(string $username): ?object
    {
        return DB::selectOne('
            SELECT adid, username, password, nama_admin, role, email, aktif
            FROM admin
            WHERE username = ?
        ', [$username]);
    }
}
