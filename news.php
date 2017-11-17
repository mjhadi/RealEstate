<?php

// fake $app, $log so that Netbeans can provide suggestions while typing code
if (false) {
    $app = new \Slim\Slim();
    $log = new Logger('main');
}

if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = array();
}

//3 last news news

$app->get('/news/toplist', function() use ($app) {
    
    
    $newsList = DB::query("SELECT TOP 3 * FROM news");
    $app->render("index.html.twig", array('list' => $newsList));
});
// veiw of list of news 
$app->get('/news/list', function() use ($app) {
    if (!$_SESSION['user'] || $_SESSION['user']['userRole'] != 'admin') {
        $app->render("access_denied.html.twig");
        return;
    }
    $userId = $_SESSION['user']['userId'];
    $newsList = DB::query("SELECT * FROM news WHERE userId =%i", $userId);
    $app->render("/news/news_list.html.twig", array('list' => $newsList));
});

// Delete News 
$app->get('/news/delete/:id', function($id) use ($app) {
    if (!$_SESSION['user'] || $_SESSION['user']['userRole'] != 'admin') {
        $app->render("access_denied.html.twig");
        return;
    }
    $news = DB::queryFirstRow("SELECT * FROM news WHERE newsId=%d AND userId=%i", $id, $_SESSION['user']['userId']);
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
    DB::delete('news', "newsId=%i AND userId=%i", $id, $_SESSION['user']['userId']);
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
        $values = DB::queryFirstRow('SELECT * FROM news WHERE newsId=%i AND userId=%i', $id, $_SESSION['user']['userId']);
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
    $lien= $app->request()->post('lien');
     $imagePath= $app->request()->post('imagePath');
    //
    $values = array('title' => $title, 'newsBody' => $newsBody, 'newsDate' => $newsDate, 'lien'=>$lien, 'imagePath'=>$imagePath);
    $errorList = array();
    //
    if (strlen($title) < 2 || strlen($title) > 100) {
        $values['title'] = '';
        array_push($errorList, "Title must be 2-100 characters long");
    }
    if (strlen($newsBody) < 2 || strlen($newsBody) > 200000) {
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
    //image of user
     $newsImage = array();
    // is file being uploaded
    if ($_FILES['newsImage']['error'] != UPLOAD_ERR_NO_FILE) {
        $newsImage = $_FILES['newsImage'];
        if ($newsImage['error'] != 0) {
            array_push($errorList, "Error uploading file");
            $log->err("Error uploading file: " . print_r($newsImage, true));
        } else {
            if (strstr($newsImage['name'], '..')) {
                array_push($errorList, "Invalid file name");
                $log->warn("Uploaded file name with .. in it (possible attack): " . print_r($newsImage, true));
            }
            // TODO: check if file already exists, check maximum size of the file, dimensions of the image etc.
            $info = getimagesize($newsImage["tmp_name"]);
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
      
            array_push($errorList, "Image is required when creating new news");
        
    }
    if ($errorList) {
        $app->render("/news/news_addedit.html.twig", array(
            "errorList" => $errorList,
            'isEditing' => ($id != -1),
            'v' => $values));
    } else { // 2. successful submission
         if ($newsImage) {
            $sanitizedFileName = preg_replace('[^a-zA-Z0-9_\.-]', '_', $newsImage['name']);
            $imagePath = '/uploads/' . $sanitizedFileName;
            if (!move_uploaded_file($newsImage['tmp_name'], $imagePath)) {
                $log->err("Error moving uploaded file: " . print_r($newsImage, true));
                $app->render('internal_error.html.twig');
                return;
            }
            // TODO: if EDITING and new file is uploaded we should delete the old one in uploads
            $values['imagePath'] = "/" . $imagePath;
        }
        if ($id != -1) {
            DB::update('news', $values, "newsId=%i", $id);
        } else {
            $values['userId'] = $_SESSION['user']['userId'];
            DB::insert('news', $values);
        }
        $app->render('/news/news_addedit_success.html.twig', array('isEditing' => ($id != -1)));
    }
})->conditions(array(
    'op' => '(edit|add)',
    'id' => '\d+'
)); // End of add-edit news

