<?php
namespace Home\Controller;
use Think\Controller;

//查询试用状态
class TrailstatusController extends Controller {

    public function index_old(){

        $openid = $_GET['openid'];
        $computer_id = $_GET['computer_id'];
        
        if (empty($openid) || empty($computer_id)) {
            echo '{"Status":"ParamEmpty", "ExpireTime":-1}';
            exit;
        }
        
        $Trail = M('Trail');
        $userResult = $Trail->where("openid='$openid'")->select();
        $query_status = "Success";

        //如果尚未试用
        if (empty($userResult)) {
            $cidResult = $Trail->where("computer_id='$computer_id'")->find();
            
            if ($cidResult == null) {

                $userInfo = M('User')->where("openid='$openid'")->select();

                if(/*!M('User')->where("openid='$openid'")->find()*/ $userInfo == null || empty($userInfo)) {
                    //尚未关注公众号
                    echo '{"Status":"Not Subscribe", "ExpireTime":-1}';
                    $query_status = "Not Subscribe";
                    exit;
                }

                if ($userInfo[0]['bind_time'] == null || empty($userInfo[0]['bind_time'])) {
                    $userInfo[0]['bind_time'] = time();
                    M('User')->where("openid='$openid'")->save($userInfo[0]);
                }

                //插入表格
                $data['openid'] = $openid;
                $data['computer_id'] = $computer_id;
                $data['trail_time'] = time();
                if(isset($_GET['new_cid']) && $_GET['new_cid'] == "1")
                    $data['id_updated'] = 1;
                $Trail->add($data);
                $expireTime = $data['trail_time'] + 30 * 24 * 3600;
                echo '{"Status":"Success", "ExpireTime":'.$expireTime.'}';
            }
            else{
                # 该电脑已经被别的微信绑定
                echo '{"Status":"NotMatchWX", "ExpireTime":-1}';
                $query_status = "NotMatchWX";
            }
        }else{//如果已经试用
            if(isset($_GET['new_cid']) && $_GET['new_cid'] == "1" && $userResult[0]['id_updated'] == "0")
            {
                $data['openid'] = $openid;
                $data['computer_id'] = $computer_id;
                $data['id_updated'] = 1;
                $Trail->where("openid='$openid'")->save($data);
            }
            $cidResult = $Trail->where("openid='$openid' and computer_id='$computer_id'")->find();
            if ($cidResult == null) {
                # 你的微信绑定的不是这台电脑
                echo '{"Status":"NotMatchPC", "ExpireTime":-1}';
                $query_status = "NotMatchPC";
            }
            else
            {
                $days = $cidResult['trail_days'];
                $expireTime = $cidResult['trail_time'] + $days * 24 * 3600;
                if ($expireTime < time()) {
                    echo '{"Status":"TrailExpire", "ExpireTime":'.$expireTime.'}';
                    $query_status = "TrailExpire";
                }
                else{
                    //file_put_contents(APP_PATH."Home/trail_query_info.txt", $openid.' '.$_SERVER["REMOTE_ADDR"].' '.date("Y-m-d h:i:sa"));
                    echo '{"Status":"Success", "ExpireTime":'.$expireTime.'}';
                }
            }
        }

        //记录查询结果
        $query_result['openid'] = $openid;
        $query_result['ip'] = $_SERVER["REMOTE_ADDR"];
        $query_result['status'] = $query_status;
        M('Trail_query')->add($query_result);
        
    }

    public function index(){

    	$openid = $_GET['openid'];
    	$computer_id = $_GET['computer_id'];
    	
    	if (empty($openid) || empty($computer_id)) {
    		echo '{"Status":"ParamEmpty", "ExpireTime":-1}';
    		exit;
    	}
    	
    	$Trail = M('Trail');
        $userResult = $Trail->where("openid='$openid'")->select();
        $query_status = "Success";

        //如果尚未试用
        if (empty($userResult)) {
        	$cidResult = $Trail->where("computer_id='$computer_id'")->find();
        	
        	if ($cidResult == null) {

                $userInfo = M('User')->where("openid='$openid'")->select();

        	    if($userInfo == null || empty($userInfo)) {
        	        //尚未关注公众号
                    echo '{"Status":"Not Subscribe", "ExpireTime":-1}';
                    $query_status = "Not Subscribe";
    		        exit;
    	        }

                if ($userInfo[0]['bind_time'] == null || empty($userInfo[0]['bind_time'])) {
                    $userInfo[0]['bind_time'] = time();
                    M('User')->where("openid='$openid'")->save($userInfo[0]);
                }

        		//插入表格
        		$data['openid'] = $openid;
        		$data['computer_id'] = $computer_id;
        		$data['trail_time'] = time();
        		if(isset($_GET['new_cid']) && $_GET['new_cid'] == "1")
        		    $data['id_updated'] = 1;
        		$Trail->add($data);
        		$expireTime = $data['trail_time'] + 30 * 24 * 3600;
        		echo '{"Status":"Success", "ExpireTime":'.$expireTime.'}';
        	}
        	else{
        		# 该电脑已经被别的微信绑定
        		echo '{"Status":"NotMatchWX", "ExpireTime":-1}';
        		$query_status = "NotMatchWX";
        	}
        }else{//如果已经试用
            if(isset($_GET['new_cid']) && $_GET['new_cid'] == "1" && $userResult[0]['id_updated'] == "0")
            {
                $data['openid'] = $openid;
                $data['computer_id'] = $computer_id;
                $data['id_updated'] = 1;
                $Trail->where("openid='$openid'")->save($data);
            }

            //如果标识了更换电脑
            if($userResult[0]['computer_id'] == $userResult[0]['openid']){

                //set computer_id 为 $computer_id
                $data['computer_id'] = $computer_id;
                $Trail->where("openid='$openid'")->save($data);
                

                $days = $userResult[0]['trail_days'];
                $expireTime = $userResult[0]['trail_time'] + $days * 24 * 3600;
                if ($expireTime < time()) {
                    echo '{"Status":"TrailExpire", "ExpireTime":'.$expireTime.'}';
                    $query_status = "TrailExpire";
                }
                else{
                    echo '{"Status":"Success", "ExpireTime":'.$expireTime.'}';
                }
            }
            else//
            {
                $cidResult = $Trail->where("openid='$openid' and computer_id='$computer_id'")->find();
                if ($cidResult == null) {

                     # 你的微信绑定的不是这台电脑
                    echo '{"Status":"NotMatchPC", "ExpireTime":-1}';
                    $query_status = "NotMatchPC";
                }
                else
                {
                    $days = $cidResult['trail_days'];
                    $expireTime = $cidResult['trail_time'] + $days * 24 * 3600;
                    if ($expireTime < time()) {
                        echo '{"Status":"TrailExpire", "ExpireTime":'.$expireTime.'}';
                        $query_status = "TrailExpire";
                    }
                    else{
                        echo '{"Status":"Success", "ExpireTime":'.$expireTime.'}';
                    }
                }
            }
        }

        //记录查询结果
        $query_result['openid'] = $openid;
        $query_result['ip'] = $_SERVER["REMOTE_ADDR"];
        $query_result['status'] = $query_status;
        M('Trail_query')->add($query_result);
        
    }

    //删除绑定
    public function setChangeComputer(){
        $openid = $_GET['openid'];
        
        if (empty($openid)) {
            echo '{"Status":"ParamEmpty", "ExpireTime":-1}';
            exit;
        }
        
        $Trail = M('Trail');
        $userResult = $Trail->where("openid='$openid'")->select();
        if (empty($userResult)) {
            echo '{"Status":"openidNotExit", "ExpireTime":-1}';
            exit;
        }

        //set computer_id = openid
        $data['computer_id'] = $openid;
        $Trail->where("openid='$openid'")->save($data);
        echo '{"Status":"Success", "ExpireTime":-1}';
    }
}
