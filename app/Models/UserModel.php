<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'user_id';
    protected $returnType       = 'object';

    protected $allowedFields    = ['name', 'email', 'password_hash', 'role', 'status'];
    protected $useTimestamps    = true;
    protected $useSoftDeletes   = false;

    protected $validationRules  = [
        'name'  => 'required|min_length[3]|max_length[120]',
        'email' => 'required|valid_email|max_length[180]|is_unique[users.email,user_id,{user_id}]',
    ];

    protected $validationMessages = [];
    protected $skipValidation     = false;

    /**
     * Find an active user by email.
     */
    public function findByEmail(string $email): ?object
    {
        return $this->where('email', $email)->where('status', 'active')->first();
    }

    /**
     * Create a user with a securely hashed password.
     */
    public function createUser(array $data): int|false
    {
        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        unset($data['password']);

        $this->insert($data, false);

        return (int) $this->db->insertID();
    }

    /**
     * Update password (expects plain, hashes it).
     */
    public function updatePassword(int $userId, string $plain): bool
    {
        return (bool) $this->update($userId, [
            'password_hash' => password_hash($plain, PASSWORD_DEFAULT),
        ]);
    }
}
