<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            $user = User::create($request->all());

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
}
