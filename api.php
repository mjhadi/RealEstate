</p>
<?php
require_once 'lib/src/Google_Client.php';
require 'lib/src/contrib/Google_Oauth2Service.php';
require_once 'lib/src/contrib/Google_PlusService.php';
session_start();
 $api = new Google_Client();
 $api->setApplicationName("InfoTuts"); // Set Application name
 $api->setClientId('709999717187-dkfrs23viohhbcgtjmc33hpfiaj9ed08.apps.googleusercontent.com'); // Set Client ID
 $api->setClientSecret('um_WC5QCy-UGPhydFBUR58C7'); //Set client Secret
 $api->setAccessType('online'); // Access method
 $api->setScopes(array('https://www.googleapis.com/auth/plus.login', 'https://www.googleapis.com/auth/plus.me', 'https://www.googleapis.com/auth/userinfo.email', 'https://www.googleapis.com/auth/userinfo.profile'));
 $api->setRedirectUri('http://www.infotuts.com/<span id="IL_AD5" class="IL_AD">demo</span>/googlelogin/login.php'); // Enter your file path (Redirect Uri) that you have set to get client ID in API console
 $service = new Google_PlusService($api);
 $URI = $api->createAuthUrl();
?>
<p style="text-align: justify;">