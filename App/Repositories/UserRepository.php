<?php
namespace App\Repositories;

use PDO;

class UserRepository extends BaseRepository
{
    protected string $table = 'users';

    public function findByEmail(string $email)
    {
        $tenantId = $this->getTenantId();
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ? AND tenant_id = ? AND deleted_at IS NULL");
        $stmt->execute([$email, $tenantId]);
        return $stmt->fetch() ?: null;
    }


    public function create(array $data)
    {
        if (isset($data['phone']) && !empty($data['phone'])) {
            $data['phone'] = \Core\Encryption::encrypt($data['phone']);
        }
        return parent::create($data);
    }

    public function update(int $id, array $data)
    {
        if (isset($data['phone']) && !empty($data['phone'])) {
            $data['phone'] = \Core\Encryption::encrypt($data['phone']);
        }
        return parent::update($id, $data);
    }

    public function decryptUser(array $user): array
    {
        if (isset($user['phone']) && !empty($user['phone'])) {
            $user['phone'] = \Core\Encryption::decrypt($user['phone']) ?? $user['phone'];
        }
        return $user;
    }
}
