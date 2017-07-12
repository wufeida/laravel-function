<?php
/**
 * @param $code 是否成功的返回码
 * @param null $msg 数据
 * @return json数据
 */
function custom_json($code,$msg=null)
{
    if ($msg)
    {
        return response()->json(['code'=>$code,'msg'=>$msg]);
    }else
    {
        return response()->json($code);
    }
}

/**
 * 接收数据 去除html标签
 */
function g($str)
{
    return strip_tags(\Illuminate\Support\Facades\Input::get($str));
}

/**
 * @param $type 类型
 * oneNum：检测一位数字
 * num：检测数字
 * phone：检测手机号
 * email：检测邮箱
 * chinese：检测纯汉字
 * nickname：检测昵称
 * password：检测密码
 * date：检测日期格式 例如:2017-05-26
 * time：检测时间格式 例如:2017-05-26 11:41:50
 * space：检测空格
 * symbol：检测非法字符
 * isNull：检测是否为null
 * url:检测网址
 * engnum:检测英文和数字
 * @param $data 要检测的数据
 * @param $name 测试的数据名称
 * @param $mix 要检测的数据最小长度
 * @param $max 要检测的数据最大长度
 * @return bool|null
 */
function check($name,$type,$data,$mix=null,$max=null)
{
    if ($data === null || $data === '')
    {
        return inf('error',$name.'不能为空');
    }
    $cou = mb_strlen($data);
    if ($mix && $max)
    {
        if ($cou < $mix)
        {
            return inf('error',$name.'长度小于'.$mix);
        }elseif ($cou > $max)
        {
            return inf('error',$name.'长度大于'.$max);
        }
    }
    if ($mix && $max == false)
    {
        if ($cou > $mix)
        {
            return inf('error',$name.'长度大于'.$mix);
        }
    }
    switch ($type)
    {
        case 'isNull':
            if ($data === null || $data === '')
                return inf('error',$name.'不能为空');
            return inf($data);
        //检测一个数字
        case 'oneNum':
            if (preg_match('/^\d{1}$/i', $data))
                return inf($data);
            return inf('error',$name.'格式错误');
        //检测数字
        case 'num':
            if (preg_match('/^\d+$/i', $data))
                return inf($data);
            return inf('error',$name.'格式错误');
        //检测手机
        case 'phone':
            if (preg_match('/^1\d{10}$/',$data))
                return inf($data);
            return inf('error',$name.'格式错误');
        //检测邮箱
        case 'email':
            if (filter_var($data,FILTER_VALIDATE_EMAIL))
                return inf($data);
            return inf('error',$name.'格式错误');
        //检测纯汉字
        case 'chinese':
            if (preg_match("/^[\x{4e00}-\x{9fa5}]+$/u",$data))
                return inf($data);
            return inf('error',$name.'格式错误');
        //检测昵称
        case 'nickname':
            $preg='/^\S{3,16}$/u';
            $preg2='/^[a-zA-Z]{5,16}$/u';
            $preg3='/^[\x{4e00}-\x{9fa5}]{3,16}$/u';
            if (preg_match("/\s/u",$data)) return inf('error',$name.'格式错误');
            if (preg_match($preg2,$data) || preg_match($preg3,$data))
            {
                return inf($data);
            }elseif (preg_match($preg,$data))
            {
                return inf($data);
            }
            return inf('error',$name.'格式错误');
        //检测密码
        case 'password':
            if (preg_match("/[\x{4e00}-\x{9fa5}]+/u",$data)) return inf('error',$name.'格式错误');
            if (preg_match("/^[\S]{6,35}$/u",$data))
                return inf($data);
            return inf('error',$name.'格式错误');
        //检测日期格式 例如:2017-05-26
        case 'date':
            if (preg_match("/^\d{4}-\d{2}-\d{2}$/s",$data))
                return inf($data);
            return inf('error',$name.'格式错误');
        //检测时间格式 例如:2017-05-26 11:41:50
        case 'time':
            if (preg_match("/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/s",$data))
                return inf($data);
            return inf('error',$name.'格式错误');
        //检测空格
        case 'space':
            if (preg_match("/\s/u",$data))
                return inf('error',$name.'格式错误');
            return inf($data);
        //检测非法字符
        case 'symbol':
            $preg = '/[\~\!\@\#\$\%\^\&\*\<\>]+/u';
            if (preg_match($preg,$data))
                return inf('error',$name.'格式错误');
            return inf($data);
        //检测url
        case 'url':
            if (filter_var($data,FILTER_VALIDATE_URL))
            return inf($data);
            return inf('error',$name.'格式错误');
        //检测英文和数字
        case 'engnum':
            $preg = '/^[a-zA-Z0-9]+$/';
            if (preg_match($preg,$data))
            return inf($data);
            return inf('error',$name.'格式错误');
    }
}

/**
 * 检测后返回数据
 * @param $code
 * @param null $msg
 * @return array
 */
function inf($code,$msg=null)
{
    if ($msg)
    {
        $data[] = $code;
        $data[] = $msg;
    }else
    {
        $data = $code;
    }
    return $data;
}

/**
 * 分页数据
 * @param $count 数据总数
 * @param $size  每页显示数量
 * @param $data  分页的数据
 * @return mixed
 */
function page($count,$size,$data)
{
    $rr['res']['count'] = $count;
    $rr['res']['cou_page'] = intval(ceil($count/$size));
    $rr['data'] = $data;
    return $rr;
}

/**
 * 无限极分类
 * note:主键是id 父级键是parent_id
 * @param object $data 需要分类的数据
 * @param string $pid  父级id 默认从0开始
 * @param string $level  级别 默认从0开始
 * @return array
 */
function tree($data,$pid='0',$level='0')
{
    $tree = array();
    foreach ($data as $v)
    {
        if ($v->parent_id==$pid)
        {
            $v->level = $level;
            $v->child = tree($data,$v->id,$level+1);
            $tree[] = $v;
        }
    }
    return $tree;
}


/**
 * 检测PC Mobile
 */
function CheckSubstrs($substrs,$text)
{
    foreach($substrs as $substr)
        if(false!==strpos($text,$substr))
        {
            return true;
        }
    return false;
}
function isMobile()
{
    $useragent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $useragent_commentsblock = preg_match('|\(.*?\)|', $useragent, $matches) > 0 ? $matches[0] : '';
    $mobile_os_list = array('Google Wireless Transcoder', 'Windows CE', 'WindowsCE', 'Symbian', 'Android', 'armv6l', 'armv5', 'Mobile', 'CentOS', 'mowser', 'AvantGo', 'Opera Mobi', 'J2ME/MIDP', 'Smartphone', 'Go.Web', 'Palm', 'iPAQ');
    $mobile_token_list = array('Profile/MIDP', 'Configuration/CLDC-', '160×160', '176×220', '240×240', '240×320', '320×240', 'UP.Browser', 'UP.Link', 'SymbianOS', 'PalmOS', 'PocketPC', 'SonyEricsson', 'Nokia', 'BlackBerry', 'Vodafone', 'BenQ', 'Novarra-Vision', 'Iris', 'NetFront', 'HTC_', 'Xda_', 'SAMSUNG-SGH', 'Wapaka', 'DoCoMo', 'iPhone', 'iPod');
    $found_mobile = CheckSubstrs($mobile_os_list, $useragent_commentsblock) || CheckSubstrs($mobile_token_list, $useragent);
    if ($found_mobile) {
        return true;
    } else {
        return false;
    }
}

    /**
     * 获取真实IP
     */
    function GetIp(){  
        $realip = '';  
        $unknown = 'unknown';  
        if (isset($_SERVER)){  
            if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)){  
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                foreach($arr as $ip){  
                    $ip = trim($ip);  
                    if ($ip != 'unknown'){  
                        $realip = $ip;  
                        break;  
                    }  
                }  
            }else if(isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP']) && strcasecmp($_SERVER['HTTP_CLIENT_IP'], $unknown)){  
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            }else if(isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']) && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)){
                $realip = $_SERVER['REMOTE_ADDR'];
            }else{  
                $realip = $unknown;  
            }  
        }else{  
            if(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), $unknown)){  
                $realip = getenv("HTTP_X_FORWARDED_FOR");
            }else if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), $unknown)){  
                $realip = getenv("HTTP_CLIENT_IP");
            }else if(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), $unknown)){
                $realip = getenv("REMOTE_ADDR");
            }else{
                $realip = $unknown;  
            }  
        }  
        $realip = preg_match("/[\d\.]{7,15}/", $realip, $matches) ? $matches[0] : $unknown;  
        return $realip;  
    }

    /**
     * 获取ip所对应的地点
     * @param string $ip
     * @return bool|mixed
     */
    function GetIpLookup($ip = ''){  
        if(empty($ip)){  
            $ip = GetIp();  
        }  
        $res = @file_get_contents('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip=' . $ip);  
        if(empty($res)){ return false; }  
        $jsonMatches = array();  
        preg_match('#\{.+?\}#', $res, $jsonMatches);  
        if(!isset($jsonMatches[0])){ return false; }  
        $json = json_decode($jsonMatches[0], true);  
        if(isset($json['ret']) && $json['ret'] == 1){  
            $json['ip'] = $ip;  
            unset($json['ret']);  
        }else{  
            return false;  
        }  
        return $json;  
    }

