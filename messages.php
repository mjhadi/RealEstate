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
    $message = $app->request()->post('message');
    
    //
    $values = array('name' => $name, 'email' => $email, 'message' => $message);
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
   

    //
    if ($errorList) { // 3. failed submission
        $app->render('/chat/send.html.twig', array(
            'errorList' => $errorList,
            'v' => $values));
    } else { // 2. successful submission
         
        
        DB::insert('messages', array('name' => $name, 'email' => $email, 'message' => $message));
        $app->render('/chat/send_success.html.twig');
    }
});