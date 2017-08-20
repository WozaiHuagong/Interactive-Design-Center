<?php
/*
+--------------------------------------------------------------------------
|   WeCenter [#RELEASE_VERSION#]
|   ========================================
|   by WeCenter Software
|   © 2011 - 2014 WeCenter. All Rights Reserved
|   http://www.wecenter.com
|   ========================================
|   Support: WeCenter@qq.com
|
+---------------------------------------------------------------------------
*/

define('IN_AJAX', TRUE);


if (!defined('IN_ANWSION'))
{
    die;
}

class elite extends AWS_CONTROLLER
{

    public function get_access_rule()
    {
        $rule_action['rule_type'] = 'white';

        $rule_action['actions'] = array(
            'list'
        );

        return $rule_action;
    }

    public function setup()
    {
        HTTP::no_cache_header();
    }

    public function list_action()
    {
        $data = array();
        foreach ($this->model('elite')->get_elite_articles() as $article) {
            $tmp=[];
            $tmp["id"] = $article["id"];
            $tmp["title"] = $article["title"];
            $tmp["message"] = $article["message"];
            $tmp["view_num"] = $article["views"];
            $tmp["publish_time"] = $article["add_time"];
            $tmp["type"] = "elite";
            $tmp["honor"] = $article["summary"];
            $attach = $this->model('publish')->get_attach('article',$article['id']);
            if (!empty($attach))
                $tmp["avatar"] = current($attach)['attachment'];
            // $user_info = $this->model("account")->get_user_info_by_uid($article["uid"],True);
            // $tmp["user_info"] = array(
            //     "uid" => $user_info["uid"],
            //     "name" => $user_info["user_name"],
            //     "avatar" => get_avatar_url($article["uid"],'max'),
            //     "honor" => $user_info["signature"]
            //     );
            $data[]=$tmp;
        }
        $result=array(
            "error" => 0,
            "eMsg" => "",
            "data" => $data);
        //echo json_encode($result,JSON_UNESCAPED_UNICODE);
        H::ajax_json_output($result);
    }

}