<!DOCTYPE html>
<html>
<head>
    <title>Laravel</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" integrity="sha256-7s5uDGW3AHqw6xtJmNNtr+OBRJUlgkNJEo78P4b0yRw= sha512-nNo+yCHEyn0smMxSswnf/OnX6/KwJuZTlNZBjauKhTK0c+zT+q5JOCx0UFhXQ6rJR9jg6Es8gPuD2uZcYDLqSw==" crossorigin="anonymous">
    <style>
        #googleMap {
            width: 500px;
            height: 400px;
        }
    </style>

</head>
<body>
<div class="container-fluid" style="margin-top: 30px;">
    <div class="row">
        <div class="col-md-4">
            <div class="panel panel-default">
                <table class="table  table-bordered table-responsive">
                    @foreach($connection as $key => $value)
                        <tr>
                            <td>{{ $key }}</td>
                            <td>{{ $value }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
        <div class="col-md-2">
            <div class="row">
                <div class="col-md-12">
                    <form action="{{ url('/') }}" method="POST" class="form-inline">
                        <div class="form-group">
                            <input type="text" name="code" class="form-control">
                        </div>
                        <input type="submit" value="Submit" class="btn btn-primary">
                    </form>
                </div>

                <div class="clearfix"></div>

                <div class="col-md-12">

                    @if (!$result)
                        @if (gettype($result) == 'array')
                            <ul class="list-unstyled">
                                @foreach ($result as $notams)
                                    @if ($notams->RESULT == 0)
                                        @foreach ($notams->NOTAMSET->NOTAM as $notam)
                                            <?php
                                            $string = substr($notam->ItemQ, strrpos($notam->ItemQ, '/') + 1);
                                            $one = substr(substr($string, 0, strpos($string, 'N')), 0, 2) .'°'. substr(substr($string, 0, strpos($string, 'N')), 2, 4) .'\' N ';

                                            if (strstr($string, 'W', true)) {

                                                $substring2 = substr(substr($string, 0, strpos($string, 'W')), strpos(substr($string, 0, strpos($string, 'W')), "N") + 1);
                                                $two = substr(substr(substr($string, 0, strpos($string, 'W')), strpos(substr($string, 0, strpos($string, 'W')), "N") + 1), 0, 2) .'°'. substr(substr(substr($string, 0, strpos($string, 'W')), strpos(substr($string, 0, strpos($string, 'W')), "N") + 1), 2, 4) .'\' W';

                                            } elseif (strstr($string, 'E', true)) {
                                                $substring2 = substr(substr($string, 0, strpos($string, 'E')), strpos(substr($string, 0, strpos($string, 'E')), "N") + 1);
                                                $two = substr(substr(substr($string, 0, strpos($string, 'E')), strpos(substr($string, 0, strpos($string, 'E')), "N") + 1), 0, 2) .'°'. substr(substr(substr($string, 0, strpos($string, 'E')), strpos(substr($string, 0, strpos($string, 'E')), "N") + 1), 2, 4) .'\' E';
                                            }

                                            ?>
                                            <li>{{ $one .' '. $two }}</li>
                                            <li>{{ $string }}</li>
                                            <li>&nbsp;</li>
                                        @endforeach
                                    @endif
                                @endforeach
                            </ul>
                        @else
                            <?php
                            $string = substr($result->NOTAMSET->NOTAM->ItemQ, strrpos($result->NOTAMSET->NOTAM->ItemQ, '/') + 1);
                            $one = substr(substr($string, 0, strpos($string, 'N')), 0, 2) .'°'. substr(substr($string, 0, strpos($string, 'N')), 2, 4) .'\' N ';
                                if (strstr($string, 'W', true)) {

                                    $substring2 = substr(substr($string, 0, strpos($string, 'W')), strpos(substr($string, 0, strpos($string, 'W')), "N") + 1);
                                    $two = substr(substr(substr($string, 0, strpos($string, 'W')), strpos(substr($string, 0, strpos($string, 'W')), "N") + 1), 0, 2) .'°'. substr(substr(substr($string, 0, strpos($string, 'W')), strpos(substr($string, 0, strpos($string, 'W')), "N") + 1), 2, 4) .'\' W';

                                } elseif (strstr($string, 'E', true)) {
                                    $substring2 = substr(substr($string, 0, strpos($string, 'E')), strpos(substr($string, 0, strpos($string, 'E')), "N") + 1);
                                    $two = substr(substr(substr($string, 0, strpos($string, 'E')), strpos(substr($string, 0, strpos($string, 'E')), "N") + 1), 0, 2) .'°'. substr(substr(substr($string, 0, strpos($string, 'E')), strpos(substr($string, 0, strpos($string, 'E')), "N") + 1), 2, 4) .'\' E';
                                }
                            ?>
                            {{ $one .' '. $two }}
                        @endif
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div id="googleMap"></div>
        </div>
    </div>
</div>
</body>
<script src="http://maps.googleapis.com/maps/api/js"></script>
<script>

    var mapOpts = {

        mapTypeId: google.maps.MapTypeId.ROADMAP,
        scrollwheel: true,
        panControl: false,
        zoomControl: false,
        mapTypeControl: false,
        scaleControl: false,
        streetViewControl: false,
        overviewMapControl: false,
        clickable: false
    };

    var LatLngList = new Array ({{implode(",", $coords)}});
    var bounds = new google.maps.LatLngBounds ();
    for (var i = 0, LtLgLen = LatLngList.length; i < LtLgLen; i++) {
        bounds.extend (LatLngList[i]);
    }
    var map = new google.maps.Map(document.getElementById("googleMap"), mapOpts);
    map.fitBounds (bounds);
    $.markersArray = [];

    var data =  {!!$jsonDocs!!};
    initializeMarkers(data);


    function displayLocation(location, step) {


        var content = '<div class="infoWindow">'+ location.text +'</div>';
        var thePoint = new google.maps.LatLng(location.lat, location.lon);
        var pinImage = new google.maps.MarkerImage("http://i.stack.imgur.com/uvFaG.png");

        var marker = new google.maps.Marker({
            map: map,
            position: thePoint,
            icon: pinImage
        });

        if ($.markersArray) $.markersArray.push(marker);
        var infowindow = new google.maps.InfoWindow({

            content: content

        });

        google.maps.event.addListener(marker, 'click', function () {

            infowindow.open(map, marker);

        });

    }

    function initializeMarkers(data) {

        if (data != 0) {

            for (var i = 0; i < data.length; i++) {

                displayLocation(data[i], i);

            }
        }
    }

</script>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha256-KXn5puMvxCw+dAYznun+drMdG1IFl3agK0p/pqT9KAo= sha512-2e8qq0ETcfWRI4HJBzQiA3UoyFk6tbNyG+qSaIBZLyW9Xf3sWZHN/lxe9fTh1U45DpPf07yj94KsUHHWe4Yk1A==" crossorigin="anonymous"></script>

</html>
