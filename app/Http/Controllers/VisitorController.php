<?php

namespace App\Http\Controllers;

use App\Models\CompanyScanner;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;

class VisitorController extends Controller
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

    public function index(Request $request)
    {
        $search = $request->search;
        $filter = $request->filter;
        $company_id = $this->jwt->user()->id;
        $perPage = $request->input('per_page', 10); // Default per page is 10, can be changed

        $data = CompanyScanner::join('users', 'users.id', 'company_scan.users_id')
            ->select('users.id', 'users.name', 'users.email', 'users.job_title', 'users.company_name')
            ->where(function ($query) use ($search) {
                $query->where('users.name', 'LIKE', '%' . $search . '%')
                    ->orWhere('users.company_name', 'LIKE', '%' . $search . '%');
            })
            ->where('company_scan.company_id', $company_id);

        if (!empty($filter)) {
            $data = $data->orderby('company_scan.id', $filter);
        } else {
            $data = $data->orderby('company_scan.id', 'desc');
        }

        $data = $data->paginate($perPage);

        // Mask emails
        $data->getCollection()->transform(function ($item) {
            $item->email = $this->maskEmail($item->email);
            return $item;
        });

        return $data
            ? response()->json(['status' => 200, 'message' => 'successfully show data', 'payload' => $data])
            : response()->json(['status' => 404, 'message' => 'Data not found', 'payload' => []], 404);
    }

    private function maskEmail($email)
    {
        $emailParts = explode('@', $email);
        $emailName = $emailParts[0];
        $emailDomain = $emailParts[1];
        $maskedName = substr($emailName, 0, 2) . str_repeat('*', strlen($emailName) - 2);

        return $maskedName . '@' . $emailDomain;
    }
}
