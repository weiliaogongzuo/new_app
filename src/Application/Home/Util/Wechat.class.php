<?php
namespace Home\Util;

class Wechat
{
	public function post($url, $data = null)
	{
	   	$curl = curl_init();
	    curl_setopt($curl, CURLOPT_URL, $url);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	    if (!empty($data))
	    {
	        curl_setopt($curl, CURLOPT_POST, 1);
	        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	    }

	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	    $output = curl_exec($curl);
	    curl_close($curl);
	    return $output;
	}

	public function getAccessToken()
	{
/*	    $request = false;
	    $tokenJson = file_get_contents(APP_PATH."Home/access_token.txt");
	    $array = json_decode($tokenJson, true);
	    if($array == null){
	        $request = true;
	    }else{
	        if($array["access_token"] == null || empty($array["access_token"]) ||
	           $array["expires_in"] == null || empty($array["expires_in"]) ||
	           $array['time'] == null || empty($array["time"]) ){
	               $request = true;
	           }
	       else{
	           if(time() > $array["expires_in"] + $array['time'])
	                $request = true;
	       }
	    }
	   
	    if(!$request) return $array["access_token"];
*/	    
		$appid = C('WECHAT_APPID');
      	$appsecret = C('WECHAT_APPSECRET');
		
		//获取access_token
      	$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
      	$json =  $this->post($url);
      	$array = json_decode($json, true);
      	$array['time'] = time();
      	file_put_contents(APP_PATH."Home/access_token.txt", json_encode($array));
      	return $array["access_token"];
	}
}
