<?php

require_once('FB.php');
require_once('Db.php');

class fb_helper
{
    private $facebook;
    private $database;

    public function __construct()
    {
        $this->facebook = new FB();
        $this->database = new Db();
    }

    function SavePostsOnPage($pageId)
    {
        $query = '/' . $pageId . '/feed/?fields=message,story,created_time,status_type,id,comments{from,message,like_count,comment_count,created_time,comments{from,like_count,comment_count,message,created_time}},attachments,likes,reactions';

        try{
        $response = $this->facebook->fb->get($query, $this->facebook->GetAccessToken());


        $graphNode = $response->getGraphEdge();

        if ($graphNode !== null) {
            if (getenv('JSON_LOGGING') === "true"){
                $this->database->AddJson("json_api_call", $graphNode->asJson(), $pageId);
            }
        }


        }catch (\Facebook\Exceptions\FacebookSDKException $e){
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        }

        while (true) {
            $graphNode = $this->ProcessItems($graphNode, $pageId);


            if ($graphNode === null) {
                return false;
            }

            if (getenv('JSON_LOGGING') === "true") {
                $this->database->AddJson("json_api_call", $graphNode->asJson(), $pageId);
            }

            sleep(1);

        }

        return true;
    }


    function ProcessItems($graphNode, $pageId)
    {
        foreach ($graphNode as $item) {
            $post = json_decode($item);

            //id is made up of pageId_postId. We can remove the
            // postId as we have the pageId and it fits as a bigint
            $post->id = str_replace($pageId . '_', '', $post->id);
            $this->database->SavePostData($post, $pageId);

            if (isset($post->comments)) {
                foreach ($post->comments as $comment) {
                    $this->database->SaveCommentData($pageId, $this->UniqueIdGenerator($comment->id), $comment, $post->id);
                    if ($comment->comment_count > 0) {
                        foreach ($comment->comments as $commentReplies) {
                            $this->database->SaveCommentData($pageId, $this->UniqueIdGenerator($commentReplies->id), $commentReplies, $post->id);
                        }
                    }
                }
            }

            if (isset($post->attachments)) {
                foreach ($post->attachments as $attachment) {
                    $this->database->SaveAttachmentData($pageId, $post->id, $attachment);
                    if (isset($attachment->subattachments)) {
                        foreach ($attachment->subattachments as $subAttachment) {
                            $this->database->SaveAttachmentData($pageId, $post->id, $subAttachment);
                        }
                    }
                }
            }
        }

        try{
            $nextNode = $this->facebook->fb->next($graphNode);
        }catch (\Facebook\Exceptions\FacebookSDKException $e){
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        }

        return $nextNode;
    }

    function UniqueIdGenerator($replaceId)
    {
        $uniqueIdArray = explode('_', $replaceId);
        if (!empty($uniqueIdArray[1])) {
            return $uniqueIdArray[1];
        } else return '';
    }

}