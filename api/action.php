<?php 

include('db/wedb.php');

const CODE_OK = 0;         //正常
const CODE_UNKNOWERR = -1; //未知错误
const CODE_UNLOGIN = 1;    //未登录
const CODE_PARAMSERROR = 2;//参数错误
const CODE_SAVE_ERROR = 3; //保存失败

/*
响应web请求的基类
*/

class actionBase{
	public $actionRet = null; //返回的数据集
	public $code;             //返回码
	public $codeStr = '';     //返回码描述
	public $db;        //数据库操作

	public function __construct(){
		$this->db = new wedDB();
		$this->code = 0;
		$this->actionRet = null;
		$this->codeStr = '';
	}

	/*
	* 返回json格式数据
	*/
	public function doJson(){
		if($this->codeStr == ''){
			 $this->codeStr = $this->getCodeStr($this->code);
		}
		if($this->code != 0){
			$ret = array(
				"code" => $this->code,
				"msg" => $this->codeStr
			);
		}else
		{
			$ret = array(
				"code" => $this->code,
				"msg" => $this->codeStr,
				"data" => $this->actionRet
			);
		}
		return json_encode($ret);
	}

	/*
	* 获取错误码对应错误信息
	*/
	public function getCodeStr($code){
		if($code === 0){
			return 'ok';
		}elseif($code === 1){
			return '未登录';
		}elseif ($code === 2){
			return '请求参数错误';
		}else if ($code === 3) {
			return '提交失败';
		}
		else{
			return '系统异常';
		}
	}

	/*
	*检查是否登陆
	*/
	
	public function checkLogin(){
		session_start();
		if(!isset($_SESSION['user'])){
			$this->code = 1;
			return false;
		}
		else
			return true;
	}
	/*
	*获取用户编号,从session中获取
	*/
	public function getUserID(){
		if($this->checkLogin()){
			$user = $_SESSION['user'];
			return $user['userid'];
		}else
			return -1;
	}

	/*
	*获取web请求参数
	*返回false则参数不存在
	*/
	public function getParam($key,&$value){
		@$value = $_REQUEST[$key];
		if(empty($value))
			return false;
		else 
			return true;
	}

	/*
	* 返回jsonp格式数据
	*/
	public function doJsonp(){
		@$callback = $_REQUEST['callback'];
		if(empty($callback)){
			$callback = '';
		}
		$json = $this->doJson();
		if($callback == '')
			return $json;
		else
			return "$callback($json)";
	}

	/*
	* 图片信息
	*/
	public function getMediaInfo($id,$wxid,$path){
		return array(
				"id" => $id,
				"wxid" => $wxid,
				"path" => $path
			);
	}
}

/*
* 处理请帖查询和更新
*/

class weddingCardAction extends actionBase{

	/*
	* 查询请帖信息
	* 可以不登陆
	* 请求参数: userid
	*/
	public function queryCard(){
		if(!$this->getParam('userid',$userid)){
			$this->code = CODE_PARAMSERROR;
			$this->codeStr = '未指定用户编号';
			return;
		}
		$cardinfo = $this->db->getWeddingCardInfo($userid);
		//echo $userid;
		//var_dump($cardinfo);
		if(empty($cardinfo)){
			return;
		}
		$cardinfo = $cardinfo[0];
		$this->actionRet = array(
			'cardid' => $cardinfo['cardid'],               //卡片编号
			'boys_name' => $cardinfo['malename'],          //男方姓名
			'girls_name' => $cardinfo['femalename'],        //女方姓名
			'wedding_date' => $cardinfo['time'],           //结婚时间
			'wedding_location' => $cardinfo['address'],    //结婚地点 
			'posx' => $cardinfo['posx'],  //经度
			'posy' => $cardinfo['posy'],  //维度
			'card' => $cardinfo['introduce'],       //卡片正文
			'pic_well' => $this->getMediaInfo($cardinfo['picwellid'],null,null),    //欢迎页图片编号
			'pic_home' => $this->getMediaInfo($cardinfo['pichomeid'],null,null),    //主页图片编号
			'pic_detail' => $this->getMediaInfo($cardinfo['picdetailid'],null,null) //详情页图片
		);  

		//$this->doJsonp();
	}

	/*
	*更新请帖信息
	*必须登陆
	*userid:用户编号
	*
	*/
	public function saveCardInfo(){
		$userid = $this->getUserID();
		//$userid = 1;
		if($userid <= 0){
			//未登陆
			$this->code = CODE_UNLOGIN;
			return;
		}

		$cardinfo = array('userid' => $userid);
		$keys = array('boys_name' => 'malename',
			'girls_name' => 'femalename',
			'wedding_date' => 'time',
			'wedding_location' => 'address',
			'posx' => 'posx',
			'posy' => 'posy',
			'card' => 'introduce',
			'pic_well_id' => 'picwellid',
			'pic_home_id' => 'pichomeid',
			'pic_detail_id' => 'picdetailid'
			);

		foreach ($keys as $key => $value) {
			if($this->getParam($key,$param)){
				$cardinfo[$value] = $param;
			}
		}

		//echo $cardinfo['time'];// = '2014-01-01';

		if($this->db->saveWedcardInfo($cardinfo) < 0){
			$this->code = CODE_SAVE_ERROR;
		}else 
			$this->code = CODE_OK;
	}


}


?>