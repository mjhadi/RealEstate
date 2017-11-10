<?php

 require_once 'src/Google_Client.php';
require_once 'src/contrib/Google_Oauth2Service.php';
session_start();

$client = new Google_Client();
$client->setApplicationName("Sample Google Login Application");

$oauth2 = new Google_Oauth2Service($client);

if (isset($_GET['code'])) 
{
 $client->authenticate($_GET['code']);
 $_SESSION['token'] = $client->getAccessToken();
 $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
 header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
 return;
}

if (isset($_SESSION['token'])) 
{
 $client->setAccessToken($_SESSION['token']);
}

if (isset($_REQUEST['logout'])) 
{
 unset($_SESSION['token']);
 $client->revokeToken();
}

if ($client->getAccessToken()) 
{
 $user = $oauth2->userinfo->get();

 $email = filter_var($user['email'], FILTER_SANITIZE_EMAIL);
 $img = filter_var($user['picture'], FILTER_VALIDATE_URL);
 $personMarkup = "$email<div><img src='$img?sz=100'></div>";

 $_SESSION['token'] = $client->getAccessToken();
}
else 
{
 $authUrl = $client->createAuthUrl();
}

if(isset($personMarkup)): print $personMarkup; //This will print user Information 
endif;

if(isset($authUrl)) 
{
 print "<a class='login' href='$authUrl'>Login With Google</a>";
}
else 
{
 print "<a class='logout' href='?logout'>Google Logout</a>";
}