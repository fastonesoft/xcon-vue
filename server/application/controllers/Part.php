<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Part extends XC_Controller
{

    public function index()
    {
        Xcon::loginCheck(function ($userinfor) {
            $result = Xcon::gets('xcPart');

            Xcon::json(Xcon::NO_ERROR, $result);
        });
    }

    public function add()
    {
        Xcon::loginCheck(function ($userinfor) {
            $params = Xcon::params();
            $params['uid'] = Xcon::uid();

            // 重复编号检测
            Xcon::existById('xcPart', Xcon::array_key($params, 'id'));
            // 重复名称检测
            $name = Xcon::array_key($params, 'name');
            Xcon::existBy('xcPart', compact('name'), '“名称”重复');

            Xcon::add('xcPart', $params);
            $result = Xcon::getByUid('xcPart', $params['uid']);

            Xcon::json(Xcon::NO_ERROR, $result);
        });
    }

    public function edit()
    {
        Xcon::loginCheck(function ($userinfor) {
            $params = Xcon::params();
            $uid = Xcon::array_key($params, 'uid');

            Xcon::setByUid('xcPart', $params, $uid);
            $result = Xcon::getByUid('xcPart', $uid);

            Xcon::json(Xcon::NO_ERROR, $result);
        });
    }

    public function del()
    {
        Xcon::loginCheck(function ($userinfor) {
            $params = Xcon::params();
            // 删除之前要确认一下
            $uid = Xcon::array_key($params, 'uid');
            $part = Xcon::checkByUid('xcPart', $uid);
            $part_id = $part->id;

            // 检测分组列表
            Xcon::existBy('xcGroup', compact('part_id'), '编号已存在分组列表');
            // 检测用户列表
            Xcon::existBy('xcUser', compact('part_id'), '编号已存在用户列表');

            // 删除
            $result = Xcon::delByUid('xcPart', $uid);

            Xcon::json(Xcon::NO_ERROR, $result);
        });
    }

}
