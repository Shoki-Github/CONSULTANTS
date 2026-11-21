<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $memberRole = Role::query()->where('name', 'registered_member')->firstOrFail();
        $user = DB::transaction(function () use ($request, $memberRole): User {
            $user = User::query()->create([
                ...$request->safe()->only(['name', 'email', 'password']),
                'status' => 'active',
            ]);
            $user->roles()->attach($memberRole);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'data' => $this->userData($user),
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->safe()->only(['email', 'password']))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = $request->user();
        if ($user->status !== 'active') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'data' => $this->userData($user),
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->userData($request->user()),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['success' => true, 'message' => 'Logged out.']);
    }

    private function userData(User $user): array
    {
        return [
            ...$user->only(['id', 'name', 'email']),
            'can_manage_consultations' => $user->hasPermission('manage_consultations'),
            'can_manage_content' => $user->hasPermission('manage_content'),
            'can_moderate_forum' => $user->hasPermission('moderate_forum'),
        ];
    }
}
