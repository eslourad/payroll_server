<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
	public function create(Request $request)
    {
	    $v = validator($request->only('username', 'password'), [
            'username' => 'required|string|min:4|max:25',
            'password' => 'required|string|min:6|max:25',
        ]);

	    if ($v->fails()) {
	        return response()->json($v->errors()->all(), 400);
	    }
	    $data = request()->only('username','password');

	    $user = \App\User::create([
	        'username' => $data['username'],
	        'password' => bcrypt($data['password']),
	        'user_level' => 0,
	    ]);

	    $response = ["status" => "success", "data" => $user->toArray()];
		return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
	}

	public function changepass(Request $request)
    {
    	$v = validator($request->only('password', 'new_password', 'confirm_password', 'id'), [
            'password' => 'required|string|min:6|max:25',
            'new_password' => 'required|string|min:6|max:25',
            'confirm_password' => 'required|same:new_password',
            'id' => 'required'
        ]);

        if ($v->fails()) {
	        return response()->json($v->errors()->all(), 400);
	    }
	    $data = request()->only('password', 'new_password', 'confirm_password', 'id');

	    if ( Hash::check($data['password'], Auth::user()->password)) {
	    	$user = \App\User::where('id', $data['id'])->first();
	    	$user->password = bcrypt($data['new_password']);
			$user = $user->save();

		    $response = ["status" => "success", "data" => 'Password changed'];
			return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
	    } else {
	    	
			$response = ["status" => "error", "data" => 'Password is incorrect'];
			return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
	    }
	    return;
    }

    public function logoutApi()
	{ 
	    if (Auth::check()) {
	       Auth::user()->token()->revoke();
	    }
	    return 'OK';
	}
}
