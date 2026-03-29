<?php
namespace App\Models;

use Core\Model;

/**
 * User Model
 */
class User extends Model
{
    protected $table = 'users';

    /**
     * Authenticate user
     */
    public function authenticate($email, $password)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Transparent upgrade to Argon2id if using older hash
            if (\Core\Auth::hashPassword($password) && password_needs_rehash($user['password'], \PASSWORD_ARGON2ID)) {
                $newHash = \Core\Auth::hashPassword($password);
                $updateStmt = $this->db->prepare("UPDATE {$this->table} SET password = ? WHERE id = ?");
                $updateStmt->execute([$newHash, $user['id']]);
            }

            unset($user['password']); // Don't store hash in session
            return $user;
        }

        return false;
    }

    /**
     * Find by email
     */
    public function findByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Create new user
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (uuid, name, email, password, role, company, phone) 
                VALUES (:uuid, :name, :email, :password, :role, :company, :phone)";

        $data['uuid'] = bin2hex(random_bytes(16)); // Simple UUID for now
        $data['password'] = \Core\Auth::hashPassword($data['password']);

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }
}
