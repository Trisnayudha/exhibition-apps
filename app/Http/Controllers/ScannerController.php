<?php

namespace App\Http\Controllers;

use App\Models\CompanyScanner;
use App\Models\Payment;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;

class ScannerController extends Controller
{
    protected $jwt;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
        $this->middleware('auth:api');
    }

    public function scan(Request $request)
    {
        $this->validate($request, ['code_payment' => 'required']);

        $code_payment = $request->input('code_payment');
        $find = Payment::where('code_payment', $code_payment)
            ->join('users', 'users.id', 'payment.users_id')
            ->select('users.id', 'users.name', 'users.company_name', 'users.job_title', 'users.image_users')
            ->first();

        return $find
            ? response()->json(['status' => 200, 'message' => 'successfully scan', 'payload' => $find])
            : response()->json(['status' => 404, 'message' => 'Data not found', 'payload' => []], 404);
    }


    public function store(Request $request)
    {
        $this->validate($request, [
            'users_id' => 'required',
        ]);
        $company_id = $this->jwt->user()->id;
        $users_id = $request->input('users_id');

        $save = new CompanyScanner();
        $save->users_id = $users_id;
        $save->company_id = $company_id;
        $save->save();

        $response['status'] = 200;
        $response['message'] = 'successfully scan';
        $response['payload'] = [];
        return response()->json($response);
    }
}
