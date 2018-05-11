<?php
namespace Home\Controller;
use Think\Controller;

class IndexController extends Controller {
    
    public function index()
    {
        header("Content-type: text/html; charset=utf-8");
        echo "这是示例项目，用了原来ipmwx项目，thinkphp3框架。";
		exit;
    }

    public function access(){

    	$signature 	= $_GET["signature"];
      	$timestamp 	= $_GET["timestamp"];
      	$nonce 		= $_GET["nonce"];
      	$token      = C('WECHAT_TOKEN');
      	$echostr	= $_GET['echostr'];

      	$array = array($token, $timestamp, $nonce);
      	sort($array, SORT_STRING);
      	$str = sha1(implode($array));

      	if ($echostr && $str == $signature) {
      		//第一次接入微信api的时候
      		echo $echostr;
      		exit;
      	}else{
      		echo $this->responseMsg();
			exit;
      	}
    }

    //接受微信的事件推送并响应
    private function responseMsg(){
    	$postStr = $GLOBALS['HTTP_RAW_POST_DATA'];
      	if(empty($postStr)) return "";	
    	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        
        $result = "";//返回响应结果
    	switch ($postObj->MsgType) {
    	 	case 'event':
    	 		$result = $this->receiveEvent($postObj);
    	 		break;
    	 	case 'text':
    	 		$result = $this->receiveText($postObj);
    	 		break;
    	 } ;
    	 return $result;
    }

    //收到event类型的消息
    private function receiveEvent($postObj)
    {
    	$result = "";
    	switch ($postObj->Event) {
    		case 'subscribe':	//首次关注微信公众号

				//没有场景id（不是通过ipm关注，也就是普通关注）的情况
                if(empty($postObj->EventKey))
				{
					
					$content = "欢迎关注鈤励科技微信公众平台！现正进行i配模升级幸运大转盘活动，祝您好运！点击活动链接参加抽奖：https://hd.faisco.cn/14770248/1OagC8VCJ38Z-MSMKgAXIA/load.html?style=57";
					$result =  $this->transmitText($postObj, $content);	//回复纯文本;
					//return $result;
				}

				//下面就是通过ipm的首次关注
    			$userInfo = $this->getWechatUserInfo($postObj->FromUserName);
    			
                //把关注的用户信息存放到数据库中
                $User = M('User');
                if( $User->where('openid="'. $postObj->FromUserName .'"')->find() ){    
                    $User->save($userInfo);
                }else{
                    $User->add($userInfo);
                }
                
                //$content = "欢迎关注鈤励科技微信公众平台！";

				$content = "欢迎关注鈤励科技微信公众平台！现正进行i配模升级幸运大转盘活动，祝您好运！点击活动链接参加抽奖：https://hd.faisco.cn/14770248/1OagC8VCJ38Z-MSMKgAXIA/load.html?style=57";
				
    			//带场景id(试用扫码)
				$scene_id = substr($postObj->EventKey, 8);
                $openid = $userInfo['openid'];
                $data['openid'] = $userInfo['openid'];
                $data['scene_id'] = $scene_id;
                $Login = M('Login');
                if ($Login->where("openid='$openid'")->find() != null) {
                 $Login->where("openid='$openid'")->delete();
                }
                $Login->add($data);

                $Trail = M('Trail');
   				$userResult = $Trail->where("openid='$openid'")->select();
    			if ($userResult == null || empty($userResult))
    				$content = "欢迎使用i配模软件!";
    			else
    			{
    				$days = $userResult[0]['trail_days'];
    				$expireTime = $userResult[0]['trail_time'] + $days * 24 * 3600;
    				if ($expireTime < time())
    					$content = "欢迎使用i配模软件!您的试用时间已到，请联系售后0755-23707116。";
    				else
    					$content = "欢迎使用i配模软件!软件将于".date("Y-m-d H:i:s", $expireTime)."到期";
    			}

    			$result =  $this->transmitText($postObj, $content);	//回复纯文本;
    			break;

    		case "SCAN": 		//已经关注公众号再次扫二维码

                if(empty($postObj->EventKey)) return $result;

                //用户信息
                $userInfo = $this->getWechatUserInfo($postObj->FromUserName);
                
               //更新用户信息
                $User = M('User');
				//$reslut = $User->where('openid="'. $postObj->FromUserName .'"')->find();
				//设计院系统管理员标识为remark  ryan20171107
                if( $User->where('openid="'. $postObj->FromUserName .'"')->find() ){
					//$userInfo['remark'] = $reslut['remark'];
                    $User->save($userInfo);
                }else{
                    $User->add($userInfo);
                }

                $content = "欢迎使用i配模软件!";

                //带场景id(试用扫码)
                $scene_id = substr($postObj->EventKey, 0);
                $openid = $userInfo['openid'];
                $t['openid'] = $userInfo['openid'];
                $t['scene_id'] = $scene_id;
                $Login = M('Login');
                if ( $Login->where("openid='$openid'")->find() ) {
                    $Login->where("openid='$openid'")->delete();
                }
                $Login->add($t);

                $Trail = M('Trail');
   				$userResult = $Trail->where("openid='$openid'")->select();
    			if ($userResult == null || empty($userResult))
    				$content = "欢迎使用i配模软件!";
    			else
    			{
    				$days = $userResult[0]['trail_days'];
    				$expireTime = $userResult[0]['trail_time'] + $days * 24 * 3600;
    				if ($expireTime < time())
    					$content = "欢迎使用i配模软件!您的试用时间已到，请联系售后0755-23707116。";
    				else
    					$content = "欢迎使用i配模软件!软件将于".date("Y-m-d H:i:s", $expireTime)."到期";
    			}   
                $result =  $this->transmitText($postObj, $content); //回复纯文本;
    			break;

            case "CLICK":   //点击自定义菜单

                if(empty($postObj->EventKey)) return $result;

                $content = "";
                $event_key = substr($postObj->EventKey, 0);

                //click关键字
                switch ($event_key) {
                    case 'trailQuery':
                        $Trail = M('Trail');
                        $userResult = $Trail->where('openid="'. $postObj->FromUserName .'"')->select();
                        if ($userResult == null || empty($userResult))
                            $content = "您尚未使用i配模软件!";
                        else
                        {
                            $days = $userResult[0]['trail_days'];
                            $expireTime = $userResult[0]['trail_time'] + $days * 24 * 3600;
                            if ($expireTime < time())
                                $content = "您的i配模试用时间已到，请联系售后0755-23707116。";
                            else
                                $content = "您的i配模将于".date("Y-m-d H:i:s", $expireTime)."到期";
                        }  
                        break;
                    case 'designInst':
						$User = M('User');
                        $userResult = $User->where('openid="'. $postObj->FromUserName .'"')->select();
                        if ($userResult == null || empty($userResult))
                            $content = "您尚未使用i配模软件!";
                        else
                        {
							//$urlToDesignInst = "http://192.168.3.7/design_inst/#/index/project/project_info?login_id=".$postObj->FromUserName;
                            $content = "点击链接登录铝模设计院管理平台"."http://www.rili-tech.com/design_inst/#/index/project/project_info?login_id=".$postObj->FromUserName;//"您的i配模将于".date("Y-m-d H:i:s", $expireTime)."到期";
							}  
						break;
                    default:
                        $content = "功能正在开发中...";
                        break;
                }

                $result =  $this->transmitText($postObj, $content); //回复纯文本;
                break;
    	}
    	return $result;
    }

    //收到Text类型的消息
    private function receiveText($postObj)
    {
		switch($postObj->Content)
		{
			case '1': //大转盘抽奖
			$content = "https://hd.faisco.cn/14770248/1OagC8VCJ3-dflHaHg7Npw/load.html?style=57";
			$result =  $this->transmitText($postObj, $content); //回复纯文本;
			break;
		}
		return $result;
    }

	//返回链接
	private function transmitLinks($object, $content)
	{
		if(!isset($content) || empty($content))
	    	return "";
		$linksTpl = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[%s]]></MsgType>
						<Title><![CDATA[抽奖大转盘链接]]></Title>
						<Description><![CDATA[抽奖大转盘链接]]></Description>
						<Url><![CDATA[%s]]></Url>
						<MsgId>%s</MsgId>
					</xml>";
		$result = sprintf($linksTpl, $object->FromUserName, $object->ToUserName, time(), $content);
	  	return $result;
	}
	
	//返回纯文本
	private function transmitText($object, $content)
	{
	  	if(!isset($content) || empty($content))
	    	return "";

	  	$textTpl = "<xml>
	                    <ToUserName><![CDATA[%s]]></ToUserName>
	                    <FromUserName><![CDATA[%s]]></FromUserName>
	 		         	<CreateTime>%s</CreateTime>
			            <MsgType><![CDATA[text]]></MsgType>
			            <Content><![CDATA[%s]]></Content>
		           </xml>";
	  	$result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content);
	  	return $result;
	}

	//返回图文消息
	private function transmitNews($object, $arr_item, $funcFlag = 0)
	{
	    //首条标题28字，其他标题39字
	    if(!is_array($arr_item))
	        return;

	    $itemTpl = "<item>
	                    <Title><![CDATA[%s]]></Title>
	                    <Description><![CDATA[%s]]></Description>
	                    <PicUrl><![CDATA[%s]]></PicUrl>
	                    <Url><![CDATA[%s]]></Url>
	                </item>";
	    $item_str = "";
	    foreach ($arr_item as $item)
	        $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);

	    $newsTpl = "<xml>
	                    <ToUserName><![CDATA[%s]]></ToUserName>
	                    <FromUserName><![CDATA[%s]]></FromUserName>
	                    <CreateTime>%s</CreateTime>
	                    <MsgType><![CDATA[news]]></MsgType>
	                    <Content><![CDATA[]]></Content>
	                    <ArticleCount>%s</ArticleCount>
	                    <Articles>
	                    $item_str</Articles>
	                    <FuncFlag>%s</FuncFlag>
	                </xml>";

	    $resultStr = sprintf($newsTpl, $object->FromUserName, $object->ToUserName, time(), count($arr_item), $funcFlag);
	    return $resultStr;
	}

	//获取微信用户信息，返回数组格式
	public function getWechatUserInfo($openid)
	{
      import("@.Util.Wechat");
      $wechatUtil = new \Home\Util\Wechat();
      $access_token = $wechatUtil->getAccessToken();

      //获取用户信息
      $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access_token&openid=$openid&lang=zh_CN";
      $json = $wechatUtil->post($url);
      return json_decode($json, true);
	}
	
    private function http_post_data($url, $data_string) {  
  
        $ch = curl_init();  
        curl_setopt($ch, CURLOPT_POST, 1);  
        curl_setopt($ch, CURLOPT_URL, $url);  
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);  
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(  
            'Content-Type: application/json; charset=utf-8',  
            'Content-Length: ' . strlen($data_string))  
        );  
        ob_start();  
        curl_exec($ch);  
        $return_content = ob_get_contents();  
        ob_end_clean();  

        return $return_content;
    }  
 
	public function setRemark($openid, $remark)
	{
	    $remark = urlencode($remark);
	    
	    $User = M('User');
        if( !$User->where('openid="'. $openid .'"')->find() ){
            $res['success'] = false;
            $res['message'] = 'Openid error';
            return;
        }
        
        import("@.Util.Wechat");
        $wechatUtil = new \Home\Util\Wechat();
        $access_token = $wechatUtil->getAccessToken();
        
        $url = "https://api.weixin.qq.com/cgi-bin/user/info/updateremark?access_token=$access_token";
        $data_string = urldecode(json_encode(array('openid'=>$openid, 'remark'=>$remark)));
        $return = json_decode($this->http_post_data($url, $data_string),true);
        if($return['errcode'] == 0)
        {
            $userInfo = $this->getWechatUserInfo($openid);
            $User->save($userInfo);
            $res['success'] = true;
        }else{
             $res['success'] = false;
             $res['message'] = 'Wechat error';
        }

        echo json_encode($res);
		exit;
	}


	public function test()
	{
	    $userInfo = $this->getWechatUserInfo('ovMfqvk3Kyzg1rv96GAbNVrfXfIw');
	    header("Content-type: text/html; charset=utf-8");
        var_dump($userInfo);
	}
}
