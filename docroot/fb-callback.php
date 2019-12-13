<?php
session_start();
require_once __DIR__ . '../../vendor/facebook/graph-sdk/src/Facebook/autoload.php'; // change path as needed
require_once 'dbg.php';

if (empty($_SESSION['fb_access_token'])) {

    try {
        $fb = new Facebook\Facebook([
            'app_id' => getenv('APP_ID'),
            'app_secret' => getenv('APP_SECRET'),
            'default_graph_version' => getenv('GRAPH_VERSION'),
        ]);
    } catch (Facebook\Exceptions\FacebookSDKException $e) {
        // When validation fails or other local issues
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;
    }

    $helper = $fb->getRedirectLoginHelper();

    try {
        $accessToken = $helper->getAccessToken();
    } catch (Facebook\Exceptions\FacebookSDKException $e) {
        // When validation fails or other local issues
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;
    }

    if (!isset($accessToken)) {
        if ($helper->getError()) {
            header('HTTP/1.0 401 Unauthorized');
            echo "Error: " . $helper->getError() . "\n";
            echo "Error Code: " . $helper->getErrorCode() . "\n";
            echo "Error Reason: " . $helper->getErrorReason() . "\n";
            echo "Error Description: " . $helper->getErrorDescription() . "\n";
        } else {
            header('HTTP/1.0 400 Bad Request');
            echo 'Bad request';
        }
        exit;
    }

// The OAuth 2.0 client handler helps us manage access tokens
    $oAuth2Client = $fb->getOAuth2Client();

// Get the access token metadata from /debug_token
    $tokenMetadata = $oAuth2Client->debugToken($accessToken);

// Validation (these will throw FacebookSDKException's when they fail)
    $tokenMetadata->validateAppId(getenv('APP_ID')); // Replace {app-id} with your app id
// If you know the user ID this access token belongs to, you can validate it here
    $tokenMetadata->validateExpiration();

    if (!$accessToken->isLongLived()) {
        // Exchanges a short-lived access token for a long-lived one
        try {
            $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            echo "<p>Error getting long-lived access token: " . $e->getMessage() . "</p>";
            exit;
        }
    }

    $_SESSION['fb_access_token'] = (string)$accessToken;
}
// User is logged in with a long-lived access token.
// You can redirect them to a members-only page.
if (getenv('DEVELOPMENT') === "true"){
    header('Location: '.getenv('DEV_URL').'monitor.php');
} else {
    header('Location: '.getenv('LIVE_URL').'monitor.php');
}
exit;
