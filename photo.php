<?php
// fake $app, $log so that Netbeans can provide suggestions while typing code
if (false) {
    $app = new \Slim\Slim();
    $log = new Logger('main');
}
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = array();
}
//add photo 
$app->get('/photo/add', function() use ($app) {
//    if (!$_SESSION['user']) {
//        $app->render('access_denied.html.twig');
//        return;
//    }
    $app->render('/photo/photo_add.html.twig');
});
//add images 
$app->post('/photo/add', function() use ($app , $log) {
//    if (!$_SESSION['user']) {
//        $app->render('access_denied.html.twig');
//        return;
//    }
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
        
            array_push($errorList, "Image is required when creating new product");
        
    }

    //
    if ($errorList) { // 3. failed submission
        $app->render('/photo/photo_add.html.twig', array(
            'errorList' => $errorList,
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
        DB::insert('images', $values);
        $app->render('/photo/photo_add_success.html.twig');
    }}
});

