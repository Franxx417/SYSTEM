<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        $users = DB::table('users')
            ->leftJoin('roles', 'roles.user_id', '=', 'users.user_id')
            ->leftJoin('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
            ->select('users.*', 'role_types.user_role_type as role')
            ->orderBy('users.name')
            ->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    public function create(Request $request)
    {
        $this->authorizeAdmin($request);
        $roleTypes = DB::table('role_types')->pluck('user_role_type', 'role_type_id');
        return view('admin.users.create', compact('roleTypes'));
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin($request);
        $data = $request->validate([
            'name' => ['required','string','max:200'],
            'email' => ['required','email','max:255'],
            'position' => ['required','string','max:100'],
            'department' => ['required','string','max:100'],
            'username' => ['required','string','max:100'],
            'password' => ['required','string','min:6'],
            'role_type_id' => ['required','string'],
        ]);

        DB::table('users')->insert([
            'name' => $data['name'],
            'email' => $data['email'],
            'position' => $data['position'],
            'department' => $data['department'],
        ]);

        $userId = DB::table('users')->where('email', $data['email'])->value('user_id');

        DB::table('login')->insert([
            'user_id' => $userId,
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
        ]);

        DB::table('roles')->insert([
            'user_id' => $userId,
            'role_type_id' => $data['role_type_id'],
        ]);

        return redirect()->route('admin.users.index')->with('status', 'User created');
    }

    private function authorizeAdmin(Request $request): void
    {
        $auth = $request->session()->get('auth_user');
        if (!$auth || !in_array($auth['role'], ['authorized_personnel','superadmin'], true)) {
            abort(403);
        }
    }
}




