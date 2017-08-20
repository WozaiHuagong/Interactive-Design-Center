<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2017/3/6
 * Time: 下午5:56
 */
echo system('cd /home/wwwroot/default/ && git pull', $output);
var_dump($output);
file_put_contents('/home/wwwroot/default/need_pull.txt', time());
if($_POST['hook']){
    $hook = json_decode($_POST['hook'], true);
    if($hook['password'] == 'hospital'){

    }
    die('success');
}
die('success run');