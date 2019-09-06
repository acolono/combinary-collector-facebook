<html>
<head>
    <title>Facebook Combinary Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css"
          integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
            integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
            crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js"
            integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut"
            crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js"
            integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k"
            crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/main.css">
    <link rel="icon" type="image/png" href="images/combinary.ico">
    <link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet">
</head>
<body>
<?php
session_start();
require_once __DIR__ . '../../vendor/facebook/graph-sdk/src/Facebook/autoload.php'; // change path as needed
require_once('FB.php');
if (empty($_SESSION['fb_access_token'])) {

$fb = new FB();

$helper = $fb->fb->getRedirectLoginHelper();

$permissions = ['manage_pages']; // Optional permissions
    if (getenv('DEVELOPMENT') === "true"){
        $loginUrl = $helper->getLoginUrl(getenv('DEV_URL').'fb-callback.php', $permissions);
    } else {
        $loginUrl = $helper->getLoginUrl(getenv('LIVE_URL').'fb-callback.php', $permissions);
    }
?>
<div class="d-flex justify-content-center">
    <img id="CombinaryLogo" src="images/combinary-icon.png"/>
</div>


<div class="d-flex justify-content-center">
    <a href=<?= htmlspecialchars($loginUrl) ?> role="button" class="btn btn-primary"><img
                src="images/facebook-icon.png" id="FacebookIcon"/>Login with Facebook!</a>
</div>
<?php
} else {
    if (getenv('DEVELOPMENT') === "true"){
        header('Location: '.getenv('DEV_URL').'monitor.php');
    } else {
        header('Location: '.getenv('LIVE_URL').'monitor.php');
    }
}
?>
</body>

</html>
