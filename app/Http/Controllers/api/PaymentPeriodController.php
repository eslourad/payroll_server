<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\paymentperiod;
use App\payment;

class PaymentPeriodController extends Controller
{   
    public function index()
    {
        $paymentperiod = paymentperiod::orderBy('status', 'desc')->orderBy('created_at', 'desc')->paginate(10);
        $response = ["status" => "success", "data" => $paymentperiod->toArray()];
        return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
    }

    public function period()
    {
        $paymentperiod = paymentperiod::select('id', 'starting_date', 'end_date')->where('status', 'PENDING')->orderBy('created_at', 'desc')->get();
        if (!$paymentperiod->isEmpty()) {
            $response = ["status" => "success", "data" => $paymentperiod->toArray()];
            return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        } else {
            $response = ["status" => "error", "data" => 'No pending payment period'];
            return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        }
        
    }

    public function complete(Request $request)
    {   
        $v = validator($request->only('id'), [
            'id' => 'required'
        ]);

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }

        $data = request()->only('id');

        $lastPayment = paymentperiod::where('id', $data['id'])->first();
        
        if ($lastPayment) {
            $lastPayment->status = 'COMPLETED';
            $lastPayment = $lastPayment->save();
        }
    }

    public function create(Request $request)
    {
        $v = validator($request->only('starting_date', 'end_date'), [
            'starting_date' => 'required',
            'end_date' => 'required'
        ]);

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }

        $data = request()->only('starting_date', 'end_date');

        // $lastPayment = paymentperiod::where('status', 'PENDING')->first();
        
        // if ($lastPayment) {
        //     $lastPayment->status = 'COMPLETED';
        //     $lastPayment = $lastPayment->save();
        // }

        $period = paymentperiod::create([
            'starting_date' => $data['starting_date'],
            'end_date' => $data['end_date'],
            'status' => 'PENDING'
        ]);
        $response = ["status" => "success", "data" => 'Payment period created'];
        return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
    }

    public function paidAgents($id)
    {
        $payments = payment::join('paymentperiods', 'payments.payment_period_id', '=',  'paymentperiods.id')->join('agents', 'payments.agent_id', '=',  'agents.id')->join('branches', 'payments.branch_id', '=',  'branches.id')->select('payments.agent_id', 'payments.id', 'agents.first_name', 'agents.last_name', 'payments.total_paid', 'branches.branch_name', 'payments.work_days', 'payments.ot_hours', 'payments.rest_days', 'payments.regular_holiday', 'payments.special_holiday', 'paymentperiods.status', 'payments.adjustments', 'payments.remarks')->where('payments.payment_period_id', $id)->orderBy('payments.created_at', 'desc')->paginate(10);
        $response = ["status" => "success", "data" => $payments->toArray()];
        return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
    }

    public function agentPayment($id)
    {
        $payments = payment::join('paymentperiods', 'payments.payment_period_id', '=',  'paymentperiods.id')->join('agents', 'payments.agent_id', '=',  'agents.id')->join('branches', 'payments.branch_id', '=',  'branches.id')->select('payments.agent_id', 'payments.id', 'payments.paid_by', 'agents.first_name', 'agents.last_name', 'payments.total_paid', 'branches.branch_name', 'paymentperiods.status', 'paymentperiods.starting_date', 'paymentperiods.end_date', 'payments.work_days', 'payments.ot_hours', 'payments.rest_days', 'payments.regular_holiday', 'payments.special_holiday', 'payments.ot_approval', 'payments.dtr', 'payments.rate', 'payments.allowance', 'payments.ot_rates', 'payments.regular_holiday_rate', 'payments.special_holiday_rate', 'payments.rest_day_rate', 'payments.sss', 'payments.pag_ibig', 'payments.phil_health', 'payments.savings', 'payments.ca_deduction', 'payments.sss_loan_deduction', 'payments.pi_loan_deduction', 'payments.total_paid', 'payments.adjustments', 'payments.created_at', 'payments.remarks', 'agents.employment_date', 'agents.title', 'agents.email')->where('payments.id', $id)->first();
        $response = ["status" => "success", "data" => $payments->toArray()];
        return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
    }

    public function search(Request $request)
    {
        $v = validator($request->only('last_name', 'is_active'), [
            'last_name' => 'required',
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

        $agent = payment::join('agents', 'payments.agent_id', '=',  'agents.id')->join('paymentperiods', 'payments.payment_period_id', '=',  'paymentperiods.id')->where('agents.last_name', 'like', '%' . $data['last_name'] . '%')->where('agents.is_active', $status)->select('paymentperiods.id', 'paymentperiods.starting_date', 'paymentperiods.end_date', 'paymentperiods.status', 'paymentperiods.created_at', 'paymentperiods.updated_at')->orderBy('status', 'desc')->paginate(10);

        if ($agent->isNotEmpty()) {
            $response = ["status" => "success", "data" => $agent->toArray()];
            return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        } else {
            $response = ["status" => "error", "data" => 'No agent found'];
            return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        }
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

        $paymentperiod = paymentperiod::find($data['id']);
        if ($paymentperiod) {
            // $prevppID = paymentperiod::orderBy('created_at', 'desc')->skip(1)->take(1)->get();
            // if (!$prevppID->isEmpty()) {
            //     $prevppID = paymentperiod::where('id', $prevppID[0]->id)->first();
            //     $prevppID->status = 'PENDING';
            //     $prevppID = $prevppID->save();
            // }
            $destroy = paymentperiod::destroy($data['id']);
            $deleteAllPayments = payment::where('payment_period_id', $data['id'])->delete();
            
            $response = ["status" => "success", "data" => 'Successfully Deleted'];
            return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        } else {
            $response = ["status" => "error", "data" => 'No payment period found with this ID'];
            return response(json_encode($response), 400, ["Content-Type" => "application/json"]);
        }
    }
}
