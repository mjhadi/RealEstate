<?php

//require_once 'Facebook/autoload.php';
require_once 'vendor/autoload.php';



$fb = new Facebook\Facebook([
  'app_id' => '129597047747220', 
  'app_secret' => '7ae0ff6e48a8d162bb82917837810b08',
  'default_graph_version' => 'v2.2',
  ]);

//return 
//	[
//	    "base_url"   => "http://slim.local/",
//	    "providers"  => [
//	        "Google"   => [
//	            "enabled" => true,
//	            "keys"    => [ "id" => "", "secret" => "" ],
//	        ],
//	        "Facebook" => [
//	            "enabled"        => true,
//	            "keys"           => [ "id" => "", "secret" => "" ],
//	            "trustForwarded" => false
//	        ],
//	        "Twitter"  => [
//	            "enabled" => true,
//	            "keys"    => [ "key" => "", "secret" => "" ]
//	        ],
//	    ],
//	    "debug_mode" => true,
//	    "debug_file" => "bug.txt",
//	];