<?php
require_once 'config.php';
set_time_limit(900);
$isCron=(php_sapi_name()==='cli'||isset($_GET['cron']));
echo "<body style='background:#000;color:#fff;font-family:monospace;padding:30px;'>";
echo "<h2>FORCEKES <span style='color:#3b82f6;'>MEDIA ENGINE 2026</span></h2>";
$newAlbs=supabaseRequest("album_settings?google_link=not.is.null",'GET');
if(is_array($newAlbs)){
foreach($newAlbs as $alb){
$slug=$alb['slug'];
$link=$alb['google_link'];
$owner=$alb['created_by']??'koen@lauwe.com';
$check=supabaseRequest("album_photos?category=eq.".rawurlencode($slug)."&limit=1",'GET');
if(empty($check)){
echo "Ingest: <strong>$slug</strong>... ";
$ch=curl_init();
curl_setopt($ch,CURLOPT_URL,$link);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0');
$html=curl_exec($ch);
curl_close($ch);
preg_match_all('/"(https:\/\/lh3\.googleusercontent\.com\/pw\/[a-zA-Z0-9\-_]+)"/',$html,$matches);
$urls=array_unique($matches[1]??[]);
$count=0;
foreach($urls as $gUrl){
if(strlen($gUrl)>60){
supabaseRequest("album_photos","POST",["category"=>$slug,"image_url"=>$gUrl."=w2400","thumbnail_url"=>$gUrl."=w500","owner_email"=>$owner,"is_visible"=>true]);
$count++;
}}
echo "<span style='color:#4ade80;'>$count gevonden.</span><br>";
}}}
$items=supabaseRequest("album_photos?or=(image_url.like.*googleusercontent*,thumbnail_url.like.*googleusercontent*)&limit=10",'GET');
if(is_array($items)&&count($items)>0){
foreach($items as $i){
$id=$i['id'];$cat=$i['category'];$upd=[];
$urls=['image_url'=>$i['image_url'],'thumbnail_url'=>$i['thumbnail_url']];
foreach($urls as $f=>$u){
if(strpos($u,'google')!==false){
$data=@file_get_contents($u);
if($data){
$ext=(strpos($u,'.mp4')!==false)?'.mp4':'.jpg';
$path=($f==='thumbnail_url'?"thumbs/":"")."$cat/$id$ext";
$nUrl=uploadToSupabase($path,$data,($ext=='.mp4'?'video/mp4':'image/jpeg'));
if($nUrl)$upd[$f]=$nUrl;
}}}
if(!empty($upd))supabaseRequest("album_photos?id=eq.$id","PATCH",$upd);
}
if(!$isCron)echo "<script>setTimeout(()=>{window.location.reload();},2000);</script>";
}
function uploadToSupabase($p,$d,$m){
$u=SUPABASE_URL."/storage/v1/object/familie-media/".rawurlencode($p);
$ch=curl_init($u);
curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"POST");
curl_setopt($ch,CURLOPT_POSTFIELDS,$d);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch,CURLOPT_HTTPHEADER,['Authorization: Bearer '.SUPABASE_SERVICE_KEY,'Content-Type: '.$m,'x-upsert: true']);
$res=curl_exec($ch);$code=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);
return($code==200||$code==201)?SUPABASE_URL."/storage/v1/object/public/familie-media/".$p:false;
}
?>