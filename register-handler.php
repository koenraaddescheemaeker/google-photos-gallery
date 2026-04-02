<?php
require_once 'config.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
$email=strtolower(trim($_POST['email']));
$nick=trim($_POST['nickname']);
$pass=$_POST['password'];
supabaseRequest("members","POST",["email"=>$email,"nickname"=>$nick,"is_approved"=>false,"role"=>"member"]);
header("Location: register.php?msg=success");
exit;
}
?>