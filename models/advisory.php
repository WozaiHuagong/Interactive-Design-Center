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


if (!defined('IN_ANWSION'))
{
    die;
}

class advisory_class extends AWS_MODEL
{

    public function get_lib_info_list($num = null)
    {
        return $this->model('article')->get_articles_list_by_topic_title("研究中心资讯",$num);
    }

    public function get_industry_info_list($num = null)
    {
        return $this->model('article')->get_articles_list_by_topic_title("行业资讯",$num);
    }
}