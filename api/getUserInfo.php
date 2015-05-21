<?php
include('db/wedb.php');
$id = $_GET['id'];
$db = new wedDB();
$data = json_encode($db->query("select * from UserInfo where userid=?",($id))); 
echo $data;
?>