<?php

namespace App;

use App\Http\Requests\Request;
use Illuminate\Database\Eloquent\Model;
use Ixudra\Curl\Facades\Curl;
use SimpleXMLElement;
use SoapClient;

class Api extends Model
{
    public static function makeConnect()
    {
        $address = 'https://fly.rocketroute.com/remote/auth';

        $req = '<?xml version="1.0" encoding="UTF-8" ?>
            <AUTH>
                <USR>' . env('API_USERNAME') . '</USR>
                <PASSWD>' . md5(env('API_PASSWORD')) . '</PASSWD>
                <DEVICEID>' . env('API_DEVICE_ID') . '</DEVICEID>
                <PCATEGORY>RocketRoute</PCATEGORY>
                <APPMD5>' . env('API_KEY') . '</APPMD5>
            </AUTH>';

        $response = Curl::to($address)
            ->withData(array('req' => $req))
            ->withOption('SSL_VERIFYPEER', false)
            ->post();

        $result = new SimpleXMLElement($response);

        return $result;

    }

    public static function getNotamCodes ($request)
    {
        $code_array = [
            'EGLL',
            'EGGW',
            'EGLF',
            'EGHI',
            'EGKA',
            'EGMD',
            'EGMC'
        ];

        $client = new SoapClient('https://apidev.rocketroute.com/notam/v1/service.wsdl');

        $codes = '';
        $result_array = array();


        if ($request)
        {

            $req = '<?xml version="1.0" encoding="UTF-8" ?>
            <REQNOTAM>
                <USR>' . env('API_USERNAME') . '</USR>
                <PASSWD>' . md5(env('API_PASSWORD')) . '</PASSWD>
                <ICAO>' . $request . '</ICAO>
            </REQNOTAM>';

            $response = $client->getNotam($req);
            $result = new SimpleXMLElement($response);
            $result_array[] = $result;

            return $result_array;

        } else {

            foreach ($code_array as $code) {
                $req = '<?xml version="1.0" encoding="UTF-8" ?>
                <REQNOTAM>
                    <USR>' . env('API_USERNAME') . '</USR>
                    <PASSWD>' . md5(env('API_PASSWORD')) . '</PASSWD>
                    <ICAO>' . $code . '</ICAO>
                </REQNOTAM>';

                $response = $client->getNotam($req);
                $result = new SimpleXMLElement($response);

                $result_array[] = $result;
            }

            return $result_array;
        }
    }


    public static function DMS2Decimal($degrees = 0, $minutes = 0, $seconds = 0, $direction = 'n') {
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
