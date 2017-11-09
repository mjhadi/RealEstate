<?php

// fake $app, $log so that Netbeans can provide suggestions while typing code
if (false) {
    $app = new \Slim\Slim();
    $log = new Logger('main');
}

if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = array();
}
// Veiw of list of chat 
$app->get('/chat/list', function() use ($app) {
    if (!$_SESSION['user'] ) {
        $app->render("access_denied.html.twig");
        return;
    }
    $userId = $_SESSION['user']['userId'];
    $chatList = DB::query("SELECT * FROM chats WHERE userId1 =%i", $userId);
    $app->render("/chat/chat.html.twig", array('list' => $chatList));
});