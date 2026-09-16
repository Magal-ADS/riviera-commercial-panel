<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function session(Request $request): JsonResponse
    {
        if ($user = $request->user()) {
            return response()->json([
                'authenticated' => true,
                'user' => $this->present($user),
                'csrfToken' => $request->session()->token(),
            ]);
        }

        $pendingId = $request->session()->get('pending_password_user_id');

        return response()->json([
            'authenticated' => false,
            'requiresPasswordChange' => (bool) $pendingId,
            'user' => $pendingId ? $this->present(User::find($pendingId)) : null,
            'csrfToken' => $request->session()->token(),
        ]);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        $user = User::query()->where('email', strtolower($credentials['email']))->first();

        if (! $user || ! Auth::validate(['email' => $user->email, 'password' => $credentials['password']])) {
            throw ValidationException::withMessages(['email' => 'E-mail ou senha incorretos.']);
        }

        if ($user->must_change_password) {
            $request->session()->put('pending_password_user_id', $user->id);

            return response()->json([
                'requiresPasswordChange' => true,
                'user' => $this->present($user),
                'csrfToken' => $request->session()->token(),
            ]);
        }

        Auth::login($user, (bool) ($credentials['remember'] ?? false));
        $request->session()->regenerate();

        return response()->json([
            'authenticated' => true,
            'user' => $this->present($user),
            'csrfToken' => $request->session()->token(),
        ]);
    }

    public function recover(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $user = User::query()->where('email', strtolower($data['email']))->first();

        if (! $user) {
            throw ValidationException::withMessages(['email' => 'E-mail não encontrado.']);
        }

        $temporaryPassword = Str::upper(Str::random(6));
        $user->update(['password' => $temporaryPassword, 'must_change_password' => true]);

        return response()->json(['temporaryPassword' => $temporaryPassword]);
    }

    public function reset(Request $request): JsonResponse
    {
        $data = $request->validate(['password' => ['required', 'string', 'min:3', 'confirmed']]);
        $userId = $request->session()->pull('pending_password_user_id') ?? $request->user()?->id;
        $user = $userId ? User::find($userId) : null;

        abort_unless($user, 401);

        $user->update(['password' => $data['password'], 'must_change_password' => false]);
        Auth::login($user, true);
        $request->session()->regenerate();

        return response()->json([
            'authenticated' => true,
            'user' => $this->present($user),
            'csrfToken' => $request->session()->token(),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'ok' => true,
            'csrfToken' => $request->session()->token(),
        ]);
    }

    private function present(?User $user): ?array
    {
        if (! $user) {
            return null;
        }

        return [
            'id' => $user->id,
            'role' => $user->role,
            'email' => $user->email,
            'nome' => $user->name,
            'areaId' => $user->area_id,
            'repId' => $user->representative_id,
            'telefone' => $user->phone,
            'mustChangePassword' => $user->must_change_password,
        ];
    }
}
