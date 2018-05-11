<?php
namespace Home\Controller;
use Think\Controller;
//20170922Ryan添加幸运大转盘
//20171023Ryan将幸运大转盘替换成铝模设计院
class MenuController extends Controller {
    
    //自定义菜单
    public function index()
    {
        $jsonmenu = 
        '{
            "button": [
                {
                    "name": "鈤励科技",
                    "sub_button": [
                        {
                            "type": "view",
                            "name": "公司简介",
                            "url": "http://www.rili-tech.com/rili-website/introduction.php"
                        },                        
                        {
                            "type": "view",
                            "name": "企业官网",
                            "url": "http://www.rili-tech.com"
                        },
                        {
                            "type": "click",
                            "name": "技术服务",
                            "key": "jishu"
                        },
                        {
                            "type": "view",
                            "name": "招贤纳士",
                            "url": "http://mp.weixin.qq.com/s?__biz=MzIwMzA1NzU5Ng==&mid=211143647&idx=1&sn=3400635918449aac4c75c3d9cd65cd39#rd"
                        }
                    ]
                },
                {
                    "name": "i配模",
                    "sub_button": [
                        {
                            "type": "view",
                            "name": "软件简介",
                            "url": "http://www.rili-tech.com/rili-website/product-ipm.php"
                        },
                        {
                            "type": "click",
                            "name": "试用查询",
                            "key": "trailQuery"
                        },
                        {
                            "type": "view",
                            "name": "用户管理",
                            "url": "http://www.rili-tech.com/crm"
                        }
                    ]
                },
				{
                    "name": "设计院",
                    "sub_button": [
                        {
                            "type": "view",
                            "name": "铝模设计院",
                            "url": "http://www.rili-tech.com/wechat"
                        },
                    ]
                },              
            ]
        }';
		//"url": "https://hd.faisco.cn/14770248/1OagC8VCJ38Z-MSMKgAXIA/load.html?style=57"
		/*暂时替换成幸运转盘20170922Ryan
		 {
                    "name": "分享交流",
                    "sub_button": [
                        {
                            "type": "view",
                            "name": "工程展示",
                            "url": "http://mp.weixin.qq.com/s?__biz=MzIwMzA1NzU5Ng==&mid=402085141&idx=1&sn=2b3775e7057599fe7b3bf62f89bccab3#rd"
                        },
                        {
                            "type": "click",
                            "name": "专题分享",
                            "key": "user_share"
                        },
                        {
                            "type": "click",
                            "name": "优秀作品",
                            "key": "coding"
                        }
                    ]
                }*/

        // access_token
        import("@.Util.Wechat");
        $wechatUtil = new \Home\Util\Wechat();
        $access_token = $wechatUtil->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token;
        $wechatUtil->post($url, $jsonmenu);
        header("Content-type: text/html; charset=utf-8");
        echo "更新自定义菜单，等待微信更新";
    }
}
