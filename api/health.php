<?php
ob_start();
ini_set('display_errors','0');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
function out(array $d,int $s=200):void{while(ob_get_level()>0)ob_end_clean();http_response_code($s);echo json_encode($d,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);exit;}
try{
 require_once __DIR__.'/../db.php';
 session_start();
 $db=$pdo->query('SELECT DATABASE() AS db')->fetch();
 $table=$pdo->query("SHOW TABLES LIKE 'users'")->fetch();
 $admin=null;
 if($table){$q=$pdo->prepare('SELECT id,name,email,role,banned_at FROM users WHERE email=:email LIMIT 1');$q->execute([':email'=>'admin@f1merch.local']);$admin=$q->fetch()?:null;}
 out(['ok'=>true,'php'=>PHP_VERSION,'database'=>$db['db']??null,'users_table'=>(bool)$table,'admin'=>$admin,'session_path'=>session_save_path()]);
}catch(Throwable $e){out(['ok'=>false,'error'=>$e->getMessage(),'php'=>PHP_VERSION],500);}
