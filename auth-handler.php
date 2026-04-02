<?php
require_once 'config.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
$email=strtolower(trim($_POST['email']));
$password=$_POST['password'];
$user=supabaseRequest("members?email=eq.$email&is_approved=eq.true",'GET')[0]??null;
if($user){
$_SESSION['user_email']=$user['email'];
$_SESSION['user_role']=$user['role'];
$_SESSION['user_nickname']=$user['nickname'];
header("Location: index.php");
}else{
header("Location: login.php?error=1");
}
exit;
}
?>