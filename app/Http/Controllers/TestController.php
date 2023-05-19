<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        // Menangkap tanggal dari request
        $date = $request->date;

        // Mencari data terakhir
        $lastEntry = DB::table('events_date')->orderBy('created_at', 'desc')->first();

        if (!empty($date)) {
            // Memeriksa apakah sudah ada data dalam tabel
            if ($lastEntry) {
                // Jika ada, memperbarui entri terakhir
                DB::table('events_date')
                    ->where('id', $lastEntry->id)
                    ->update(['date' => $date]);
            } else {
                // Jika tidak ada, memasukkan data baru
                DB::table('events_date')->insert(['date' => $date]);
            }

            $message = 'Date has been updated/inserted';
            $payloadDate = $date;
        } else {
            // Jika request->date kosong, menampilkan data terakhir
            $message = 'Showing last date';
            $payloadDate = $lastEntry ? $lastEntry->date : null;
        }

        return response()->json([
            'status' => 200,
            'message' => $message,
            'payload' => [
                'date' => $payloadDate
            ]
        ]);
    }
}
