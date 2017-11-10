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
    if (!$_SESSION['user']) {
        $app->render("access_denied.html.twig");
        return;
    }
    $userId = $_SESSION['user']['userId'];
    $chatList = DB::query("SELECT * FROM chats WHERE userId1 =%i", $userId);
    $app->render("/chat/chat.html.twig", array('list' => $chatList));
});
// send message
$app->get('/chat/send', function() use ($app) {
    if (!$_SESSION['user']) {
        $app->render("access_denied.html.twig");
        return;
    }
    $app->render('/chat/send.html.twig');
});

$app->post('/chat/send', function() use ($app, $log) {
    $name = $app->request()->post('name');
    $email = $app->request()->post('email');
    $message = $app->request()->post('message');
    $userId2 = $app->request()->post('userId2');
    //
    $values = array('name' => $name, 'email' => $email, 'message' => $message, 'userId2' => $userId2);
    $errorList = array();
    //
    if (strlen($name) < 2 || strlen($name) > 50) {
        $values['name'] = '';
        array_push($errorList, "Name must be between 2 and 50 characters long");
    }
    

    $values['userId1'] = $_SESSION['user']['userId'];
    //
    if ($errorList) { // 3. failed submission
        $app->render('/chat/send.html.twig', array(
            'errorList' => $errorList,
            'v' => $values));
    } else { // 2. successful submission
    
        DB::insert('chats', array($values));
        $app->render('/chat/send_success.html.twig');
    }
});
