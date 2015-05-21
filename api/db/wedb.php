<?php
include("mysql.php");
include("config.php");

/*
*封装wedding card 的一些操作
*/ 
class wedDB extends mySqlDB{
	function __construct(){
		//echo MYSQL_HOST;
		$config = array(
			"host" => MYSQL_HOST,
			"user" => MYSQL_USER,
			"password" => MYSQL_PSW,
			"database" => MYSQL_DB
		);
		mySqlDB::__construct($config);
	}

	/*
	*根据微信openid 获取对应用户编号
	*返回用户编号
	*/
	public function getUserID($openid){
		$sql = 'select userid from UserInfo where openid=?';
		$ret = $this->query($sql,($openid));
		if(is_null($ret))
			return -1;
		if(empty($ret)){
			return 0;
		}else
			return $ret[0]['userid'];
	}

	/*
	* 保存微信用户信息
	* 返回微信用户信息对应本应用id
	*/
	public function saveUserInfo($openid,$nickname,$city,$sex,$province,$country,$headimgurl){
		$userid = $this->getUserID($openid);
		if($userid > 0)
			return $userid;
		$sql = 'insert into UserInfo(openid,nickname,city,sex,province,country,headimgurl)'.
			'values(?,?,?,?,?,?,?)';
		$params = array($openid,$nickname,$city,$sex,$province,$country,$headimgurl);
		$userid = $this->insert($sql,$params);
		return $userid;
	}

	/*
	 *获取用户卡片编号
	 *一个用户一个卡片
	 * 返回卡片编号,-1,表示无结果
	*/
	 public function getWedcardId($userid){
	 	$sql = 'select cardid from WedCard where userid=?';
	 	$ret = $this->query($sql,($userid));
	 	if(is_null($ret))
			return -1;
		if(empty($ret)){
			return 0;
		}else
			return $ret[0]['cardid'];
	 }

	/*
	*保存婚礼邀请卡内容
	*保存成功，还回卡片编号,保存失败,返回-1
	*cardid:卡片编号,userid:用户编号,malename:男方姓名,femalename:女方姓名
	*time:婚宴时间,posx:经度,posy:维度,address:地址,introduce:正文,
	*picwellid: 欢迎页图片ID
	*pichomeid: 首页图片
	*picdetailid: 详情页图片
	*/
	public function saveWedcardInfo($cardInfo){
		$userid = $cardInfo["userid"]; //用户编号不能为空
		//$cardid = $cardInfo[""]
		$keys = array('malename','femalename','time','posx','posy',
			'address','introduce','picwellid','pichomeid','picdetailid');
		$cardid = $this->getWedcardId($userid);
		//echo $cardid;
		//exit;
		if($cardid <= 0){
			//不存在，新建一个
			$sql = 'insert WedCard(userid) values(?)';
			$cardid = $this->insert($sql,($userid));
			if($cardid <= 0)
				return -1; //保存失败
		}

		$fiels = 'userid=userid';
		$values = array();
		foreach($keys as $key){
			if(array_key_exists($key,$cardInfo)){
				$fiels = $fiels . ",$key=?";
				array_push($values,$cardInfo[$key]);
			}
		}
		array_push($values,$cardid);
		//var_dump($fiels);
		//var_dump($values);
		$sql = "update WedCard set $fiels where cardid=?";
		//echo $sql;

		return $this->update($sql,$values);
	}

	/*
	* 获取请帖详情
	*/
	public function getWeddingCardInfo($userid){
		return $this->query("select * from WedCard where userid=?",($userid));
	}
}

//wedDB::test();
//$db = new wedDB();
//$db->test();
//echo $db->update("update UserInfo set nickname=?",("王风云"));
//var_dump($db->query("select * from UserInfo where openid=?",("0123")));
//echo $db->getUserID("0123");
//$id = $db->saveUserInfo('0123','风云521','阜阳',1,'安徽','中国','img/123.jpg');
//echo $id;
//var_dump($db->query("select * from UserInfo where userid=?",($id)));
/*
$cardInfo = array('userid' => 42,
	'malename'=>'男孩',
	'femalename'=>'女孩',
	'time'=>'2015-01-02',
	'posx'=>168.756879,
	'posy'=>'10.123454',
	'address'=>'北京朝阳区',
	'introduce' => '欢迎大家光临寒舍',
	'picwellid' => 1,
	'pichomeid' => 2,
	'picdetailid' => 3);
$db->saveWedcardInfo($cardInfo);
var_dump($db->query("select * from WedCard where userid=?",(42)));
*/
?>