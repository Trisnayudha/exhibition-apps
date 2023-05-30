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
                'question' => 'Pre-Show Information & Support?',
                'optional' => 'PG',
                'listPG' => ['Very Satisfied', 'Satisfied', 'Dissatisfied']
            ],
            [
                'id' => 2,
                'question' => 'Exhibitor recognition – marketing/promotional materials',
                'optional' => 'PG',
                'listPG' => ['Very Satisfied', 'Satisfied', 'Dissatisfied']
            ],
            [
                'id' => 3,
                'question' => 'How did the event/ exhibition floor layout',
                'optional' => 'PG',
                'listPG' => ['Very Satisfied', 'Satisfied', 'Dissatisfied']
            ],
            [
                'id' => 4,
                'question' => 'Event mobile app',
                'optional' => 'PG',
                'listPG' => ['Very Satisfied', 'Satisfied', 'Dissatisfied']
            ],
            [
                'id' => 5,
                'question' => 'How was your experience with the app?',
                'optional' => 'PG',
                "listPG" => ['I enjoyed it and found it useful', 'I used the app and positive about it', 'I used the app and experienced a couple of issues']
            ],
            [
                'id' => 6,
                'question' => 'Please rate your overall Indonesia Miner Conference &amp; Exhibition 2023
                experience',
                'optional' => 'PG',
                'listPG' => ['Very Satisfied', 'Satisfied', 'Dissatisfied']
            ],
            [
                'id' => 7,
                'question' => 'Please share about your experience at this year’s show or suggestions on how we
                can make the event better for you in the future:',
                'optional' => 'Essay',
                'listPG' => []
            ],
            [
                'id' => 8,
                'question' => 'How likely are you to participate in Indonesia Miner 2024?',
                'optional' => 'PG',
                'listPG' => ['Definitely', 'Most Likely', 'Undecided', 'Will Not Exhibiting']
            ],
            [
                'id' => 9,
                'question' => 'When would be the best time for you to receive the event prospectus/ kit prior to the
                event date?',
                'optional' => 'PG',
                'listPG' => ['3 months', '6 months', '1 year']
            ],
            [
                'id' => 10,
                'question' => 'If you are not planning to exhibit, what is your primary reason?',
                'optional' => 'Essay',
                'listPG' => []
            ],
            // Add more questions here
        ];

        // // Generate random optional values for additional questions
        // $optionalValues = ['PG', 'Essay'];

        // for ($i = 4; $i <= 10; $i++) {
        //     $randomIndex = array_rand($optionalValues);
        //     $optional = $optionalValues[$randomIndex];

        //     $questions[] = [
        //         'id' => $i,
        //         'question' => "Question $i",
        //         'optional' => $optional,
        //     ];
        // }

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
