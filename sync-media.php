<?php
require_once 'config.php';
set_time_limit(0);
ob_implicit_flush(true);
ob_end_flush();
echo "<!DOCTYPE html><html lang='nl'><head><meta charset='UTF-8'><title>LIVE SYNC | Forcekes</title><script src='https://cdn.tailwindcss.com'></script><style>body{background:#020202;color:#4ade80;font-family:monospace;font-size:12px;}</style></head><body><div class='p-10'>";
function writeLog($msg,$type='info'){ $t=date('H:i:s'); $c=$type==='ok'?'#4ade80':'#3b82f6'; echo "<div><span style='color:#666;'>[$t]</span> <span style='color:$c;'>".htmlspecialchars($msg)."</span></div><script>window.scrollTo(0,document.body.scrollHeight);</script>"; flush(); }
writeLog("REVOLUTIE: Conversie naar WebP geactiveerd.");
$albs=supabaseRequest("album_settings",'GET');
foreach($albs as $alb){
$slug=$alb['slug']; $link=$alb['google_link'];
if(!$link) continue;
$check=supabaseRequest("album_photos?category=eq.".rawurlencode($slug)."&limit=1",'GET');
if(empty($check)){
writeLog("INGEST: Bron laden voor '$slug'...");
$ch=curl_init(); curl_setopt($ch,CURLOPT_URL,$link); curl_setopt($ch,CURLOPT_RETURNTRANSFER,true); curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true); curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0'); $html=curl_exec($ch); curl_close($ch);
preg_match_all('/"(https:\/\/lh3\.googleusercontent\.com\/pw\/[a-zA-Z0-9\-_]+)"/',$html,$matches);
$urls=array_unique($matches[1]??[]);
foreach($urls as $gUrl){ if(strlen($gUrl)>60){ supabaseRequest("album_photos","POST",["category"=>$slug,"image_url"=>$gUrl."=w2400","thumbnail_url"=>$gUrl."=w500","is_visible"=>true]); }}
writeLog("INGEST OK: ".count($urls)." items in wachtrij.",'ok');
}}
$limit=100;
for($p=0;$p<$limit;$p++){
$items=supabaseRequest("album_photos?or=(image_url.like.*googleusercontent*,thumbnail_url.like.*googleusercontent*)&limit=1",'GET');
if(empty($items)){ writeLog("KLAAR: Alles is vlijmscherp en geoptimaliseerd.",'ok'); break; }
$i=$items[0]; $id=$i['id']; $cat=$i['category']; $upd=[];
writeLog("OPTIMALISATIE: Item $id naar WebP...");
$urls=['image_url'=>$i['image_url'],'thumbnail_url'=>$i['thumbnail_url']];
foreach($urls as $f=>$u){
if(strpos($u,'google')!==false){
$rawData=@file_get_contents($u);
if($rawData){
$isVid=(strpos($u,'.mp4')!==false || strpos($u,'.mov')!==false);
if(!$isVid){
$img=imagecreatefromstring($rawData);
ob_start();
imagewebp($img,null,80);
$data=ob_get_clean();
imagedestroy($img);
$ext='.webp'; $mime='image/webp';
}else{
$data=$rawData; $ext='.mp4'; $mime='video/mp4';
}
$path=($f==='thumbnail_url'?"thumbs/":"")."$cat/$id$ext";
$nUrl=uploadToSupabase($path,$data,$mime);
if($nUrl) $upd[$f]=$nUrl;
}}}
if(!empty($upd)) supabaseRequest("album_photos?id=eq.$id","PATCH",$upd);
}
function uploadToSupabase($p,$d,$m){
$u=SUPABASE_URL."/storage/v1/object/familie-media/".rawurlencode($p);
$ch=curl_init($u); curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"POST"); curl_setopt($ch,CURLOPT_POSTFIELDS,$d); curl_setopt($ch,CURLOPT_RETURNTRANSFER,true); curl_setopt($ch,CURLOPT_HTTPHEADER,['Authorization: Bearer '.SUPABASE_SERVICE_KEY,'Content-Type: '.$m,'x-upsert: true']); $res=curl_exec($ch); $code=curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
return($code==200||$code==201)?SUPABASE_URL."/storage/v1/object/public/familie-media/".$p:false;
}
echo "</div></body></html>";
?>