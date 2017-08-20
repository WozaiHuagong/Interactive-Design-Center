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

class elite_class extends AWS_MODEL
{

    public function get_elite_articles($num = null)
    {
        return $this->model('article')->get_articles_list_by_topic_title("行业精英",$num);
    }

    public function bind_elite_articles_to_users($article_id,$user_id)
    {
        $this->update('article',array('uid'=>$user_id),' id = '.$article_id);
    }

    public function get_elites()
    {
        $elite_group_id = $this->fetch_row('users_group',' group_name = "行业精英"')['group_id'];
        return $this->fetch_all('users',' group_id = '.$elite_group_id);
    }

    public function get_next_show_index()
    {
        return end($this->model('article')->get_articles_list_by_topic_title("行业精英"))['show_index']+1;
    }

    public function get_elite_article_by_id($id)
    {
        foreach ($this->model('article')->get_articles_list_by_topic_title("行业精英") as $key => $article) {
            if($article['id'] = $id)
                return $article;
        }
        return null;
    }


}