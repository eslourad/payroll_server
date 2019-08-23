<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\announcements;
use App\paymentperiod;
use App\branch;
use App\agents;

class AnnounceController extends Controller
{	
	public function index()
    {
        $announcements = announcements::orderBy('created_at', 'desc')->get();

        $response = ["status" => "success", "data" => $announcements->toArray()];
        return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
    }

    public function create(Request $request)
    {
	    $v = validator($request->only('title', 'body'), [
	        'title' => 'required',
	        'body' => 'required'
	    ]);

	    if ($v->fails()) {
	        return response()->json($v->errors()->all(), 400);
	    }

	    $data = request()->only('title', 'body');

    	$branch = announcements::create([
	        'title' => $data['title'],
	        'body' => $data['body']
	    ]);

	    $response = ["status" => "success", "data" => $branch->toArray()];
		return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
   
	}

	public function delete(Request $request)
    {
	    $v = validator($request->only('id'), [
	        'id' => 'required'
	    ]);

	    if ($v->fails()) {
	        return response()->json($v->errors()->all(), 400);
	    }

	    $data = request()->only('id');

    	$announcement = announcements::where('id',$data['id'])->delete();

	    $response = ["status" => "success", "data" => 'Successfully deleted'];
		return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
   
	}

	public function card($id)
    {	

    	$first_name = agents::select('first_name')->where('id', $id)->first();
    	if (!$first_name) {
    		$first_name = 'Admin';
    	} else {
    		$first_name = $first_name->first_name;
    	}
        $completed_payments = paymentperiod::where('status', 'COMPLETED')->count();
        $total_payments = paymentperiod::count();
        $total_agent = agents::where('isTL', '0')->count();
        $total_tl = agents::where('isTL', '1')->count();
        $total_branch = branch::count();
        $stats = [
        	'first_name' => $first_name,
            'completed_payments' => $completed_payments,
            'total_payments' => $total_payments,
            'total_agents' => $total_agent,
            'total_tl' => $total_tl,
            'total_branch' => $total_branch
        ];
        
        $response = ["status" => "success", "data" => $stats];
        return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
    }
}
