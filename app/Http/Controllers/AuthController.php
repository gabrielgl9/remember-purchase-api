<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Http\Requests\loginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    /** Success Message **/
    private $successStatus = '201';

    /** Fail Message */
    private $failStatus = '401';

    /**
     * Create a user
     *
     */
    public function signup(AuthRequest $request)
    {
        // Start a transaction
        DB::beginTransaction();

        try {
            // Register a new user
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password)
            ]);

            // Do Login
            Auth::login($user);

            // Get your token
            $tokenResult = $user->createToken('Personal Access Token');

            // Commit the transaction
            DB::commit();

            // Return json success response
            return response()->json([
                'access_token' => $tokenResult->accessToken,
                'token_type'   => 'Bearer',
            ], $this->successStatus);

        } catch (\Throwable $th) {
            // Rollback the transaction
            DB::rollBack();

            // Return json failed response
            return response()->json([
                'error'   => 'falha ao tentar cadastrar esse usuário',
                'message' => $th->getMessage()
            ], $this->failStatus);
        }
    }

    /**
     * Do Login
     *
     */
    public function login(loginRequest $request)
    {
        // Do attempt with the data passed
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Unauthorized'
            ], $this->failStatus);
        }

        // Find the user
        $user = $request->user();

        // Get your token
        $tokenResult = $user->createToken('Personal Access Token');

        // Return json success response
        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type'   => 'Bearer',
        ], $this->successStatus);
    }
}
