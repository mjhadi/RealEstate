<?php

// fake $app, $log so that Netbeans can provide suggestions while typing code
if (false) {
    $app = new \Slim\Slim();
    $log = new Logger('main');
}

if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = array();
}

//if (!isset($_SESSION['facebook_access_token'])) {
//    $_SESSION['facebook_access_token'] = array();
//}

// User Login 
$app->get('/user/login', function() use ($app) {
    $app->render('user/login_user.html.twig');
});

$app->post('/user/login', function() use ($app , $log) {
    $email = $app->request()->post('email');
    $pass = $app->request()->post('pass');
    $row = DB::queryFirstRow("SELECT * FROM users WHERE email=%s", $email);
    $error = false;
    if (!$row) {
        $error = true; // user not found
    } else {
        if (password_verify($pass, $row['password'])) {
            $error = false; // password invalid
        }
    }
    if ($error) {
        $app->render('/user/login_user.html.twig', array('error' => true));
    } else {
        unset($row['password']);
        $_SESSION['user'] = $row;
        $_SESSION['facebook_access_token'] = array();
        $app->render('/user/login_user_success.html.twig', array('userSession' => $_SESSION['user']));
    }
});

//Use login with Social account
$app->get('/user/sociallogin', function() use ($app) {
     $app->render('user/sociallogin.html.twig', array('user' => $_SESSION['facebook_access_token']));
});
$app->post('/user/sociallogin', function() use ($app) {
    //check if user has an account
        $row = DB::queryFirstField('SELECT userId from users WHERE email = %s', $_SESSION['facebook_access_token']['email']);
    if (!$row) {
       
        $result = DB::insert('users', array(
                    'name' => $_SESSION['facebook_access_token']['fName'].' '.$_SESSION['facebook_access_token']['lName'],
                    'email' => $_SESSION['facebook_access_token']['email'],
                    'socialId' => $_SESSION['facebook_access_token']['ID'],
        ));
        if ($result) {
            $userID = DB::insertId();
            $log->debug(sprintf("Regisetred fbUser %s with userId %s", $_SESSION['facebook_access_token']['first_name'], $userID));
            $_SESSION['facebook_access_token']['userID'] = $userID;
        }
      
    }
       $app->render('/user/sociallogin.html.twig');
});
    

// User Logout
$app->get('/user/logout', function() use ($app, $log) {
    $_SESSION['user'] = array();
    $_SESSION['facebook_access_token'] = array();
    $app->render('logout_user.html.twig');
});

// Is user email registered
$app->get('/isemailregistered/:email', function($email) use ($app) {
    $row = DB::queryFirstRow("SELECT * FROM users WHERE email=%s", $email);
    echo!$row ? "" : '<span style="background-color: red; font-weight: bold;">Email already taken</span>';
});

//Register User
$app->get('/user/register', function() use ($app) {
    $app->render('/user/register_user.html.twig');
});

$app->post('/user/register', function() use ($app, $log) {
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

// password Reset Request function
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// password Reset Request
$app->map('/passreset/request', function() use ($app, $log) {
    if ($app->request()->isGet()) {
        // State 1: first show
        $app->render('user/passreset_request.html.twig');
        return;
    }
    // in Post - receiving submission
    $email = $app->request()->post('email');
    $user = DB::queryFirstRow("SELECT * FROM users WHERE email=%s", $email);
    if ($user) {
        $secretToken = generateRandomString(50);
        /* Version 1: delete-and-insert 2 operations */
        /* DB::delete('passresets', 'userId=%d', $user['id']);
          DB::insert('passresets', array(
          'userId' => $user['id'],
          $secretToken,
          'expiryDateTime' => date("Y-m-d H:i:s", strtotime("+1 day"))
          )); */
        /* Version 2: insertUpdate */
        DB::insertUpdate('passresets', array(
            'userId' => $user['userId'],
            'secretToken' => $secretToken,
            'expiryDateTime' => date("Y-m-d H:i:s", strtotime("+5 minutes"))
        ));
        $url = 'http://' . $_SERVER['SERVER_NAME'] . '/passreset/token/' . $secretToken;
        $emailBody = $app->view()->render('user/passreset_email.html.twig', array(
            'name' => $user['name'], // or 'username' or 'firstName'
            // 'name' => 'User', if you don't have user's name in your database
            'url' => $url
        ));
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html\r\n";
        $headers .= "From: Noreply <noreply@ipd10.com>\r\n";
        $headers .= "Date: " . date("Y-m-d H:i:s");
        $toEmail = sprintf("%s <%s>", htmlentities($user['name']), $user['email']);
        // $headers.= sprintf("To: %s\r\n", $user['email']);

        mail($toEmail, "Your password reset for " . $_SERVER['SERVER_NAME'], $emailBody, $headers);
        $log->info('Email sent for password reset for user id=' . $user['userId']);
        $app->render('user/passreset_request_success.html.twig');
    } else { // State 3: failed request, email not registered
        $app->render('user/passreset_request.html.twig', array('error' => true));
    }
})->via('GET', 'POST');

$app->map('/passreset/token/:secretToken', function($secretToken) use ($app, $log) {
    $row = DB::queryFirstRow("SELECT * FROM passresets WHERE secretToken=%s", $secretToken);
    if (!$row) { // row not found
        $app->render('user/passreset_notfound_expired.html.twig');
        return;
    }
    if (strtotime($row['expiryDateTime']) < time()) {
        // row found but token expired
        $app->render('user/passreset_notfound_expired.html.twig');
        return;
    }
    //
    $user = DB::queryFirstRow("SELECT * FROM users WHERE userId=%d", $row['userId']);
    if (!$user) {
        $log->err(sprintf("Passreset for token %s user userId=%d not found", $row['secretToken'], $row['userId']));
        $app->render('error_internal.html.twig');
        return;
    }
    if ($app->request()->isGet()) { // State 1: first show
        $app->render('user/passreset_form.html.twig', array(
            'name' => $user['name'], 'email' => $user['email']
        ));
    } else { // receiving POST with new password
        $pass1 = $app->request()->post('pass1');
        $pass2 = $app->request()->post('pass2');
        // FIXME: verify quality of the new password using a function
        $errorList = array();
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
        if ($errorList) { // 3. failed submission
            $app->render('user/passreset_form.html.twig', array(
                'errorList' => $errorList,
                'name' => $user['name'],
                'email' => $user['email']
            ));
        } else { // 2. successful submission
            DB::update('users', array('password' => $pass1), 'userId=%d', $user['userId']);
            $app->render('user/passreset_form_success.html.twig');
        }
    }
})->via('GET', 'POST');
// user profile
$app->get('/user/profile', function() use ($app) {
     if (!$_SESSION['user'] ) {
        $app->render("access_denied.html.twig");
        return;
    }
    $userId = $_SESSION['user']['userId'];
    $user = DB::queryFirstRow("SELECT * FROM users WHERE userId =%i", $userId);
    $app->render('/User/profile_user.html.twig', array('user' => $user));
});
//update profile
$app->post('/user/profile', function() use ($app) {
    $name = $app->request()->post('name');
    $email = $app->request()->post('email');
    $address = $app->request()->post('address');
    $pathImage = $app->request()->post('pathImage');
    $pass1 = $app->request()->post('pass1');
    $pass2 = $app->request()->post('pass2');
    //
    $values = array('name' => $name, 'email' => $email, 'address' => $address, 'pathImage' =>$pathImage);
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
        $app->render('/user/profile_user.html.twig', array(
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
        DB::update('users', array('name' => $name, 'email' => $email, 'address' => $address,  'password' => $passEnc, 'pathImage'=>$pathImage));
        $app->render('/user/register_user_success.html.twig');
    }
});

//get list of user
$app->get('/chat/send', function() use ($app) {
    if (!$_SESSION['user'] ) {
        $app->render("access_denied.html.twig");
        return;
    }
    $list = DB::query("SELECT * FROM users");
    $app->render("/chat/send.html.twig", array('list' => $list));
});
// Fetch all countries list
  function getUsers() {
   
      $list = DB::query("SELECT * FROM users");
      
        return $list;
     
   }
