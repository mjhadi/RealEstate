<?php

// fake $app, $log so that Netbeans can provide suggestions while typing code
if (false) {
    $app = new \Slim\Slim();
    $log = new Logger('main');
}
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = array();
}

// view list of images 
$app->get('/photo/list', function() use ($app) {
    if (!$_SESSION['user']) {
        $app->render("access_denied.html.twig");
        return;
    }
    $imagesList = DB::query("SELECT * FROM images");
    $app->render("/photo/photo_list.html.twig", array('list' => $imagesList));
});
//add /edit photo 
$app->get('/photo/:op(/:id)', function($op, $id =-1) use ($app) {
    if (!$_SESSION['user']) {
        $app->render('access_denied.html.twig');
        return;
    }
    if (($op == 'add' && $id != -1) || ($op == 'edit' && $id == -1)) {
        $app->render('not_found.html.twig');
        return;
    }
    if ($id != -1) {
        $values = DB::queryFirstRow('SELECT * FROM images WHERE imageId=%i', $id);
        if (!$values) {
            $app->render('not_found.html.twig');
            return;
        }
    } else { // nothing to load from database - adding
        $values = array();
    }
    $app->render('/photo/photo_addedit.html.twig', array(
        'v' => $values,
        'isEditing' => ($id != -1)));
})->conditions(array(
    'op' => '(edit|add)',
    'id' => '\d+'
));
//add / edit images 
$app->post('/photo/:op(/:id)', function($op, $id =-1) use ($app, $log) {
    if (!$_SESSION['user']) {
        $app->render('access_denied.html.twig');
        return;
    }
    if (($op == 'add' && $id != -1) || ($op == 'edit' && $id == -1)) {
        $app->render('not_found.html.twig');
        return;
    }
    $imageTitle = $app->request()->post('imageTitle');
    $imagePath = $app->request()->post('imagePath');

    //
    $values = array('imageTitle' => $imageTitle, 'imagePath' => $imagePath);

    $errorList = array();
    //
    if (strlen($imageTitle) < 2 || strlen($imageTitle) > 50) {
        $values['imageTitle'] = '';
        array_push($errorList, "Image Title must be between 2 and 50 characters long");
    }
    $userImage = array();
    // is file being uploaded
    if ($_FILES['userImage']['error'] != UPLOAD_ERR_NO_FILE) {
        $userImage = $_FILES['userImage'];
        if ($userImage['error'] != 0) {
            array_push($errorList, "Error uploading file");
            $log->err("Error uploading file: " . print_r($userImage, true));
        } else {
            if (strstr($userImage['name'], '..')) {
                array_push($errorList, "Invalid file name");
                $log->warn("Uploaded file name with .. in it (possible attack): " . print_r($userImage, true));
            }
            // TODO: check if file already exists, check maximum size of the file, dimensions of the image etc.
            $info = getimagesize($userImage["tmp_name"]);
            if ($info == FALSE) {
                array_push($errorList, "File doesn't look like a valid image");
            } else {
                if ($info['mime'] == 'image/jpeg' || $info['mime'] == 'image/gif' || $info['mime'] == 'image/png') {
                    // image type is valid - all good
                } else {
                    array_push($errorList, "Image must be a JPG, GIF, or PNG only.");
                }
            }
        }
    } else { // no file uploaded
          if ($op == 'add') {
        array_push($errorList, "Image is required when creating new picture");
          }
    }

    //
    if ($errorList) { // 3. failed submission
        $app->render('/photo/photo_addedit.html.twig', array(
            "errorList" => $errorList,
            'isEditing' => ($id != -1),
            'v' => $values));
    } else { // 2. successful submission
        if ($userImage) {
            $sanitizedFileName = preg_replace('[^a-zA-Z0-9_\.-]', '_', $userImage['name']);
            $imagePath = 'uploads/' . $sanitizedFileName;
            if (!move_uploaded_file($userImage['tmp_name'], $imagePath)) {
                $log->err("Error moving uploaded file: " . print_r($userImage, true));
                $app->render('internal_error.html.twig');
                return;
            }
            // TODO: if EDITING and new file is uploaded we should delete the old one in uploads
            $values['imagePath'] = "/" . $imagePath;
            if ($id != -1) {
                DB::update('images', $values, "imageId=%i", $id);
            } else {
                DB::insert('images', $values);
            }
            $app->render('/photo/photo_addedit_success.html.twig', array('isEditing' => ($id != -1)));
        }
    }
})->conditions(array(
    'op' => '(edit|add)',
    'id' => '\d+'
));
// delete images 
$app->get('/photo/delete/:id', function($id) use ($app) {
    if (!$_SESSION['user'] ) {
        $app->render("access_denied.html.twig");
        return;
    }
    $images = DB::queryFirstRow("SELECT * FROM images WHERE imageId=%d", $id);
    if (!$images) {
        $app->render("not_found.html.twig");
        return;
    }
    $app->render("/photo/photo_delete.html.twig", array('images' => $images));
});

$app->post('/photo/delete/:id', function($id) use ($app) {
    if (!$_SESSION['user'] ) {
        $app->render("access_denied.html.twig");
        return;
    }
    $confirmed = $app->request()->post('confirmed');
    if ($confirmed != 'true') {
        $app->render('not_found.html.twig');
        return;
    }
    DB::delete('images', "imageId=%i", $id);
    if (DB::affectedRows() == 0) {
        $app->render('not_found.html.twig');
    } else {
        $app->render('/photo/photo_delete_success.html.twig');
    }
});
