<?php

//require_once 'Facebook/autoload.php';
require_once 'vendor/autoload.php';

return 
	[
	    "base_url"   => "http://slim.local/",
	    "providers"  => [
	        "Google"   => [
	            "enabled" => true,
	            "keys"    => [ "id" => "", "secret" => "" ],
	        ],
	        "Facebook" => [
	            "enabled"        => true,
	            "keys"           => [ "id" => "", "secret" => "" ],
	            "trustForwarded" => false
	        ],
	        "Twitter"  => [
	            "enabled" => true,
	            "keys"    => [ "key" => "", "secret" => "" ]
	        ],
	    ],
	    "debug_mode" => true,
	    "debug_file" => "bug.txt",
	];