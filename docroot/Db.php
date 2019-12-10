<?php
require_once ('dbg.php');

class Db
{

    private $host;
    private $dbname;
    private $user;
    private $pwd;

    public function __construct()
    {
        $this->host = getenv('DB_HOST');
        $this->dbname = getenv('DB_NAME');
        $this->user = getenv('DB_USER');
        $this->pwd = getenv('DB_PWD');
    }

    public function Connect()
    {
        $dbconn = pg_connect("host=" . $this->host . " dbname=" . $this->dbname . " user=" . $this->user . " password=" . $this->pwd);

        return $dbconn;
    }

    public function Add($table_name, $array)
    {
        $res = pg_insert($this->Connect(), $table_name, $array);

        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    public function Remove($tableName, $array)
    {
        $res = pg_delete($this->Connect(), $tableName, $array);
        return $res;
    }

    public function Query($query)
    {

        $result = pg_query($this->Connect(), $query);
        return $result;
    }

    function CheckIdExists($id, $tableName)
    {
        $query = "select id from " . $tableName . " where id=" . $id;

        $row = pg_fetch_row($this->Query($query));

        if ($row[0] === $id) {
            return true;
        }

        return false;
    }

    function CheckPageIdExists($page_id)
    {
        if (!empty($page_id)) {
            $query = "select page_id from post where page_id =" . $page_id;

            $row = pg_fetch_row($this->Query($query));

            if ($row[0] === $page_id) {
                return true;
            }

            return false;
        }
    }

    function SaveCommentData($pageId, $commentId, $comment, $feedId)
    {
        if (empty($comment->message)) {
            $comment->message = "";
        }

        $commenter_id = "";
        $commenter_name = "";

        if (isset($comment->from)) {
            $commenter_id = $comment->from->id;
            $commenter_name = $comment->from->name;
        }

        $commentArray = [
            "id" => $commentId,
            "post_id" => $feedId,
            "created_time" => $comment->created_time->date,
            "message" => $comment->message,
            "comment_count" => $comment->comment_count,
            "like_count" => $comment->like_count,
            "commenter_id" => $commenter_id,
            "commenter_name" => $commenter_name,
            "page_id" => $pageId
        ];

        if (!$this->CheckIdExists($commentId, 'comment')) {
            $this->Add('comment', $commentArray);
        }
    }

    function SaveAttachmentData($pageId, $feedId, $attachment)
    {
        $mediaURL = "";
        $targetUrl = "";
        $target_id = "";

        if (empty($attachment->description)) {
            $attachment->description = "";
        }
        if (!empty($attachment->target->id)) {
            $target_id = $attachment->target->id;
        }
        if (!empty($attachment->media->image->src)) {
            $mediaURL = $attachment->media->image->src;
        }
        if (!empty($attachment->target->url)) {
            $targetUrl = $attachment->target->url;
        }

        if (isset($attachment->description_tags)) {
            $this->SaveAttachmentDescriptionTags($pageId, $feedId, $attachment->description_tags);
        }

        $attachmentArray = [
            "id" => $feedId,
            "target_id" => $target_id,
            "description" => $attachment->description,
            "media_url" => $mediaURL,
            "attachment_type" => $attachment->type,
            "target_url" => $targetUrl,
            "page_id" => $pageId
        ];

        $this->Add('attachment', $attachmentArray);
    }

    function SavePostData($post, $pageId)
    {
        if (empty($post->story)) {
            $post->story = "";
        }
        if (empty($post->message)) {
            $post->message = "";
        }

        $postArray = [
            "id" => $post->id,
            "page_id" => $pageId,
            "type" => $post->type,
            "created_time" => $post->created_time,
            "story" => $post->story,
            "message" => $post->message
        ];

        $this->Add('post', $postArray);
    }

    function SaveReactionData($reaction)
    {
        $reactionArray = [
            "page_id" => $reaction['page_id'],
            "parent_id" => $reaction['parent_id'],
            "profile_id" => $reaction['profile_id'],
            "profile_name" => $reaction['profile_name'],
            "type" => $reaction['type'],
            "created_time" => $reaction['created_time'],
        ];
        $this->Add('reaction', $reactionArray);
    }

    function SaveAttachmentDescriptionTags($pageId, $feedId, $attachmentDescriptionTags)
    {

        foreach ($attachmentDescriptionTags as $description_tag) {

            $descriptionTagArray = [
                "post_id" => $feedId,
                "person_id" => $description_tag->id,
                "person_name" => $description_tag->name,
                "type" => $description_tag->type,
                "page_id" => $pageId
            ];

            $this->Add('description_tags', $descriptionTagArray);
        }
    }

    function ContentExists($page_list)
    {
        foreach ($page_list as $pageId) {
            if ($this->CheckPageIdExists($pageId)) {
                return true;
            }
        }

        return false;
    }

    function DeleteAll($pageId)
    {
        $res_1 = pg_delete($this->Connect(), 'attachment', ['page_id' => $pageId]);
        $res_2 = pg_delete($this->Connect(), 'comment', ['page_id' => $pageId]);
        $res_3 = pg_delete($this->Connect(), 'description_tags', ['page_id' => $pageId]);
        $res_4 = pg_delete($this->Connect(), 'reaction', ['page_id' => $pageId]);
        $res_5 = pg_delete($this->Connect(), 'post', ['page_id' => $pageId]);
        $res_6 = pg_delete($this->Connect(), 'json_api_call', ['page_id' => $pageId]);
        $res_7 = pg_delete($this->Connect(), 'json_webhook', ['page_id' => $pageId]);

        if ($res_1 === TRUE && $res_2 === TRUE && $res_3 === TRUE && $res_4 === TRUE && $res_5 === TRUE && $res_6 === TRUE && $res_7 === TRUE) {
            echo "<div class='d-flex justify-content-center'>";
            echo "Successfully deleted all records";
            echo "</div>";
        } else {
            echo "<div class='d-flex justify-content-center'>";
            echo "Unable to delete records";
            echo "</div>";
        }
    }

    function AddJson($tableName, $rawJson, $pageId) {
        if(!is_int($pageId)) $pageId=0;
        $conn = $this->Connect();
        $query = "INSERT INTO ". pg_escape_identifier($conn,$tableName) . " (raw, page_id) VALUES($1, $2)";
        $result = pg_query_params($conn, $query, [$rawJson, $pageId]);

        if ($result) {
            return true;
        } else {
            dbg(['query'=>[$query],[$rawJson, $pageId]]);
            dbg(['pgError'=>pg_last_error($conn)]);
            return false;
        }
    }

}
