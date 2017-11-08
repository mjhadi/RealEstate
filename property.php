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
$app->get('/property/add', function() use ($app) {
    if (!$_SESSION['user']) {
        $app->render('access_denied.html.twig');
        return;
    }
    $app->render('/property/property_addedit.html.twig');
});

$app->post('/property/add', function() use ($app) {
    if (!$_SESSION['user']) {
        $app->render('access_denied.html.twig');
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
    $values = array('propertyType' => $propertyType, 'latitude' => $latitude, 'longitude' => $longitude, 'beds' => $beds, 'baths' => $baths, 'price' => $price, 'squreFeet' => $squreFeet);

    $errorList = array();
    //
    //validate Latitude
    if ($latitude == '' || $latitude < -90 || $latitude > 90) {
        array_push($errorList, "Latitude must be between -90 and 90.");
        $values['latitude'] = "";
    }

    //validate Longitude
    if ($longitude == '' || $longitude < -180 || $longitude > 180) {
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
    //
    if ($errorList) { // 3. failed submission
        $app->render('property_addedit.html.twig', array(
            'errorList' => $errorList,
            'v' => $values));
    } else { // 2. successful submission
        $values['ownerId'] = $_SESSION['user']['id'];
        DB::insert('todos', $values);
        $app->render('/property/property_addedit_success.html.twig');
    }
});
