<?php
require_once ('dbg.php');
require_once ('webhook_helper.php');

//validate verification requests
if (isset($_GET['hub_verify_token'])){
	VerificationValidation($_GET['hub_verify_token']);
}

//process event notifications
if (isset($_SERVER['HTTP_X_HUB_SIGNATURE'])){
	dbg(['HTTP_X_HUB_SIGNATURE'=>$_SERVER['HTTP_X_HUB_SIGNATURE']]);
	if(CheckSha()){
		ProcessEvent();
	}
}

function ProcessEvent(){
    $webhookHelper = new webhook_helper();
    
    $json_str = file_get_contents('php://input');

    $webhookHelper->PreProcessWebhook($json_str);
    }

function VerificationValidation($hub_verify_token){
	$verify_token = getenv('VERIFY_TOKEN');

	if ($hub_verify_token === $verify_token){
		echo $_GET['hub_challenge'];
		return;
	}else{
		echo "404";
		return;
	}
}

function CheckSha(){

	$secret = getenv('APP_SECRET');
	$json_str = file_get_contents('php://input');
	
	$secret_sha = 'sha1=' . hash_hmac('sha1', $json_str, $secret);
	dbg(['checksum'=>$secret_sha]);
	if(hash_equals($secret_sha, $_SERVER['HTTP_X_HUB_SIGNATURE'])){
		echo '200 OK HTTPS';
		dbg(['checksum'=>'ok']);
		return true;
	}else{
		dbg(['checksum'=>'wrong']);
		return false;
	}
}

