<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\JWTAuth;

class PinController extends Controller
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

    public function setUpPin(Request $request)
    {
        $company_id = $this->jwt->user()->id;
        $newPin = $request->newPin;

        // Validasi pin harus terdiri dari 6 karakter angka
        if (!is_numeric($newPin) || strlen($newPin) != 6) {
            return response()->json([
                'status' => 400,
                'message' => 'Invalid PIN format. PIN must be a 6-digit number',
                'payload' => null
            ]);
        }

        // Ambil data perusahaan dari database
        $data = Company::where('id', $company_id)->first();

        // Update PIN di database
        $data->pin = Hash::make($newPin);
        $data->save();

        // Kembalikan response JSON dengan pesan sukses
        return response()->json([
            'status' => 200,
            'message' => 'PIN successfully setup',
        ]);
    }

    public function checkPin(Request $request)
    {
        $company_id = $this->jwt->user()->id;
        $pin = $request->pin;

        // Ambil data perusahaan dari database
        $data = Company::where('id', $company_id)->first();

        // Periksa apakah PIN benar
        if (Hash::check($pin, $data->pin)) {
            return response()->json([
                'status' => 200,
                'message' => 'Correct PIN',
                'payload' => null
            ]);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'Wrong PIN',
                'payload' => null
            ]);
        }
    }


    public function deletePin()
    {
        $company_id = $this->jwt->user()->id;
        $query = DB::table('company')
            ->where('id', '=', $company_id)
            ->update(['pin' => null]);

        return response()->json([
            'status' => 200,
            'message' => 'Delete PIN',
            'payload' => null
        ]);
    }
}
