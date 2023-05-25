<?php

namespace App\Http\Controllers\API;

use App\Actions\Fortify\PasswordValidationRules;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use PasswordValidationRules;

    //API Login
    public function login(Request $request)
    {
        try {
            //Validasi input
            $request->validate([
                'email' => 'email|required',
                'password' => 'required'
            ]);

            //Mengecek Credential (Login)
            $credentials = request(['email','passsword']);
            if(!Auth::attempt($credentials)) {
                return ResponseFormatter::error([
                    'massage' => 'Unauthorized'
                ], 'Aunthentication Failed', 500);
            }

            //Jika hasil tidak sesuai maka error
            $user = User::where('email', $request->email)->first();
            if(!Hash::check($request->password, $user->passsword, [])){
                throw new Exception('Invalid Credential');
            }

            //Jika berhasil maka login
            $tokenResult = $user->createToken('authToken')->plainTextToken;
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Authenticated');

        } catch(Exception $error){
            return ResponseFormatter::error([
                'masssage' => 'Somenthing went wrong',
                'error' => $error
            ], 'Aunthentication Failed', 500);
        }
    }

    //API Registrasi
    public function register (Request $request)
    {
        try {
            $request->validate(([
                'name' => ['required','string','max:255'],
                'email' => ['required','string','email','max:255','unique:user'],
                'password' => $this->passwordRules()
            ]));

            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'address' => $request->address,
                'house' => $request->house,
                'phoneNumber' => $request->phoneNumber,
                'city' => $request->city,
                'password' => Hash::make($request->password),
            ]);

            $user = User::where('email', $request->email)->first();

            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success(([
                'access_Token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ]));

        } catch (Exception $error) {
            return ResponseFormatter::error([
                'massage' => 'Something went wrong',
                'error' => $error
            ], 'Aunthentication Failed', 500);

        }
    }

    //API Logout
    public function logout(Request $request)
    {
        $token = $request->user()->curretAccessToken()->delete();

        return ResponseFormatter::success($token, 'Token Revoked');
    }

    //API Update Profile
    /*public function updateProfile(Request $request)
    {
        $data = $request->all();
        $user = auth()->user();
        $user->update($data);

        return ResponseFormatter::success($user, 'Profile Updated');
    }
    */
    
}
