<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\deductions;
use App\branch;
use App\agents;
use App\paymentperiod;
use App\payment;
use App\savings;
class DeductionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $deduction = deductions::find(1);
        $response = ["status" => "success", "data" => $deduction->toArray()];
        return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $v = validator($request->only('sss', 'pag_ibig', 'phil_health', 'savings'), [
            'sss' => 'required',
            'pag_ibig' => 'required',
            'phil_health' => 'required',
            'savings' => 'required'
        ]);

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }

        $data = request()->only('sss', 'pag_ibig', 'phil_health', 'savings');

        $deduction = deductions::where('id', 1)->first();
        if (!$deduction) {
            $deduction = deductions::create([
                'sss' => $data['sss'],
                'pag_ibig' => $data['pag_ibig'],
                'phil_health' => $data['phil_health'],
                'savings' => $data['savings']
            ]);
            $response = ["status" => "success", "data" => 'Deduction created'];
            return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        } else {
            $deduction->sss = $data['sss'];
            $deduction->pag_ibig = $data['pag_ibig'];
            $deduction->phil_health = $data['phil_health'];
            $deduction->savings = $data['savings'];
            $deduction = $deduction->save();
            $response = ["status" => "success", "data" => 'Deduction information updated'];
            return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        }
    }

    //Reports
    public function searchPP(Request $request)
    {
        $v = validator($request->only('payment_period'), [
            'payment_period' => 'required'
        ]);

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }

        $data = request()->only('payment_period');

        $paymentPeriod = paymentperiod::select('id','starting_date', 'end_date', 'created_at')
            ->where('starting_date', '<=', $data['payment_period'])
            ->where('end_date', '>=', $data['payment_period'])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($paymentPeriod->isNotEmpty()) {
            $response = ["status" => "success", "data" => $paymentPeriod->toArray()];
            return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        } else {
            $response = ["status" => "error", "data" => 'No data found'];
            return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        }
    }
    public function searchStore(Request $request)
    {
        $v = validator($request->only('store_name'), [
            'store_name' => 'required'
        ]);

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }

        $data = request()->only('store_name');

        $branch = branch::select('id', 'branch_name', 'is_active')->where('branch_name', 'like', '%' . $data['store_name'] . '%')->get();

        if ($branch->isNotEmpty()) {
            $response = ["status" => "success", "data" => $branch->toArray()];
            return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        } else {
            $response = ["status" => "error", "data" => 'No data found'];
            return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        }
    }

    public function searchAgents(Request $request)
    {
        $v = validator($request->only('store_id', 'pp_id'), [
            'store_id' => 'required',
            'pp_id' => 'required'
        ]);

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }

        $data = request()->only('store_id', 'pp_id');

        $payment = payment::join('agents', 'payments.agent_id', '=', 'agents.id')->join('savings', 'payments.agent_id', '=', 'savings.agent_id')->select('payments.*', 'agents.last_name', 'agents.first_name', 'agents.title', 'savings.deduction_rate', 'savings.ca_balance')->where('payment_period_id', 'like', '%' . $data['pp_id'] . '%')
        ->where('payments.branch_id', 'like', '%' . $data['store_id'] . '%')
        ->get();

        if ($payment->isNotEmpty()) {
            $response = ["status" => "success", "data" => $payment->toArray()];
            return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        } else {
            $response = ["status" => "error", "data" => 'No data found'];
            return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        }
    }

}
