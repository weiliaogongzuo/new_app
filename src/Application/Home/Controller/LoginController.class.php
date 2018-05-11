<?php
namespace Home\Controller;
use Think\Controller;

//场景1的二维码
class LoginController extends Controller {
    public function index(){
        if (!isset($_POST['username']) || empty($_POST['username']) ||
            !isset($_POST['password']) || empty($_POST['password'])) {
            $data['success'] = false;
            $data['errors']  = 'username or password empty';
            echo json_encode($data);
            exit;
        }

        if ($_POST['username'] != 'rili' || $_POST['password'] != 'rili-tech@0803') {
            $data['success'] = false;
            $data['errors']  = 'username or password error';
            echo json_encode($data);
            exit;
        }

        $data['success'] = true;
        $data['data']['id'] = 1;
        $data['data']['role']= 'admin';
        echo json_encode($data);
    }
}
