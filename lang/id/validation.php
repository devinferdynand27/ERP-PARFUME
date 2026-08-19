<?php

return [
    'required' => 'Kolom :attribute wajib diisi.',
    'string' => 'Kolom :attribute harus berupa teks.',
    'numeric' => 'Kolom :attribute harus berupa angka.',
    'integer' => 'Kolom :attribute harus berupa bilangan bulat.',
    'max' => [
        'string' => 'Kolom :attribute maksimal :max karakter.',
        'numeric' => 'Kolom :attribute maksimal :max.',
    ],
    'min' => [
        'string' => 'Kolom :attribute minimal :min karakter.',
        'numeric' => 'Kolom :attribute minimal :min.',
    ],
    'unique' => 'Kolom :attribute sudah dipakai, gunakan nilai lain.',
    'exists' => 'Kolom :attribute yang dipilih tidak valid.',
    'email' => 'Kolom :attribute harus berupa alamat email yang valid.',
    'date' => 'Kolom :attribute harus berupa tanggal yang valid.',
    'boolean' => 'Kolom :attribute harus bernilai benar atau salah.',
    'gt' => [
        'numeric' => 'Kolom :attribute harus lebih besar dari :value.',
    ],
    'gte' => [
        'numeric' => 'Kolom :attribute harus lebih besar atau sama dengan :value.',
    ],

    'attributes' => [
        'nama_barang' => 'nama barang',
        'kategori' => 'kategori',
        'ukuran' => 'ukuran botol',
        'kualitas' => 'kualitas bibit',
        'nama_supplier' => 'nama supplier',
        'kontak' => 'kontak',
        'alamat' => 'alamat',
        'kode_produk' => 'kode produk',
        'nama_produk' => 'nama produk',
        'mbid' => 'barang',
        'harga_beli_default' => 'harga beli default',
        'harga_jual_default' => 'harga jual default',
        'stok' => 'stok',
        'stok_minimum' => 'stok minimum',
        'tanggal' => 'tanggal',
        'qty' => 'jumlah',
        'harga_beli' => 'harga beli',
        'harga_jual' => 'harga jual',
    ],
];
