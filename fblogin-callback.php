<?php

session_start();
require_once 'Facebook/autoload.php';
require_once 'vendor/autoload.php';


$fb = new Facebook\Facebook([
  'app_id' => '129597047747220',
  'app_secret' => '7ae0ff6e48a8d162bb82917837810b08',
  'default_graph_version' => 'v2.5',
  'persistent_data_handler' => 'session'
]);

$helper = $fb->getRedirectLoginHelper();

try {
    $accessToken = $helper->getAccessToken();
} catch (Facebook\Exceptions\FacebookResponseException $e) {
    // When Graph returns an error
    echo 'Graph returned an error: ' . $e->getMessage();
    exit;
} catch (Facebook\Exceptions\FacebookSDKException $e) {
    // When validation fails or other local issues
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
}

if (!isset($accessToken)) {
    if ($helper->getError()) {
        header('HTTP/1.0 401 Unauthorized');
        echo "Error: " . $helper->getError() . "\n";
        echo "Error Code: " . $helper->getErrorCode() . "\n";
        echo "Error Reason: " . $helper->getErrorReason() . "\n";
        echo "Error Description: " . $helper->getErrorDescription() . "\n";
    } else {
        header('HTTP/1.0 400 Bad Request');
        echo 'Bad request';
    }
    exit;
}
$fb->setDefaultAccessToken($accessToken);

try {
    $response = $fb->get('/me?locale=en_US&fields=id,name,email,first_name,last_name');
    $userNode = $response->getGraphUser();
} catch (Facebook\Exceptions\FacebookResponseException $e) {
    // When Graph returns an error
    echo 'Graph returned an error: ' . $e->getMessage();
    exit;
} catch (Facebook\Exceptions\FacebookSDKException $e) {
    // When validation fails or other local issues
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
}

//echo 'Logged in as ' . $userNode->getName();
$fbUser = array(
    'fName' => $userNode->getFirstName(),
    'lName' => $userNode->getLastName(),
    'email' => $userNode->getEmail(),
    'ID' => $userNode->getId(),
);

$_SESSION['facebook_access_token'] = $fbUser;
$_SESSION['user'] = array();

header("Location:/sociallogin");

