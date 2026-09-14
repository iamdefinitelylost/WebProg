<?php
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json; charset=UTF-8');
// security.php initializes the session; db.php provides the PDO connection.
$validCategories=['headwear','outerwear','tees','archive'];
function response($d,$s=200){http_response_code($s);echo json_encode($d,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_INVALID_UTF8_SUBSTITUTE);exit;}
function admin(){
 if(empty($_SESSION['user']) || ($_SESSION['user']['role']??'')!=='admin' || empty($_SESSION['admin_authenticated'])){
  response(['success'=>false,'error'=>'Administrator login required.'],401);
 }
}
function migrate($pdo){
 $defs=['image_path'=>'VARCHAR(255) NULL','stock'=>'INT NOT NULL DEFAULT 25','team'=>'VARCHAR(80) NULL','sizes'=>'VARCHAR(120) NULL','limited_edition'=>'TINYINT(1) NOT NULL DEFAULT 0','featured'=>'TINYINT(1) NOT NULL DEFAULT 0'];
 foreach($defs as $c=>$d){$q=$pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='products' AND COLUMN_NAME=:c");$q->execute([':c'=>$c]);if(!(int)$q->fetchColumn())$pdo->exec("ALTER TABLE products ADD COLUMN `$c` $d");}
}
migrate($pdo);
function uploadImage($file){if(!$file||($file['error']??4)===4)return null;if($file['error']!==0||$file['size']>5242880)response(['error'=>'Product photo must be 5MB or smaller.'],422);$mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);$map=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];if(!isset($map[$mime]))response(['error'=>'Only JPG, PNG, and WebP are allowed.'],422);$dir=__DIR__.'/../uploads/products';if(!is_dir($dir))mkdir($dir,0755,true);$name=bin2hex(random_bytes(12)).'.'.$map[$mime];if(!move_uploaded_file($file['tmp_name'],$dir.'/'.$name))response(['error'=>'Could not save image.'],500);return 'uploads/products/'.$name;}
function deleteImage($p){if($p){$f=__DIR__.'/../'.ltrim($p,'/');if(is_file($f))@unlink($f);}}
function data(){if(stripos($_SERVER['CONTENT_TYPE']??'','multipart/form-data')!==false)return $_POST;return json_decode(file_get_contents('php://input'),true)?:[];}
function row($r){$r['id']=(int)$r['id'];$r['price']=(float)$r['price'];$r['stock']=(int)$r['stock'];$r['limited_edition']=(bool)$r['limited_edition'];$r['featured']=(bool)$r['featured'];$r['sizes']=$r['sizes']?array_values(array_filter(array_map('trim',explode(',',$r['sizes'])))):[];return $r;}
function getAll($pdo,$id=null){$sql='SELECT id,name,description AS `desc`,price,category,image_path AS image,stock,team,sizes,limited_edition,featured FROM products';if($id){$q=$pdo->prepare($sql.' WHERE id=:id');$q->execute([':id'=>$id]);$r=$q->fetch(PDO::FETCH_ASSOC);if(!$r)response(['error'=>'Product not found.'],404);response(row($r));}$q=$pdo->query($sql.' ORDER BY featured DESC, id ASC');$out=[];foreach($q as $r)$out[]=row($r);response($out);}
function payload($d){global $validCategories;$err=[];if(trim((string)($d['name']??''))==='')$err[]='Name is required.';if(trim((string)($d['desc']??''))==='')$err[]='Description is required.';if(!isset($d['price'])||!is_numeric($d['price'])||$d['price']<0)$err[]='Valid price is required.';if(!in_array($d['category']??'', $validCategories,true))$err[]='Valid category is required.';if(isset($d['stock'])&&((int)$d['stock']<0))$err[]='Stock cannot be negative.';if($err)response(['errors'=>$err],422);return ['name'=>trim($d['name']),'desc'=>trim($d['desc']),'price'=>(float)$d['price'],'category'=>$d['category'],'stock'=>(int)($d['stock']??25),'team'=>trim((string)($d['team']??'')),'sizes'=>is_array($d['sizes']??null)?implode(',',$d['sizes']):trim((string)($d['sizes']??'')),'limited'=>(int)!empty($d['limited_edition']),'featured'=>(int)!empty($d['featured'])];}
$m=$_SERVER['REQUEST_METHOD'];$id=(int)($_GET['id']??0);
try{
 if($m==='GET'){getAll($pdo,$id);}
 admin();
 if(in_array($m,['POST','PUT','DELETE'],true)) require_csrf();
 if($m==='POST'){
  if(($_GET['action']??'')==='reset'){ $pdo->exec('DELETE FROM products');$pdo->exec('ALTER TABLE products AUTO_INCREMENT=1');$defs=[['Pit Lane Cap','Six-panel structured cap, embroidered badge, curved brim.',38,'headwear',30,'Ferrari','S,M,L',0,1],['Team Snapback','Flat-brim snapback, structured crown, mesh rear panel.',34,'headwear',40,'McLaren','S,M,L',0,0],['Paddock Hoodie','Heavyweight fleece, badge on chest, checker trim on cuff.',96,'outerwear',18,'Ferrari','S,M,L,XL,XXL',1,1],['Grid Windbreaker','Packable shell, taped seams, badge on left chest.',118,'outerwear',12,'Mercedes','S,M,L,XL',1,1],['Race Day Tee','Mid-weight cotton, front badge print, back number placeholder.',32,'tees',50,'Red Bull Racing','S,M,L,XL,XXL',1,1],['Champion Tee','Soft-wash cotton, minimal front hit, checker collar tape.',30,'tees',45,'McLaren','S,M,L,XL,XXL',0,0],['Archive Overalls','Reissued pit-crew overalls from the archive, twill badge.',145,'archive',8,'Ferrari','S,M,L,XL',1,0],['Legacy Varsity Jacket','Wool-body varsity jacket, leather sleeves, archive numerals.',165,'archive',6,'Mercedes','S,M,L,XL',1,0]];$s=$pdo->prepare('INSERT INTO products(name,description,price,category,stock,team,sizes,limited_edition,featured) VALUES(?,?,?,?,?,?,?,?,?)');foreach($defs as $x)$s->execute($x);getAll($pdo);}
  $d=payload(data());$image=uploadImage($_FILES['image']??null);$s=$pdo->prepare('INSERT INTO products(name,description,price,category,image_path,stock,team,sizes,limited_edition,featured) VALUES(:n,:d,:p,:c,:i,:s,:t,:z,:l,:f)');$s->execute([':n'=>$d['name'],':d'=>$d['desc'],':p'=>$d['price'],':c'=>$d['category'],':i'=>$image,':s'=>$d['stock'],':t'=>$d['team'],':z'=>$d['sizes'],':l'=>$d['limited'],':f'=>$d['featured']]);getAll($pdo,(int)$pdo->lastInsertId());
 }
 if($m==='PUT'){
  if(!$id)response(['error'=>'Missing product id.'],400);$q=$pdo->prepare('SELECT image_path FROM products WHERE id=:id');$q->execute([':id'=>$id]);$old=$q->fetch();if(!$old)response(['error'=>'Product not found.'],404);$d=payload(data());$image=$old['image_path'];$body=data();if(!empty($body['remove_image'])){$image=null;deleteImage($old['image_path']);}$new=uploadImage($_FILES['image']??null);if($new){deleteImage($old['image_path']);$image=$new;}$s=$pdo->prepare('UPDATE products SET name=:n,description=:d,price=:p,category=:c,image_path=:i,stock=:s,team=:t,sizes=:z,limited_edition=:l,featured=:f WHERE id=:id');$s->execute([':n'=>$d['name'],':d'=>$d['desc'],':p'=>$d['price'],':c'=>$d['category'],':i'=>$image,':s'=>$d['stock'],':t'=>$d['team'],':z'=>$d['sizes'],':l'=>$d['limited'],':f'=>$d['featured'],':id'=>$id]);getAll($pdo,$id);
 }
 if($m==='DELETE'){if(!$id)response(['error'=>'Missing product id.'],400);$q=$pdo->prepare('SELECT image_path FROM products WHERE id=:id');$q->execute([':id'=>$id]);$p=$q->fetch();if(!$p)response(['error'=>'Product not found.'],404);$pdo->prepare('DELETE FROM products WHERE id=:id')->execute([':id'=>$id]);deleteImage($p['image_path']);response(['success'=>true]);}
 response(['error'=>'Method not allowed.'],405);
}catch(Throwable $e){error_log('[F1 MERCH products.php] '.$e->getMessage());response(['success'=>false,'error'=>'Unable to process the product request.'],500);}
