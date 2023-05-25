<?php

namespace App\Http\Controllers;

use App\Models\CompanyQuestioner;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;

class QuestionerController extends Controller
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
        $questions = [
            [
                'id' => 1,
                'question' => 'What is your return policy?',
                'optional' => 'PG',
            ],
            [
                'id' => 2,
                'question' => 'How do I track my order?',
                'optional' => 'Essay',
            ],
            [
                'id' => 3,
                'question' => 'Can I cancel my order?',
                'optional' => 'PG',
            ],
            // Add more questions here
        ];

        // Generate random optional values for additional questions
        $optionalValues = ['PG', 'Essay'];

        for ($i = 4; $i <= 10; $i++) {
            $randomIndex = array_rand($optionalValues);
            $optional = $optionalValues[$randomIndex];

            $questions[] = [
                'id' => $i,
                'question' => "Question $i",
                'optional' => $optional,
            ];
        }

        $response = [
            'status' => 200,
            'message' => 'Successfully show Question data',
            'payload' => $questions,
        ];

        return response()->json($response);
    }

    public function store(Request $request)
    {
        $company_id = $this->jwt->user()->id;
        $check = CompanyQuestioner::where('company_id', $company_id)->first();
        if (empty($check)) {

            $save = new CompanyQuestioner();
            $save->question_1 = $request->question_1;
            $save->question_2 = $request->question_2;
            $save->question_3 = $request->question_3;
            $save->question_4 = $request->question_4;
            $save->question_5 = $request->question_5;
            $save->question_6 = $request->question_6;
            $save->question_7 = $request->question_7;
            $save->question_8 = $request->question_8;
            $save->question_9 = $request->question_9;
            $save->question_10 = $request->question_10;
            $save->company_id = $company_id;
            $save->save();

            $response = [
                'status' => 200,
                'message' => 'Successfully send Question ',
                'payload' => null,
            ];
        } else {
            $response = [
                'status' => 404,
                'message' => 'Anda sudah mensubmit question ',
                'payload' => null,
            ];
        }

        return response()->json($response);
    }
}
