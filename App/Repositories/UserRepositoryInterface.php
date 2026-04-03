<?php
declare(strict_types=1);

namespace App\Repositories;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function findByEmail(string $email): ?array;
    public function decryptUser(array $user): array;
}
