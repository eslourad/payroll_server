<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\branch;
use App\agents;
use App\User;

class BranchController extends Controller
{	
	public function index()
	{
		$branch = branch::leftJoin('agents', 'branches.team_lead', '=',  'agents.id')->select('branches.id', 'branches.branch_name', 'agents.first_name', 'agents.last_name', 'branches.is_active')->orderBy('branches.is_active', 'desc')->orderBy('branches.created_at', 'desc')->paginate(10);
		if ($branch->isNotEmpty()) {
	    	$response = ["status" => "success", "data" => $branch->toArray()];
			return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
	    } else {
			$response = ["status" => "error", "data" => 'No branch added'];
			return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
	    }
	}

	public function view($id)
    {
    	return branch::leftJoin('agents', 'branches.team_lead', '=',  'agents.id')->leftJoin('users', 'branches.team_lead', '=',  'users.agent_id')->select('branches.id', 'agents.id AS agent_id', 'branches.branch_name', 'agents.first_name', 'agents.last_name', 'branches.is_active', 'branches.rate', 'branches.ot_rates', 'branches.regular_holidays', 'branches.special_holidays', 'branches.rest_days', 'users.username')->where('branches.id', $id)->first();
    }

	public function create(Request $request)
    {
	    $v = validator($request->only('branch_name', 'rate', 'ot_rates', 'regular_holidays', 'special_holidays','rest_days', 'team_lead'), [
	        'branch_name' => 'required|string|max:255',
	        'rate' => 'required|integer',
	        'ot_rates' => 'required|integer',
	        'regular_holidays' => 'required|integer',
	        'special_holidays' => 'required|integer',
	        'rest_days' => 'required|integer'
	    ]);

	    if ($v->fails()) {
	        return response()->json($v->errors()->all(), 400);
	    }

	    $data = request()->only('branch_name', 'rate', 'ot_rates', 'regular_holidays', 'special_holidays', 'rest_days');

	    $branch = branch::where('branch_name', '=', $data['branch_name'])->first();
	    if (!$branch) {
	    	$branch = branch::create([
		        'branch_name' => $data['branch_name'],
		        'rate' => $data['rate'],
		        'ot_rates' => $data['ot_rates'],
		        'regular_holidays' => $data['regular_holidays'],
		        'special_holidays' => $data['special_holidays'],
		        'rest_days' => $data['rest_days']
		    ]);

	    	// $agent = agents::select('first_name', 'last_name')->where('id', $branch->team_lead)->first();

	    	// $user = User::create([
	    	// 	'username' => $agent->last_name . substr($agent->first_name, 0, 1),
	    	// 	'password' => bcrypt('secret')
	    	// ]);

		    $response = ["status" => "success", "data" => $branch->toArray()];
			return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
	    } else {
	    	$response = ["status" => "error", "data" => 'Branch name already taken'];
			return response(json_encode($response), 400, ["Content-Type" => "application/json"]);
	    }	   
	}

	public function update(Request $request)
    {
    	$v = validator($request->only('id', 'branch_name', 'rate', 'ot_rates', 'regular_holidays', 'special_holidays', 'rest_days', 'team_lead', 'is_active'), [
    		'id' => 'required|integer',
	        'branch_name' => 'required|string|max:255',
	        'rate' => 'required|integer',
	        'ot_rates' => 'required|integer',
	        'special_holidays' => 'required|integer',
	        'regular_holidays' => 'required|integer',
	        'rest_days' => 'required|integer',
	        'team_lead' => 'max:65535',
	        'is_active' => 'required|integer|max:1',
	    ]);

	    if ($v->fails()) {
	        return response()->json($v->errors()->all(), 400);
	    }

	    $data = request()->only('id', 'branch_name', 'rate', 'ot_rates', 'regular_holidays', 'special_holidays', 'rest_days', 'team_lead', 'is_active');

	    $branch = branch::find($data['id']);
	    if ($branch === null) {
	    	$response = ["status" => "error", "data" => 'No branch found'];
			return response(json_encode($response), 400, ["Content-Type" => "application/json"]);
	    } else {
	    	if($data['is_active'] == 0) {
	    		$teamLead = '';
	    		$oldUser = User::where('agent_id', $branch->team_lead)->first();
		    	if ($oldUser) {
			    	$oldUser->status = 0;
			    	$oldUser = $oldUser->save();
			    }
	    	} else {
	    		$teamLead = $data['team_lead'];
	    	
		    	if ($branch->team_lead !== $data['team_lead']) {
		    		
	    			$agent = agents::select('id', 'first_name', 'last_name')->where('id', intval($data['team_lead']))->first();
	    			if($agent){
	    				$accountExist = User::where('agent_id', $data['team_lead'])->first();
		    			if ($accountExist) {
		    				$accountExist->status = 1;
		    				$accountExist = $accountExist->save();
		    			} else {
		    				$user = User::create([
					    		'username' => $agent->last_name . substr($agent->first_name, 0, 1) . $agent->id,
					    		'password' => bcrypt('secret'),
					    		'agent_id' => $agent->id
					    	]);
		    			}
	    			}
	    			$oldUser = User::where('agent_id', $branch->team_lead)->first();
			    	if ($oldUser) {
				    	$oldUser->status = 0;
				    	$oldUser = $oldUser->save();
				    }
		    	}
		    }

	    	$branchCheck = branch::where('branch_name', '!=', $branch->branch_name)->where('branch_name', $data['branch_name'])->first();
    		if ($branchCheck === null) {
    			$branch->branch_name = $data['branch_name'];
		    	$branch->rate = $data['rate'];
		    	$branch->ot_rates = $data['ot_rates'];
		    	$branch->special_holidays = $data['special_holidays'];
		    	$branch->regular_holidays = $data['regular_holidays'];
		    	$branch->rest_days = $data['rest_days'];
		    	$branch->team_lead = $teamLead;
		    	$branch->is_active = $data['is_active'];
		    	$branch = $branch->save();
		    	$response = ["status" => "success", "data" => 'Branch information updated'];
				return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
    		} else {
		    	$response = ["status" => "validation error", "data" => 'Branch name already taken'];
				return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
		    }
	    }
		
    }

    public function search(Request $request)
    {
    	$v = validator($request->only('branch_name', 'is_active'), [
	        'branch_name' => 'required|string|max:255',
	        'is_active' => 'required'
	    ]);

	    if ($v->fails()) {
	        return response()->json($v->errors()->all(), 400);
	    }

	    $data = request()->only('branch_name', 'is_active');

	    if ($data['is_active']) {
	    	$status = 1;
	    } else {
	    	$status = 0;
	    }

    	$branch = branch::select('id', 'branch_name', 'team_lead', 'is_active')->where('branch_name', 'like', '%' . $data['branch_name'] . '%')->where('is_active', $status)->paginate(10);
    	if ($branch->isNotEmpty()) {
	    	$response = ["status" => "success", "data" => $branch->toArray()];
			return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
	    } else {
			$response = ["status" => "error", "data" => 'No branch found'];
			return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
	    }
    }

    public function branchName()
	{
		$branch = branch::select('id', 'branch_name')->where('is_active', 1)->orderBy('branch_name')->get();
		if ($branch->isNotEmpty()) {
	    	$response = ["status" => "success", "data" => $branch->toArray()];
			return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
	    } else {
			$response = ["status" => "error", "data" => 'No branch added'];
			return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
	    }
	}

	public function branchNameAgent(Request $request)
    {
    	$v = validator($request->only('agent_id'), [
	        'agent_id' => 'required'
	    ]);

	    if ($v->fails()) {
	        return response()->json($v->errors()->all(), 400);
	    }

	    $data = request()->only('agent_id');

	    $branch = branch::select('id', 'branch_name')->where('team_lead', $data['agent_id'])->get();
		if ($branch) {
	    	$response = ["status" => "success", "data" => $branch->toArray()];
			return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
	    } else {
			$response = ["status" => "error", "data" => 'No branch added'];
			return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
	    }

	}

	public function suggestBranch(Request $request)
	{
	    $v = validator($request->only('name'), [
	      'name' => 'required|string'
	    ]);
	    $data = request()->only('name');
	    $result = [];

	    $branch = branch::select('id', 'branch_name')->whereNotNull('team_lead')->where('is_active', 1)->where('branch_name', 'like', '%' . $data['name'] . '%')->orderBy('branch_name')->get();
	    foreach($branch as $row) {
	      $each = [
	        'id' => $row->id,
	        'branch_name' => $row->branch_name
	      ];
	      array_push($result, $each);
	    }
	    $response = ["status" => "success", "data" => $result];
	    return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
	}

	<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\branch;
use App\agents;
use App\User;

class BranchController extends Controller
{	
	public function index()
	{
		$branch = branch::leftJoin('agents', 'branches.team_lead', '=',  'agents.id')->select('branches.id', 'branches.branch_name', 'agents.first_name', 'agents.last_name', 'branches.is_active')->orderBy('branches.is_active', 'desc')->orderBy('branches.created_at', 'desc')->paginate(10);
		if ($branch->isNotEmpty()) {
	    	$response = ["status" => "success", "data" => $branch->toArray()];
			return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
	    } else {
			$response = ["status" => "error", "data" => 'No branch added'];
			return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
	    }
	}

	public function view($id)
    {
    	return branch::leftJoin('agents', 'branches.team_lead', '=',  'agents.id')->leftJoin('users', 'branches.team_lead', '=',  'users.agent_id')->select('branches.id', 'agents.id AS agent_id', 'branches.branch_name', 'agents.first_name', 'agents.last_name', 'branches.is_active', 'branches.rate', 'branches.ot_rates', 'branches.regular_holidays', 'branches.special_holidays', 'branches.rest_days', 'users.username')->where('branches.id', $id)->first();
    }

	public function create(Request $request)
    {
	    $v = validator($request->only('branch_name', 'rate', 'ot_rates', 'regular_holidays', 'special_holidays','rest_days', 'team_lead'), [
	        'branch_name' => 'required|string|max:255',
	        'rate' => 'required|integer',
	        'ot_rates' => 'required|integer',
	        'regular_holidays' => 'required|integer',
	        'special_holidays' => 'required|integer',
	        'rest_days' => 'required|integer'
	    ]);

	    if ($v->fails()) {
	        return response()->json($v->errors()->all(), 400);
	    }

	    $data = request()->only('branch_name', 'rate', 'ot_rates', 'regular_holidays', 'special_holidays', 'rest_days');

	    $branch = branch::where('branch_name', '=', $data['branch_name'])->first();
	    if (!$branch) {
	    	$branch = branch::create([
		        'branch_name' => $data['branch_name'],
		        'rate' => $data['rate'],
		        'ot_rates' => $data['ot_rates'],
		        'regular_holidays' => $data['regular_holidays'],
		        'special_holidays' => $data['special_holidays'],
		        'rest_days' => $data['rest_days']
		    ]);

	    	// $agent = agents::select('first_name', 'last_name')->where('id', $branch->team_lead)->first();

	    	// $user = User::create([
	    	// 	'username' => $agent->last_name . substr($agent->first_name, 0, 1),
	    	// 	'password' => bcrypt('secret')
	    	// ]);

		    $response = ["status" => "success", "data" => $branch->toArray()];
			return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
	    } else {
	    	$response = ["status" => "error", "data" => 'Branch name already taken'];
			return response(json_encode($response), 400, ["Content-Type" => "application/json"]);
	    }	   
	}

	public function update(Request $request)
    {
    	$v = validator($request->only('id', 'branch_name', 'rate', 'ot_rates', 'regular_holidays', 'special_holidays', 'rest_days', 'team_lead', 'is_active'), [
    		'id' => 'required|integer',
	        'branch_name' => 'required|string|max:255',
	        'rate' => 'required|integer',
	        'ot_rates' => 'required|integer',
	        'special_holidays' => 'required|integer',
	        'regular_holidays' => 'required|integer',
	        'rest_days' => 'required|integer',
	        'team_lead' => 'max:65535',
	        'is_active' => 'required|integer|max:1',
	    ]);

	    if ($v->fails()) {
	        return response()->json($v->errors()->all(), 400);
	    }

	    $data = request()->only('id', 'branch_name', 'rate', 'ot_rates', 'regular_holidays', 'special_holidays', 'rest_days', 'team_lead', 'is_active');

	    $branch = branch::find($data['id']);
	    if ($branch === null) {
	    	$response = ["status" => "error", "data" => 'No branch found'];
			return response(json_encode($response), 400, ["Content-Type" => "application/json"]);
	    } else {
	    	if($data['is_active'] == 0) {
	    		$teamLead = '';
	    		$oldUser = User::where('agent_id', $branch->team_lead)->first();
		    	if ($oldUser) {
			    	$oldUser->status = 0;
			    	$oldUser = $oldUser->save();
			    }
	    	} else {
	    		$teamLead = $data['team_lead'];
	    	
		    	if ($branch->team_lead !== $data['team_lead']) {
		    		
	    			$agent = agents::select('id', 'first_name', 'last_name')->where('id', intval($data['team_lead']))->first();
	    			if($agent){
	    				$accountExist = User::where('agent_id', $data['team_lead'])->first();
		    			if ($accountExist) {
		    				$accountExist->status = 1;
		    				$accountExist = $accountExist->save();
		    			} else {
		    				$user = User::create([
					    		'username' => $agent->last_name . substr($agent->first_name, 0, 1) . $agent->id,
					    		'password' => bcrypt('secret'),
					    		'agent_id' => $agent->id
					    	]);
		    			}
	    			}
	    			$oldUser = User::where('agent_id', $branch->team_lead)->first();
			    	if ($oldUser) {
				    	$oldUser->status = 0;
				    	$oldUser = $oldUser->save();
				    }
		    	}
		    }

	    	$branchCheck = branch::where('branch_name', '!=', $branch->branch_name)->where('branch_name', $data['branch_name'])->first();
    		if ($branchCheck === null) {
    			$branch->branch_name = $data['branch_name'];
		    	$branch->rate = $data['rate'];
		    	$branch->ot_rates = $data['ot_rates'];
		    	$branch->special_holidays = $data['special_holidays'];
		    	$branch->regular_holidays = $data['regular_holidays'];
		    	$branch->rest_days = $data['rest_days'];
		    	$branch->team_lead = $teamLead;
		    	$branch->is_active = $data['is_active'];
		    	$branch = $branch->save();
		    	$response = ["status" => "success", "data" => 'Branch information updated'];
				return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
    		} else {
		    	$response = ["status" => "validation error", "data" => 'Branch name already taken'];
				return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
		    }
	    }
		
    }

    public function search(Request $request)
    {
    	$v = validator($request->only('branch_name', 'is_active'), [
	        'branch_name' => 'required|string|max:255',
	        'is_active' => 'required'
	    ]);

	    if ($v->fails()) {
	        return response()->json($v->errors()->all(), 400);
	    }

	    $data = request()->only('branch_name', 'is_active');

	    if ($data['is_active']) {
	    	$status = 1;
	    } else {
	    	$status = 0;
	    }

    	$branch = branch::select('id', 'branch_name', 'team_lead', 'is_active')->where('branch_name', 'like', '%' . $data['branch_name'] . '%')->where('is_active', $status)->paginate(10);
    	if ($branch->isNotEmpty()) {
	    	$response = ["status" => "success", "data" => $branch->toArray()];
			return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
	    } else {
			$response = ["status" => "error", "data" => 'No branch found'];
			return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
	    }
    }

    public function branchName()
	{
		$branch = branch::select('id', 'branch_name')->where('is_active', 1)->orderBy('branch_name')->get();
		if ($branch->isNotEmpty()) {
	    	$response = ["status" => "success", "data" => $branch->toArray()];
			return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
	    } else {
			$response = ["status" => "error", "data" => 'No branch added'];
			return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
	    }
	}

	public function branchNameAgent(Request $request)
    {
    	$v = validator($request->only('agent_id'), [
	        'agent_id' => 'required'
	    ]);

	    if ($v->fails()) {
	        return response()->json($v->errors()->all(), 400);
	    }

	    $data = request()->only('agent_id');

	    $branch = branch::select('id', 'branch_name')->where('team_lead', $data['agent_id'])->get();
		if ($branch) {
	    	$response = ["status" => "success", "data" => $branch->toArray()];
			return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
	    } else {
			$response = ["status" => "error", "data" => 'No branch added'];
			return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
	    }

	}

	public function suggestBranch(Request $request)
	{
	    $v = validator($request->only('name'), [
	      'name' => 'required|string'
	    ]);
	    $data = request()->only('name');
	    $result = [];

	    $branch = branch::select('id', 'branch_name')->where('is_active', 1)->where('branch_name', 'like', '%' . $data['name'] . '%')->orderBy('branch_name')->get();
	    foreach($branch as $row) {
	      $each = [
	        'id' => $row->id,
	        'branch_name' => $row->branch_name
	      ];
	      array_push($result, $each);
	    }
	    $response = ["status" => "success", "data" => $result];
	    return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
	}
	
	public function suggestBranchExclusiveToPayment(Request $request)
	{
	    $v = validator($request->only('name'), [
	      'name' => 'required|string'
	    ]);
	    $data = request()->only('name');
	    $result = [];

	    $branch = branch::select('id', 'branch_name')->whereNotNull('team_lead')->where('is_active', 1)->where('branch_name', 'like', '%' . $data['name'] . '%')->orderBy('branch_name')->get();
	    foreach($branch as $row) {
	      $each = [
	        'id' => $row->id,
	        'branch_name' => $row->branch_name
	      ];
	      array_push($result, $each);
	    }
	    $response = ["status" => "success", "data" => $result];
	    return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
	}
}

}
