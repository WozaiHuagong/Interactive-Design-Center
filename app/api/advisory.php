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

class advisory extends AWS_CONTROLLER
{

    public function get_access_rule()
    {
        $rule_action['rule_type'] = 'white';

        $rule_action['actions'] = array(
            'libInfo',
            'industryInfo'
        );

        return $rule_action;
    }

    public function setup()
    {
        HTTP::no_cache_header();
    }

    public function libInfo_action()
    {
        if (isset($_GET['number'])) $list = $this->model('advisory')->get_lib_info_list(intval($_GET['number']));
        else                        $list = $this->model('advisory')->get_lib_info_list();
        foreach ($list as $lib) {
            $tmp=[];
            $tmp["title"] = $lib["title"];
            $tmp["id"] = $lib["id"];
            $tmp["summary"] = $lib["summary"];
            $tmp["message"] = $lib["message"];
            $tmp["publish_time"] = $lib["publish_time"];
            $tmp["view_num"] = $lib["views"];
            $tmp["type"] = 'libInfo';
            $attach = $this->model('publish')->get_attach('article',$lib['id']);
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

    public function industryInfo_action()
    {
        if (isset($_GET['number'])) $list = $this->model('advisory')->get_industry_info_list(intval($_GET['number']));
        else                        $list = $this->model('advisory')->get_industry_info_list();
        foreach ($list as $industry) {
            $tmp=[];
            $tmp["title"] = $industry["title"];
            $tmp["id"] = $industry["id"];
            $tmp["summary"] = $industry["summary"];
            $tmp["message"] = $industry["message"];
            $tmp["publish_time"] = $industry["publish_time"];
            $tmp["view_num"] = $industry["views"];
            $tmp["type"] = 'industryInfo';
            $attach = $this->model('publish')->get_attach('article',$industry['id']);
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