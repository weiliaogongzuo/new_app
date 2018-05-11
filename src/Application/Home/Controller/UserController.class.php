<?php
namespace Home\Controller;
use Think\Controller;

class UserController extends Controller {
    public function index(){
    	$start = $_GET['start'];
		$count = $_GET['count'];
        $keyword = trim($_GET['keyword']);
		
        $where = "";
        if ($keyword != "") {
            $where = " WHERE a.openid LIKE '%$keyword%' OR a.computer_id LIKE '%$keyword%' OR b.nickname LIKE '%$keyword%' OR b.country LIKE '%$keyword%' OR b.province LIKE '%$keyword%' OR b.city LIKE '%$keyword%' OR b.real_name LIKE '%$keyword%' OR b.mobile_phone LIKE '%$keyword%' OR b.qq LIKE '%$keyword%' OR b.remark LIKE '%$keyword%'";  
        }

        $sql = "SELECT a.openid, a.computer_id, a.trail_time, a.trail_days, b.nickname, b.headimgurl, b.country, b.province, b.city, b.sex, b.real_name, b.mobile_phone, b.qq, b.remark, b.bind_time FROM ipm_trail a JOIN ipm_user b ON a.openid = b.openid ".$where."order by a.trail_time+a.trail_days*86400 DESC LIMIT $start, $count";

        $Model = new \Think\Model();
        $result = $Model->query($sql);
        $indexCtrl = new IndexController();
        foreach ($result as $item) {

            //第一次试用到现在的总时间
            $spanTime = time() - intval($item['trail_time']);
            //总可用时间（秒）
            $trailTime = intval($item['trail_days']) * 86400;

        	$data['openid'] = $item['openid'];
        	$data['nickname'] = $item['nickname'];
        	$data['headimgurl'] = $item['headimgurl'];
        	$data['trail_time'] = date('Y-m-d H:i:s', $item['trail_time']);
            $data['over_time'] = date('Y-m-d H:i:s', $item['trail_time'] + $trailTime);
            $data['delay_days'] = intval($item['trail_days']);
        	$data['address'] = $item['country'].' '.$item['province'].' '.$item['city'];
            $data['computer_id'] = $item['computer_id'];
			$data['remark'] = $item['remark'];
            $data['bind_time'] = date('Y-m-d H:i:s', $item['bind_time']);;
            $data['real_name'] = $item['real_name'];
            $data['mobile_phone'] = $item['mobile_phone'];
            $data['qq'] = $item['qq'];
            $data['sex'] = '';
            if ($item['sex'] == 1) {
                $data['sex'] = '男';
            }
            else if ($item['sex'] == 2) {
                $data['sex'] = '女';
            }
 
			if ($spanTime >= $trailTime) {
				$data['remain_days'] = '过期';
			}
			else{
				$data['remain_days'] = intval(($trailTime - $spanTime) / 86400) + 1;
			}
			$res[] = $data;
        }

        if ($result == null) 
            echo "[]";
        else
		  echo json_encode($res);
    }

     public function updateTrail()
    {
    	if(	!isset($_POST['openid']) || empty($_POST['openid']) ||
    		!isset($_POST['remark']) ||
    		!isset($_POST['computer_id']) || empty($_POST['computer_id']) ||
    		!isset($_POST['remain_days']) || empty($_POST['remain_days']) )
    	{
    		$res['success'] = false;
    		$res['message'] = '修改的信息不能为空';
    		echo json_encode($res);
    		return;
    	}

    	if (!is_numeric($_POST['remain_days'])) {
    		$update['trail_days'] = 0;
    	}else{
    		$update['trail_days'] = intval($_POST['remain_days']);
    	}

    	$update['computer_id'] = $_POST['computer_id'];
		$update['trail_time'] = time();

		$m_trail = M('trail');
		if ($m_trail->where('computer_id="'.$_POST['computer_id'].'" and openid <> "'.$_POST['openid'].'"')->find()) {
			$res['success'] = false;
    		$res['message'] = $_POST['computer_id'].' 已被使用，请使用其他电脑ID';
    		echo json_encode($res);
    		return;
		}

    	if( $m_trail->where('openid="'.$_POST['openid'].'"')->find() )
    	{
			$m_trail->where('openid="'.$_POST['openid'].'"')->save($update);
    	}

        $m_user = M('user');
        $updateUser['real_name'] = $_POST['real_name'];
        $updateUser['mobile_phone'] = $_POST['mobile_phone'];
        $updateUser['qq'] = $_POST['qq'];
        $updateUser['remark'] = $_POST['remark'];

        if( $m_user->where('openid="'.$_POST['openid'].'"')->find() )
        {
            $m_user->where('openid="'.$_POST['openid'].'"')->save($updateUser);
        }

        $indexCtrl = new IndexController();
        $indexCtrl->setRemark($_POST['openid'], $_POST['remark']);
    }


    public function deleteTrail()
    {
    	if(	!isset($_POST['openid']) || empty($_POST['openid']) )
    	{
    		$res['success'] = false;
    		$res['message'] = 'request data error';
    		echo json_encode($res);
    		return;
    	}

    	$m_trail = M('trail');
    	if( $m_trail->where('openid="'.$_POST['openid'].'"')->find() )
    	{
			$m_trail->where('openid="'.$_POST['openid'].'"')->delete();
    	}

    	$res['success'] = true;
    	echo json_encode($res);
    }
    
    public function refreshUserInfo()
    {
    	if(	!isset($_POST['openid']) || empty($_POST['openid']) )
    	{
    		$res['success'] = false;
    		$res['message'] = 'request data error';
    		echo json_encode($res);
    		return;
    	}

    	$indexCtrl = new IndexController();
    	$wxUserInfo = $indexCtrl->getWechatUserInfo($_POST['openid']);

    	if( M('user')->where('openid="'.$_POST['openid'].'"')->find() )
    		M('user')->where('openid="'.$_POST['openid'].'"')->save($wxUserInfo);

    	$res['success'] = true;
        $res['wx']['nickname'] = $wxUserInfo['nickname'];
        $res['wx']['headimgurl'] = $wxUserInfo['headimgurl'];
        $res['wx']['address'] = $wxUserInfo['country'].' '.$wxUserInfo['province'].' '.$wxUserInfo['city'];
        $res['wx']['remark'] = $wxUserInfo['remark'];
        $res['wx']['sex'] = '';
        if ($wxUserInfo['sex'] == 1) {
            $res['wx']['sex'] = '男';
        }
        else if ($wxUserInfo['sex'] == 2) {
            $res['wx']['sex'] = '女';
        }

    	echo json_encode($res);
    }
    
    public function refreshAllUserInfo()
    {
        $indexCtrl = new IndexController();
        $result = M('trail')->field("openid")->select();

        foreach($result as $item)
        {
            $wxUserInfo = $indexCtrl->getWechatUserInfo($item['openid']);
    		M('user')->where('openid="'.$item['openid'].'"')->save($wxUserInfo);
        }
        
    	$res['success'] = true;
    	echo json_encode($res);
    }

    //i配模调用的查看个人信息
    public function getUserTrailByOpenId(){
        if( !isset($_GET['openid']) || empty($_GET['openid']) )
        {
            $res['success'] = false;
            echo json_encode($res);
            return;
        }

        $openid = $_GET['openid'];

        $indexCtrl = new IndexController();
        $wxUserInfo = $indexCtrl->getWechatUserInfo($openid);

        if( M('user')->where('openid="'.$openid.'"')->find() )
            M('user')->where('openid="'.$openid.'"')->save($wxUserInfo);


        //来自微信服务器的数据
        $res['success'] = true;
        $res['nickname'] = $wxUserInfo['nickname'];
        $res['headimgurl'] = $wxUserInfo['headimgurl'];
        $res['address'] = $wxUserInfo['country'].' '.$wxUserInfo['province'].' '.$wxUserInfo['city'];

        $results = M('user')->where('openid="'.$openid.'"')->select();
        if ($results == null || empty($results)) {
            $res['success'] = false;
            echo json_encode($res);
            return;
        }

        //用户表数据
        $res['real_name'] = $results[0]['real_name'];
        $res['mobile_phone'] = $results[0]['mobile_phone'];
        $res['qq'] = $results[0]['qq'];

        //试用表数据
        $results = M('trail')->where('openid="'.$openid.'"')->select();
        if ($results == null || empty($results)) {
            $res['over_time'] = "";
        }
        else
        {
            $trailTime = intval($results[0]['trail_days']) * 86400;
            $res['over_time'] = date('Y-m-d H:i:s', intval($results[0]['trail_time']) + $trailTime);
        }

        echo json_encode($res);
    }

    //i配模调用的更新个人信息
    public function updateUserInfo(){
        if( !isset($_POST['openid']) || empty($_POST['openid']) )
        {
            return;
        }

        $m_user = M('user');
        $updateUser['real_name'] = $_POST['real_name'];
        $updateUser['mobile_phone'] = $_POST['mobile_phone'];
        $updateUser['qq'] = $_POST['qq'];

        if( $m_user->where('openid="'.$_POST['openid'].'"')->find() )
        {
            $m_user->where('openid="'.$_POST['openid'].'"')->save($updateUser);
        }

    }
}