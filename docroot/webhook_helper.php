<?php
ob_start();
session_start();
require_once ('dbg.php');
require_once ('Db.php');
date_default_timezone_set('UTC');

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
        dbg(['webhookJson'=>$json_obj]);

        $data = $json_obj->entry[0]->changes[0];

        $idArray = $this->ExplodeId($data->value->post_id);

        $parentIdArray = $this->ExplodeId($data->value->parent_id);
        $page_id = $parentIdArray[0];
        $parent_id = $parentIdArray[1];

        if (getenv('JSON_LOGGING') === "true"){
            $logged = $this->database->AddJson("json_webhook", $raw_json, $page_id);
            dbg(['value->item'=>$data->value->item]);
        }

        if ( $data->value->item === "status" ) {

                $postArray = [
                        'id' => $idArray[1],
                        'page_id' => $idArray[0],
                        'type' => $data->value->item,
                        'created_time' => $this->ConvertUnixTime($data->value->created_time),
                        'story' => '',
                        'message' => $data->value->message
                ];

                $post = (object) $postArray;
                $this->database->SavePostData( $post, $post->page_id );
                return;
        }

        if ( $data->value->item === "post" ) {
                if ( $data->value->verb === "remove" ) {
                        $removeArray = [
                                "id" => $idArray[1]
                        ];
                }
                $this->database->Remove("post", $removeArray);
                return;
        }
        
        if ($data->value->item === "comment") {

                $commentIdArray = $this->ExplodeId($data->value->comment_id);
                $feedId = $idArray[1];
                $commentId = $commentIdArray[1];

                if ($data->value->verb === "add") {

                        $comment = [
                                "created_time" => [
                                        "date" => $this->ConvertUnixTime($data->value->created_time)
                                ],
                                "message" => $data->value->message,
                                "comment_count" => 0,
                                "like_count" => 0,
                                "from" => [
                                        "id" => $data->value->from->id,
                                        "name" => $data->value->from->name
                                ]
                        ];

                        $comment = json_decode(json_encode($comment));
                        $this->database->SaveCommentData($page_id, $commentId, $comment, $feedId);

                } elseif ($data->value->verb === "remove") {

                        $removeArray = [
                                "id" => $commentId
                        ];
                        $this->database->Remove("comment", $removeArray);

                }

                return;
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
            return;
        }

        dbg(['ignoring'=>$data]);
    }

    function ConvertUnixTime($unixTime)
    {
        return date("c", $unixTime);
    }

    function ExplodeId($id)
    {
        $splitArray = explode('_', $id);
        return $splitArray;
    }

}
