<?php

// fake $app, $log so that Netbeans can provide suggestions while typing code
if (false) {
    $app = new \Slim\Slim();
    $log = new Logger('main');
}
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = array();
}
// add property
$app->get('/property/:op(/:id)', function($op, $id = -1) use ($app) {
    if (!$_SESSION['user']) {
        $app->render('access_denied.html.twig');
        return;
    }
    if (($op == 'add' && $id != -1) || ($op == 'edit' && $id == -1)) {
        echo "INVALID REQUEST"; // FIXME on Monday - display standard 404 from slim
        return;
    }
    //
    if ($id != -1) {
        $values = DB::queryFirstRow('SELECT * FROM property WHERE propertyId=%i', $id);
        if (!$values) {
            echo "NOT FOUND";  // FIXME on Monday - display standard 404 from slim
            return;
        }
    } else { // nothing to load from database - adding
        $values = array();
    }
    $app->render('/property/property_addedit.html.twig', array(
        'v' => $values,
        'isEditing' => ($id != -1)
    ));
})->conditions(array(
    'op' => '(edit|add)',
    'id' => '\d+'
));

$app->post('/property/:op(/:id)', function($op, $id = -1) use ($app) {
    if (!$_SESSION['user']) {
        $app->render('access_denied.html.twig');
        return;
    }
    if (($op == 'add' && $id != -1) || ($op == 'edit' && $id == -1)) {
        echo "INVALID REQUEST"; // FIXME on Monday - display standard 404 from slim
        return;
    }
    $propertyType = $app->request()->post('propertyType');
    $latitude = $app->request()->post('latitude');
    $longitude = $app->request()->post('longitude');
    $beds = $app->request()->post('beds');
    $baths = $app->request()->post('baths');
    $price = $app->request()->post('price');
    $squreFeet = $app->request()->post('squreFeet');
    //
    $values = array('propertyType' => $propertyType, 'latitude' => $latitude, 'longitude' => $longitude, 'beds' => $beds,
        'baths' => $baths, 'price' => $price, 'squreFeet' => $squreFeet);

    $errorList = array();
    //
    //validate Latitude
    if ($latitude == '' || $latitude < -90 || $latitude > 90) {
        array_push($errorList, "Latitude must be between -90 and 90.");
        $values['latitude'] = "";
    }
    //validate Longitude
    if ($longitude == '' || $longitude < -180.0000 || $longitude > 180.0000) {
        array_push($errorList, "Longitude must be between -180 and 180.");
        $values['longitude'] = "";
    }
    // validate price
    if (empty($price) || $price < 0 || $price > 99999999.99) {
        $values['price'] = '';
        array_push($errorList, "Price must be between 0 and 99999999.99");
    }
    // validate squreFeet
    if (empty($squreFeet) || $squreFeet < 0 || $squreFeet > 99999999.99) {
        $values['squreFeet'] = '';
        array_push($errorList, "SqureFeet must be between 1 and 99999999.99");
    }
    // validate baths
    if (empty($baths) || $baths < 0 || $baths > 10) {
        $values['baths'] = '';
        array_push($errorList, "The number of baths must be between 1 and 10");
    }
    // validate beds
    if (empty($beds) || $beds < 0 || $beds > 10) {
        $values['beds'] = '';
        array_push($errorList, "The number of beds must be between 1 and 10");
    }
    //validate image
    $propertyImage = array();

    //

    if ($errorList) { // 3. failed submission
    } else { // 2. successful submission
        $values['userId'] = $_SESSION['user']['userId'];
        if ($id != -1) {
            DB::update('property', $values, "propertyId=%i", $id);
        } else {
            DB::insert('property', $values);
        }

        $app->render('/property/property_addedit_success.html.twig', array('isEditing' => ($id != -1)));
    }
})->conditions(array(
    'op' => '(edit|add)',
    'id' => '\d+'
));

// Veiw of list of property 
$app->get('/property/list', function() use ($app) {
    if (!$_SESSION['user']) {
        $app->render("access_denied.html.twig");
        return;
    }
    $userId = $_SESSION['user']['userId'];
    $propertyList = DB::query("SELECT * FROM property WHERE userId =%i", $userId);
    $app->render("/property/property_list.html.twig", array('list' => $propertyList));
});

// Delete Property 
$app->get('/property/delete/:id', function($id) use ($app) {
    if (!$_SESSION['user']) {
        $app->render("access_denied.html.twig");
        return;
    }
    $property = DB::queryFirstRow("SELECT * FROM property WHERE propertyId=%d AND userId=%i", $id, $_SESSION['user']['userId']);
    if (!$property) {
        $app->render("/not_found.html.twig");
        return;
    }
    $app->render("/property/property_delete.html.twig", array('p' => $property));
});

$app->post('/property/delete/:id', function($id) use ($app) {
    if (!$_SESSION['user']) {
        $app->render("access_denied.html.twig");
        return;
    }
    $confirmed = $app->request()->post('confirmed');
    if ($confirmed != 'true') {
        $app->render('/not_found.html.twig');
        return;
    }
    DB::delete('property', "propertyId=%i AND userId=%i", $id, $_SESSION['user']['userId']);
    if (DB::affectedRows() == 0) {
        $app->render('/not_found.html.twig');
    } else {
        $app->render('/property/property_delete_success.html.twig');
    }
});
// google maps for all property

$app->get('/property/googlemaps', function() use ($app) {
    if (!$_SESSION['user']) {
        $app->render("access_denied.html.twig");
        return;
    }
    $userId = $_SESSION['user']['userId'];
    $propertyList = DB::query("SELECT * FROM property WHERE userId =%i", $userId);
    if (!$propertyList) {
        $app->render("/not_found.html.twig");
        return;
    }
    $app->render("/property/google_maps_list.html.twig", array('list' => $propertyList));
});


// google maps for one property

$app->get('/property/googlemap/:id', function($id) use ($app) {
    if (!$_SESSION['user']) {
        $app->render("access_denied.html.twig");
        return;
    }

    $property = DB::queryFirstRow("SELECT * FROM property WHERE propertyId=%d AND userId=%i", $id, $_SESSION['user']['userId']);
    if (!$property) {
        $app->render("/not_found.html.twig");
        return;
    }
    $app->render("/property/google_maps.html.twig", array('property' => $property));
});


// Search Property

$app->get('/property/search', function() use ($app, $log) {
//    if (!$_SESSION['user']) {
//        $app->render("access_denied.html.twig");
//        return;
//    }
    $app->render('/property/search_property.html.twig');
});

$app->post('/property/search', function() use ($app, $log) {
//    if (!$_SESSION['user']) {
//        $app->render("access_denied.html.twig");
//        return;
//    }
    $latA = $app->request()->post('latA');
    $latB = $app->request()->post('latB');
    $longA = $app->request()->post('longA');
    $longB = $app->request()->post('longB');

    $values = array('latA' => $latA, 'latB' => $latB, 'longA' => $longA, 'longB' => $longB);
//    if (isset($_GET['location'])) {
//        // Checkbox is selected
//        $values = DB::query('SELECT * from property WHERE latitude LIKE %ss AND longitude LIKE %ss', $search, $search);
//    }
////    if (isset($_GET['price'])) {
////        // Checkbox is selected
////        $values = DB::query('SELECT * from property WHERE price LIKE %ss', $search);
////    }
       $values = DB::query('SELECT * from property WHERE latitude BETWEEN %ss AND longitude LIKE %ss', $search, $search);
    $app->render('/property/search_property.html.twig', array('v' => $values));
});

// calcul distance between 2 latitude and 2 longitude
function haversineGreatCircleDistance(
$latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000) {
    // convert from degrees to radians
    $latFrom = deg2rad($latitudeFrom);
    $lonFrom = deg2rad($longitudeFrom);
    $latTo = deg2rad($latitudeTo);
    $lonTo = deg2rad($longitudeTo);

    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;

    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    return $angle * $earthRadius;
}
