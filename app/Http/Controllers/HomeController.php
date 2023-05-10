<?php

namespace App\Http\Controllers;

use App\Models\CompanyQuestioner;
use App\Models\CompanyScanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function checkingQuestioner()
    {
        $company_id = $this->jwt->user()->id;
        $check = CompanyQuestioner::where('company_id', $company_id)->first();

        if ($check) {
            $status = 200;
            $message = 'Questioner Done';
            $payload =  true;
        } else {
            $status = 200;
            $message = 'Questioner Input';
            $payload = false;
        }

        return response()->json(compact('status', 'message', 'payload'));
    }

    public function getChart(Request $request)
    {
        $day = $request->input('day');
        $company_id = $this->jwt->user()->id;

        $data = CompanyScanner::select(DB::raw("DATE_FORMAT(CONVERT_TZ(created_at, '+00:00', '+07:00'), '%H %p') as time, COUNT(*) as total"))
            ->whereDate('created_at', $day)
            ->where('company_id', $company_id)
            ->groupBy(DB::raw("DATE_FORMAT(CONVERT_TZ(created_at, '+00:00', '+07:00'), '%H %p')"))
            ->get();

        $response = [
            'status' => 200,
            'message' => 'Successfully show data',
            'payload' =>
            $data->map(function ($item) {
                return ['x' => $item->time, 'y' => $item->total];
            })
        ];

        return response()->json($response);
    }
}
