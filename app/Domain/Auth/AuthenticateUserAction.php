<?php

namespace App\Domain\Auth;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthenticateUserAction
{
    public function __construct(
        private readonly ConnectionInterface $db
    ) {}

    /**
     * Validate credentials against legacy login/users/roles tables
     * and return the session payload.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle(string $username, string $password): array
    {
        $row = $this->db->table('login')
            ->join('users', 'login.user_id', '=', 'users.user_id')
            ->leftJoin('roles', 'roles.user_id', '=', 'users.user_id')
            ->leftJoin('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
            ->select(
                'login.*',
                'users.name',
                'users.email',
                'users.position',
                'users.department',
                'role_types.user_role_type'
            )
            ->where('login.username', $username)
            ->first();

        if (! $row || ! Hash::check($password, $row->password)) {
            throw ValidationException::withMessages([
                'username' => 'The provided credentials are incorrect.',
            ]);
        }

        return [
            'user_id' => $row->user_id,
            'name' => $row->name,
            'email' => $row->email,
            'position' => $row->position,
            'department' => $row->department,
            'role' => $row->user_role_type,
        ];
    }
}
