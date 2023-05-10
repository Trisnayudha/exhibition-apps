<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth as FacadesJWTAuth;
use Tymon\JWTAuth\JWTAuth;

class ProfileController extends Controller
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

    public function index()
    {
        $company_id = $this->jwt->user()->id;

        $data = Company::where('id', $company_id)
            ->select('name', 'company_name', 'job_title', 'image', 'image_cropping as users_image', 'email')
            ->first();

        // Ubah email menjadi bintang-bintang pada setiap karakter di depan dan di belakang simbol '@'
        $hiddenEmail = preg_replace('/(?<=.).(?=[^@]*?.@)|(?<=@.).(?=.*?.)/', '*', $data->email);
        $data->email_secret = $hiddenEmail;

        $response = [
            'status' => 200,
            'message' => 'successfully show data',
            'payload' => $data ? $data : [],
        ];

        return response()->json($response);
    }

    public function faq()
    {
        $faq = [
            [
                'id' => 1,
                'question' => 'What is your return policy?',
                'answer' => 'Our return policy is...',
            ],
            [
                'id' => 2,
                'question' => 'How do I track my order?',
                'answer' => 'You can track your order by...',
            ],
            [
                'id' => 3,
                'question' => 'Can I cancel my order?',
                'answer' => 'Yes, you can cancel your order by...',
            ]
        ];

        $response = [
            'status' => 200,
            'message' => 'Successfully show FAQ data',
            'payload' => $faq,
        ];
        return response()->json($response);
    }

    public function logout()
    {
        // Ambil user_id dari token JWT
        $user_id = FacadesJWTAuth::parseToken()->authenticate()->id;

        // Invalidasi token JWT untuk user_id yang bersangkutan
        FacadesJWTAuth::invalidate(FacadesJWTAuth::getToken(), $user_id);

        // Kembalikan response JSON dengan pesan logout sukses
        return response()->json([
            'status' => 200,
            'message' => 'Logout successful',
            'payload' => []
        ]);
    }

    public function editPin(Request $request)
    {
        $company_id = $this->jwt->user()->id;
        $oldPin = $request->oldPin;
        $newPin = $request->newPin;

        // Validasi panjang PIN
        if (strlen($newPin) !== 6) {
            return response()->json([
                'status' => 400,
                'message' => 'PIN must be 6 characters long',
            ]);
        }


        // Ambil data perusahaan dari database
        $data = Company::where('id', $company_id)->first();

        // Periksa apakah PIN lama benar
        if (!Hash::check($oldPin, $data->pin)) {
            return response()->json([
                'status' => 400,
                'message' => 'Wrong old PIN',
            ]);
        }

        // Update PIN di database
        $data->pin = Hash::make($newPin);
        $data->save();

        // Kembalikan response JSON dengan pesan sukses
        return response()->json([
            'status' => 200,
            'message' => 'PIN successfully updated',
        ]);
    }
}
