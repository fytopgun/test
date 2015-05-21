<?php
	include('wx/wxapi.php');
	include('db/wedb.php');
	session_start();
	@$code = $_GET["code"];
	echo("code=".@$code);
	//exit;
	if(empty($code))
	{
		echo "can not get user info";
		exit();
	}
	$wx = new wxApi();
 	$user = $wx->GetUserInfo($code);
 	//echo(123);
 	$db = new wedDB();
 	$userid = $db->getUserID($user['openid']);

 	if($userid<0){
 		//$openid,$nickname,$city,$sex,$province,$country,$headimgurl
 		$userid = $db->saveUserInfo($user['openid'],$user['nickname'],
 		 $user['city'], $user['sex'], $user['province'], $user['country'], $user['headimgurl']);
 	}
 	$user = array_merge_recursive($user,array('userid'=>$userid));

 	$_SESSION['user'] = $user;

 	var_dump($user);
?>