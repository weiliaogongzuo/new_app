<?php
namespace Home\Controller;
use Think\Controller;

//场景1的二维码
class QrcodeController extends Controller {

    public function index(){

        //场景id
        $scene_id = file_get_contents(APP_PATH."Home/wechat_scene_max_id.txt");

        if($scene_id >= 4294967295)
            $scene_id = 2;
        file_put_contents(APP_PATH."Home/wechat_scene_max_id.txt", $scene_id+1);

        // access_token
        import("@.Util.Wechat");
        $wechatUtil = new \Home\Util\Wechat();
        $access_token = $wechatUtil->getAccessToken();

        // ticket
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=$access_token";
        //$data = "{\"expire_seconds\": 604800, \"action_name\": \"QR_SCENE\", \"action_info\": {\"scene\": {\"scene_id\": $scene_id }}}";
        $data = '{"expire_seconds":604800, "action_name": "QR_SCENE", "action_info":{"scene":{"scene_id":'. $scene_id .'}}}';
        $json = $wechatUtil->post($url, $data);
        $array = json_decode($json, true);
        $ticket = $array["ticket"];    

        // qrcode
        $url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=$ticket";
        echo '{"scene_id":'.$scene_id.',"url":"'.$url.'"}';
    }

    //场景1扫码的时候，不断轮询该方法, 返回用户信息
    public function pollScan(){
        $scene_id = $_GET['scene_id'];

        $Login = M('Login');
        $result = $Login->where("scene_id=$scene_id")->find();
        if($result)
        {
            $User = M('User');
            $userInfo = $User->where('openid="'. $result['openid'].'"')->find();
            if($userInfo)
                echo json_encode($userInfo, JSON_UNESCAPED_UNICODE);
            else 
                echo "";
        }
        else
        {
            echo "";
        }
        
    }
}