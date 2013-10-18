<?php

require_once ('codebird.php');
require_once ('../keys.php');
\Codebird\Codebird::setConsumerKey($CONSUMER_KEY, $CONSUMER_SECRET); // static, see 'Using multiple Codebird instances'

$cb = \Codebird\Codebird::getInstance();
session_start();
$prev_page = $_GET['prev_page'];

if (! isset($_SESSION['oauth_token'])) {
    // get the request token
    $reply = $cb->oauth_requestToken(array(
        'oauth_callback' => 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
    ));

    // store the token
    $cb->setToken($reply->oauth_token, $reply->oauth_token_secret);
    $_SESSION['oauth_token'] = $reply->oauth_token;
    $_SESSION['oauth_token_secret'] = $reply->oauth_token_secret;
    $_SESSION['oauth_verify'] = true;

    // redirect to auth website
    $auth_url = $cb->oauth_authorize();
    header('Location: ' . $auth_url);
    die();

} elseif (isset($_GET['oauth_verifier']) && isset($_SESSION['oauth_verify'])) {
    // verify the token
    $cb->setToken($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
    unset($_SESSION['oauth_verify']);

    // get the access token
    $reply = $cb->oauth_accessToken(array(
        'oauth_verifier' => $_GET['oauth_verifier']
    ));

    // store the token (which is different from the request token!)
    $_SESSION['oauth_token'] = $reply->oauth_token;
    $_SESSION['oauth_token_secret'] = $reply->oauth_token_secret;
	$cb->setToken($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);    
	$user_account = (array) $cb->account_settings();
	$user_screenname = $user_account[screen_name];

    $_SESSION['user_screenname'] = $user_screenname;

    // send to same URL, without oauth GET parameters
    if (strpos($prev_page,'q=') !== false){
    	header('Location: http://keepr.com' . $prev_page);
    }
    else {
    	header('Location: http://www.keepr.com/index.php?q=@' . $user_screenname);      
    }
    die();
}

// assign access token on each page load
$cb->setToken($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
?>

