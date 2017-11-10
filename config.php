<?php

require_once 'Facebook/autoload.php';
require_once 'vendor/autoload.php';

//facebook login
$fb = new Facebook\Facebook([
  'app_id' => '129597047747220',
  'app_secret' => '7ae0ff6e48a8d162bb82917837810b08',
  'default_graph_version' => 'v2.5',
  'persistent_data_handler' => 'session'
]);




