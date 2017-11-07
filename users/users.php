<?php

// fake $app, $log so that Netbeans can provide suggestions while typing code
if (false) {
    $app = new \Slim\Slim();
    $log = new Logger('main');
}
<<<<<<< HEAD

// User Login
=======
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = array();
}
>>>>>>> 5c0d0c977be0f9a55610074042a62c70bf0ff755
$app->get('/user/login', function() use ($app) {
    $app->render('user/login_user.html.twig');
});

$app->post('/user/login', function() use ($app) {
    $email = $app->request()->post('email');
    $pass = $app->request()->post('pass');
    $row = DB::queryFirstRow("SELECT * FROM users WHERE email=%s", $email);
    $error = false;
    if (!$row) {
        $error = true; // user not found
    } else {
        if (password_verify($pass, $row['password']) == FALSE) {
            $error = true; // password invalid
        }
    }
    if ($error) {
        $app->render('/user/login_user.html.twig', array('error' => true));
    } else {
        unset($row['password']);
        $_SESSION['user'] = $row;
        $app->render('/user/login_success.html.twig', array('userSession' => $_SESSION['user']));
    }
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

$app->post('/user/register', function() use ($app) {
    $name = $app->request()->post('name');
    $email = $app->request()->post('email');
    $address = $app->request()->post('address');
    $userRole = $app->request()->post('userRole');
    $phone = $app->request()->post('phone');
    $pass1 = $app->request()->post('pass1');
    $pass2 = $app->request()->post('pass2');
    //
    $values = array('name' => $name, 'email' => $email, 'address' => $address, 'userRole' => $userRole, 'phone' => $phone);
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
    } else { // TODO: do a better check for password quality (lower/upper/numbers/special)
        if (strlen($pass1) < 2 || strlen($pass1) > 50) {
            array_push($errorList, "Password too short, must be 6 characters or longer");
        }
        if (preg_match('/[A-Z]/', $pass1) != 1 || preg_match('/[a-z]/', $pass1) != 1 || preg_match('/[0-9]/', $pass1) != 1) {
            array_push($errorList, "Password must contain at least one lowercase, "
                    . "one uppercase letter, and a digit");
        }
    }    
    //
    if ($errorList) { // 3. failed submission
        $app->render('/user/register_user.html.twig', array(
            'errorList' => $errorList,
            'v' => $values));
    } else { // 2. successful submission
        $passEnc = password_hash($pass1, PASSWORD_BCRYPT);
        DB::insert('users', array('name' => $name, 'email' => $email, 'address' => $address, 'userRole' => $userRole, 'phone' => $phone, 'password' => $passEnc));
        $app->render('/user/register_user_success.html.twig');
    }
});
