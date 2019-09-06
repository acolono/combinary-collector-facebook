<?php
session_start();

require_once __DIR__ . '../../vendor/facebook/graph-sdk/src/Facebook/autoload.php'; // change path as needed
require_once('fb_helper.php');
require_once('info_helper.php');

$fb_helper = new fb_helper();
$facebook = new FB();
$database = new Db();
$info = new info_helper();
//array used to check if the pages exist for a particular user
$page_list = [];
?>

<html lang="en">
<head>
    <title>Facebook Combinary</title>
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
    <script>
        function ChangeCheckbox(elementId) {
            document.getElementById(elementId).checked = true;
        }

        window.fbAsyncInit = function () {
            FB.init({
                appId: <?= getenv('APP_ID') ?>,
                autoLogAppEvents: true,
                xfbml: true,
                version: 'v3.2'
            });
        };

        (function (d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) {
                return;
            }
            js = d.createElement(s);
            js.id = id;
            js.src = "https://connect.facebook.net/en_US/sdk.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    </script>

</head>
<body>
<div>
    <div class="d-flex justify-content-center">
        <img id="CombinaryLogo" src="images/combinary-icon.png"/>
    </div>
    <div class="d-flex justify-content-center">
        <h3>Manage Pages</h3>
    </div>
    <div class="d-flex justify-content-center">
        <p>To add more pages logout and log back in and select the page you would like to import.</p>
    </div>
    <div class="d-flex justify-content-center">
        <form action="" METHOD="POST">
            <?php
            foreach ($facebook->GetUserPages() as $page) {
                echo "<div>";
                echo "<input type='checkbox' id='$page->id' name='page_list[]' value='$page->id,$page->name'>";
                echo "<label for='page'>" . $page->name . "</label>";
                echo "</div>";
                array_push($page_list, $page->id);
                if ($database->CheckPageIdExists($page->id)) {
                    ?>
                    <script>
                        ChangeCheckbox(<?= $page->id ?>);
                    </script>
                    <?php
                }
            }
            ?>
            <button class="btn btn-secondary" type="submit" name="unsubscribe">Unsubscribe from Webhooks</button>
            <button class="btn btn-secondary" type="submit" name="delete_all">Delete all page data</button>
            <button class="btn btn-secondary" type="submit" name="submit">Monitor Pages</button>
            <button class="btn btn-secondary" name="logout">Logout</button>
        </form>
    </div>

<?php

if (isset($_POST['submit'])){
    if (getenv('DEVELOPMENT') === "true"){
        header('Location: '.getenv('DEV_URL').'monitor.php', true, 302);
    } else {
        header('Location: '.getenv('LIVE_URL').'monitor.php', true, 302);
    }
    exit;
}

if (isset($_POST['unsubscribe'])){

    if (!empty($_POST['page_list'])) {
        foreach ($_POST['page_list'] as $page) {
            $pageArray = explode(',', $page);
            $facebook->UnsubscribeWebhooks($pageArray[0]);
        }
    }
}

if (isset($_POST['delete_all'])){

    if (!empty($_POST['page_list'])) {
        foreach ($_POST['page_list'] as $page) {
            $pageArray = explode(',', $page);
            $database->DeleteAll($pageArray[0]);
        }
    }
}

if (isset($_POST['manage'])){
    if (getenv('DEVELOPMENT') === "true"){
        header('Location: '.getenv('DEV_URL').'manage.php', true, 302);
    } else {
        header('Location: '.getenv('LIVE_URL').'manage.php', true, 302);
    }
    exit;
}

if (isset($_POST['logout'])) {
    ?>
    <script>
        FB.logout(function (response) {

        });

    </script>
    <?php
    if ($facebook->DestroyAccessToken()) {
        $_SESSION['fb_access_token'] = "";
        if (getenv('DEVELOPMENT') === "true"){
            header('Location: '.getenv('DEV_URL').'index.php', true, 302);
        } else {
            header('Location: '.getenv('LIVE_URL').'index.php', true, 302);
        }
        exit;
    }
}

?>

</html>
