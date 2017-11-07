<?php 

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

session_start();

require_once 'vendor/autoload.php' ;


//meekrodb
DB::$dbName = 'cp4809_realestat';
DB::$user = 'cp4809_realestat';
DB::$encoding = 'utf8';
DB::$password = 'b=XdL_Ar[tAm'; //Collage Password


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



$twig = $app->view()->getEnvironment();
$twig->addGlobal('userSession', $_SESSION['user']);

if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = array();
}

$app->get('/', function() use ($app) {
    echo 'This is realestate project';
});

require_once 'admin/admin.php';
$app->run();

