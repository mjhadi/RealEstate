<?php

// fake $app, $log so that Netbeans can provide suggestions while typing code
if (false) {
    $app = new \Slim\Slim();
    $log = new Logger('main');
}

if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = array();
}
// View of list of chat 
$app->get('/chat/list', function() use ($app) {
    if (!$_SESSION['user'] ) {
        $app->render("access_denied.html.twig");
        return;
    }
    $userId = $_SESSION['user']['userId'];
    $chatList = DB::query("SELECT * FROM chats WHERE userId1 =%i", $userId);
    $app->render("/chat/chat.html.twig", array('list' => $chatList));
});
// send message
$app->get('/chat/send', function() use ($app) {
    if (!$_SESSION['user'] ) {
        $app->render("access_denied.html.twig");
        return;
    }
    $app->render('/chat/send.html.twig');
});

$app->post('/chat/send', function() use ($app, $log) {
    $name = $app->request()->post('name');
    $email = $app->request()->post('email');
    $address = $app->request()->post('address');
    $userRole = $app->request()->post('userRole');
    $pathImage = $app->request()->post('pathImage');
    $phone = $app->request()->post('phone');
    $pass1 = $app->request()->post('pass1');
    $pass2 = $app->request()->post('pass2');
    //
    $values = array('name' => $name, 'email' => $email, 'address' => $address, 'userRole' => $userRole, 'phone' => $phone, 'pathImage' =>$pathImage);
    $errorList = array();
    //
    if (strlen($name) < 2 || strlen($name) > 50) {
        $values['name'] = '';
        array_push($errorList, "Name must be between 2 and 50 characters long");
    }
    if (filter_var($email, FILTER_VALIDATE_EMAIL) == FALSE) {
        $values['email'] = '';
        array_push($errorList, "Email must look like a valid email");
    } else {
        $row = DB::queryFirstRow("SELECT * FROM users WHERE email=%s", $email);
        if ($row) {
            $values['email'] = '';
            array_push($errorList, "Email already in use");
        }
    }
    if ($pass1 != $pass2) {
        array_push($errorList, "Passwords don't match");
    } else { 
        if (strlen($pass1) < 2 || strlen($pass1) > 50) {
            array_push($errorList, "Password too short, must be 6 characters or longer");
        }
        if (preg_match('/[A-Z]/', $pass1) != 1 || preg_match('/[a-z]/', $pass1) != 1 || preg_match('/[0-9]/', $pass1) != 1) {
            array_push($errorList, "Password must contain at least one lowercase, "
                    . "one uppercase letter, and a digit");
        }
    }
    //image of user
     $memberImage = array();
    // is file being uploaded
    if ($_FILES['memberImage']['error'] != UPLOAD_ERR_NO_FILE) {
        $memberImage = $_FILES['memberImage'];
        if ($memberImage['error'] != 0) {
            array_push($errorList, "Error uploading file");
            $log->err("Error uploading file: " . print_r($memberImage, true));
        } else {
            if (strstr($memberImage['name'], '..')) {
                array_push($errorList, "Invalid file name");
                $log->warn("Uploaded file name with .. in it (possible attack): " . print_r($memberImage, true));
            }
            // TODO: check if file already exists, check maximum size of the file, dimensions of the image etc.
            $info = getimagesize($memberImage["tmp_name"]);
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
        $app->render('/user/register_user.html.twig', array(
            'errorList' => $errorList,
            'v' => $values));
    } else { // 2. successful submission
          if ($memberImage) {
            $sanitizedFileName = preg_replace('[^a-zA-Z0-9_\.-]', '_', $memberImage['name']);
            $imagePath = '/uploads/' . $sanitizedFileName;
            if (!move_uploaded_file($memberImage['tmp_name'], $imagePath)) {
                $log->err("Error moving uploaded file: " . print_r($memberImage, true));
                $app->render('internal_error.html.twig');
                return;
            }
            // TODO: if EDITING and new file is uploaded we should delete the old one in uploads
            $values['pathImage'] = "/" . $imagePath;
        }
        $passEnc = password_hash($pass1, PASSWORD_BCRYPT);
        DB::insert('users', array('name' => $name, 'email' => $email, 'address' => $address, 'userRole' => $userRole, 'phone' => $phone, 'password' => $passEnc, 'pathImage'=>$pathImage));
        $app->render('/user/register_user_success.html.twig');
    }
});