<?php

//facebook login
$fb = new Facebook\Facebook([
  'app_id' => '881440665321233',
  'app_secret' => 'dd9d7b83433033b6b7ed21682f7d9619',
  'default_graph_version' => 'v2.5',
  'persistent_data_handler' => 'session'
]);
