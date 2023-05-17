<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

    public function index(Request $request)
    {
        return response()->json([
            'status' => 200,
            'message' => 'show date events',
            'payload' => [
                'date' => $request->date
            ]
        ]);
    }
}
