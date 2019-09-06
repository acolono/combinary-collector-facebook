<?php
session_start();

require_once('Db.php');

class webhook_helper
{
    private $database;

    public function __construct()
    {
        $this->database = new Db();

    }

    function PreProcessWebhook($json_obj)
    {

        $raw_json = $json_obj;

        $json_obj = json_decode($json_obj);


        $data = $json_obj->entry[0]->changes[0];

        $idArray = $this->ExplodeId($data->value->post_id);

        $parentIdArray = $this->ExplodeId($data->value->parent_id);
        $page_id = $parentIdArray[0];
        $parent_id = $parentIdArray[1];

        if (getenv('JSON_LOGGING') === "true"){
            $this->database->AddJson("json_webhook", $raw_json, $page_id);
        }

        if ($data->value->item === "comment") {
            $commentIdArray = $this->ExplodeId($data->value->comment_id);


            $feedId = $idArray[1];

            $commentId = $commentIdArray[1];
            $from = [
                "id" => $data->from->id,
                "name" => $data->from->name
            ];

            $comment = [
                "created_time" => $this->ConvertUnixTime($json_obj->entry[0]->time),
                "message" => $data->message,
                "comment_count" => 0,
                "like_count" => 0,
                "from" => $from
            ];

            $this->database->SaveCommentData($page_id, $commentId, $comment, $feedId);
        }

        if ($data->value->item === "reaction") {

            if ($data->value->verb === "add") {

                $reaction = [
                    "page_id" => $page_id,
                    "parent_id" => $parent_id,
                    "profile_id" => $data->value->from->id,
                    "profile_name" => $data->value->from->name,
                    "type" => $data->value->reaction_type,
                    "created_time" => $this->ConvertUnixTime($data->value->created_time),
                ];

                $this->database->SaveReactionData($reaction);

            } elseif ($data->value->verb === "remove") {

                $removeArray = [
                    "parent_id" => $parent_id,
                    "profile_id" => $data->value->from->id,
                    "type" => $data->value->reaction_type
                ];

                $this->database->Remove("reaction", $removeArray);
            }
        }
    }

    function ConvertUnixTime($unixTime)
    {
        date_default_timezone_set('Europe/Vienna');
        return date('Y-m-d h:i:s', $unixTime);
    }

    function ExplodeId($id)
    {
        $splitArray = explode('_', $id);
        return $splitArray;
    }

}
