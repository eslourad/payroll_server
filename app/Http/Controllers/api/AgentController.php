<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\agents;
use App\User;
use App\branch;

class AgentController extends Controller
{
  public function index()
  {
    $agent = agents::join('branches', 'agents.branch_id', '=',  'branches.id')->select('agents.id', 'agents.first_name', 'agents.last_name', 'branches.branch_name', 'agents.is_active', 'agents.created_at')->where('agents.isTL', '0')->orderBy('is_active', 'desc')->orderBy('created_at', 'desc')->paginate(10);
    if ($agent->isNotEmpty()) {
      $response = ["status" => "success", "data" => $agent->toArray()];
      return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
    } else {
      $response = ["status" => "error", "data" => 'No agent added'];
      return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
    }
  }

  public function view($id)
  {
    return agents::leftJoin('users', 'agents.id', '=',  'users.agent_id')->select('first_name', 'last_name', 'dob', 'email', 'employment_date', 'title', 'mobile_number', 'current_address', 'permanent_address', 'emergency_person', 'emergency_number', 'high_school', 'is_hs_grad', 'hs_grad_year', 'college', 'is_college_grad', 'hs_grad_year', 'college', 'hs_grad_year', 'college', 'college_grad_year', 'college_course', 'technical_school', 'is_ts_grad', 'ts_program', 'ts_year', 'image_file_name', 'image_file_size', 'image_date_uploaded', 'psa_file_name', 'psa_file_size', 'psa_date_uploaded', 'nbi_file_name', 'nbi_file_size', 'nbi_date_uploaded', 'sss', 'pag_ibig', 'phil_health', 'bank_name', 'account_name', 'account_number', 'branch_id', 'company_name', 'position', 'from', 'to', 'company_name_two', 'position_two', 'from_two', 'to_two', 'company_name_three', 'position_three', 'from_three', 'to_three', 'allowance', 'sss_file', 'pag_ibig_file', 'phil_health_file', 'is_active', 'agents.id', 'users.username')->where('agents.id', $id)->first();
  }

  public function create(Request $request)
  {
    $v = validator($request->only('first_name', 'last_name', 'dob', 'email', 'employment_date', 'title', 'mobile_number', 'current_address', 'permanent_address', 'emergency_person', 'emergency_number', 'high_school', 'is_hs_grad', 'hs_grad_year', 'college', 'is_college_grad', 'hs_grad_year', 'college', 'hs_grad_year', 'college', 'college_grad_year', 'college_course', 'technical_school', 'is_ts_grad', 'ts_program', 'ts_year', 'image_file_name', 'image_file_size', 'image_date_uploaded', 'psa_file_name', 'psa_file_size', 'psa_date_uploaded', 'nbi_file_name', 'nbi_file_size', 'nbi_date_uploaded', 'sss', 'pag_ibig', 'phil_health', 'bank_name', 'account_name', 'account_number', 'branch_id', 'image_file', 'psa_file', 'nbi_file', 'sss_file', 'pag_ibig_file', 'phil_health_file', 'company_name', 'position', 'from', 'to', 'company_name_two', 'position_two', 'from_two', 'to_two', 'company_name_three', 'position_three', 'from_three', 'to_three', 'allowance'), [
          'first_name' => 'required|string|max:50',
          'last_name' => 'required|string|max:50',
          'dob' => 'required|string|date',
          'email' => 'required|string|max:255',
          'title' => 'required|string|max:255',
          'employment_date' => 'required|string|max:255',
          'mobile_number' => 'required|string',
          'current_address' => 'required|string|max:255',
          'permanent_address' => 'required|string|max:255',
          'emergency_person' => 'required|string|max:50',
          'emergency_number' => 'required|string',
          'high_school' => 'required|string|max:255',
          'is_hs_grad' => 'required|integer',
          'college' => 'max:255',
          'is_college_grad' => 'integer',
          'college_course' => 'max:255',
          'technical_school' => 'max:255',
          'is_ts_grad' => '',
          'ts_program' => 'max:255',
          'image_file_name' => 'required|string',
          'image_file_size' => 'required|string|max:255',
          'image_date_uploaded' => 'required|string',
          'psa_file_name' => 'required|string',
          'psa_file_size' => 'required|string|max:255',
          'psa_date_uploaded' => 'required|string',
          'nbi_file_name' => 'required|string',
          'nbi_file_size' => 'required|string|max:255',
          'nbi_date_uploaded' => 'required|string',
          'sss' => 'required|string',
          'pag_ibig' => 'required|string',
          'phil_health' => 'required|string',
          'bank_name' => 'required|string|max:255',
          'account_name' => 'required|string|max:255',
          'account_number' => 'required|string',
          'branch_id' => 'integer|max:50',
          'company_name' => 'string|max:255',
          'position' => 'string|max:255',
          'from' => 'integer',
          'to' => 'integer',
          'company_name_two' => 'max:255',
          'position_two' => 'max:255',
          'company_name_three' => 'max:255',
          'position_three' => 'max:255',
          'allowance' => 'required|string'
    ]);

    if ($v->fails()) {
        return response()->json($v->errors()->all(), 400);
    }
    $data = request()->only('first_name', 'last_name', 'dob', 'email', 'employment_date', 'title', 'mobile_number', 'current_address', 'permanent_address', 'emergency_person', 'emergency_number', 'high_school', 'is_hs_grad', 'hs_grad_year', 'college', 'is_college_grad', 'hs_grad_year', 'college', 'hs_grad_year', 'college', 'college_grad_year', 'college_course', 'technical_school', 'is_ts_grad', 'ts_program', 'ts_year', 'image_file_name', 'image_file_size', 'image_date_uploaded', 'psa_file_name', 'psa_file_size', 'psa_date_uploaded', 'nbi_file_name', 'nbi_file_size', 'nbi_date_uploaded', 'sss', 'pag_ibig', 'phil_health', 'bank_name', 'account_name', 'account_number', 'branch_id', 'image_file', 'psa_file', 'nbi_file', 'sss_file', 'pag_ibig_file', 'phil_health_file', 'company_name', 'position', 'from', 'to', 'company_name_two', 'position_two', 'from_two', 'to_two', 'company_name_three', 'position_three', 'from_three', 'to_three', 'allowance');


    function handleFiles($fileImage) {
      $exploded = explode(',', $fileImage);

      $decoded = base64_decode($exploded[1]);

      if(str_contains($exploded[0], 'jpeg'))
        $extension = 'jpg';
      else
        $extension = 'png';

      $fileName = str_random().'.'.$extension;

      $path = public_path().'/uploads/'.$fileName;

      file_put_contents($path , $decoded);

      return $fileName;
    }

    $image_file_name = handleFiles($data['image_file']);
    $psa_file_name = handleFiles($data['psa_file']);
    $nbi_file_name = handleFiles($data['nbi_file']);
    if($data['sss_file'] !== null) {
      $sss_file = handleFiles($data['sss_file']);
    } else {
      $sss_file = '';
    }
    if($data['pag_ibig_file'] !== null) {
      $pag_ibig_file = handleFiles($data['pag_ibig_file']);
    } else {
      $pag_ibig_file = '';
    }
    if($data['pag_ibig_file'] !== null) {
      $phil_health_file = handleFiles($data['pag_ibig_file']);
    } else {
      $phil_health_file = '';
    }
    

    $agent = \App\agents::create([
      'first_name' => $data['first_name'],
      'last_name' => $data['last_name'],
      'dob' => $data['dob'],
      'email' => $data['email'],
      'title' => $data['title'],
      'employment_date' => $data['employment_date'],
      'mobile_number' => $data['mobile_number'],
      'current_address' => $data['current_address'],
      'permanent_address' => $data['permanent_address'],
      'emergency_person' => $data['emergency_person'],
      'emergency_number' => $data['emergency_number'],
      'high_school' => $data['high_school'],
      'is_hs_grad' => $data['is_hs_grad'],
      'hs_grad_year' => $data['hs_grad_year'],
      'college' => $data['college'],
      'is_college_grad' => $data['is_college_grad'],
      'college_grad_year' => $data['college_grad_year'],
      'college_course' => $data['college_course'],
      'technical_school' => $data['technical_school'],
      'is_ts_grad' => $data['is_ts_grad'],
      'ts_program' => $data['ts_program'],
      'ts_year' => $data['ts_year'],
      'image_file_name' => $image_file_name,
      'image_file_size' => $data['image_file_size'],
      'image_date_uploaded' => $data['image_date_uploaded'],
      'psa_file_name' => $psa_file_name,
      'psa_file_size' => $data['psa_file_size'],
      'psa_date_uploaded' => $data['psa_date_uploaded'],
      'nbi_file_name' => $nbi_file_name,
      'nbi_file_size' => $data['nbi_file_size'],
      'nbi_date_uploaded' => $data['nbi_date_uploaded'],
      'sss' => $data['sss'],
      'pag_ibig' => $data['pag_ibig'],
      'phil_health' => $data['phil_health'],
      'bank_name' => $data['bank_name'],
      'account_name' => $data['account_name'],
      'account_number' => $data['account_number'],
      'branch_id' => $data['branch_id'],
      'company_name' => $data['company_name'],
      'position' => $data['position'],
      'from' => $data['from'],
      'to' => $data['to'],
      'company_name_two' => $data['company_name_two'],
      'position_two' => $data['position_two'],
      'from_two' => $data['from_two'],
      'to_two' => $data['to_two'],
      'company_name_three' => $data['company_name_three'],
      'position_three' => $data['position_three'],
      'from_three' => $data['from_three'],
      'to_three' => $data['to_three'],
      'sss_file' => $sss_file,
      'pag_ibig_file' => $pag_ibig_file,
      'phil_health_file' => $phil_health_file,
      'is_active' => 1,
      'allowance' => $data['allowance'],
    ]);

    $savings = \App\savings::create([
      'ca_balance' => '0',
      'total_savings' => '0',
      'deduction_rate' => '0',
      'loan_sss' => '0',
      'loan_sss_deduction' => '0',
      'loan_pag_ibig' => '0',
      'loan_pi_deduction' => '0',
      'agent_id' => $agent->id
    ]);

    $user = User::create([
      'username' => preg_replace('/\s+/', '', $agent->last_name . substr($agent->first_name, 0, 1) . $agent->id),
      'password' => bcrypt('secret'),
      'agent_id' => $agent->id,
      'user_level' => '2'
    ]);

    $response = ["status" => "success", "data" => $agent->toArray()];
    return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
	}

  public function search(Request $request)
  {
    $v = validator($request->only('last_name', 'is_active'), [
        'last_name' => 'required|string|max:255',
        'is_active' => 'required'
    ]);

    if ($v->fails()) {
        return response()->json($v->errors()->all(), 400);
    }

    $data = request()->only('last_name', 'is_active');

    if ($data['is_active']) {
      $status = 1;
    } else {
      $status = 0;
    }

    $agent = agents::join('branches', 'agents.branch_id', '=',  'branches.id')->select('agents.id', 'agents.first_name', 'agents.last_name', 'branches.branch_name', 'agents.is_active', 'agents.created_at')->where('agents.last_name', 'like', '%' . $data['last_name'] . '%')->where('agents.isTL', '0')->where('agents.is_active', $status)->paginate(10);
    if ($agent->isNotEmpty()) {
      $response = ["status" => "success", "data" => $agent->toArray()];
      return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
    } else {
      $response = ["status" => "error", "data" => 'No brand specialist found'];
      return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
    }
  }
  public function status(Request $request)
  {
    $v = validator($request->only('id', 'is_active'), [
        'id' => 'required|integer',
        'is_active' => 'required'
    ]);

    if ($v->fails()) {
        return response()->json($v->errors()->all(), 400);
    }

    $data = request()->only('is_active', 'id');

    $agent = agents::find($data['id']);
    if ($agent === null) {
      $response = ["status" => "error", "data" => 'No brand specialist found'];
      return response(json_encode($response), 400, ["Content-Type" => "application/json"]);
    } else {
      $agent->is_active = $data['is_active'];
      $agent = $agent->save();
      $response = ["status" => "success", "data" => 'Agent status updated'];
      return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
    }
  }
  public function update(Request $request)
  {
    $v = validator($request->only('id', 'first_name', 'last_name', 'dob', 'email', 'employment_date', 'title', 'mobile_number', 'current_address', 'permanent_address', 'emergency_person', 'emergency_number', 'high_school', 'is_hs_grad', 'hs_grad_year', 'college', 'is_college_grad', 'hs_grad_year', 'college', 'hs_grad_year', 'college', 'college_grad_year', 'college_course', 'technical_school', 'is_ts_grad', 'ts_program', 'ts_year', 'image_file_name', 'image_file_size', 'image_date_uploaded', 'psa_file_name', 'psa_file_size', 'psa_date_uploaded', 'nbi_file_name', 'nbi_file_size', 'nbi_date_uploaded', 'sss', 'pag_ibig', 'phil_health', 'bank_name', 'account_name', 'account_number', 'branch_id', 'image_file', 'psa_file', 'nbi_file', 'sss_file', 'pag_ibig_file', 'phil_health_file', 'company_name', 'position', 'from', 'to', 'company_name_two', 'position_two', 'from_two', 'to_two', 'company_name_three', 'position_three', 'from_three', 'to_three', 'allowance'), [
          'first_name' => 'required|string|max:50',
          'last_name' => 'required|string|max:50',
          'dob' => 'required|string|date',
          'email' => 'required|string|max:255',
          'title' => 'required|string|max:255',
          'employment_date' => 'required|string|max:255',
          'mobile_number' => 'required|string',
          'current_address' => 'required|string|max:255',
          'permanent_address' => 'required|string|max:255',
          'emergency_person' => 'required|string|max:50',
          'emergency_number' => 'required|string',
          'high_school' => 'required|string|max:255',
          'is_hs_grad' => 'required|integer',
          'college' => 'max:255',
          'is_college_grad' => 'integer',
          'college_course' => 'max:255',
          'technical_school' => 'max:255',
          'is_ts_grad' => '',
          'ts_program' => 'max:255',
          'image_file_name' => 'required|string',
          'image_file_size' => 'required|string|max:255',
          'image_date_uploaded' => 'required|string',
          'psa_file_name' => 'required|string',
          'psa_file_size' => 'required|string|max:255',
          'psa_date_uploaded' => 'required|string',
          'nbi_file_name' => 'required|string',
          'nbi_file_size' => 'required|string|max:255',
          'nbi_date_uploaded' => 'required|string',
          'sss' => 'required|string',
          'pag_ibig' => 'required|string',
          'phil_health' => 'required|string',
          'bank_name' => 'required|string|max:255',
          'account_name' => 'required|string|max:255',
          'account_number' => 'required|string',
          'branch_id' => 'integer|max:50',
          'company_name' => 'string|max:255',
          'position' => 'string|max:255',
          'from' => 'integer',
          'to' => 'integer',
          'company_name_two' => 'max:255',
          'position_two' => 'max:255',
          'company_name_three' => 'max:255',
          'position_three' => 'max:255',
          'allowance' => 'required|string'
    ]);

    if ($v->fails()) {
        return response()->json($v->errors()->all(), 400);
    }
    $data = request()->only('id', 'first_name', 'last_name', 'dob', 'email', 'employment_date', 'title', 'mobile_number', 'current_address', 'permanent_address', 'emergency_person', 'emergency_number', 'high_school', 'is_hs_grad', 'hs_grad_year', 'college', 'is_college_grad', 'hs_grad_year', 'college', 'hs_grad_year', 'college', 'college_grad_year', 'college_course', 'technical_school', 'is_ts_grad', 'ts_program', 'ts_year', 'image_file_name', 'image_file_size', 'image_date_uploaded', 'psa_file_name', 'psa_file_size', 'psa_date_uploaded', 'nbi_file_name', 'nbi_file_size', 'nbi_date_uploaded', 'sss', 'pag_ibig', 'phil_health', 'bank_name', 'account_name', 'account_number', 'branch_id', 'image_file', 'psa_file', 'nbi_file', 'sss_file', 'pag_ibig_file', 'phil_health_file', 'company_name', 'position', 'from', 'to', 'company_name_two', 'position_two', 'from_two', 'to_two', 'company_name_three', 'position_three', 'from_three', 'to_three', 'allowance');

    $agent = agents::find($data['id']);
    if ($agent === null) {
      $response = ["status" => "error", "data" => 'No brand specialist found'];
      return response(json_encode($response), 400, ["Content-Type" => "application/json"]);
    } else {
      
      if($agent->branch_id !== $data['branch_id']){
        $oldUser = User::where('agent_id', $data['id'])->first();
        if ($oldUser) {
          $oldUser->status = 0;
          $oldUser = $oldUser->save();
        }

        $branch = branch::find($agent->branch_id);
        if ($branch) {
          $branch->team_lead = '';
          $branch = $branch->save();
        }
      }

      function handleFiles($fileImage) {
        $exploded = explode(',', $fileImage);

        $decoded = base64_decode($exploded[1]);

        if(str_contains($exploded[0], 'jpeg'))
          $extension = 'jpg';
        else
          $extension = 'png';

        $fileName = str_random().'.'.$extension;

        $path = public_path().'/uploads/'.$fileName;

        file_put_contents($path , $decoded);

        return $fileName;
      }
      
      $path = public_path().'/uploads/';
      $agent->first_name = $data['first_name'];
      $agent->last_name = $data['last_name'];
      $agent->dob = $data['dob'];
      $agent->email = $data['email'];
      $agent->title = $data['title'];
      $agent->employment_date = $data['employment_date'];
      $agent->mobile_number = $data['mobile_number'];
      $agent->current_address = $data['current_address'];
      $agent->permanent_address = $data['permanent_address'];
      $agent->emergency_person = $data['emergency_person'];
      $agent->emergency_number = $data['emergency_number'];
      $agent->high_school = $data['high_school'];
      $agent->is_hs_grad = $data['is_hs_grad'];
      $agent->hs_grad_year = $data['hs_grad_year'];
      $agent->college = $data['college'];
      $agent->is_college_grad = $data['is_college_grad'];
      $agent->college_grad_year = $data['college_grad_year'];
      $agent->college_course = $data['college_course'];
      $agent->technical_school = $data['technical_school'];
      $agent->is_ts_grad = $data['is_ts_grad'];
      $agent->ts_program = $data['ts_program'];
      $agent->ts_year = $data['ts_year'];
      $image = agents::where('id', $data['id'])->where('image_file_name', $data['image_file_name'])->first();
      if (!$image) {
        unlink($path .  $agent->image_file_name);
        $image_file_name = handleFiles($data['image_file']);
        $agent->image_file_name = $image_file_name;
        $agent->image_file_size = $data['image_file_size'];
        $agent->image_date_uploaded = $data['image_date_uploaded'];
      }
      $psa = agents::where('id', $data['id'])->where('psa_file_name', $data['psa_file_name'])->first();
      if (!$psa) {
        unlink($path .  $agent->psa_file_name);
        $psa_file_name = handleFiles($data['psa_file']);
        $agent->psa_file_name = $psa_file_name;
        $agent->psa_file_size = $data['psa_file_size'];
        $agent->psa_date_uploaded = $data['psa_date_uploaded'];
      }
      $nbi = agents::where('id', $data['id'])->where('nbi_file_name', $data['nbi_file_name'])->first();
      if (!$nbi) {
        unlink($path .  $agent->nbi_file_name);
        $nbi_file_name = handleFiles($data['nbi_file']);
        $agent->nbi_file_name = $nbi_file_name;
        $agent->nbi_file_size = $data['nbi_file_size'];
        $agent->nbi_date_uploaded = $data['nbi_date_uploaded'];
      }
      $agent->sss = $data['sss'];
      $agent->pag_ibig = $data['pag_ibig'];
      $agent->phil_health = $data['phil_health'];
      $agent->bank_name = $data['bank_name'];
      $agent->account_name = $data['account_name'];
      $agent->account_number = $data['account_number'];
      $agent->branch_id = $data['branch_id'];
      $agent->company_name = $data['company_name'];
      $agent->position = $data['position'];
      $agent->from = $data['from'];
      $agent->to = $data['to'];
      $agent->company_name_two = $data['company_name_two'];
      $agent->position_two = $data['position_two'];
      $agent->from_two = $data['from_two'];
      $agent->to_two = $data['to_two'];
      $agent->company_name_three = $data['company_name_three'];
      $agent->position_three = $data['position_three'];
      $agent->from_three = $data['from_three'];
      $agent->to_three = $data['to_three'];
      if($data['sss_file'] != '') {
        if (preg_match('/data:/', $data['sss_file']) && $agent->sss_file != '') {
          unlink($path .  $agent->sss_file);
        }
        $sss_file = handleFiles($data['sss_file']);
        $agent->sss_file = $sss_file;
        
      }
      if($data['pag_ibig_file'] != '') {
        if (preg_match('/data:/', $data['pag_ibig_file']) && $agent->pag_ibig_file != '') {
          unlink($path .  $agent->pag_ibig_file);
        }
        $pag_ibig_file = handleFiles($data['pag_ibig_file']);
        $agent->pag_ibig_file = $pag_ibig_file;
        
      }
      if($data['phil_health_file'] != '') {
        if (preg_match('/data:/', $data['phil_health_file']) && $agent->phil_health_file != '') {
          unlink($path .  $agent->phil_health_file);
        }
        $phil_health_file = handleFiles($data['phil_health_file']);
        $agent->phil_health_file = $phil_health_file;
      }
      $agent->allowance = $data['allowance'];
      $agent = $agent->save();
      $response = ["status" => "success", "data" => 'Branch information updated'];
      return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
    }
  }
  public function name(Request $request)
  {
    $v = validator($request->only('name'), [
      'name' => 'required|string'
    ]);
    $data = request()->only('name');
    $result = [];

    $agent = agents::select('id', 'first_name', 'last_name')->where('is_active', 1)->where('last_name', 'like', '%' . $data['name'] . '%')->orderBy('last_name')->get();
    foreach($agent as $row) {
      $each = [
        'id' => $row->id,
        'agent_name' => $row->first_name . ' ' . $row->last_name
      ];
      array_push($result, $each);
    }
    $response = ["status" => "success", "data" => $result];
    return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
  }

  public function findName(Request $request)
  {
    $v = validator($request->only('id'), [
      'id' => 'required|integer'
    ]);

    $data = request()->only('id');
    $agent = agents::select('first_name', 'last_name')->where('id', $data['id'])->first();
    $name =  $agent->first_name . ' ' . $agent->last_name;
    $response = ["status" => "success", "data" => $name];
    return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
    
  }

  public function nameWithBranch(Request $request)
  {
    $v = validator($request->only('name', 'id'), [
      'name' => 'required|string',
      'id' => 'required'
    ]);
    $data = request()->only('name', 'id');
    $result = [];

    $agent = agents::select('id', 'first_name', 'last_name')->where('isTL', '1')->where('is_active', 1)->where('last_name', 'like', '%' . $data['name'] . '%')->orderBy('last_name')->get();
    foreach($agent as $row) {
      $each = [
        'id' => $row->id,
        'agent_name' => $row->first_name . ' ' . $row->last_name
      ];
      array_push($result, $each);
    }
    $response = ["status" => "success", "data" => $result];
    return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
  }

  public function nameWithBranchPayment(Request $request)
  {
    $v = validator($request->only('name', 'id', 'tlId'), [
      'name' => 'required|string',
      'id' => 'required',
      'tlId' => 'required',
    ]);
    $data = request()->only('name', 'id', 'tlId');
    $result = [];

    if($data['tlId'] == 0) {
      $agent = agents::select('id', 'first_name', 'last_name')->where('is_active', 1)
      ->where('last_name', 'like', '%' . $data['name'] . '%')
      ->where(function ($query) use ($data) {
        $query->where('branch_id', $data['id'])
              ->orWhere('isTL', '1');
      })->orderBy('last_name')->get();
    } else {
      $agent = agents::select('id', 'first_name', 'last_name')->where('is_active', 1)
      ->where('last_name', 'like', '%' . $data['name'] . '%')
      ->where(function ($query) use ($data) {
        $query->where('branch_id', $data['id'])
              ->orWhere('id', $data['tlId']);
      })->orderBy('last_name')->get();
    }
    

    foreach($agent as $row) {
      $each = [
        'id' => $row->id,
        'agent_name' => $row->first_name . ' ' . $row->last_name
      ];
      array_push($result, $each);
    }
    $response = ["status" => "success", "data" => $result];
    return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
  }

  public function promote(Request $request) {
    $v = validator($request->only('id'), [
      'id' => 'required'
    ]);
    $data = request()->only('id');
    $result = [];

    $agent = agents::where('id', $data['id'])->where('is_active', 1)->first();
    $agent->isTL = '1';
    $agent->branch_id = '0';
    $agent = $agent->save();

    $user = user::where('agent_id', $data['id'])->first();
    $user->user_level = '0';
    $user = $user->save();

    $response = ["status" => "success", "data" => 'Agent successfully promoted'];
    return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
  }

  public function teamlead()
  {
    $result = [];
    $agent = agents::leftJoin('users', 'users.agent_id', '=',  'agents.id')->select('agents.id', 'agents.first_name', 'agents.last_name', 'agents.is_active', 'agents.created_at', 'users.username')->where('agents.isTL', '1')->orderBy('agents.is_active', 'desc')->orderBy('agents.created_at', 'desc')->paginate(10);
    if ($agent->isNotEmpty()) {
      $response = ["status" => "success", "data" => $agent->toArray()];
      return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
    } else {
      $response = ["status" => "error", "data" => 'No team leader added'];
      return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
    }
  }

  public function tlsearch(Request $request)
  {
    $v = validator($request->only('last_name', 'is_active'), [
        'last_name' => 'required|string|max:255',
        'is_active' => 'required'
    ]);

    if ($v->fails()) {
        return response()->json($v->errors()->all(), 400);
    }

    $data = request()->only('last_name', 'is_active');

    if ($data['is_active']) {
      $status = 1;
    } else {
      $status = 0;
    }

    $agent = agents::join('users', 'users.agent_id', '=',  'agents.id')->select('agents.id', 'agents.first_name', 'agents.last_name', 'agents.is_active', 'agents.created_at', 'users.username')->where('agents.last_name', 'like', '%' . $data['last_name'] . '%')->where('agents.is_active', $status)->where('agents.isTL', '1')->paginate(10);
    if ($agent->isNotEmpty()) {
      $response = ["status" => "success", "data" => $agent->toArray()];
    return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
    } else {
    $response = ["status" => "error", "data" => 'No team leader found'];
    return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
    }
  }

  public function tlbranches(Request $request)
  {
    $v = validator($request->only('id'), [
        'id' => 'required'
    ]);

    if ($v->fails()) {
        return response()->json($v->errors()->all(), 400);
    }

    $data = request()->only('id');

    $agent = branch::select('branch_name')->where('team_lead', $data['id'])->get();
    if ($agent->isNotEmpty()) {
      $response = ["status" => "success", "data" => $agent->toArray()];
    return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
    } else {
    $response = ["status" => "error", "data" => 'No team leader found'];
    return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
    }
  }
}
