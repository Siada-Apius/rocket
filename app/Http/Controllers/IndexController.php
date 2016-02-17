<?php

namespace App\Http\Controllers;

use App\Api;
use App\Http\Requests;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index(Request $request) {

        $result = Api::getNotamCodes($request->code);

        $connection = Api::makeConnect();

        if ($connection->RESULT != 'SUCCESS') {
            $connection = null;
        }

        return view('welcome', compact('result', 'connection'));
    }
}