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
    $app->render('todo_addedit.html.twig');
});

$app->post('/property/add', function() use ($app) {
    if (!$_SESSION['user']) {
        $app->render('access_denied.html.twig');
        return;
    }
    $task = $app->request()->post('task');
    $dueDate = $app->request()->post('dueDate');
    $isDone = $app->request()->post('isDone');
    $isDone = empty($isDone) ? "pending" : "done";
    //
    $values = array('task' => $task, 'dueDate' => $dueDate, 'isDone' => $isDone);

    $errorList = array();
    //
    if (strlen($task) < 2 || strlen($task) > 50) {
        $values['task'] = '';
        array_push($errorList, "Task must be between 2 and 50 characters long");
    }
    if (date_parse($dueDate) == FALSE) {
        $values['dueDate'] = '';
        array_push($errorList, "Date seems invalid");
    }
    //
    if ($errorList) { // 3. failed submission
        $app->render('todo_addedit.html.twig', array(
            'errorList' => $errorList,
            'v' => $values));
    } else { // 2. successful submission
        $values['ownerId'] = $_SESSION['user']['id'];
        DB::insert('todos', $values);
        $app->render('todo_addedit_success.html.twig');
    }
});
