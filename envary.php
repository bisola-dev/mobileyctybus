<?php 
$surn = $_SESSION['SURNAME'];
$firs = $_SESSION['FIRSTNAME'];
$midd = $_SESSION['MIDDLENAME'];

$staffy = $_SESSION['staffy']; 

$name= $surn.' '.$firs.' '.$midd; 

if ($staffy == ""){header("Location:logout.php");}

?>