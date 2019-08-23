<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['middleware' => 'auth:api'], function(){
	
	Route::get('branch','api\BranchController@index');
	Route::get('branch/{id}','api\BranchController@view');
	Route::get('branchName','api\BranchController@branchName');
	Route::post('branch/new','api\BranchController@create');
	Route::post('branch/update','api\BranchController@update');
	Route::post('branch/search','api\BranchController@search');
	Route::post('branch/branchNameAgent','api\BranchController@branchNameAgent');
	Route::post('branch/suggestBranch','api\BranchController@suggestBranch');
	Route::post('branch/suggestBranchExclusiveToPayment','api\BranchController@suggestBranch');

	Route::get('agent','api\AgentController@index');
	Route::get('agent/{id}','api\AgentController@view');
	Route::post('agent/new','api\AgentController@create');
	Route::post('agent/search','api\AgentController@search');
	Route::post('agent/status','api\AgentController@status');
	Route::post('agent/update','api\AgentController@update');
	Route::post('agent/name','api\AgentController@name');
	Route::post('agent/findName','api\AgentController@findName');
	Route::post('agent/nameBranch','api\AgentController@nameWithBranch');
	Route::post('agent/nameBranchPayment','api\AgentController@nameWithBranchPayment');
	Route::post('agent/promote','api\AgentController@promote');
	Route::get('teamlead','api\AgentController@teamlead');
	Route::post('tl/search','api\AgentController@tlsearch');
	Route::post('tl/branches','api\AgentController@tlbranches');

	Route::get('deductions','api\DeductionsController@index');
	Route::post('deductions/new','api\DeductionsController@create');

	Route::get('savings/all','api\SavingsController@showAgentCA');
	Route::get('savings/{id}','api\SavingsController@index');
	Route::post('savings/search','api\SavingsController@search');
	Route::post('savings/search/savings','api\SavingsController@searchSavings');
	Route::post('savings/new','api\SavingsController@create');
	Route::get('savings/ca/{id}','api\SavingsController@cashAdvance');
	Route::get('savings/ca/all/{id}','api\SavingsController@cashAdvanceAll');

	Route::get('paymentperiod/period','api\PaymentPeriodController@period');
	Route::get('paymentperiod','api\PaymentPeriodController@index');
	Route::post('paymentperiod/new','api\PaymentPeriodController@create');
	Route::post('paymentperiod/search','api\PaymentPeriodController@search');
	Route::get('paymentperiod/agents/{id}','api\PaymentPeriodController@paidAgents');
	Route::get('paymentperiod/payment/{id}','api\PaymentPeriodController@agentPayment');
	Route::post('paymentperiod/delete','api\PaymentPeriodController@delete');
	Route::post('paymentperiod/complete','api\PaymentPeriodController@complete');

	Route::get('announcement','api\AnnounceController@index');
	Route::post('announcement/new','api\AnnounceController@create');
	Route::post('announcement/delete','api\AnnounceController@delete');

	Route::post('payment/new','api\PaymentController@create');
	Route::post('payment/delete','api\PaymentController@delete');
	Route::post('payment/adjustment','api\PaymentController@adjust');
	Route::get('payment/mypayments/{id}','api\PaymentController@mypayments');

	Route::post('encoder/new','api\UsersController@create');

	Route::post('reports','api\PaymentController@viewreports');
	Route::post('reports/search','api\PaymentController@searchreports');
	Route::post('reports/ppdetail','api\PaymentController@ppdetailreports');
	Route::post('reports/ppdetail/search','api\PaymentController@ppdetailsearch');
	Route::post('reports/store','api\PaymentController@tldetail');
	Route::post('reports/store/search','api\PaymentController@tlsearch');
	Route::post('reports/agents','api\PaymentController@agentsdetail');
	Route::post('reports/agents/search','api\PaymentController@agentssearch');
	Route::post('reports/search/pp','api\DeductionsController@searchPP');
	Route::post('reports/search/store','api\DeductionsController@searchStore');
	Route::post('reports/search/agents','api\DeductionsController@searchAgents');

	Route::get('cards/{id}','api\AnnounceController@card');

	Route::post('user/pass','api\UsersController@changepass');
	Route::get('user/logout','api\UsersController@logoutApi');
	Route::get('/user', function (Request $request) {
	    return $request->user();
	});
});