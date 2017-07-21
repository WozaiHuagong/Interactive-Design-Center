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

class project extends AWS_CONTROLLER
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

    public function working_action()
    {
        if (isset($_GET['number'])) $list = $this->model('project')->get_working_list(intval($_GET['number']));
        else $list = $this->model('project')->get_working_list();
        foreach ($list as $lib) {
            $tmp=[];
            $tmp["title"] = $lib["title"];
            $tmp["id"] = $lib["id"];
            $tmp["summary"] = $lib["summary"];
            $tmp["message"] = $lib["message"];
            $tmp["publish_time"] = $lib["publish_time"];
            $tmp["view_num"] = $lib["views"];
            $tmp["type"] = 'projectWroking';
            $data[]=$tmp;
        }
        $result=array(
            "error" => 0,
            "eMsg" => "",
            "data" => $data);
        //echo json_encode($result,JSON_UNESCAPED_UNICODE);
        H::ajax_json_output($result);
    }

    public function finished_action()
    {
        if (isset($_GET['number'])) $list = $this->model('project')->get_finished_list(intval($_GET['number']));
        else $list = $this->model('project')->get_finished_list();
        foreach ($list as $lib) {
            $tmp=[];
            $tmp["title"] = $lib["title"];
            $tmp["id"] = $lib["id"];
            $tmp["summary"] = $lib["summary"];
            $tmp["message"] = $lib["message"];
            $tmp["publish_time"] = $lib["publish_time"];
            $tmp["view_num"] = $lib["views"];
            $tmp["type"] = 'projectFinished';
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