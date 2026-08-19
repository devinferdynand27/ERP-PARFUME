<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;

class Admin implements Authenticatable
{
    public ?int $adid;
    public ?string $username;
    public ?string $password;
    public ?string $nama_admin;
    public ?string $role;
    public ?string $email;
    public ?int $aktif;

    public function __construct(array $attributes = [])
    {
        $this->adid = $attributes['adid'] ?? null;
        $this->username = $attributes['username'] ?? null;
        $this->password = $attributes['password'] ?? null;
        $this->nama_admin = $attributes['nama_admin'] ?? null;
        $this->role = $attributes['role'] ?? null;
        $this->email = $attributes['email'] ?? null;
        $this->aktif = $attributes['aktif'] ?? null;
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName(): string
    {
        return 'adid';
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier(): mixed
    {
        return $this->adid;
    }

    /**
     * Get the name of the password attribute for the user.
     *
     * @return string
     */
    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword(): string
    {
        return $this->password ?? '';
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string|null
     */
    public function getRememberToken(): ?string
    {
        return null;
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string  $value
     * @return void
     */
    public function setRememberToken($value): void
    {
        // Not used
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName(): string
    {
        return '';
    }
}
