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

if (! defined('IN_ANWSION'))
{
    die();
}

class abc extends AWS_ADMIN_CONTROLLER
{
    public function setup()
    {
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(310));
    }

    public function index_action()
    {
        $this->crumb(AWS_APP::lang()->_t('abc管理'), 'admin/abc/list/');
        TPL::assign('list',$this->model('abc')->get_question_list());
        TPL::output('admin/abc/list');
    }
    public function post_action(){
        foreach ($_POST['ids'] as $id) {
            $toShow=[];
            $_POST['SY'][$id]?$toShow['SY']=$id:$toShow['SY']=NULL;
            $_POST['WD'][$id]?$toShow['WD']=$id:$toShow['WD']=NULL;
            $_POST['WZ'][$id]?$toShow['WZ']=$id:$toShow['WZ']=NULL;
            $this->model('abc')->update_question_toShow($id,$toShow);
        }
        H::ajax_json_output(AWS_APP::RSM(array(
            'url' => get_js_url('/admin/abc/')
        ), 1, null));
    }
    public function c_action()
    {

        if ($report_list = $this->model('abc')->get_report_list('status = ' . intval($_GET['status'])." AND type = 'C'", $_GET['page'], $this->per_page))
        {
            $report_total = $this->model('abc')->found_rows();

            $userinfos = $this->model('account')->get_user_info_by_uids(fetch_array_value($report_list, 'uid'));

            foreach ($report_list as $key => $val)
            {
                $report_list[$key]['user'] = $userinfos[$val['uid']];
            }
        }
        $this->crumb(AWS_APP::lang()->_t('用户留言'), 'admin/abc/c/');

        TPL::assign('list', $report_list);
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(310));

        TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
            'base_url' => get_js_url('/admin/question/report_list/status-' . intval($_GET['status'])),
            'total_rows' => $report_total,
            'per_page' => $this->per_page
        ))->create_links());

        TPL::output('admin/abc/report_list');
    }

    public function report_manage_action()
    {
        if (! $_POST['report_ids'])
        {
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('请选择内容进行操作')));
        }

        if (! $_POST['action_type'])
        {
            H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('请选择操作类型')));
        }

        if ($_POST['action_type'] == 'delete')
        {
            foreach ($_POST['report_ids'] as $val)
            {
                $this->model('question')->delete_report($val);
            }
        }
        else if ($_POST['action_type'] == 'handle')
        {
            foreach ($_POST['report_ids'] as $val)
            {
                $this->model('abc')->update_report($val, array(
                    'status' => 1
                ));
            }
        }

        H::ajax_json_output(AWS_APP::RSM(null, 1, null));
    }

}