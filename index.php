<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>小米运动步数</title>
<style type="text/css">
form {border:dashed}
        .juzhong {text-align: left; margin: 50px auto; padding-left:50px; width: 800px; font-size: 20px; font-family: "宋体";}
        .youduiqi{margin-left:655px; margin-top: 10px;}
        .shuru {width: auto}
        .zhanghao{width: 200px; height: 25px; margin-top: 10px; margin-bottom: 10px;}
        .mima{width: 200px;height: 25px;margin-top: 10px; margin-bottom: 10px;}
        .bushu{width: 200px;height: 25px;margin-top: 10px; margin-bottom: 10px;}
        form {margin: 20px auto;}
</style>
</head>
  
<body>
<div class="juzhong" style="text-align:center;">
        <p align="center" style="margin-bottom: 30px;">小米运动刷步</p>
        <form method="get" action="./">
                小米运动账号<br><input type="text" class="zhanghao" name="user" />
                <br>
                小米运动密码<br><input type="text" class="mima" name="password" />
                <br>
                小米运动步数<br><input type="text" class="bushu" name="step" />
                <br>
                <div class="youduiqi">
                <input type="reset" value="重置"> <input type="submit" value="确定">
                </div>
        </form>
        </div>
<div class="juzhong" style="text-align:center;">
<?php

function request_post($url = '', $post_data = array(), $header = array()) {
    if (empty($url) || empty($post_data)) {
        return false;
    }
    if (empty($header)) {
        $header = 0;
    }
    $o = "";
    foreach ( $post_data as $k => $v )
    {
        $o.= "$k=" . urlencode( $v ). "&" ;
    }
    $post_data = substr($o,0,-1);
    $postUrl = $url;
    $curlPost = $post_data;
    $ch = curl_init();//初始化curl
    curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
    curl_setopt($ch, CURLOPT_HEADER, true);// 返回 response_header, 该选项非常重要,如果不为 true, 只会获得响应的正文
    curl_setopt($ch,CURLOPT_HTTPHEADER,$header);//设置header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
    $data = curl_exec($ch);//运行curl
    curl_close($ch);
    // print_r($data."</br>");
    return $data;
}


function request_get($url = '', $header = array()) {
    if (empty($url)) {
        return false;
    }
    if (empty($header)) {
        $header = 0;
    }
    $postUrl = $url;
    $ch = curl_init();//初始化curl
    curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
    curl_setopt($ch, CURLOPT_HEADER, true);// 返回 response_header, 该选项非常重要,如果不为 true, 只会获得响应的正文
    curl_setopt($ch,CURLOPT_HTTPHEADER, $header);//设置header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $data = curl_exec($ch);//运行curl
    curl_close($ch);
    // print_r($data."</br>");
    return $data;
}

//匹配返回值
function get_code($location,$key){
    preg_match_all("/\"".$key."\":\"([^\"]*)\"/",$location,$code); 
    // print_r("返回的请求中</br>");
    // print_r($ke."</br>");
    // print_r($code[1][0]."</br>");
    $code = $code[1][0];
    if(isset($code)){
        print_r($key."获取成功！</br>");
        return $code;
    }else{
        print_r($key."获取失败！</br>");
        return 0;
    };

}

function login($user,$password){
    $url1 = "https://api-user.huami.com/registrations/+86".$user."/tokens";
    $data1['client_id'] = 'HuaMi';
    $data1['password'] = $password;
    $data1['redirect_uri'] = 'https://s3-us-west-2.amazonaws.com/hm-registration/successsignin.html';
    $data1['token'] = 'access';
    $header = array();
    $header[] = 'Content-Type:application/x-www-form-urlencoded;charset=UTF-8';
    $header[] = 'User-Agent:MiFit/4.6.0 (iPhone; iOS 14.0.1; Scale/2.00)';

    $res1 = request_post($url1, $data1, $header);
    
    preg_match_all("/access=([^&]*)&/",$res1,$code);
    $code = $code[1][0];
    // print_r("返回的请求中</br>");
    // print_r("access</br>");
    // print_r($code."</br>");
    if(isset($code)){
        print_r("access_code获取成功！</br>");
    }else{
        print_r("登录失败！</br>");
        return array(0,0);
    }

    $url2 = "https://account.huami.com/v2/client/login";
    $data2['app_name'] = "com.xiaomi.hm.health";
    $data2['app_version'] = "4.6.0";
    $data2['code'] = $code;
    $data2['country_code'] = "CN";
    $data2['device_id'] = "2C8B4939-0CCD-4E94-8CBA-CB8EA6E613A1";
    $data2['device_model'] = "phone";
    $data2['grant_type'] = "access_token";
    $data2['third_name'] = "huami_phone";
    $res2 = request_post($url2, $data2, $header);
    
    $login_token = get_code($res2,"login_token");
    $user_id = get_code($res2,"user_id");
    return array($login_token,$user_id);

}


#获取app_token
function get_app_token($login_token){
    $url = "https://account-cn.huami.com/v1/client/app_tokens?app_name=com.xiaomi.hm.health&dn=api-user.huami.com%2Capi-mifit.huami.com%2Capp-analytics.huami.com&login_token=".$login_token."&os_version=4.1.0";
    $header = array();
    $header[] = 'User-Agent:MiFit/4.6.0 (iPhone; iOS 14.0.1; Scale/2.00)';
    $response = request_get($url,$header);
    
    $app_token = get_code($response,"app_token");

    return $app_token;
}


function main($user, $password, $step){
    print_r("账号是".$user."密码是".$password."步数是".$step."</br>");

    $login= login($user,$password);
    // print_r($login."</br>");
    $login_token = $login[0];
    $user_id = $login[1];
    // print_r("获取的：login_token</br>");
    // print_r($login_token."</br>");
    // print_r("获取的：user_id</br>");
    // print_r($user_id."</br>");
    if(empty($login_token)){
        print_r("登陆失败！</br>");
        return "login fail!";
    }

    $app_token = get_app_token($login_token);
    $data_json = file_get_contents('data_json.txt');
    $data_json = $data_json.date('Y-m-d')."\"}]"; // 拼接json
    // print_r("$data_json</br>");
    $arr1 = array("12345", "321123");
    $arr2 = array($step, "DA932FFFFE8816E7");
    $data_json = str_replace($arr1, $arr2, $data_json);
    // print_r("拼接上传的 data_json</br>");
    // print_r($data_json."</br>");

    
    $url = "https://api-mifit-cn.huami.com/v1/data/band_data.json?&t=".time();
    // print_r($url."</br>");

    $header = array();
    $header[] = 'apptoken:'.$app_token;
    $header[] = 'User-Agent:Mozilla/5.0 (iPhone; CPU iPhone OS 13_4_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 MicroMessenger/7.0.12(0x17000c2d) NetType/WIFI Language/zh_CN';

    
    $data['data_json'] = $data_json;
    $data['userid'] = $userid;
    $data['device_type'] = '0';
    $data['last_sync_data_time'] = '1628426964';
    $data['last_deviceid'] = 'DA932FFFFE8816E7';

    $response = request_post($url, $data, $header);
    // print_r($response);
    // print_r("</br>");

    $message = get_code($response,"message");  //success
    // print_r($message);
    // print_r("</br>");
    if($message=="success"){
        print_r("步数上传成功！！！");
    }

    $rest = substr($response, 0, 10);  //HTTP/2 200
    print_r($rest);
    print_r("</br>");

    return $message;
}

if(array_key_exists("user", $_POST)|array_key_exists("user", $_GET)){
    if(isset($_POST["user"])){$user = $_POST["user"];}else{$user = $_GET["user"];}
    if(isset($_POST["password"])){$password = $_POST["password"];}else{$password = $_GET["password"];}
    if(isset($_POST["step"])){$step = $_POST["step"];}else{$step = $_GET["step"];}
    main($user, $password, $step);
}else{
    print_r("上面输入账号信息后，在来看看");
}


?>
</div>
<div class="juzhong" style="text-align:center;">
<a href="https://www.unkaer.cf/" target="_blank">作者</a></div>
</body>
</html>