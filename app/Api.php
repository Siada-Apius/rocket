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
}
