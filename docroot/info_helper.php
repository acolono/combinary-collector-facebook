<?php

require_once 'Db.php';

class info_helper
{
    private $database;
    private $user_page_list;
    private $facebook;

    public function __construct()
    {
        $this->database = new Db();
        $this->facebook = new FB();
        $this->user_page_list = $this->facebook->GetUserPages();
    }

    public function GetLatestComment($filter)
    {
        $all_user_ids = [];
        $all_comments = [];

        $query = 'SELECT id FROM post WHERE page_id =' . $filter;
        $all = pg_fetch_all($this->database->Query($query));
        if (!empty($all)) {
            foreach ($all as $post_id) {
                array_push($all_user_ids, $post_id);
            }
        }

        foreach ($all_user_ids as $id) {
            $query = 'SELECT created_time, message FROM comment WHERE post_id =' . $id['id'];
            $comment = pg_fetch_row($this->database->Query($query));
            if (!empty($comment)) {
                array_push($all_comments, [
                    'created_time' => $comment[0],
                    'message' => $comment[1]
                ]);
            }
        }

        return $all_comments[0]['message'];
    }

    public function GetLatestPost($filter)
    {
        $query = 'SELECT story, message FROM post WHERE page_id =' . $filter . ' order by created_time desc';
        $all = pg_fetch_all($this->database->Query($query));
        if (!empty($all)) {
            foreach ($all as $latest_post) {
                if (!empty($latest_post['message'])) {
                    return $latest_post['message'];
                }
            }
        }

        $query = 'select message from post order by created_time desc';
        $row = pg_fetch_row($this->database->Query($query));
        return $row[0];
    }

    public function GetMostLiked($filter)
    {
        $likes = [];
        $like_count = 0;
        $highest_like_comment = '';

        $query = 'SELECT id FROM post WHERE page_id =' . $filter;
        $all = pg_fetch_all($this->database->Query($query));
        if (!empty($all)) {
            foreach ($all as $post_id) {
                $query = 'SELECT message, like_count FROM comment WHERE post_id =' . $post_id['id'];
                $like = pg_fetch_row($this->database->Query($query));
                if (!empty($like)) {
                    array_push($likes,
                        [
                            'message' => $like[0],
                            'total_likes' => $like[1]
                        ]);
                }
            }
        }

        foreach ($likes as $like) {
            if ($like['total_likes'] > $like_count) {
                $like_count = $like['total_likes'];
                $highest_like_comment = $like['message'];
            }
        }

        return $highest_like_comment . ' [' . $like_count . ']';
    }

    public function GetMostCommented($filter)
    {

        $comments = [];
        $comment_count = 0;
        $highest_commented = '';

        $query = 'SELECT id FROM post WHERE page_id =' . $filter;
        $all = pg_fetch_all($this->database->Query($query));
        if (!empty($all)) {
            foreach ($all as $post_id) {
                $query = 'SELECT message, comment_count FROM comment WHERE post_id =' . $post_id['id'];
                $comment = pg_fetch_row($this->database->Query($query));
                if (!empty($comment)) {
                    array_push($comments,
                        [
                            'message' => $comment[0],
                            'total_comments' => $comment[1]
                        ]);
                }
            }
        }

        foreach ($comments as $comment) {
            if ($comment['total_comments'] > $comment_count) {
                $comment_count = $comment['total_comments'];
                $highest_commented = $comment['message'];
            }
        }

        return $highest_commented . ' [' . $comment_count . ']';

    }

    public function GetMedia($filter)
    {

        $post_id_time = [];

            $query = 'SELECT id, created_time FROM post WHERE page_id =' . $filter . ' order by created_time desc';
            $all = pg_fetch_all($this->database->Query($query));
            if (!empty($all)) {
                foreach ($all as $id) {
                    array_push($post_id_time, [
                        'created_time' => $id['created_time'],
                        'id' => $id['id']
                    ]);
                }

            }
        arsort($post_id_time);

        $query = 'SELECT media_url FROM attachment WHERE id=' . $post_id_time[0]['id'];
        $url = pg_fetch_row($this->database->Query($query));
        if (!empty($url))
            return $url[0];

    }

    public function GetTotalLikes($filter)
    {

        $like_counter = 0;

        $query = 'SELECT id FROM post WHERE page_id =' . $filter;
        $all = pg_fetch_all($this->database->Query($query));
        if (!empty($all)) {
            foreach ($all as $post_id) {
                $query = 'SELECT like_count FROM comment WHERE post_id =' . $post_id['id'];
                $like = pg_fetch_row($this->database->Query($query));
                if (!empty($like)) {
                    $like_counter += (int)$like[0];
                }
            }
        }

        $reactionQuery = 'SELECT id FROM reaction WHERE type = \'like\' AND page_id='.$filter;
        $all_reaction = pg_fetch_all($this->database->Query($reactionQuery));
        if (!empty($all_reaction)) {
            $numberReactionLikes = count($all_reaction);
            $like_counter += $numberReactionLikes;
        }

        return $like_counter;
    }

    public function GetTotalComments($filter)
    {
        $comment_counter = 0;

        $query = 'SELECT id FROM post WHERE page_id =' . $filter;
        $all = pg_fetch_all($this->database->Query($query));
        if (!empty($all)) {
            foreach ($all as $post_id) {
                $query = 'SELECT comment_count FROM comment WHERE post_id =' . $post_id['id'];
                $comment_count = pg_fetch_row($this->database->Query($query));
                $comment_counter = $comment_counter + (int)$comment_count[0];
            }
            $query = 'SELECT count(*) from comment where page_id=' . $filter;
            $page_comments_count = pg_fetch_row($this->database->Query($query));
            $comment_counter += (int)$page_comments_count[0];
        }

        if ($comment_counter > 0) {

            echo '<h3>Total comments...</h3>';
            echo '<p>' . $comment_counter . '</p>';

        }
    }

}