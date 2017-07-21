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

class carousel extends AWS_CONTROLLER
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
        foreach ($this->model('carousel')->get_carousel_list(True) as $carousel) {
            $tmp=[];
            $tmp["title"] = $carousel["title"];
            $tmp["content"] = $carousel["content"];
            $tmp["show_index"] = $carousel["show_index"];
            $tmp["url"] = $carousel["url"];
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