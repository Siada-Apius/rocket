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

                    $lat = $this->DMS2Decimal(substr($dir1, 0, 2), substr($dir1, 2, 4), 0, $first);
                    $lon = $this->DMS2Decimal(substr($dir2, 0, 2), substr($dir2, 2, 4), 0, $second);

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

    function DMS2Decimal($degrees = 0, $minutes = 0, $seconds = 0, $direction = 'n') {
        //converts DMS coordinates to decimal
        //returns false on bad inputs, decimal on success

        //direction must be n, s, e or w, case-insensitive
        $d = strtolower($direction);
        $ok = array('n', 's', 'e', 'w');

        //degrees must be integer between 0 and 180
        if(!is_numeric($degrees) || $degrees < 0 || $degrees > 180) {
            $decimal = false;
        }
        //minutes must be integer or float between 0 and 59
        elseif(!is_numeric($minutes) || $minutes < 0 || $minutes > 59) {
            $decimal = false;
        }
        //seconds must be integer or float between 0 and 59
        elseif(!is_numeric($seconds) || $seconds < 0 || $seconds > 59) {
            $decimal = false;
        }
        elseif(!in_array($d, $ok)) {
            $decimal = false;
        }
        else {
            //inputs clean, calculate
            $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);

            //reverse for south or west coordinates; north is assumed
            if($d == 's' || $d == 'w') {
                $decimal *= -1;
            }
        }

        return $decimal;
    }

}