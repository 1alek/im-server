<?php

/**
 * IM Notifications Server
 *
 * @author Alek.su via a@alek.su
 */


 defined('APP_ROOT') or exit;

 use Workerman\Worker;

 require_once APP_ROOT . '/app/icq.php';
 require_once APP_ROOT . '/app/telegram.php';

 //////////////////////////////////////////////////////////////////////////////////////////////////////////////
 // LOAD CONFIG

 $_CONFIG = include( APP_ROOT . '/config/config.php' );
 file_exists( APP_ROOT . '/config/config.local.php') && $_CONFIG = array_merge($_CONFIG, include APP_ROOT. '/config/config.local.php');

 // set options
 date_default_timezone_set($_CONFIG['app']['timezone']);
 if ($_CONFIG['app']['debug']) {
    error_reporting(E_iALL);
    ini_set('display_errors', 1);
 }

 //////////////////////////////////////////////////////////////////////////////////////////////////////////////
 // START HTTP WORKERS

 $http_worker = new Worker($_CONFIG['workers']['listen']);
 // 4 processes
 $http_worker->name  = "apiprocess";
 $http_worker->count = $_CONFIG['workers']['count'];
 // Emitted when data received
 $http_worker->onMessage = function($connection, $data)
 {
    global $_CONFIG;
    $body="404";

    if (isset($_POST['proto']) && is_string($_POST['proto'])) {
    
	if ($_POST['proto'] === 'icq') {

	    $icq = new Icq();

	    if ( isset($_POST['uin']) && is_string($_POST['uin']) && isset($_POST['message']) && is_string($_POST['message']) ) {
        	$uin     = $_POST['uin'];
		$message = $_POST['message'];
		$body    = "uin: $uin\n"
    			 . "message: $message\n";
        	if ( $icq->send($uin, $message) ) {
	    	    $code  = 200;
    		    $body .= "status: OK";
    		} else {
            	    $code  = 502;
            	    $body .= "status: FAIL";
            	    $body .= "reason: api request fail";
    		}
	    }

	} elseif ($_POST['proto'] === 'telegram') {

	    $tg = new Telegram($_CONFIG['messenger']['telegram']['apikey']);

	    if ( isset($_POST['chat_id']) && is_string($_POST['chat_id']) && isset($_POST['message']) && is_string($_POST['message']) ) {
        	$chat_id = $_POST['chat_id'];
		$message = $_POST['message'];

                if ( $tg->sendMessage(['chat_id' => $chat_id, 'text' => $message]) ) {
                    $code  = 200;
                    $body .= "status: OK";
                } else {
                    $code  = 502;
                    $body .= "status: FAIL";
                    $body .= "reason: api request fail";
                }

	    }

	}    

    }

    // create icq
    // $_GET, $_POST, $_COOKIE, $_SESSION, $_SERVER, $_FILES are available
    //    var_dump($_GET, $_POST, $_COOKIE, $_SESSION, $_SERVER, $_FILES);
    // send data to client

    $connection->send($body);

 };

 Worker::runAll();
