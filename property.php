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
    //validate image
     $propertyImage = array();
    // is file being uploaded
//    if ($_FILES['propertyImage']['error'] != UPLOAD_ERR_NO_FILE) {
//        $propertyImage = $_FILES['propertyImage'];
//        if ($propertyImage['error'] != 0) {
//            array_push($errorList, "Error uploading file");
//            $log->err("Error uploading file: " . print_r($propertyImage, true));
//        } else {
//            if (strstr($propertyImage['name'], '..')) {
//                array_push($errorList, "Invalid file name");
//                $log->warn("Uploaded file name with .. in it (possible attack): " . print_r($propertyImage, true));
//            }
//            // TODO: check if file already exists, check maximum size of the file, dimensions of the image etc.
//            $info = getimagesize($propertyImage["tmp_name"]);
//            if ($info == FALSE) {
//                array_push($errorList, "File doesn't look like a valid image");
//            } else {
//                if ($info['mime'] == 'image/jpeg' || $info['mime'] == 'image/gif' || $info['mime'] == 'image/png') {
//                    // image type is valid - all good
//                } else {
//                    array_push($errorList, "Image must be a JPG, GIF, or PNG only.");
//                }
//            }
//        }
//    } else { // no file uploaded
//        if ($op == 'add') {
//            array_push($errorList, "Image is required when creating new product");
//        }
//    }
    //
    if ($errorList) { // 3. failed submission
//        if ($propertyImage) {
//            $sanitizedFileName = preg_replace('[^a-zA-Z0-9_\.-]', '_', $propertyImage['name']);
//            $imagePath = 'uploads/' . $sanitizedFileName;
//            if (!move_uploaded_file($propertyImage['tmp_name'], $imagePath)) {
//                $log->err("Error moving uploaded file: " . print_r($propertyImage, true));
//                $app->render('internal_error.html.twig');
//                return;
//            }
//            // TODO: if EDITING and new file is uploaded we should delete the old one in uploads
//            $values['imageId'] = "/" . $imagePath;
//        }
//        
        $app->render('/property/property_addedit.html.twig',  array(
            'errorList' => $errorList,
            'isEditing' => ($id != -1),
            'v' => $values));
    } else { // 2. successful submission
        $values['userId'] = $_SESSION['user']['userId'];
        if ($id != -1) {
            DB::update('property', $values, "propertyId=%i", $id);
        } else {
                DB::insert('property', $values);
        }
  
        $app->render('/property/property_addedit_success.html.twig' , array('isEditing' => ($id != -1)));
    }
})->conditions(array(
    'op' => '(edit|add)',
    'id' => '\d+'
));

// Veiw of list of property 
$app->get('/property/list', function() use ($app) {
    if (!$_SESSION['user'] ) {
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
