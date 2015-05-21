<?php
/*
*更新喜帖
*/ 

include('action.php');

$act = new weddingCardAction();
$act->saveCardInfo();
echo $act->doJsonp();

?>