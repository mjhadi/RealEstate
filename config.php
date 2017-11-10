<?php

//facebook login
$fb = new Facebook\Facebook([
  'app_id' => '129597047747220',
  'app_secret' => '7ae0ff6e48a8d162bb82917837810b08',
  'default_graph_version' => 'v2.5',
  'persistent_data_handler' => 'session'
]);

//$helper = $fb->getRedirectLoginHelper();
////$permissions = ['public_profile', 'email', 'user_location']; // optional
//$loginUrl = $helper->getLoginUrl('http://realestate.ipd10.com/fblogin-callback.php', $permissions);
//$logoutUrl = $helper->getLoginUrl('http://realestate.ipd10.com/fblogout-callback.php', $permissions);


