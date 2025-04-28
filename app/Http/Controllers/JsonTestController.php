<?php

namespace App\Http\Controllers;

use App\Models\Users;
use App\Models\User;
use Illuminate\Http\Request;

class JsonTestController extends Controller
{
    public function calendar(){
        return view('content.calendar');
    }
    public function Check(){
        $data = Users::get();
        return response()->json([
            'nama' => $data
        ]);
    }

    public function Store(Request $request){
        // $data = Pengguna::Where('id');
        $data = Users::get('name');

        return response()->json([
            'nama' => $data
        ]);
    }
}