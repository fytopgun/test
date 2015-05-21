<?php

//$wx = new wxApi();
//$wx->test();

/**
*封装微信的一些操作
*/
class wxApi 
{
	private $appId = "wx49dbc6655b89326f"; //"wxa78d8e4fc30f0735";//
	private $appSecret = "f586d4dc704d7f35d251d6aa5a2a81f0"; //"c029faf4ccb7068bfe206c333ddd525a";//
	const tokenURL = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s";
	const homeURL = "http://www.wifimeishi.cn/wedding/wxuser.php";
	function __construct()
	{
		# code...
	}

	private function httpGet($url) {
    	$curl = curl_init();
    	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($curl, CURLOPT_TIMEOUT, 500);
    	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    	curl_setopt($curl, CURLOPT_URL, $url);

    	$res = curl_exec($curl);
    	curl_close($curl);

    	return $res;
    }

	/**
	* http get 请求
	*/
	public function get($url)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		//curl_setopt($curl, CURLOPT_HEADER, 1);
		//curl_setopt($ch, CURLOPT_TIMEOUT, 3);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($curl);
		curl_close($curl);
		return $data;
	}

	/**
	* https get 请求
	*/
	public function gets($url)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		//curl_setopt($curl, CURLOPT_HEADER, 1);	
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。
		$data = curl_exec($curl);
		curl_close($curl);
		//var_dump($data);
		return $data;
	}

	/**
	* 获取微信 token
	*/
	public function getToken(&$token,&$time)
	{
		//@$url = sprintf( $this::tokenURL,$this::appiD,$this::appsecret);
		//echo($url);
		$data = $this->gets($url);
		$json = json_decode($data);
		if(empty($json->access_token))
		{
			//获取token信息失败
			return false;
		}else
		{
			//获取token信息成功
			$token = $json->access_token;
			$time = $json->expires_in;
			return true;
		}
	}

	/**
	*获取随机串
	*/
	private function createNonceStr($length = 16) {
    	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    	$str = "";
    	for ($i = 0; $i < $length; $i++) {
      		$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    	}
    	return $str;
    }

    /**
    *获取jsticket
    **/
    private function getJsApiTicket() {
     // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
    	$data = json_decode(file_get_contents("jsapi_ticket.json"));
    	if ($data->expire_time < time()) {
      		$accessToken = $this->getAccessToken();
      	// 如果是企业号用以下 URL 获取 ticket
      	// $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
      	$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
      	$res = json_decode($this->httpGet($url));
      	$ticket = $res->ticket;
      	if ($ticket) {
        	$data->expire_time = time() + 7000;
        	$data->jsapi_ticket = $ticket;
        	$fp = fopen("jsapi_ticket.json", "w");
        	fwrite($fp, json_encode($data));
        	fclose($fp);
      	}
    	} else {
      		$ticket = $data->jsapi_ticket;
    	}

    	return $ticket;
  	}

  	/**
    *获取AccessToken
    **/
  	private function getAccessToken() {
    	// access_token 应该全局存储与更新，以下代码以写入到文件中做示例
    	$data = json_decode(file_get_contents("access_token.json"));
    	if ($data->expire_time < time()) {
      	// 如果是企业号用以下URL获取access_token
      	// $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
      		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
      		//echo $url;
      		$res = json_decode($this->httpGet($url));
      		$access_token = $res->access_token;
      	if ($access_token) {
        	$data->expire_time = time() + 7000;
        	$data->access_token = $access_token;
        	$fp = fopen("access_token.json", "w");
        	fwrite($fp, json_encode($data));
        	fclose($fp);
      	}
    	} else {
    		//echo "122";
      		$access_token = $data->access_token;
    	}
    	return $access_token;
  	}

  	public function getSignPackage($url) {
    	$jsapiTicket = $this->getJsApiTicket();

    	// 注意 URL 一定要动态获取，不能 hardcode.
    	$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    	//$url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    	$timestamp = time();
    	$nonceStr = $this->createNonceStr();

    	// 这里参数的顺序要按照 key 值 ASCII 码升序排序
    	$string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

    	$signature = sha1($string);

    	$signPackage = array(
      		"appId"     => $this->appId,
      		"nonceStr"  => $nonceStr,
      		"timestamp" => $timestamp,
      		"url"       => $url,
      		"signature" => $signature,
      		"rawString" => $string
    		);
    	return $signPackage; 
   	}

   	/*
   	* 跳转到微信登陆授权页
   	*/
   	public function goOAuth()
   	{
   		$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->appId.
   			'&redirect_uri=' .$this::homeURL.'&response_type=code&scope=snsapi_userinfo&state=123#wechat_redirect';  
   		//echo $url;
    	header("Location:".$url);
   	}

   	/*
   	* 获取用户信息
   	*参数code为微信授权code
   	*/
   	public function GetUserInfo($code)
   	{
   		$get_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='
   			.$this->appId.'&secret='.$this->appSecret.'&code='.$code.'&grant_type=authorization_code';  
		$res = $this->httpGet($get_token_url);
		//echo $res; 
		$json_obj = json_decode($res,true);  
  
		//根据openid和access_token查询用户信息  
		@$access_token = $json_obj['access_token'];  
		@$openid = $json_obj['openid'];  
		$get_user_info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='
			.$access_token.'&openid='.$openid.'&lang=zh_CN';  
		$res = $this->httpGet($get_user_info_url);
		//$res = mb_convert_encoding($res,"UTF-8","GBK"); 
		//echo $res;
		//解析json  
		$user_obj = json_decode($res,true);
		return $user_obj;  
		//$_SESSION['user'] = $user_obj;  
		//print_r($user_obj);  
   	}
	/*
	* 测试
	*/
	public function test()
	{
		//$data = $this->get("http://www.baidu.com");
		//echo($data);
		/*
		if ($this->getToken($token,$time))
		{
			echo "token:". $token;
			echo "  time:". $time;
		}
		else
			echo "get token failed";
			*/

		//echo($this->getAccessToken());

		//echo($this->getJsApiTicket());
		//echo("123");
		
		$data = json_encode($this->getSignPackage());
		@$jsonp = $_GET["callback"];
		//echo $jsonp;
		if(empty($jsonp))
		{
			echo $data;
		} else
		{
			echo "$jsonp($data)";
		}
	}
}
?>