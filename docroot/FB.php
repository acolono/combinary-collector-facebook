<?php

class FB
{
    private $app_id;
    private $secret;
    private $graph_version;
    private $access_token;
    private $verify_token;
    private $endpoint_access_token;

    public $fb;
    public $webhook_helper;

    public function __construct()
    {
        $this->app_id = getenv('APP_ID');
        $this->secret = getenv('APP_SECRET');
        $this->graph_version = getenv('GRAPH_VERSION');
        $this->verify_token = getenv('VERIFY_TOKEN');
        $this->endpoint_access_token = getenv('ENDPOINT_ACCESS_TOKEN');
        try {
            $this->fb = new \Facebook\Facebook([
                'app_id' => $this->app_id,
                'app_secret' => $this->secret,
                'default_graph_version' => $this->graph_version,
            ]);
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            echo $e->getMessage();
            exit;
        }

        if (isset($_SESSION['fb_access_token'])) {
            $this->access_token = $_SESSION['fb_access_token'];
        }


    }

    public function GetAccessToken()
    {
        return $this->access_token;
    }

    public function DestroyAccessToken()
    {
        $this->access_token = "";
        return true;
    }

    public function GetEndPointAccessToken()
    {
        return $this->endpoint_access_token;
    }

    public function GetVerifyToken()
    {
        return $this->verify_token;
    }

    function WebhookStatus($pageId){
        $query = '/'.$pageId.'?fields=is_webhooks_subscribed';

        try {
            $statusQuery = $this->fb->get($query, $this->GetAccessToken());
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Unauthorized response exception please contact the application developer. Details: '. $e->getMessage();
            exit;
        }

        $statusNode = $statusQuery->getGraphNode();
        $status = $statusNode->getField('is_webhooks_subscribed');
        return $status;

    }

    function SubscribeToWebhooks($pageId)
    {

        $query = '/'.$pageId.'?fields=access_token';
        $pageAccessTokenQuery = $this->fb->get($query, $this->GetAccessToken());
        $pageAccessTokenNode = $pageAccessTokenQuery->getGraphNode();
        $pageAccessToken = $pageAccessTokenNode->getField('access_token');

        try {

            $response = $this->fb->post('/' . $pageId . '/subscribed_apps',
                [
                    'subscribed_fields' => 'feed'
                ],
                $pageAccessToken
            );

        } catch (Facebook\Exceptions\FacebookAuthorizationException $e) {
            echo 'Unauthorized exception please contact the application developer. Details: '. $e->getMessage();
            exit;
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Unauthorized response exception please contact the application developer. Details: '. $e->getMessage();
            exit;
        }

        $result = $response->getGraphNode();

        $success = $result->getField('success');

        if ($success === true) {
            echo "<div class='d-flex justify-content-center'>";
            echo 'Successfully subscribed';
            echo "</div>";
            return true;
        }

        return $success;
    }

    function SubscribeAppToWebhooks()
    {

        if (getenv('DEVELOPMENT') === "true") {
            $callback_url = getenv('DEV_URL').'webhooks.php';
        } else {
            $callback_url = getenv('LIVE_URL').'webhooks.php';
        }
        try {
            $response = $this->fb->post(getenv('APP_ID') . '/subscriptions',
                [
                    'object' => 'page',
                    'callback_url' => $callback_url,
                    "fields" => "feed",
                    "verify_token" => $this->verify_token,
                ],
                $this->endpoint_access_token
            );
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        $result = $response->getGraphNode();
        $success = $result->getField('success');

        return $success;

    }

    function UnsubscribeWebhooks($page_id){
        $query = $page_id.'/subscribed_apps';
        try {
            $response = $this->fb->delete($query,
                [
                    "verify_token" => $this->verify_token,
                ],
                $this->endpoint_access_token
            );

            $result = $response->getGraphNode();
            $success = $result->getField('success');

            if ($success === true) {
                echo "<div class='d-flex justify-content-center'>";
                echo 'Successfully unsubscribed';
                echo "</div>";
                return true;
            }
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            echo "<div class='d-flex justify-content-center'>";
            echo $e->getMessage();
            echo "</div>";
            exit;
        }
    }

    function GetUserPages()
    {
        $query = '/me/accounts';
        try {
            $response = $this->fb->get($query, $this->access_token);
            $result = $response->getGraphEdge();
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            echo $e->getMessage();
            exit;
        }

        $pages = json_decode($result);

        return $pages;

    }

}
