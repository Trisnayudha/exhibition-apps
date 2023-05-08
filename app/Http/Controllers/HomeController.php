<?php

namespace App\Http\Controllers;

use App\Models\CompanyScanner;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;

class HomeController extends Controller
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

    public function countVisitor(Request $request)
    {
        $day = $request->input('day');
        $company_id = $this->jwt->user()->id;

        $query = CompanyScanner::query();
        $query->where('company_id', $company_id);

        if (!empty($day)) {
            $query->whereDate('created_at', $day);
        }

        $data = $query->count();

        $response = [
            'status' => 200,
            'message' => 'successfully show data',
            'payload' => $data ? $data : 0,
        ];

        return response()->json($response);
    }


    public function homeBanner()
    {
        $data = [
            ['image' => 'https://indonesiaminer.com/new-home/logo/1.png'],
            ['image' => 'https://indonesiaminer.com/new-home/logo/2.png'],
            ['image' => 'https://indonesiaminer.com/new-home/logo/3.png'],
        ];
        $response = [
            'status' => 200,
            'message' => 'Successfully show data',
            'payload' => $data
        ];
        return response()->json($response);
    }
}
