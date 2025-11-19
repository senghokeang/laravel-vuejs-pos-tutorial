<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function list(Request $request)
    {
        // get param value
        $username = $request->username;
        $role = $request->role ?? 0;
        $sortBy = $request->sortBy ?? 'created_at';
        $orderBy = $request->orderBy ?? 'desc';
        try {
            $data = User::select('id', 'username', 'role', 'active', 'created_at')
                ->when($username, function ($query) use ($username) {
                    $query->where('username', 'like', '%' . $username . '%');
                })
                ->when($role, function ($query) use ($role) {
                    $query->where('role',  $role);
                })
                ->orderBy($sortBy, $orderBy)
                ->paginate(50);
            $response['success'] = true;
            $response['data'] = $data;
        } catch (Exception $ex) {
            abort($ex->getCode(), $ex->getMessage());
        }

        return response()->json($response);
    }


    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|alpha_num|unique:users,username,' . $request->id,
            'role' => 'required',
            'password' => [$request->id > 0 ? 'nullable' : 'required', 'confirmed'],
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'errors' => $validator->errors()
                ]
            );
        }
        try {
            if ($request->id > 0) {
                $data = User::find($request->id);
            } else {
                $data = new User();
                $data->created_by_id = $request->user()->id;
            }

            $data->updated_by_id = $request->user()->id;
            $data->username = $request->username;
            $data->role = $request->role;
            $data->active = $request->active == 'on';
            if ($request->password)
                $data->password = bcrypt($request->password);
            $data->save();
            $response['success'] = true;
            $response['data'] = null;
        } catch (Exception $ex) {
            abort($ex->getCode(), $ex->getMessage());
        }
        return response()->json($response);
    }

    public function edit(Request $request)
    {
        return response()->json(User::select('id', 'username', 'active', 'role')->findOrFail($request->id));
    }

    public function delete(Request $request)
    {
        $data = User::findOrFail($request->id);
        $data->deleted_id = $request->user()->id;
        $data->delete();
        return response()->json();
    }
}
