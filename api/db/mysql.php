<?php

//mySqlDB::test();

class mySqlDB{
	private $dbh;
	private $host;
	private $user;
	private $psw;
	private $dbase;

	function __construct($config){
		$this->host = $config["host"];
		//print_r($this->host);
		$this->user = $config["user"];
		//print_r($this->user);
		$this->psw = $config["password"];
		//print_r($this->psw);
		$this->dbase = $config["database"];
		//print_r($this->dbase);
		//try {
		$this->dbh = new PDO("mysql:host=".$this->host.";dbname=".$this->dbase,$this->user,$this->psw);
		//catch(PDOException $e) {
        //    print_r($e);
        //}
        $this->dbh->query("SET NAMES utf8");
	}

	/*
	* 执行查询,返回数据集
	* 如果查询失败，返回Null
	*/
	public function query($sql,$params){
		$stmt = self::execute($sql,$params);
		if($stmt)
        	return $stmt->fetchAll(PDO::FETCH_ASSOC);
        else
        	return null;
	}

	/*
	* 执行插入操作
	* 返回插入自增主键的ID
	* -1表示插入失败
	*/
	public function insert($sql,$params){
		if(self::execute($sql,$params)){
			return $this->dbh->lastInsertId();
		}else{
			return -1;
		}
	}

	/*
	*执行更新操作
	*返回更新的条数
	*返回-1表示更新失败
	*/
	public function update($sql,$params){
		$stmt = self::execute($sql,$params);
		if($stmt){
			return $stmt->rowCount();
		}else
			return -1;
	}
	//beginTransaction
	//commit
	//rollBack
	public function beginTrans(){
		$this->dbh->beginTransaction();
	}

	public function commit(){
		$this->dbh->commit();
	}

	public function rollBack(){
		$this->dbh->rollBack();
	}

	/*
	* 执行sql语句
	* 可以参数化，参数用??代替
	*/
	public function execute($sql,$params){
		try {
            $stmt = $this->dbh->prepare($sql);
            if($params!==null) {
                if(is_array($params) || is_object($params)) {
                    $i=1;
                    foreach($params as $param) {
                        $stmt->bindValue($i++,$param);
                    }
                } else {
                    $stmt->bindValue(1,$params);
                }
            }
            if($stmt->execute()) {
                return $stmt;//$this->dbh->lastInsertId();
            } else {
                $err=$stmt->errorInfo();
                throw new PDOException($err[2],$err[1]);
            }
        } catch(PDOException $e) {
            print_r($e);
            return null;
        }
	}

	public static function test(){
		
		//include("../config.php");
		$config = array(
			"host" => MYSQL_HOST,
			"user" => MYSQL_USER,
			"password" => MYSQL_PSW,
			"database" => MYSQL_DB
		);

		$sql = new mySqlDB($config);
		$sql->beginTrans();
		try{
			$ret = $sql->update("update UserInfo set nickname=?",("123"));
			echo " update:" . $ret;
			if($ret < 0){
				throw new PDOException("error");
			}
			$ret = $sql->insert("insert into UserInfo(openid) values(?)",("1235678"));
			echo "insert:" . $ret;
			if($ret<0) {
				throw new PDOException("error");
			} 
			$sql->commit();
			echo('commit');
		}
		catch(PDOException $e){
			$sql->rollBack();
			echo "rollback";
		}

		var_dump($sql->query("select * from UserInfo where openid=?",("1235678")));
		
	}
}

?>