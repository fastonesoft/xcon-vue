<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dataed extends XC_Controller
{

    public function index()
    {
        Xcon::loginCheck(function ($userinfor) {
            // 标的清单
            $end = Xcon::date();
            $begin = Xcon::date();
            $result = Xcon::getsBy('xvData', "data=1 and dataed=0 and create_time between '$begin' and '$end'");

            Xcon::json(Xcon::NO_ERROR, $result);
        });
    }

    public function find()
    {
        Xcon::loginCheck(function ($userinfor) {
            // 标的查询
            $params = Xcon::params();
            $begin = Xcon::array_key($params, 'begin');
            $end = Xcon::array_key($params, 'end');
            $result = Xcon::getsBy('xvData', "data=1 and dataed=0 and create_time between '$begin' and '$end'");

            Xcon::json(Xcon::NO_ERROR, $result);
        });
    }

    public function back()
    {
        Xcon::loginCheck(function ($userinfor) {
            // 标的退回
            $params = Xcon::params();
            $uid = Xcon::array_key($params, 'uid');

            // 查询标的
            $data = Xcon::checkByUid('xvData', $uid);
            $data_id = $data->id;
            $exam_id = Xcon::EXAM_DATA;

            $result = Xcon::delBy('xcDataExam', compact('data_id', 'exam_id'));

            Xcon::json(Xcon::NO_ERROR, $result);
        });
    }

    public function exam()
    {
        Xcon::loginCheck(function ($userinfor) {
            $params = Xcon::params();
            $uid = Xcon::array_key($params, 'uid');

            // 检测标的是否存在
            $data = Xcon::checkByUid('xcData', $uid);
            $data_id = $data->id;

            // 审核，提交测算
            $user_id = $userinfor->id;
            $exam_id = Xcon::EXAM_DATAED;
            $exam_time = Xcon::datetime();
			$examed = 1;
			$team = 1;

            // 检测标的是否通过审核
            Xcon::existBy('xcDataExam', compact('data_id', 'exam_id'), '“标的”已经通过审核！');

            // 提交
            $uid = Xcon::uid();
            $result = Xcon::add('xcDataExam', compact('uid', 'data_id', 'exam_id', 'user_id', 'exam_time', 'examed', 'team'));

            Xcon::json(Xcon::NO_ERROR, $result);
        });
    }

}
