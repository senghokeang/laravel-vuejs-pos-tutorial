<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // validation
        $rules = [
            'username' => 'required',
            'password' => 'required'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails())
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);

        $credentials = [
            'username' => $request->username,
            'password' => $request->password,
            'active' => 1,
        ];

        if (Auth::attempt($credentials)) {
            return response()->json([
                'success' => true
            ]);
        }
        return response()->json([
            'success' => false,
            'errors' => [
                'username' => ['Incorrect username or password']
            ]
        ]);
    }

    public function user(Request $request)
    {
        $data = $request->user()->only('id', 'username', 'role');
        $data['server_time'] = date('d-M-Y H:i:s');
        return response()->json($data);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['success' => true]);
    }

    // Change password
    public function changePassword(Request $request)
    {
        $rules = [
            'old_password' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    if (!Hash::check($value, $request->user()->password)) {
                        $fail('The old password is incorrect');
                    }
                }
            ],
            'new_password' => ['required', 'confirmed'],
        ];
        //validate data
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails())
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);

        // change the password
        try {
            $user = $request->user();
            $user->updated_at = now();
            $user->updated_by_id = $user->id;
            $user->password = bcrypt($request->new_password);
            $user->save();

            $response['success'] = true;
            $response['data'] = null;
        } catch (Exception $ex) {
            abort($ex->getCode(), $ex->getMessage());
        }
        return response()->json($response);
    }
}
