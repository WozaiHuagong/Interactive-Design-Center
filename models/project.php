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

class project_class extends AWS_MODEL
{

    public function get_working_list($num = null)
    {
        return $this->model('article')->get_articles_list_by_topic_title("在研项目",$num);
    }

    public function get_finished_list($num = null)
    {
        return $this->model('article')->get_articles_list_by_topic_title("研究成果",$num);
    }
}