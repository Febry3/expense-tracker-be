<?php

namespace App\Http\Controllers\Api;

use Throwable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth as Authorization;

class Auth extends Controller
{
    public function register(Request $request)
    {
        try {
            $validateRequest = Validator::make(
                $request->all(),
                [
                    'username' => 'required|unique:users,username',
                    'email' => 'required|unique:users,email|email',
                    'password' => 'required',
                ]
            );

            if ($validateRequest->fails()) {
                return Utils::responseHelper(403, false, "Username or Email or Password shouldn't be empty or invalid type", $validateRequest->errors());
            }

            $user = User::create(
                [
                    'username' => $request->username,
                    'email' => $request->email,
                    'password' => $request->password,
                ]
            );

            return Utils::responseHelper(200, true, "account created", token: $user->createToken('Token')->plainTextToken);
        } catch (Throwable $err) {
            return Utils::responseHelper(500, false, $err->getMessage());
        }
    }

    public function login(Request $request)
    {
        try {
            $validateRequest = Validator::make(
                $request->all(),
                [
                    'email' => 'required',
                    'password' => 'required',
                ]
            );

            if ($validateRequest->fails()) {
                return Utils::responseHelper(403, false, "Email or Password shouldn't be empty or invalid type", $validateRequest->errors());
            }

            if (!Authorization::attempt($request->only(['email', 'password']))) {
                return Utils::responseHelper(401, false, "Email or Password doesn't match");
            }

            $user = User::where("email", "=", $request->email)->first();

            return Utils::responseHelper(200, true, "login success", token: $user->createToken('Token')->plainTextToken);
        } catch (Throwable $err) {
            return Utils::responseHelper(500, false, $err->getMessage());
        }
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return Utils::responseHelper(200, true, "logged out");
    }
}
