<?php

 return
 [
    'app' => [
        'version'       => '1.0.0',
        'date_format'   => "Y/m/d H:i:s",
        'timezone'      => "Europe/Moscow",
        'debug'         => false
    ],

    'workers' => [
	'listen' => 'http://127.0.0.1:80',
	'count'  => 4
    ],

    'messenger' => [

	'icq' => [
	    'uin'         => '0000000',
	    'password'    => '',
	    'token'       => [],
	    'token_renew' => '86400'
	],

	'telegram' => [
	    'apikey'    => '',
	    'use_proxy' => 0,
	    'proxy'     => 'socks5://user:pass@127.0.0.1:8080'
	]

    ]

 ];
