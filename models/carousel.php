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

class carousel_class extends AWS_MODEL
{

    public function get_carousel_list($only_on=null)
    {
        if($only_on == null)
            return $this->fetch_all('carousel',null,'show_index');
        else
            return $this->fetch_all('carousel','status = 1','show_index');
    }

    public function get_carousel_by_id($carousel_id)
    {
        return $this->fetch_row('carousel', ' id = '.intval($carousel_id));
    }

    public function update_carousel($carousel_id,$show_index,$title,$content,$status=0)
    {
        $this->update('carousel',array(
            'show_index'=>$show_index,
            'title' => $title,
            'content' => $content,
            'status' => $status
            ),' id = '.$carousel_id);
    }

    public function put_carousel($uid,$show_index,$url,$title=NULL,$content=NULL)
    {

        // $elite_group_id = $this->fetch_row('users_group',' group_name = "行业精英"')['group_id'];
        // return $this->fetch_all('users',' group_id = '.$elite_group_id);
        $now = time();
        $this->insert('carousel',array(
            'uid' => $uid,
            'show_index' => $show_index,
            'title' => $title,
            'content' => $content,
            'url' => $url,
            'add_time' => $now,
            'update_time' => $now
            ));
    }

    public function del_carousel($carousel_id)
    {
        $this->delete('carousel', 'id = ' . intval($carousel_id));
    }

        public function move_up($id)
    {
        $carousel_list = $this->get_carousel_list();
        foreach ($carousel_list as $key => $value) {
            if($value['id'] == $id){
                $target_key = $key;
                break;
            }
        }
        if($target_key == 0)
            return;
        else
        {
            $upper_show_index = $carousel_list[$target_key-1]['show_index'];
            $upper_id = $carousel_list[$target_key-1]['id'];

            $downner_show_index = $carousel_list[$target_key]['show_index'];
            $downner_id = $carousel_list[$target_key]['id'];
            $this->update('carousel',array('show_index'=>$downner_show_index),' id = '.$upper_id);
            $this->update('carousel',array('show_index'=>$upper_show_index),' id = '.$downner_id);
        }
    }

    public function move_down($id)
    {
        $carousel_list = $this->get_carousel_list();
        foreach ($carousel_list as $key => $value) {
            if($value['id'] == $id){
                $target_key = $key;
                break;
            }
        }
        if($carousel_list[$target_key]['id'] == end($carousel_list)['id'])
            return;
        else
        {
            $upper_show_index = $carousel_list[$target_key]['show_index'];
            $upper_id = $carousel_list[$target_key]['id'];

            $downner_show_index = $carousel_list[$target_key+1]['show_index'];
            $downner_id = $carousel_list[$target_key+1]['id'];

            $this->update('carousel',array('show_index'=>$downner_show_index),' id = '.$upper_id);
            $this->update('carousel',array('show_index'=>$upper_show_index),' id = '.$downner_id);
        }
    }
}