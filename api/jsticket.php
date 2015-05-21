<?php
	include('wx/wxapi.php');
	@$url = $_GET["url"];
    $wx = new wxApi();
	$data = json_encode($wx ->getSignPackage($url));
	@$jsonp = $_GET["callback"];
	//echo $jsonp;
	if(empty($jsonp))
	{
		echo $data;
	} else
	{
		echo "$jsonp($data)";
	}
?>