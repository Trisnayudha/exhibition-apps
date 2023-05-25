<?php

namespace App\Http\Controllers;

use App\Models\CompanyScanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
// use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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
            ->select('users.id', 'users.name', 'users.email', 'users.job_title', 'users.company_name', 'users.image_users')
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

    public function requestExport(Request $request)
    {

        $this->validate($request, [
            'email' => 'required|email|exists:company,email',
        ]);
        $day = $request->day; //2023-05-14
        $email = $request->email;
        $company_id = $this->jwt->user()->id;

        // Buat query dasar
        $query = CompanyScanner::join('users', 'users.id', 'company_scan.users_id')
            ->where('company_scan.company_id', $company_id)
            ->select('company_scan.created_at', 'users.name', 'users.job_title', 'users.company_name', 'users.email', 'users.phone');

        // Tambahkan kondisi where jika $day tidak kosong
        $query->when(!empty($day), function ($query) use ($day) {
            $query->whereRaw('DATE(company_scan.created_at) = ?', [$day]);
        });

        // Ambil data dari database
        $data = $query->get();


        // Buat file Excel baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Tambahkan header
        $sheet->setCellValue('A1', 'Time Scan');
        $sheet->setCellValue('B1', 'Name');
        $sheet->setCellValue('C1', 'Job Title');
        $sheet->setCellValue('D1', 'Company Name');
        $sheet->setCellValue('E1', 'Email');
        $sheet->setCellValue('F1', 'Phone Number');

        // Tambahkan data
        $row = 2;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item->created_at);
            $sheet->setCellValue('B' . $row, $item->name);
            $sheet->setCellValue('C' . $row, $item->job_title);
            $sheet->setCellValue('D' . $row, $item->company_name);
            $sheet->setCellValue('E' . $row, $item->email);
            $sheet->setCellValue('F' . $row, $item->phone);
            $row++;
        }

        // Simpan file Excel ke dalam buffer
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        // Kirim file Excel sebagai lampiran email
        $filename = 'data_' . ($day ? date('Ymd', strtotime($day)) : 'all') . '.xlsx'; // tambahkan tanggal pada nama file jika $day tidak kosong
        $attachment = [
            'data' => $content,
            'filename' => $filename,
            'options' => ['mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        ];
        $send = Mail::send('emails.report', [], function ($message) use ($attachment, $email) {
            $message->to($email)
                ->subject('Data Pemindaian')
                ->attachData($attachment['data'], $attachment['filename'], $attachment['options']);
        });

        if (count(Mail::failures()) > 0) {
            return response()->json(['status' => 500, 'message' => 'Failed to send Excel file', 'payload' => Mail::failures()]);
        } else {
            return response()->json(['status' => 200, 'message' => 'Successfully create and send Excel file', 'payload' => $send]);
        }
    }

    public function getPie(Request $request)
    {
        try {
            $company_id = $this->jwt->user()->id;
            $category = $request->category ?? 'company_name';
            $data = DB::table('company_scan')
                ->join('users', 'company_scan.users_id', '=', 'users.id')
                ->select(DB::raw("users.$category as company, COUNT(*) as total"))
                ->where('company_scan.company_id', $company_id)
                ->groupBy('users.' . $category)
                ->get();

            $response = [
                'status' => 200,
                'message' => 'Successfully show data',
                'payload' => $data->map(function ($item) {
                    return ['x' => $item->total, 'y' => $item->company];
                })
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            $errorResponse = [
                'status' => 500,
                'message' => 'Error retrieving data',
                'error' => $e->getMessage()
            ];

            return response()->json($errorResponse, 500);
        }
    }
}
