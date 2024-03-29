<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Back extends XC_Controller
{

    public function index()
    {
        Xcon::loginCheck(function ($userinfor) {
            // 测算反馈
            $result = Xcon::getsBy('xvData', 'counted=1 and back=0');

            Xcon::json(Xcon::NO_ERROR, $result);
        });
    }

    public function find()
    {
        Xcon::loginCheck(function ($userinfor) {
            // 反馈查询
            $params = Xcon::params();
            $begin = Xcon::array_key($params, 'begin');
            $end = Xcon::array_key($params, 'end');

            $result = Xcon::getsBy('xvData', "counted=1 and back=0 and create_time between '$begin' and '$end'");

            Xcon::json(Xcon::NO_ERROR, $result);
        });
    }

    public function tax()
    {
        Xcon::loginCheck(function ($userinfor) {
            // 测算列表
            $params = Xcon::params();
            $data_id = Xcon::array_key($params, 'data_id');

            $result = Xcon::getsBy('xvDataTax', compact('data_id'));
            Xcon::json(Xcon::NO_ERROR, $result);
        });
    }

    public function edit()
    {
        Xcon::loginCheck(function ($userinfor) {
            $params = Xcon::params();
            $uid = Xcon::array_key($params, 'uid');
            $price_end = Xcon::array_key($params, 'price_end');
            $price_tax = Xcon::array_key($params, 'price_tax');

            // 计算最终价格
            $price = $price_end + $price_tax;

            // 修改价格
            Xcon::setByUid('xcData', compact('price_end', 'price_tax', 'price'), $uid);

            $result = Xcon::getByUid('xvData', $uid);
            Xcon::json(Xcon::NO_ERROR, $result);
        });
    }


    public function upto()
    {
        Xcon::loginCheck(function ($userinfor) {
            $params = Xcon::params();
            $uid = Xcon::array_key($params, 'uid');

            // 检测标的是否存在
            $data = Xcon::checkByUid('xcData', $uid);
            $data_id = $data->id;

            // 反馈，提交审核
            $user_id = $userinfor->id;
            $exam_id = Xcon::EXAM_BACK;
            $exam_time = date('Y-m-d H:i:s');

            // 检测标的是否提交反馈审核
            Xcon::existBy('xcDataExam', compact('data_id', 'exam_id'), '“标的”已经提交反馈审核！');

            // 提交
            $uid = Xcon::uid();
            $result = Xcon::add('xcDataExam', compact('uid', 'data_id', 'exam_id', 'user_id', 'exam_time'));

            Xcon::json(Xcon::NO_ERROR, $result);
        });
    }

}
