-- Tambah kolom role ke tabel admin
ALTER TABLE admin ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'kasir' AFTER nama_admin;

-- Ubah kolom email menjadi NOT NULL dan buat UNIQUE
ALTER TABLE admin MODIFY COLUMN email VARCHAR(150) NOT NULL;
ALTER TABLE admin ADD CONSTRAINT uq_admin_email UNIQUE (email);

-- Tambah index untuk role
ALTER TABLE admin ADD INDEX idx_admin_role (role);

-- Seed user admin (password: admin123)
INSERT INTO admin (username, password, nama_admin, role, email, aktif, created_at, updated_at)
VALUES (
    'admin',
    '$2y$12$FHOAe0LsDd/vdRFY.dColOnmNglb320oolso2LzpSVbiHiiGWhd8e',
    'Admin',
    'admin',
    'admin@gmail.com',
    1,
    NOW(),
    NOW()
);

-- Seed user kasir (password: kasir123)
INSERT INTO admin (username, password, nama_admin, role, email, aktif, created_at, updated_at)
VALUES (
    'kasir',
    '$2y$12$TtBcEB9MDHH2YCGh8Gez1uoeO85nXpLt9/pyNe7nm4gA.1MG9AGlK',
    'Kasir',
    'kasir',
    'kasir@gmail.com',
    1,
    NOW(),
    NOW()
);
