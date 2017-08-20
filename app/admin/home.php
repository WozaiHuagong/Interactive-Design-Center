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

class home extends AWS_ADMIN_CONTROLLER
{
    public function setup()
    {
        $this->crumb(AWS_APP::lang()->_t('轮播图管理'), "admin/home/list/");

        if (!$this->user_info['permission']['is_administortar'])
        {
            H::redirect_msg(AWS_APP::lang()->_t('你没有访问权限, 请重新登录'), '/');
        }

        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(343));
    }

    public function list_action()
    {
        // TPL::assign('list', json_decode($this->model('system')->build_home_json('question'), true));
        // $list = json_decode($this->model('system')->build_home_json('question'), true);

        //echo '=============';
        //p($list);
        //TPL::assign('category_option', $this->model('system')->build_category_html('question', 0, 0, null, false));
        //TPL::assign('target_category', $this->model('system')->build_category_html('question', 0, null));

        TPL::assign('list',$this->model('carousel')->get_carousel_list());
        TPL::output('admin/home/list');
    }

    public function edit_action()
    {
        if (!$category_info = $this->model('carousel')->get_carousel_by_id($_GET['carousel_id']))
        {
            H::redirect_msg(AWS_APP::lang()->_t('指定分类不存在'), '/admin/home/list/');
        }
        //p($category_info);
        TPL::assign('category', $category_info);
        TPL::assign('pid', $category_info['parent_id']);
        //TPL::assign('category_option', $this->model('system')->build_home_html($category_info['type'],0, $category_info['parent_id'], null, false));
        TPL::output('admin/home/edit');
    }

    public function move_up_action()
    {
        if(isset($_GET['id']))
            $this->model('carousel')->move_up($_GET['id']);
        $this->list_action();
    }

    public function move_down_action()
    {
        if(isset($_GET['id']))
            $this->model('carousel')->move_down($_GET['id']);
        $this->list_action();
    }

}