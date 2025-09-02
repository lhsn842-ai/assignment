<?php

namespace App\GraphQL\Mutations;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class AuthMutation
{
    public function login($rootValue, array $args, GraphQLContext $context = null)
    {
        $user = User::where('email', $args['email'])->first();

        if (!$user || !Hash::check($args['password'], $user->password)) {
            return [
                'status' => 0,
                'message' => 'Invalid credentials',
                'token' => null,
                'user' => null,
            ];
        }

        //$token = $user->createToken('graphql-token')->plainTextToken;
        if (Auth::attempt(['email' => $args['email'], 'password' => $args['password']])) {
            return [
                'status' => 1,
                'message' => 'Logged in successfully',
                'token' => Auth::user()->createToken('Personal Access Token')->plainTextToken,
                'user' => Auth::user(),
            ];
        } else {
            return [
                'status' => 0,
                'message' => 'Invalid credentials',
                'token' => null,
                'user' => null,
            ];
        }
    }
}
