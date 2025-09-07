<?php

namespace App\GraphQL\Mutations;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class AuthMutation
{
    public function login($_, array $args)
    {
        $user = User::where('email', $args['email'])->first();

        if (! $user || ! Hash::check($args['password'], $user->password)) {
            return [
                'status' => 401,
                'message' => 'Invalid credentials',
                'token' => null,
                'user' => null,
            ];
        }

        // 🔑 Log the user into the session (for Sanctum cookie + broadcasting)
        Auth::login($user, true);

        // 🔑 Also issue an API token (optional but useful for XHR calls)
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'status' => 200,
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
        ];
    }
}
