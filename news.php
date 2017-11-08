<?php

// fake $app, $log so that Netbeans can provide suggestions while typing code
if (false) {
    $app = new \Slim\Slim();
    $log = new Logger('main');
}

if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = array();
}


// veiw of list of news 
$app->get('/news/list', function() use ($app) {
    if (!$_SESSION['user'] || $_SESSION['user']['userRole'] != 'admin') {
        $app->render("access_denied.html.twig");
        return;
    }
    $userId = $_SESSION['user']['id'];
    $newsList = DB::query("SELECT * FROM news WHERE userId =%i", $userId);
    $app->render("/news/news_list.html.twig", array('list' => $newsList));
});

// Delete News 
$app->get('/news/delete/:id', function($id) use ($app) {
    if (!$_SESSION['user'] || $_SESSION['user']['userRole'] != 'admin') {
        $app->render("access_denied.html.twig");
        return;
    }
    $news = DB::queryFirstRow("SELECT * FROM news WHERE id=%d AND userId=%i", $id, $_SESSION['user']['id']);
    if (!$news) {
        $app->render("/not_found.html.twig");
        return;
    }
    $app->render("/news/news_delete.html.twig", array('n' => $news));
});

$app->post('/news/delete/:id', function($id) use ($app) {
    if (!$_SESSION['user'] || $_SESSION['user']['userRole'] != 'admin') {
        $app->render("access_denied.html.twig");
        return;
    }
    $confirmed = $app->request()->post('confirmed');
    if ($confirmed != 'true') {
        $app->render('/not_found.html.twig');
        return;
    }
    DB::delete('news', "id=%i AND userId=%i", $id, $_SESSION['user']['id']);
    if (DB::affectedRows() == 0) {
        $app->render('/not_found.html.twig');
    } else {
        $app->render('/news/news_delete_success.html.twig');
    }
});

// Add-Edit News
$app->get('/news/:op(/:id)', function($op, $id = -1) use ($app) {
    if (!$_SESSION['user'] || $_SESSION['user']['userRole'] != 'admin') { // check if the user logged in and role is admin
        $app->render('access_denied.html.twig');
        return;
    }
    if (($op == 'add' && $id != -1) || ($op == 'edit' && $id == -1)) {
        $app->render('/not_found.html.twig');
        return;
    }
    //
    if ($id != -1) {
        $values = DB::queryFirstRow('SELECT * FROM news WHERE id=%i AND userId=%i', $id, $_SESSION['user']['id']);
        if (!$values) {
            $app->render('/not_found.html.twig');
            return;
        }
    } else { // nothing to load from database - adding
        $values = array();
    }
    $app->render('/news/news_addedit.html.twig', array(
        'v' => $values,
        'isEditing' => ($id != -1)
    ));
})->conditions(array(
    'op' => '(edit|add)',
    'id' => '\d+'
));

$app->post('/news/:op(/:id)', function($op, $id = -1) use ($app, $log) {
    if (!$_SESSION['user'] || $_SESSION['user']['userRole'] != 'admin') {
        $app->render('access_denied.html.twig');
        return;
    }
    if (($op == 'add' && $id != -1) || ($op == 'edit' && $id == -1)) {
        $app->render('/not_found.html.twig');
        return;
    }
    //
    $title = $app->request()->post('title');
    $newsBody = $app->request()->post('newsBody');
    $newsDate = $app->request()->post('newsDate');
    //
    $values = array('title' => $title, 'newsBody' => $newsBody, 'newsDate' => $newsDate);
    $errorList = array();
    //
    if (strlen($title) < 2 || strlen($title) > 100) {
        $values['title'] = '';
        array_push($errorList, "Title must be 2-100 characters long");
    }
    if (strlen($newsBody) < 2 || strlen($newsBody) > 2000) {
        $values['newsBody'] = '';
        array_push($errorList, "News Body must be 2-2000 characters long");
    }
    if (!DateTime::createFromFormat('Y-m-d', $newsDate)) {
        $values['newsDate'] = '';
        array_push($errorList, "Date seems invalid. Please enter in this format yyyy-mm-dd.");        
    }
    // 
    if (date_parse($newsDate) == FALSE) {
        $values['newsDate'] = '';
        array_push($errorList, "Date seems invalid. Please enter in this format yyyy-mm-dd."); 
    } 
    if ($errorList) {
        $app->render("/news/news_addedit.html.twig", array(
            "errorList" => $errorList,
            'isEditing' => ($id != -1),
            'v' => $values));
    } else { // 2. successful submission
        if ($id != -1) {
            DB::update('news', $values, "id=%i", $id);
        } else {
            $values['userId'] = $_SESSION['user']['id'];
            DB::insert('news', $values);
        }
        $app->render('/news/news_addedit_success.html.twig', array('isEditing' => ($id != -1)));
    }
})->conditions(array(
    'op' => '(edit|add)',
    'id' => '\d+'
)); // End of add-edit news

