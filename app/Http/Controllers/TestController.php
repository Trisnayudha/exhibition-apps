<?php

namespace App\Http\Controllers;

class TestController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function index()
    {

        return response()->json([
            'status' => 200,
            'message' => 'show date events',
            'payload' => [
                'date' => '2023-05-17'
            ]
        ]);
    }
}
