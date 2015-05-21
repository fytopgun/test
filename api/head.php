<?php 
    session_start();
	if(!isset($_SESSION['user'])){    
    	header("Location:wxlogin.php");  
	}else{  
    	print_r($_SESSION['user']);  
	} 
?>