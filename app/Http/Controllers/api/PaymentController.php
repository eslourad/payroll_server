<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\payment;
use App\savings;
use App\deductions;
use App\branch;
use App\agents;
use App\paymentperiod;

class PaymentController extends Controller
{
    public function create(Request $request) {
        $v = validator($request->only('branch_id', 'agent_id', 'payment_period_id', 'work_days', 'ot_hours', 'rest_days', 'regular_holiday', 'special_holiday', 'ot_approval', 'dtr', 'paid_by', 'adjustments', 'adjustmentSign', 'remarks'), [
            'branch_id' => 'required',
            'agent_id' => 'required',
            'payment_period_id' => 'required',
            'work_days' => 'required',
            'ot_hours' => 'required',
            'rest_days' => 'required',
            'regular_holiday' => 'required',
            'special_holiday' => 'required',
            'dtr' => 'required',
            'paid_by' => 'required',
            'adjustments' => 'required',
            'adjustmentSign' => 'required'
        ]);

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }

        $data = request()->only('branch_id', 'agent_id', 'payment_period_id', 'work_days', 'ot_hours', 'rest_days', 'regular_holiday', 'special_holiday', 'ot_approval', 'dtr', 'paid_by', 'adjustments', 'adjustmentSign', 'remarks');

        function handleFiles($fileImage, $passType) {
          $exploded = explode(',', $fileImage);

          $decoded = base64_decode($exploded[1]);

          if(str_contains($exploded[0], 'jpeg'))
            $extension = 'jpg';
          else
            $extension = 'png';

          $fileName = str_random().'.'.$extension;

          $type = $passType === 'ot' ? 'OT' : 'DTR';

          $path = public_path().'/uploads/payment/'.$type.$fileName;

          file_put_contents($path , $decoded);

          return $fileName;
        }

        if($data['ot_approval'] !== null) {
          $ot_file = handleFiles($data['ot_approval'], 'ot');
        } else {
          $ot_file = '';
        }

        if($data['dtr'] !== null) {
          $dtr = handleFiles($data['dtr'], 'dtr');
        } else {
          $dtr = '';
        }

        $deduction = deductions::find(1);

        if (!$deduction) {
            $sss = 0;
            $pagibig = 0;
            $phil_health = 0;
            $savings_deduction = 0;
        } else {
            $sss = $deduction->sss;
            $pagibig = $deduction->pag_ibig;
            $phil_health = $deduction->phil_health;
            $savings_deduction = $deduction->savings;
        }

        $branch = branch::where('id', $data['branch_id'])->first();

        $otRateToAdd = ($branch->ot_rates / 100) * $branch->rate;
        $actualOTRate = $otRateToAdd + $branch->rate;
        $dividedByHours = $actualOTRate / 8;

        $branchRestDay = ($branch->rest_days / 100) * $branch->rate;
        $branchRegularHoliday = ($branch->regular_holidays / 100) * $branch->rate;
        $branchSpecialHoliday = ($branch->special_holidays / 100) * $branch->rate;


        $totalOT = $dividedByHours * $data['ot_hours'];
        $totalRestDay = $branchRestDay * $data['rest_days'];
        $totalRegularHoliday = $branchRegularHoliday * $data['regular_holiday'];
        $totalSpecialHoliday = $branchSpecialHoliday * $data['special_holiday'];
        $totalRate = $branch->rate * $data['work_days'];
        $totalPayment = $totalOT + $totalRestDay + $totalRegularHoliday + $totalSpecialHoliday + $totalRate;

        $agent = agents::select('allowance')->where('id', $data['agent_id'])->first();
        $totalPayment = $totalPayment + $agent->allowance;
        if($data['adjustmentSign']) {
            $adjustments = $data['adjustments'];
            $totalPayment = $totalPayment + $data['adjustments'];
        } else {
            $adjustments = '-' . $data['adjustments'];
            $totalPayment = $totalPayment - $data['adjustments'];
        }
        

        $savings = savings::where('agent_id', $data['agent_id'])->first();
        
        if($savings->total_savings < 3500) {
            $testIfover = $savings->total_savings + $savings_deduction;
            if($testIfover > 3500) {
                $savings_deduction = 3500 - $savings->total_savings;
            }
            $totalPayment = $totalPayment - $savings_deduction;
            $savings->total_savings = $savings->total_savings + $savings_deduction;
            $savings->save();
        } else {
            $savings_deduction = 0;
        }
        $caBalance = $savings->ca_balance - $savings->deduction_rate;
        if ($caBalance > 0) {
            $deductedByCA = $savings->deduction_rate;
            $savings->ca_balance = $caBalance;
            $savings->save();
        } else {
            $deductedByCA = $savings->ca_balance;
            $caBalance = 0;
            $savings->ca_balance = $caBalance;
            $savings->save();
        }
        $sssLoan = $savings->loan_sss - $savings->loan_sss_deduction;
        if ($sssLoan > 0) {
            $deductedBySSSLoan = $savings->loan_sss_deduction;
            $savings->loan_sss = $sssLoan;
            $savings->save();
        } else {
            $deductedBySSSLoan = $savings->loan_sss;
            $sssLoan = 0;
            $savings->loan_sss = $sssLoan;
            $savings->save();
        }
        $piLoan = $savings->loan_pag_ibig - $savings->loan_pi_deduction;
        if ($piLoan > 0) {
            $deductedByPiLoan = $savings->loan_pi_deduction;
            $savings->loan_pag_ibig = $piLoan;
            $savings->save();
        } else {
            $deductedByPiLoan = $savings->loan_pag_ibig;
            $piLoan = 0;
            $savings->loan_pag_ibig = $piLoan;
            $savings->save();
        }

        $totalPayment = $totalPayment - $sss;
        $totalPayment = $totalPayment - $pagibig;
        $totalPayment = $totalPayment - $phil_health;
        $totalPayment = $totalPayment - $deductedByCA;
        $totalPayment = $totalPayment - $deductedBySSSLoan;
        $totalPayment = $totalPayment - $deductedByPiLoan;

        $tlID = branch::select('team_lead')->where('id', $data['branch_id'])->first();

        if($tlID->team_lead == $data['agent_id']) {
            $isTL = '1';
        } else {
            $isTL = '0';
        }

        $isExisting = payment::where('agent_id', $data['agent_id'])->where('payment_period_id', $data['payment_period_id'])->first();
        if (!$isExisting) {
            $payment = payment::create([
                'agent_id' => $data['agent_id'],
                'branch_id' => $data['branch_id'],
                'tl_id' => $tlID->team_lead,
                'is_tl' => $isTL,
                'payment_period_id' => $data['payment_period_id'],
                'work_days' => $data['work_days'],
                'ot_hours' => $data['ot_hours'],
                'rest_days' => $data['rest_days'],
                'regular_holiday' => $data['regular_holiday'],
                'special_holiday' => $data['special_holiday'],
                'ot_approval' => 'OT' . $ot_file,
                'dtr' => 'DTR'. $dtr,
                'paid_by' => $data['paid_by'],
                'rate' => $branch->rate,
                'allowance' => $agent->allowance,
                'ot_rates' => $branch->ot_rates,
                'regular_holiday_rate' => $branch->regular_holidays,
                'special_holiday_rate' =>$branch->special_holidays,
                'rest_day_rate' => $branch->rest_days,
                'sss' => $sss,
                'pag_ibig' => $pagibig,
                'phil_health' => $phil_health,
                'savings' => $savings_deduction,
                'ca_deduction' => $deductedByCA,
                'sss_loan_deduction' => $deductedBySSSLoan,
                'pi_loan_deduction' => $deductedByPiLoan,
                'total_paid' => $totalPayment,
                'adjustments' => $adjustments,
                'remarks' => $data['remarks'],
            ]);

            $response = ["status" => "success", "data" => $payment->toArray()];
            return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        } else {
            $response = ["status" => "error", "data" => 'Agent already paid'];
            return response(json_encode($response), 400, ["Content-Type" => "application/json"]);
        }
    }

    public function delete(Request $request) {
        $v = validator($request->only('id', 'agent_id'), [
            'id' => 'required',
            'agent_id' => 'required'
        ]);

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }

        $data = request()->only('id', 'agent_id');
        //undo savings and ca
        $payment = payment::find($data['id']);
        $agentSaving = savings::where('agent_id', $data['agent_id'])->first();
        $agentSaving->total_savings = $agentSaving->total_savings - $payment->savings;
        $agentSaving->ca_balance = $agentSaving->ca_balance + $payment->ca_deduction;
        $agentSaving->loan_sss = $agentSaving->loan_sss + $payment->sss_loan_deduction;
        $agentSaving->loan_pag_ibig = $agentSaving->loan_pag_ibig + $payment->pi_loan_deduction;
        $agentSaving = $agentSaving->save();
        $path = public_path().'/uploads/payment/';
        
        if ($agentSaving) {
            $successDeletion = payment::destroy($data['id']);
            unlink($path .  $payment->dtr);
            $response = ["status" => "success", "data" => 'Successfully Deleted'];
            return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        } else {
            $response = ["status" => "error", "data" => 'No payment found with this ID'];
            return response(json_encode($response), 400, ["Content-Type" => "application/json"]);
        }
    }

    public function adjust(Request $request) {
        $v = validator($request->only('id', 'adjustments', 'remarks', 'adjustmentSign'), [
            'id' => 'required',
            'adjustments' => 'required',
            'adjustmentSign' => 'required'
        ]);

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }

        $data = request()->only('id', 'adjustments', 'remarks', 'adjustmentSign');

        $payment = payment::find($data['id']);
        if ($payment) {
            $payment->total_paid = $payment->total_paid - $payment->adjustments;
            if($data['adjustmentSign']) {
                $adjustments = $data['adjustments'];
                $payment->total_paid = $payment->total_paid + $data['adjustments'];
            } else {
                $adjustments = '-' . $data['adjustments'];
                $payment->total_paid = $payment->total_paid - $data['adjustments'];
            }
            $payment->adjustments = $adjustments;
            $payment->remarks = $data['remarks'];
            $payment = $payment->save();
            
            $response = ["status" => "success", "data" => 'Successfully adjusted'];
            return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        } else {
            $response = ["status" => "error", "data" => 'No payment found with this ID'];
            return response(json_encode($response), 400, ["Content-Type" => "application/json"]);
        }
    }

    public function mypayments($id)
    {
        $payments = payment::join('paymentperiods', 'payments.payment_period_id', '=',  'paymentperiods.id')->join('agents', 'payments.agent_id', '=',  'agents.id')->join('branches', 'payments.branch_id', '=',  'branches.id')->select('payments.id', 'agents.first_name', 'agents.last_name', 'payments.total_paid', 'branches.branch_name', 'payments.work_days', 'payments.ot_hours', 'payments.rest_days', 'payments.regular_holiday', 'payments.special_holiday', 'paymentperiods.status', 'payments.created_at')->where('payments.agent_id', $id)->orderBy('payments.created_at', 'desc')->paginate(10);
        $response = ["status" => "success", "data" => $payments->toArray()];
        return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
    }


    // REPORT START
    public function viewreports(Request $request) {
        $v = validator($request->only('show_all'), [
            'show_all' => 'required'
        ]);

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }

        $data = request()->only('show_all');

        if($data['show_all'] == true) {
            $payments = paymentperiod::select('id','starting_date', 'end_date', 'status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();
            $payments->map(function($value) {
                 //bs
                $payments = payment::join('paymentperiods', 'payments.payment_period_id', '=',  'paymentperiods.id')
                    ->where('payments.payment_period_id', $value->id)
                    ->where('is_tl', '0')
                    ->count();
                $value->bsPaid = $payments;
                //tl
                $tlPaid = payment::join('paymentperiods', 'payments.payment_period_id', '=',  'paymentperiods.id')
                    ->where('is_tl', '1')
                    ->where('payments.payment_period_id', $value->id)
                    ->count();
                $value->tlPaid = $tlPaid;
                
                //net pay
                $total = payment::where('payments.payment_period_id', $value->id)->sum('total_paid');
                $value->total = $total;
                return $value;
            });
        } else {
            $payments = paymentperiod::select('id','starting_date', 'end_date', 'status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

            $payments->getCollection()->transform(function ($value) {
                //bs
                $payments = payment::join('paymentperiods', 'payments.payment_period_id', '=',  'paymentperiods.id')
                    ->where('payments.payment_period_id', $value->id)
                    ->where('is_tl', '0')
                    ->count();
                $value->bsPaid = $payments;
                //tl
                $tlPaid = payment::join('paymentperiods', 'payments.payment_period_id', '=',  'paymentperiods.id')
                    ->where('is_tl', '1')
                    ->where('payments.payment_period_id', $value->id)
                    ->count();
                $value->tlPaid = $tlPaid;
                
                //net pay
                $total = payment::where('payments.payment_period_id', $value->id)->sum('total_paid');
                $value->total = $total;
                return $value;
            });
        }
        
        //stats
        $completed_payments = paymentperiod::where('status', 'COMPLETED')->count();
        $total_payments = paymentperiod::count();
        $total_agent = agents::where('isTL', '0')->count();
        $total_tl = agents::where('isTL', '1')->count();
        $total_branch = branch::count();
        $stats = [
            'completed_payments' => $completed_payments,
            'total_payments' => $total_payments,
            'total_agents' => $total_agent,
            'total_tl' => $total_tl,
            'total_branch' => $total_branch
        ];
        
        $response = ["status" => "success", "data" => $payments->toArray(), "stats" => $stats];
        return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
    }

    public function searchreports(Request $request) {
        $v = validator($request->only('payment_period', 'show_all'), [
            'payment_period' => 'required',
            'show_all' => 'required'
        ]);

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }

        $data = request()->only('payment_period', 'show_all');

        if($data['show_all'] == true) {
            $now = date($data['payment_period']);
            $payments = paymentperiod::select('id','starting_date', 'end_date', 'created_at')
                ->where('starting_date', '<=', $now)
                ->where('end_date', '>=', $now)
                ->orderBy('created_at', 'desc')
                ->get();

            $payments->map(function($value) {
                //bs
                $payments = payment::join('paymentperiods', 'payments.payment_period_id', '=',  'paymentperiods.id')
                    ->where('payments.payment_period_id', $value->id)
                    ->where('is_tl', '0')
                    ->count();
                $value->bsPaid = $payments;
                //tl
                $tlPaid = payment::join('paymentperiods', 'payments.payment_period_id', '=',  'paymentperiods.id')
                    ->where('is_tl', '1')
                    ->where('payments.payment_period_id', $value->id)
                    ->count();
                $value->tlPaid = $tlPaid;
                //net pay
                $total = payment::where('payments.payment_period_id', $value->id)->sum('total_paid');
                $value->total = $total;
                return $value;
            });
        } else {
            $now = date($data['payment_period']);
            $payments = paymentperiod::select('id','starting_date', 'end_date', 'created_at')
                ->where('starting_date', '<=', $now)
                ->where('end_date', '>=', $now)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
            $payments->getCollection()->transform(function ($value) {
                //bs
                $payments = payment::join('paymentperiods', 'payments.payment_period_id', '=',  'paymentperiods.id')
                    ->where('payments.payment_period_id', $value->id)
                    ->where('is_tl', '0')
                    ->count();
                $value->bsPaid = $payments;
                //tl
                $tlPaid = payment::join('paymentperiods', 'payments.payment_period_id', '=',  'paymentperiods.id')
                    ->where('is_tl', '1')
                    ->where('payments.payment_period_id', $value->id)
                    ->count();
                $value->tlPaid = $tlPaid;
                //net pay
                $total = payment::where('payments.payment_period_id', $value->id)->sum('total_paid');
                $value->total = $total;
                return $value;
            });
        }

        if ($payments->isNotEmpty()) {
            $response = ["status" => "success", "data" => $payments->toArray()];
            return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        } else {
            $response = ["status" => "error", "data" => 'No data found'];
            return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        }
    }

    public function ppdetailreports(Request $request) {
        $v = validator($request->only('id', 'show_all'), [
            'id' => 'required',
            'show_all' => 'required'
        ]);

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }

        $data = request()->only('id', 'show_all');
        if($data['show_all'] == true) {
            $payments = payment::select('tl_id')
                ->where('payment_period_id', $data['id'])
                ->groupBy('tl_id')
                ->orderBy('created_at', 'desc')
                ->get();
            $payments->map(function($value) use ($data) {
                //store
                $store = payment::select('branch_id')
                    ->where('tl_id', $value->tl_id)
                    ->where('payment_period_id', $data['id'])
                    ->distinct('branch_id')
                    ->count('branch_id');
                $value->storeCount = $store;
                // //bs
                $payments = payment::where('is_tl', '0')
                    ->where('tl_id', $value->tl_id)
                    ->where('payment_period_id', $data['id'])
                    ->count();
                $value->bsPaid = $payments;
                // //tl
                $tlPaid = payment::where('agent_id', $value->tl_id)
                    ->where('payments.payment_period_id', $data['id'])
                    ->count();
                $agentFName = agents::select('first_name', 'last_name')
                    ->where('id', $value->tl_id)
                    ->first();
                $value->first_name = $agentFName->first_name;
                $value->last_name = $agentFName->last_name;  
                $value->isTLPaid = $tlPaid;
                // //net pay
                $total = payment::where('tl_id', $value->tl_id)
                    ->where('payments.payment_period_id', $data['id'])
                    ->sum('total_paid');
                $value->total = $total;
                return $value;
            });
        } else {
            $payments = payment::select('tl_id')
                ->where('payment_period_id', $data['id'])
                ->groupBy('tl_id')
                ->orderBy('created_at', 'desc')
                ->paginate(10);
            $payments->getCollection()->transform(function ($value) use ($data){
                //store
                $store = payment::select('branch_id')
                    ->where('tl_id', $value->tl_id)
                    ->where('payment_period_id', $data['id'])
                    ->distinct('branch_id')
                    ->count('branch_id');
                $value->storeCount = $store;
                // //bs
                $payments = payment::where('is_tl', '0')
                    ->where('tl_id', $value->tl_id)
                    ->where('payment_period_id', $data['id'])
                    ->count();
                $value->bsPaid = $payments;
                // //tl
                $tlPaid = payment::where('agent_id', $value->tl_id)
                    ->where('payments.payment_period_id', $data['id'])
                    ->count();
                $agentFName = agents::select('first_name', 'last_name')
                    ->where('id', $value->tl_id)
                    ->first();
                $value->first_name = $agentFName->first_name;
                $value->last_name = $agentFName->last_name;  
                $value->isTLPaid = $tlPaid;
                // //net pay
                $total = payment::where('tl_id', $value->tl_id)
                    ->where('payments.payment_period_id', $data['id'])
                    ->sum('total_paid');
                $value->total = $total;
                return $value;
            });
        }

        if ($payments->isNotEmpty()) {
            $response = ["status" => "success", "data" => $payments->toArray()];
            return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        } else {
            $response = ["status" => "error", "data" => 'No data found'];
            return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        }
    }

    public function ppdetailsearch(Request $request) {
        $v = validator($request->only('id', 'tlId', 'show_all'), [
            'id' => 'required',
            'tlId' => 'required',
            'show_all' => 'required'
        ]);

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }

        $data = request()->only('id', 'tlId', 'show_all');
        if($data['show_all'] == true) {
            $payments = payment::select('tl_id')
                ->join('agents', 'payments.tl_id', '=', 'agents.id')
                ->where('last_name', 'like', '%' . $data['tlId'] . '%')
                ->where('payment_period_id', $data['id'])
                ->groupBy('tl_id')
                ->orderBy('payments.created_at', 'desc')
                ->get();
            $payments->map(function($value) use ($data) {
                //store
                $store = payment::select('branch_id')
                    ->where('tl_id', $value->tl_id)
                    ->where('payment_period_id', $data['id'])
                    ->distinct('branch_id')
                    ->count('branch_id');
                $value->storeCount = $store;
                // //bs
                $payments = payment::where('is_tl', '0')
                    ->where('tl_id', $value->tl_id)
                    ->where('payment_period_id', $data['id'])
                    ->count();
                $value->bsPaid = $payments;
                // //tl
                $tlPaid = payment::where('agent_id', $value->tl_id)
                    ->where('payments.payment_period_id', $data['id'])
                    ->count();
                $agentFName = agents::select('first_name', 'last_name')
                    ->where('id', $value->tl_id)
                    ->first();
                $value->first_name = $agentFName->first_name;
                $value->last_name = $agentFName->last_name;  
                $value->isTLPaid = $tlPaid;
                // //net pay
                $total = payment::where('tl_id', $value->tl_id)
                    ->where('payments.payment_period_id', $data['id'])
                    ->sum('total_paid');
                $value->total = $total;
                return $value;
            });
        } else {
            $payments = payment::select('tl_id')
                ->join('agents', 'payments.tl_id', '=', 'agents.id')
                ->where('last_name', 'like', '%' . $data['tlId'] . '%')
                ->where('payment_period_id', $data['id'])
                ->groupBy('tl_id')
                ->orderBy('payments.created_at', 'desc')
                ->paginate(10);

            $payments->getCollection()->transform(function ($value) use ($data){
                //store
                $store = payment::select('branch_id')
                    ->where('tl_id', $value->tl_id)
                    ->where('payment_period_id', $data['id'])
                    ->distinct('branch_id')
                    ->count('branch_id');
                $value->storeCount = $store;
                // //bs
                $payments = payment::where('is_tl', '0')
                    ->where('tl_id', $value->tl_id)
                    ->where('payment_period_id', $data['id'])
                    ->count();
                $value->bsPaid = $payments;
                // //tl
                $tlPaid = payment::where('agent_id', $value->tl_id)
                    ->where('payments.payment_period_id', $data['id'])
                    ->count();
                $agentFName = agents::select('first_name', 'last_name')
                    ->where('id', $value->tl_id)
                    ->first();
                $value->first_name = $agentFName->first_name;
                $value->last_name = $agentFName->last_name;  
                $value->isTLPaid = $tlPaid;
                // //net pay
                $total = payment::where('tl_id', $value->tl_id)
                    ->where('payments.payment_period_id', $data['id'])
                    ->sum('total_paid');
                $value->total = $total;
                return $value;
            });
        }

        if ($payments->isNotEmpty()) {
            $response = ["status" => "success", "data" => $payments->toArray()];
            return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        } else {
            $response = ["status" => "error", "data" => 'No data found'];
            return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        }
    }

    public function tldetail(Request $request) {
        $v = validator($request->only('id', 'tl_id', 'show_all'), [
            'tl_id' => 'required',
            'id' => 'required',
            'show_all' => 'required'
        ]);

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }

        $data = request()->only('tl_id', 'id', 'show_all');
        if($data['show_all'] == true) {
            $payments = payment::select('branch_id as id')
                ->where('tl_id', $data['tl_id'])
                ->groupBy('branch_id')
                ->orderBy('created_at', 'desc')
                ->get();
            $payments->map(function($value) use ($data) {
                //store name
                $name = branch::select('branch_name')
                ->where('id', $value->id)
                ->first();
                $value->branch_name = $name->branch_name;
                //bs
                $payments = payment::where('is_tl', '0')
                ->where('payment_period_id', $data['id'])
                ->where('branch_id', $value->id)
                ->count();
                $value->bsPaid = $payments;
                //net pay
                $total = payment::where('branch_id', $value->id)->where('payments.payment_period_id', $data['id'])->sum('total_paid');
                $value->total = $total;
                return $value;
            });
        } else {
            $payments = payment::select('branch_id as id')
                ->where('tl_id', $data['tl_id'])
                ->groupBy('branch_id')
                ->orderBy('created_at', 'desc')
                ->paginate(10);
            $payments->getCollection()->transform(function ($value) use ($data){
                //store name
                $name = branch::select('branch_name')
                ->where('id', $value->id)
                ->first();
                $value->branch_name = $name->branch_name;
                //bs
                $payments = payment::where('is_tl', '0')
                ->where('payment_period_id', $data['id'])
                ->where('branch_id', $value->id)
                ->count();
                $value->bsPaid = $payments;
                //net pay
                $total = payment::where('branch_id', $value->id)->where('payments.payment_period_id', $data['id'])->sum('total_paid');
                $value->total = $total;
                return $value;
            });
        }

        if ($payments->isNotEmpty()) {
            $response = ["status" => "success", "data" => $payments->toArray()];
            return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        } else {
            $response = ["status" => "error", "data" => 'No data found'];
            return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        }
    }

    public function tlsearch(Request $request) {
        $v = validator($request->only('id', 'tl_id', 'txt_search', 'show_all'), [
            'tl_id' => 'required',
            'id' => 'required',
            'txt_search' => 'required',
            'show_all' => 'required'
        ]);

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }

        $data = request()->only('tl_id', 'id', 'txt_search', 'show_all');
        if($data['show_all'] == true) {
            $payments = payment::join('branches', 'payments.branch_id', '=',  'branches.id')
                ->select('branch_id as id')
                ->where('branch_name', 'like', '%' . $data['txt_search'] . '%')
                ->where('tl_id', $data['tl_id'])
                ->groupBy('branch_id')
                ->orderBy('payments.created_at', 'desc')
                ->get();
            $payments->map(function($value) use ($data) {
                //store name
                $name = branch::select('branch_name')
                ->where('id', $value->id)
                ->first();
                $value->branch_name = $name->branch_name;
                //bs
                $payments = payment::where('is_tl', '0')
                ->where('payment_period_id', $data['id'])
                ->where('branch_id', $value->id)
                ->count();
                $value->bsPaid = $payments;
                //net pay
                $total = payment::where('branch_id', $value->id)->where('payments.payment_period_id', $data['id'])->sum('total_paid');
                $value->total = $total;
                return $value;
            });
        } else {
            $payments = payment::join('branches', 'payments.branch_id', '=',  'branches.id')
                ->select('branch_id as id')
                ->where('branch_name', 'like', '%' . $data['txt_search'] . '%')
                ->where('tl_id', $data['tl_id'])
                ->groupBy('branch_id')
                ->orderBy('payments.created_at', 'desc')
                ->paginate(10);
            $payments->getCollection()->transform(function ($value) use ($data){
                //store name
                $name = branch::select('branch_name')
                ->where('id', $value->id)
                ->first();
                $value->branch_name = $name->branch_name;
                //bs
                $payments = payment::where('is_tl', '0')
                ->where('payment_period_id', $data['id'])
                ->where('branch_id', $value->id)
                ->count();
                $value->bsPaid = $payments;
                //net pay
                $total = payment::where('branch_id', $value->id)->where('payments.payment_period_id', $data['id'])->sum('total_paid');
                $value->total = $total;
                return $value;
            });
        }
        if ($payments->isNotEmpty()) {
            $response = ["status" => "success", "data" => $payments->toArray()];
            return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        } else {
            $response = ["status" => "error", "data" => 'No data found'];
            return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        }
    }

    public function agentsdetail(Request $request) {
        $v = validator($request->only('pp_id', 'tl_id', 'store_id', 'show_all'), [
            'pp_id' => 'required',
            'tl_id' => 'required',
            'store_id' => 'required',
            'show_all' => 'required'
        ]);

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }

        $data = request()->only('pp_id', 'tl_id', 'store_id', 'show_all');
        if($data['show_all'] == true) {
            $payments = payment::join('agents', 'payments.agent_id', '=', 'agents.id')
                ->select('agents.id', 'agents.first_name', 'agents.last_name', 'agents.title', 'payments.created_at', 'payments.total_paid')
                ->where('payments.payment_period_id', $data['pp_id'])
                ->where('payments.tl_id', $data['tl_id'])
                ->where('payments.branch_id', $data['store_id'])
                ->get();

            $payments->map(function($value) use ($data) {
                //is TL
                $isTL = payment::where('agent_id', $data['tl_id'])
                    ->where('agent_id', $value->id)
                    ->count();
                $value->isTL = $isTL;
                return $value;
            });
        } else {
            $payments = payment::join('agents', 'payments.agent_id', '=', 'agents.id')
                ->select('agents.id', 'agents.first_name', 'agents.last_name', 'agents.title', 'payments.created_at', 'payments.total_paid')
                ->where('payments.payment_period_id', $data['pp_id'])
                ->where('payments.tl_id', $data['tl_id'])
                ->where('payments.branch_id', $data['store_id'])
                ->paginate(10);

            $payments->getCollection()->transform(function ($value) use ($data){
                //is TL
                $isTL = payment::where('agent_id', $data['tl_id'])
                    ->where('agent_id', $value->id)
                    ->count();
                $value->isTL = $isTL;
                return $value;
            });
        }

        if ($payments->isNotEmpty()) {
            $response = ["status" => "success", "data" => $payments->toArray()];
            return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        } else {
            $response = ["status" => "error", "data" => 'No data found'];
            return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        }
    }

    
    public function agentssearch(Request $request) {
        $v = validator($request->only('pp_id', 'tl_id', 'store_id', 'txt_search', 'show_all'), [
            'pp_id' => 'required',
            'tl_id' => 'required',
            'store_id' => 'required',
            'txt_search' => 'required',
            'show_all' => 'required'
        ]);

        if ($v->fails()) {
            return response()->json($v->errors()->all(), 400);
        }

        $data = request()->only('pp_id', 'tl_id', 'store_id', 'txt_search', 'show_all');
        if($data['show_all'] == true) {
            $payments = payment::join('agents', 'payments.agent_id', '=', 'agents.id')
                ->select('agents.id', 'agents.first_name', 'agents.last_name', 'agents.title', 'payments.created_at', 'payments.total_paid')
                ->where('payments.payment_period_id', $data['pp_id'])
                ->where('payments.tl_id', $data['tl_id'])
                ->where('payments.branch_id', $data['store_id'])
                ->where('last_name', 'like', '%' . $data['txt_search'] . '%')
                ->get();

            $payments->map(function($value) use ($data) {
                //is TL
                $isTL = payment::where('agent_id', $data['tl_id'])
                    ->where('agent_id', $value->id)
                    ->count();
                $value->isTL = $isTL;
                return $value;
            });
        } else {
            $payments = payment::join('agents', 'payments.agent_id', '=', 'agents.id')
                ->select('agents.id', 'agents.first_name', 'agents.last_name', 'agents.title', 'payments.created_at', 'payments.total_paid')
                ->where('payments.payment_period_id', $data['pp_id'])
                ->where('payments.tl_id', $data['tl_id'])
                ->where('payments.branch_id', $data['store_id'])
                ->where('last_name', 'like', '%' . $data['txt_search'] . '%')
                ->paginate(10);

            $payments->getCollection()->transform(function ($value) use ($data){
                //is TL
                $isTL = payment::where('agent_id', $data['tl_id'])
                    ->where('agent_id', $value->id)
                    ->count();
                $value->isTL = $isTL;
                return $value;
            });
        }
        

        if ($payments->isNotEmpty()) {
            $response = ["status" => "success", "data" => $payments->toArray()];
            return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        } else {
            $response = ["status" => "error", "data" => 'No data found'];
            return response(json_encode($response), 200, ["Content-Type" => "application/json"]);
        }
    }
}
