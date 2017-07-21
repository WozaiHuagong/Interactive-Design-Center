<?php

/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2017/4/18
 * Time: 上午10:02
 */
class shang extends AWS_ADMIN_CONTROLLER
{
    public function setup()
    {

    }

    public function sortout_action(){
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(858));
        if ($this->is_post()) {
            foreach ($_POST as $key => $val) {
                if ($key == 'start_date' OR $key == 'end_date') {
                    $val = base64_encode($val);
                }
                if ( $key == 'uid' OR $key == 'from_uid') {
                    $val = rawurlencode($val);
                }
                $param[] = $key . '-' . $val;
            }

            H::ajax_json_output(AWS_APP::RSM(array(
                'url' => get_js_url('/admin/shang/sortout/' . implode('__', $param))
            ), 1, null));
        }

        $where = [];
        $where[] = 'sortout_id > 0';
        $where[] = 'type = \'sortout\'';
        if($_GET['uid']){
            if(is_digits($_GET['uid'])){
                $where[] = 'uid = ' . intval($_GET['uid']);
            }else{
                $uids = [];
                $users = $this->model('ad')->fetch_all('users', 'user_name like \'%'. $this->model('ad')->quote($_GET['uid']) .'%\'');
                if($users){
                    foreach ($users as $key => $val){
                        $uids[$val['uid']] = $val['uid'];
                    }
                }else{
                    $uids = [-1];
                }
                $where[] = 'uid IN(' . implode(',', $uids) . ')';
            }
        }
        if($_GET['start_date']){
            $where[] = 'add_time >= ' . strtotime(base64_decode($_GET['start_date']));
        }
        if($_GET['end_date']){
            $where[] = 'add_time < ' . (strtotime(base64_decode($_GET['end_date'])) + 86400);
        }

        $list = $this->model('shang')->fetch_page('question_shang', implode(' AND ', $where), 'id desc', $_GET['page'], $this->per_page);
        foreach ($list as $key => $val){
            $list[$key]['sortout_info'] = $this->model('question')->fetch_row('sortout', 'id = ' . intval($val['sortout_id']));
            $list[$key]['user_info'] = $this->model('question')->fetch_row('users', 'uid = ' . intval($val['uid']));
            $list[$key]['question_info'] = $this->model('question')->fetch_row('question', 'question_id = ' . intval($list[$key]['sortout_info']['question_id']));
        }

        TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
            'base_url' => get_js_url('/admin/shang/sortout/'),
            'total_rows' => $this->model('shang')->found_rows(),
            'per_page' => $this->per_page
        ))->create_links());

        $this->crumb(AWS_APP::lang()->_t('整理文章打赏'), 'admin/shang/sortout/');

        TPL::assign('list', $list);

        TPL::output('admin/shang/sortout');
    }

    public function question_action(){
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(859));

        if ($this->is_post()) {
            foreach ($_POST as $key => $val) {
                if ($key == 'start_date' OR $key == 'end_date') {
                    $val = base64_encode($val);
                }
                if ( $key == 'uid' OR $key == 'from_uid') {
                    $val = rawurlencode($val);
                }
                $param[] = $key . '-' . $val;
            }

            H::ajax_json_output(AWS_APP::RSM(array(
                'url' => get_js_url('/admin/shang/question/' . implode('__', $param))
            ), 1, null));
        }

        $where = [];
        $where[] = "question_id > 0";
        $where[] = "type = 'question'";
        if($_GET['uid']){
            if(is_digits($_GET['uid'])){
                $where[] = 'uid = ' . intval($_GET['uid']);
            }else{
                $uids = [];
                $users = $this->model('ad')->fetch_all('users', 'user_name like \'%'. $this->model('ad')->quote($_GET['uid']) .'%\'');
                if($users){
                    foreach ($users as $key => $val){
                        $uids[$val['uid']] = $val['uid'];
                    }
                }else{
                    $uids = [-1];
                }
                $where[] = 'uid IN(' . implode(',', $uids) . ')';
            }
        }
        if($_GET['from_uid']){
            if(is_digits($_GET['from_uid'])){
                $where[] = 'from_uid = ' . intval($_GET['from_uid']);
            }else{
                $uids = [];
                $users = $this->model('ad')->fetch_all('users', 'user_name like \'%'. $this->model('ad')->quote($_GET['from_uid']) .'%\'');
                if($users){
                    foreach ($users as $key => $val){
                        $uids[$val['uid']] = $val['uid'];
                    }
                }else{
                    $uids = [-1];
                }
                $where[] = 'from_uid IN(' . implode(',', $uids) . ')';
            }
        }
        if($_GET['start_date']){
            $where[] = 'add_time >= ' . strtotime(base64_decode($_GET['start_date']));
        }
        if($_GET['end_date']){
            $where[] = 'add_time < ' . (strtotime(base64_decode($_GET['end_date'])) + 86400);
        }

        $list = $this->model('shang')->fetch_page('question_shang', implode(' AND ', $where), 'id desc', $_GET['page'], $this->per_page);
        foreach ($list as $key => $val){
            $list[$key]['question_info'] = $this->model('question')->fetch_row('question', 'question_id = ' . intval($val['question_id']));
            $list[$key]['user_info'] = $this->model('question')->fetch_row('users', 'uid = ' . intval($val['uid']));
        }

        TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
            'base_url' => get_js_url('/admin/shang/question/'),
            'total_rows' => $this->model('shang')->found_rows(),
            'per_page' => $this->per_page
        ))->create_links());

        $this->crumb(AWS_APP::lang()->_t('问题打赏'), 'admin/shang/question/');

        TPL::assign('list', $list);

        TPL::output('admin/shang/question');
    }

    public function answer_action(){
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(860));
        if ($this->is_post()) {
            foreach ($_POST as $key => $val) {
                if ($key == 'start_date' OR $key == 'end_date') {
                    $val = base64_encode($val);
                }
                if ( $key == 'uid' OR $key == 'from_uid') {
                    $val = rawurlencode($val);
                }
                $param[] = $key . '-' . $val;
            }

            H::ajax_json_output(AWS_APP::RSM(array(
                'url' => get_js_url('/admin/shang/answer/' . implode('__', $param))
            ), 1, null));
        }

        $where = [];
        $where[] = "question_id > 0";
        $where[] = "type = 'answer'";
        if($_GET['uid']){
            if(is_digits($_GET['uid'])){
                $where[] = 'uid = ' . intval($_GET['uid']);
            }else{
                $uids = [];
                $users = $this->model('ad')->fetch_all('users', 'user_name like \'%'. $this->model('ad')->quote($_GET['uid']) .'%\'');
                if($users){
                    foreach ($users as $key => $val){
                        $uids[$val['uid']] = $val['uid'];
                    }
                }else{
                    $uids = [-1];
                }
                $where[] = 'uid IN(' . implode(',', $uids) . ')';
            }
        }
        if($_GET['from_uid']){
            if(is_digits($_GET['from_uid'])){
                $where[] = 'from_uid = ' . intval($_GET['from_uid']);
            }else{
                $uids = [];
                $users = $this->model('ad')->fetch_all('users', 'user_name like \'%'. $this->model('ad')->quote($_GET['from_uid']) .'%\'');
                if($users){
                    foreach ($users as $key => $val){
                        $uids[$val['uid']] = $val['uid'];
                    }
                }else{
                    $uids = [-1];
                }
                $where[] = 'from_uid IN(' . implode(',', $uids) . ')';
            }
        }
        if($_GET['start_date']){
            $where[] = 'add_time >= ' . strtotime(base64_decode($_GET['start_date']));
        }
        if($_GET['end_date']){
            $where[] = 'add_time < ' . (strtotime(base64_decode($_GET['end_date'])) + 86400);
        }

        $list = $this->model('shang')->fetch_page('question_shang', implode(' AND ', $where), 'id desc', $_GET['page'], $this->per_page);
        foreach ($list as $key => $val){
            $list[$key]['question_info'] = $this->model('question')->fetch_row('question', 'question_id = ' . intval($val['question_id']));
            $list[$key]['user_info'] = $this->model('question')->fetch_row('users', 'uid = ' . intval($val['uid']));
        }

        TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
            'base_url' => get_js_url('/admin/shang/answer/'),
            'total_rows' => $this->model('shang')->found_rows(),
            'per_page' => $this->per_page
        ))->create_links());

        $this->crumb(AWS_APP::lang()->_t('回答打赏'), 'admin/shang/answer/');

        TPL::assign('list', $list);

        TPL::output('admin/shang/answer');
    }

    public function article_action(){
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(861));

        if ($this->is_post()) {
            foreach ($_POST as $key => $val) {
                if ($key == 'start_date' OR $key == 'end_date') {
                    $val = base64_encode($val);
                }
                if ( $key == 'uid' OR $key == 'from_uid') {
                    $val = rawurlencode($val);
                }
                $param[] = $key . '-' . $val;
            }

            H::ajax_json_output(AWS_APP::RSM(array(
                'url' => get_js_url('/admin/shang/article/' . implode('__', $param))
            ), 1, null));
        }

        $where = [];
        $where[] = "item_type = 'article'";
        if($_GET['uid']){
            if(is_digits($_GET['uid'])){
                $where[] = 'uid = ' . intval($_GET['uid']);
            }else{
                $uids = [];
                $users = $this->model('ad')->fetch_all('users', 'user_name like \'%'. $this->model('ad')->quote($_GET['uid']) .'%\'');
                if($users){
                    foreach ($users as $key => $val){
                        $uids[$val['uid']] = $val['uid'];
                    }
                }else{
                    $uids = [-1];
                }
                $where[] = 'uid IN(' . implode(',', $uids) . ')';
            }
        }
        if($_GET['from_uid']){
            if(is_digits($_GET['from_uid'])){
                $where[] = 'from_uid = ' . intval($_GET['from_uid']);
            }else{
                $uids = [];
                $users = $this->model('ad')->fetch_all('users', 'user_name like \'%'. $this->model('ad')->quote($_GET['from_uid']) .'%\'');
                if($users){
                    foreach ($users as $key => $val){
                        $uids[$val['uid']] = $val['uid'];
                    }
                }else{
                    $uids = [-1];
                }
                $where[] = 'from_uid IN(' . implode(',', $uids) . ')';
            }
        }
        if($_GET['start_date']){
            $where[] = 'add_time >= ' . strtotime(base64_decode($_GET['start_date']));
        }
        if($_GET['end_date']){
            $where[] = 'add_time < ' . (strtotime(base64_decode($_GET['end_date'])) + 86400);
        }

        $list = $this->model('shang')->fetch_page('shang', implode(' AND ', $where), 'id desc', $_GET['page'], $this->per_page);
        foreach ($list as $key => $val){
            $list[$key]['article_info'] = $this->model('question')->fetch_row('article', 'id = ' . intval($val['item_id']));
            $list[$key]['from_user'] = $this->model('question')->fetch_row('users', 'uid = ' . intval($val['from_uid']));
            $list[$key]['user_info'] = $this->model('question')->fetch_row('users', 'uid = ' . intval($val['uid']));
        }

        TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
            'base_url' => get_js_url('/admin/shang/article/'),
            'total_rows' => $this->model('shang')->found_rows(),
            'per_page' => $this->per_page
        ))->create_links());

        $this->crumb(AWS_APP::lang()->_t('文章打赏'), 'admin/shang/article/');

        TPL::assign('list', $list);

        TPL::output('admin/shang/article');
    }

    public function shang_action(){
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(862));
        if ($this->is_post()) {
            foreach ($_POST as $key => $val) {
                if ($key == 'start_date' OR $key == 'end_date') {
                    $val = base64_encode($val);
                }
                if ( $key == 'uid' OR $key == 'from_uid') {
                    $val = rawurlencode($val);
                }
                $param[] = $key . '-' . $val;
            }

            H::ajax_json_output(AWS_APP::RSM(array(
                'url' => get_js_url('/admin/shang/shang/' . implode('__', $param))
            ), 1, null));
        }

        $where = [];
        if($_GET['uid']){
            if(is_digits($_GET['uid'])){
                $where[] = 'uid = ' . intval($_GET['uid']);
            }else{
                $uids = [];
                $users = $this->model('ad')->fetch_all('users', 'user_name like \'%'. $this->model('ad')->quote($_GET['uid']) .'%\'');
                if($users){
                    foreach ($users as $key => $val){
                        $uids[$val['uid']] = $val['uid'];
                    }
                }else{
                    $uids = [-1];
                }
                $where[] = 'uid IN(' . implode(',', $uids) . ')';
            }
        }
        if($_GET['from_uid']){
            if(is_digits($_GET['from_uid'])){
                $where[] = 'from_uid = ' . intval($_GET['from_uid']);
            }else{
                $uids = [];
                $users = $this->model('ad')->fetch_all('users', 'user_name like \'%'. $this->model('ad')->quote($_GET['from_uid']) .'%\'');
                if($users){
                    foreach ($users as $key => $val){
                        $uids[$val['uid']] = $val['uid'];
                    }
                }else{
                    $uids = [-1];
                }
                $where[] = 'from_uid IN(' . implode(',', $uids) . ')';
            }
        }
        if($_GET['shang_id']){
            $where[] = 'from_question_shang = ' . intval($_GET['shang_id']);
        }
        if($_GET['start_date']){
            $where[] = 'add_time >= ' . strtotime(base64_decode($_GET['start_date']));
        }
        if($_GET['end_date']){
            $where[] = 'add_time < ' . (strtotime(base64_decode($_GET['end_date'])) + 86400);
        }

        $list = $this->model('shang')->fetch_page('shang', implode(' AND ', $where), 'id desc', $_GET['page'], $this->per_page);
        foreach ($list as $key => $val){
            $item = $this->model('finance')->getItemType($val['item_type'], $val['item_id']);
            $list[$key]['title'] = $item['name'];
            $list[$key]['info']  = $item['info'];
            $list[$key]['link']  = $item['link'];
            $list[$key]['from_user'] = $this->model('question')->fetch_row('users', 'uid = ' . intval($val['from_uid']));
            $list[$key]['user_info'] = $this->model('question')->fetch_row('users', 'uid = ' . intval($val['uid']));
        }

        TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
            'base_url' => get_js_url('/admin/shang/shang/'),
            'total_rows' => $this->model('shang')->found_rows(),
            'per_page' => $this->per_page
        ))->create_links());

        $this->crumb(AWS_APP::lang()->_t('打赏明细'), 'admin/shang/shang/');

        TPL::assign('list', $list);

        TPL::output('admin/shang/shang');
    }

    public function comment_action(){
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list(863));

        if ($this->is_post()) {
            foreach ($_POST as $key => $val) {
                if ($key == 'start_date' OR $key == 'end_date') {
                    $val = base64_encode($val);
                }
                if ( $key == 'uid' OR $key == 'from_uid') {
                    $val = rawurlencode($val);
                }
                $param[] = $key . '-' . $val;
            }

            H::ajax_json_output(AWS_APP::RSM(array(
                'url' => get_js_url('/admin/shang/comment/' . implode('__', $param))
            ), 1, null));
        }

        $where = [];
        if($_GET['uid']){
            if(is_digits($_GET['uid'])){
                $where[] = 'uid = ' . intval($_GET['uid']);
            }else{
                $uids = [];
                $users = $this->model('ad')->fetch_all('users', 'user_name like \'%'. $this->model('ad')->quote($_GET['uid']) .'%\'');
                if($users){
                    foreach ($users as $key => $val){
                        $uids[$val['uid']] = $val['uid'];
                    }
                }else{
                    $uids = [-1];
                }
                $where[] = 'uid IN(' . implode(',', $uids) . ')';
            }
        }
        if($_GET['from_uid']){
            if(is_digits($_GET['from_uid'])){
                $where[] = 'from_uid = ' . intval($_GET['from_uid']);
            }else{
                $uids = [];
                $users = $this->model('ad')->fetch_all('users', 'user_name like \'%'. $this->model('ad')->quote($_GET['from_uid']) .'%\'');
                if($users){
                    foreach ($users as $key => $val){
                        $uids[$val['uid']] = $val['uid'];
                    }
                }else{
                    $uids = [-1];
                }
                $where[] = 'from_uid IN(' . implode(',', $uids) . ')';
            }
        }
        if($_GET['start_date']){
            $where[] = 'add_time >= ' . strtotime(base64_decode($_GET['start_date']));
        }
        if($_GET['end_date']){
            $where[] = 'add_time < ' . (strtotime(base64_decode($_GET['end_date'])) + 86400);
        }
        if($_GET['type']){
            $where[] = "item_type = '{$_GET['type']}'";
        }else{
            $where[] = "(item_type = 'article_comment' OR item_type = 'sortout_comment')";
        }

        $list = $this->model('shang')->fetch_page('shang', implode(' AND ', $where), 'id desc', $_GET['page'], $this->per_page);
        foreach ($list as $key => $val){
            $item = $this->model('finance')->getItemType($val['item_type'], $val['item_id']);
            $list[$key]['title'] = $item['name'];
            $list[$key]['info']  = $item['info'];
            $list[$key]['link']  = $item['link'];
            $list[$key]['from_user'] = $this->model('question')->fetch_row('users', 'uid = ' . intval($val['from_uid']));
            $list[$key]['user_info'] = $this->model('question')->fetch_row('users', 'uid = ' . intval($val['uid']));
        }

        TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
            'base_url' => get_js_url('/admin/shang/comment/'),
            'total_rows' => $this->model('shang')->found_rows(),
            'per_page' => $this->per_page
        ))->create_links());

        $this->crumb(AWS_APP::lang()->_t('评论打赏'), 'admin/shang/comment/');

        TPL::assign('list', $list);

        TPL::output('admin/shang/comment');
    }
}