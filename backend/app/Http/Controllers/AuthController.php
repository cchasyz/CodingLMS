<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Admins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function register(Request $request){
        $request->validate([
            'full_name' => ['required'],
            'username' => ['required', 'min:3', 'unique:users,username', 'regex:/^[a-zA-Z1-9._]+$/'],
            'password' => ['required', 'min:6']
        ]);

        $password = Hash::make($request->password);

        $user = User::create([
            'full_name' => $request->full_name,
            'username' => $request->username,
            'password' => $password,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $output = [
            'full_name' => $user->full_name,
            'username' => $user->username,
            'updated_at' => $user->updated_at,
            'created_at' => $user->created_at,
            'token' => $token,
            'role' => 'user'
        ];

        return response()->json([
            'message' => 'success',
            'token' => $output
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function login(Request $request){
        $request->validate([
            'username' => ['required'],
            'password' => ['required']
        ]);

        $user = User::where('username', $request->username)->first();
        if(!$user){
            $admin = Admins::where('username', $request->username)->first();
            if($admin){
                if(!Hash::check($request->password, $admin->password)){
                    return response()->json([
                        'message' => 'username or password is wrong!'
                    ], 400);
                }
                $token = $admin->createToken('auth_token', ['admin'])->plainTextToken;
                $output = [
                    'username' => $admin->username,
                    'updated_at' => $admin->updated_at,
                    'created_at' => $admin->created_at,
                    'token' => $token,
                    'role' => 'admin'
                ];
                return response()->json([
                    'message' => 'success',
                    'data' => $output
                ]);
            } 
            return response()->json([
                'message' => 'username or password is wrong!'
            ], 400);
        }
        if(!Hash::check($request->password, $user->password)){
            return response()->json([
                'message' => 'username or password is wrong!'
            ], 400);
        }
        $token = $user->createToken('auth_token', ['user'])->plainTextToken;
        $output = [
            'full_name' => $user->full_name,
            'username' => $user->username,
            'updated_at' => $user->updated_at,
            'created_at' => $user->created_at,
            'token' => $token,
            'role' => 'user'
        ];
        return response()->json([
            'message' => 'success',
            'data' => $output
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function logout(){
        auth()->user()->tokens()->delete();
        return response()->json([
            'message' => 'logout successful'
        ]);
    }
}
