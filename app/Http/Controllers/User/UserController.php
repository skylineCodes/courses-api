<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Register Users To The Storage
     */
    public function register(Request $request)
    {
        try {
            $this->validate($request, [
                'name'    => 'required|string',
                'email'    => 'required|email',
                'password' => 'required',
            ]);

            $user = $this->user->create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            $response = response()->json([
                'status' => 201,
                'data' => $user
            ]);
        } catch (Exception $e) {
            Log::emergency("File: " . $e->getFile() . PHP_EOL .
                "Line: " . $e->getLine() . PHP_EOL .
                "Message: " . $e->getMessage());

            $response = response()->json(['error' => $e->getMessage()], 400);
        }

        return $response;
    }

    /**
     * Login Users
     */
    public function login(Request $request)
    {
        try {
            $this->validate($request, [
                'email'    => 'required|email',
                'password' => 'required',
            ]);

            $credentials = request(['email', 'password']);

            // dd(Auth::guard('web')->attempt(['email' => $request->email, 'password' => $request->password], false, false));

            if (!Auth::guard('web')->attempt($credentials)) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 401);
            }

            $user = auth()->guard('web')->user();

            $token = $user->createToken('Insidify');

            $tokenResult = $token->token;
            $tokenResult->save();

            $response = response()->json([
                'data' => $user,
                'access_token' => $token->accessToken,
                'expires_at' => Carbon::parse(
                    $tokenResult->expires_at
                )->toDateTimeString()
            ], 200);
        } catch (Exception $e) {
            Log::emergency("File: " . $e->getFile() . PHP_EOL .
                "Line: " . $e->getLine() . PHP_EOL .
                "Message: " . $e->getMessage());

            $response = response()->json(['error' => $e->getMessage()], 400);
        }

        return $response;
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->token()->revoke();

            $response = response()->json([
                'message' => 'Successfully logged out'
            ]);
        } catch (Exception $e) {
            Log::emergency("File: " . $e->getFile() . PHP_EOL .
                "Line: " . $e->getLine() . PHP_EOL .
                "Message: " . $e->getMessage());

            $response = response()->json(['error' => $e->getMessage()], 400);
        }

        return $response;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
