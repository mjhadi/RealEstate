<?php 

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

session_start();

//require_once 'Facebook/autoload.php';
require_once 'vendor/autoload.php';
//require_once 'socialconfig.php';

DB::$dbName = 'cp4809_realestate';
DB::$user = 'cp4809_realestat';
DB::$host = 'ipd10.com';
DB::$encoding = 'utf8';
DB::$password = 'b=XdL_Ar[tAm'; //Collage Password

//Error handling
DB::$error_handler = 'sql_error_handler';
DB::$nonsql_error_handler = 'nonsql_error_handler';

function sql_error_handler($params) {
    global $app, $log;
    $log->err("SQL Error: " . $params['error']);
    $log->err(" in query: " . $params['query']);
    http_response_code(500);
    $app->render('error_internal.html.twig');
    die;
}

function nonsql_error_handler($params) {
    global $app, $log;
    $log->err("SQL Error: " . $params['error']);
    http_response_code(500);
    $app->render('error_internal.html.twig');
    die;
}

// Slim creation and setup
$app = new \Slim\Slim(array(
    'view' => new \Slim\Views\Twig()
        ));

$view = $app->view();
$view->parserOptions = array(
    'debug' => true,
    'cache' => dirname(__FILE__) . '/cache'
);
$view->setTemplatesDirectory(dirname(__FILE__) . '/templates');


// create a log channel
$log = new Logger('main');
$log->pushHandler(new StreamHandler('logs/everything.log', Logger:: DEBUG));
$log->pushHandler(new StreamHandler('logs/errors.log', Logger::ERROR));

//if ($_SERVER['SERVER_NAME'] != 'localhost') {
////sessions 
//    $helper = $fb->getRedirectLoginHelper();
//    $permissions = ['public_profile', 'email']; // optional
//    $loginUrl = $helper->getLoginUrl('http://realestate.ipd10.com/callback.php', $permissions);
//    $logoutUrl = $helper->getLoginUrl('http://realestate.ipd10.com/callback.php', $permissions);
//}

if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = array();
}

//if (!isset($_SESSION['facebook_access_token'])) {
//    $_SESSION['facebook_access_token'] = array();
//}

$twig = $app->view()->getEnvironment();
//if ($_SERVER['SERVER_NAME'] != 'localhost') {
//    $twig->addGlobal('fbUser', $_SESSION['facebook_access_token']);
//    $twig->addGlobal('loginUrl', $loginUrl);
//}
//$twig->addGlobal('userSession', $_SESSION['user']);

$app->get('/', function() use ($app) {
     $propertyList = DB::query("SELECT * FROM property");
     $app->render('index.html.twig' , array('list'=>$propertyList));
});


require_once 'users.php';
require_once 'news.php';
require_once 'photo.php';
require_once 'property.php';
require_once 'messages.php';

$app->run();

