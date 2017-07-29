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
            'working',
            'finished'
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
        foreach ($list as $working) {
            $tmp=[];
            $tmp["title"] = $working["title"];
            $tmp["id"] = $working["id"];
            $tmp["summary"] = $working["summary"];
            $tmp["message"] = $working["message"];
            $tmp["publish_time"] = $working["publish_time"];
            $tmp["view_num"] = $working["views"];
            $tmp["type"] = 'projectWroking';
            $attach = $this->model('p ublish')->get_attach('article',$working['id']);
            $attach_list =array();
            foreach ($attach as $key => $value) {
                $attach_list[] = $value["attachment"];
            }
            $tmp["attach"] = $attach_list;
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
        foreach ($list as $finished) {
            $tmp=[];
            $tmp["title"] = $finished["title"];
            $tmp["id"] = $finished["id"];
            $tmp["summary"] = $finished["summary"];
            $tmp["message"] = $finished["message"];
            $tmp["publish_time"] = $finished["publish_time"];
            $tmp["view_num"] = $finished["views"];
            $tmp["type"] = 'projectFinished';
            $attach = $this->model('p ublish')->get_attach('article',$finished['id']);
            $attach_list =array();
            foreach ($attach as $key => $value) {
                $attach_list[] = $value["attachment"];
            }
            $tmp["attach"] = $attach_list;
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