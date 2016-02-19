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

        $coords = array();
        $i = 0;

        foreach ($result as $notams) {
            if ($notams->RESULT == 0) {
                foreach ($notams->NOTAMSET->NOTAM as $key => $notam) {
                    $string = substr($notam->ItemQ, strrpos($notam->ItemQ, '/') + 1);

                    $letter = preg_replace('#[0-9^]#', '', $string);

                    $first = substr($letter, 0, 1);
                    $second = substr($letter, 1, 2);

                    $data = explode($first, $string);
                    $dir1 = $data[0];
                    $dir2 = preg_replace('#[A-Z]#', '', $data[1]);

                    $lat = Api::DMS2Decimal(substr($dir1, 0, 2), substr($dir1, 2, 4), 0, $first);
                    $lon = Api::DMS2Decimal(substr($dir2, 0, 2), substr($dir2, 2, 4), 0, $second);

                    if ( ! empty($lat) && !empty($lon)) {
                        $coords[] = "new google.maps.LatLng ({$lat},{$lon})";
                        $jsonDocs[$i]["lat"] = $lat;
                        $jsonDocs[$i]["lon"] = $lon;
                        $jsonDocs[$i]["text"] = $notam->ItemE;


                        $i++;
                    }
                }
            }
        }

        $jsonDocs = json_encode($jsonDocs);

        return view('welcome', compact('result', 'connection', 'coords', 'jsonDocs'));
    }


}