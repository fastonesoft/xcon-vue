<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends XBASE_Controller
{

    // 首页内容
    public function index()
    {
        $this->load->view('home.html');
    }

    // 返回当前页面的状态编号
    public function token()
    {
        $token = md5(session_id());
        Xcon::json(Xcon::NO_ERROR, $token);
    }

    // 登录
    public function login()
    {
        Xcon::errorCheck(function () {
            $params = Xcon::params();
            $id = Xcon::array_key($params, 'id');
            $pass = Xcon::array_key($params, 'pass');
            $token = Xcon::array_key($params, 'token');

            // 检测token是否正确
            if (md5(md5(session_id()) . $pass) !== $token) {
                Xcon::error(Xcon::ERROR_APP, '登录密码检验失败!');
            }

            // 检测用户是否存在
            $user = Xcon::checkBy('xcUser', compact('id'), '帐号不存在！');

            if ($user->id === $id && $user->pass === $pass) {
                // 一、记录登录 用户信息
                Xcon::sess_set('XcSession', $user);

                // 二、查询用户、权限，并返回客户端
                // 1、用户
                $user = Xcon::getById('xvUserAll', $id);
                $last_visit_time = date('Y-m-d H:i:s');
                Xcon::setById('xcUser', compact('last_visit_time'), $id);
                // 2、权限列表
                $roles = [];

                // 3、菜单
                $user_id = $id;
                $types = Xcon::getsBy('xvUserMenuType', compact('user_id'), 'type_id');
                $menus = Xcon::getsBy('xvUserMenu', compact('user_id'), 'type_id, ord');

                Xcon::json(Xcon::NO_ERROR, compact('user', 'types', 'menus', 'roles'));
            } else {
                Xcon::error(Xcon::ERROR_APP, '登录帐号、密码有误！');
            }
        });
    }

    // 登录状态
    public function logstatus()
    {
        return $this->userinfor !== null;
    }

    // 退出
    public function logout()
    {
        Xcon::sess_destroy();
    }
}
