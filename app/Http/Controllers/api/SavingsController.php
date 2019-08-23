<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\savings;
use App\agents;
use App\cashadvance;

class SavingsController extends Controller
{
    public function index($id)
    {
	    return savings::where('agent_id', $id)->first();
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

	    $agent = agents::leftJoin('branches', 'agents.branch_id', '=',  'branches.id')
	    	->select('agents.id', 'agents.first_name', 'agents.last_name', 'branches.branch_name', 'agents.is_active', 'agents.created_at')
	    	->where('agents.last_name', 'like', '%' . $data['last_name'] . '%')
	    	->where('agents.is_active', $status)->paginate(10);
	    if ($agent->isNotEmpty()) {
	      $response = ["status" => "success", "data" => $agent->toArray()];
	    return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
	    } else {
	    $response = ["status" => "error", "data" => 'No user found'];
	    return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
	    }
    }

    public function create(Request $request)
    {
        $v = validator($request->only('agent_id', 'deduction_rate', 'cash_advance', 'sss_deduction', 'sss_loan_new', 'pi_deduction', 'pi_loan_new'), [
            'agent_id' => 'required|integer',
            'deduction_rate' => 'required|string',
            'cash_advance' => 'required|string',
            'sss_deduction' => 'required|string',
            'sss_loan_new' => 'required|string',
            'pi_deduction' => 'required|string',
            'pi_loan_new' => 'required|string'
        ]);

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }

        $data = request()->only('agent_id', 'deduction_rate', 'cash_advance', 'sss_deduction', 'sss_loan_new', 'pi_deduction', 'pi_loan_new');

        $savings = savings::where('agent_id', $data['agent_id'])->first();
        $savings->deduction_rate = $data['deduction_rate'];
        $savings->ca_balance = $savings->ca_balance + $data['cash_advance'];
        $savings->loan_sss_deduction = $data['sss_deduction'];
        $savings->loan_sss = $savings->loan_sss + $data['sss_loan_new'];
        $savings->loan_pi_deduction = $data['pi_deduction'];
        $savings->loan_pag_ibig = $savings->loan_pag_ibig + $data['pi_loan_new'];
        $savings = $savings->save();
        $cashadvance = cashadvance::create([
            'agent_id' => $data['agent_id'],
            'cash_advance' => $data['cash_advance'],
            'sss_loan' => $data['sss_loan_new'],
            'pag_ibig_loan' => $data['pi_loan_new']
        ]);
        $response = ["status" => "success", "data" => 'Cash advance created'];
        return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
    }

    public function cashAdvance($id)
    {
        return cashadvance::where('agent_id', $id)->paginate(10);
    }

    public function cashAdvanceAll($id)
    {
        return cashadvance::where('agent_id', $id)->get();
    }

    public function showAgentCA()
    {
        $agent = agents::leftJoin('branches', 'agents.branch_id', '=',  'branches.id')->select('agents.id', 'agents.first_name', 'agents.last_name', 'branches.branch_name', 'agents.is_active', 'agents.created_at')->orderBy('is_active', 'desc')->orderBy('created_at', 'desc')->paginate(10);
        if ($agent->isNotEmpty()) {
          $response = ["status" => "success", "data" => $agent->toArray()];
          return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        } else {
          $response = ["status" => "error", "data" => 'No agent added'];
          return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        }
    }

    public function searchSavings(Request $request)
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

        $agent = agents::leftJoin('branches', 'agents.branch_id', '=',  'branches.id')
            ->leftJoin('savings', 'agents.id', '=', 'savings.agent_id')
            ->select('agents.id', 'agents.first_name', 'agents.last_name', 'branches.branch_name', 'agents.is_active', 'agents.created_at')
            ->where('agents.last_name', 'like', '%' . $data['last_name'] . '%')
            ->where('agents.is_active', $status)
            ->where('savings.total_savings', '3500')
            ->paginate(10);
        if ($agent->isNotEmpty()) {
          $response = ["status" => "success", "data" => $agent->toArray()];
        return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        } else {
        $response = ["status" => "error", "data" => 'No user found'];
        return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        }
    }
}
