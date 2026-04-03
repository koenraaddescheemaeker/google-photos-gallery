<?php
require_once 'config.php';
set_time_limit(0);
ob_implicit_flush(true);
ob_end_flush();
echo "<!DOCTYPE html><html lang='nl'><head><meta charset='UTF-8'><title>LIVE SYNC | Forcekes</title><script src='https://cdn.tailwindcss.com'></script><style>body{background:#020202;color:#4ade80;font-family:'JetBrains Mono','Fira Code',monospace;font-size:12px;line-height:1.5;} .log-entry{border-bottom:1px solid rgba(255,255,255,0.03);padding:4px 0;} .timestamp{color:#666;margin-right:10px;} .status-ok{color:#4ade80;font-weight:bold;} .status-info{color:#3b82f6;} .status-warn{color:#facc15;}</style></head><body>";
echo "<div class='max-w-5xl mx-auto p-10'><header class='mb-10 border-b border-white/10 pb-6 flex justify-between items-end'><div><h1 class='text-white text-xl font-black uppercase tracking-tighter'>Media Engine <span class='text-blue-600'>Live</span></h1><p class='text-zinc-500 text-[10px] uppercase tracking-widest mt-1'>De architectuur is heilig</p></div><div id='pulse' class='w-3 h-3 bg-blue-600 rounded-full animate-pulse'></div></header>";
echo "<div id='log-container' class='space-y-1 mb-20'>";
function writeLog($msg,$type='info'){
$t=date('H:i:s');
$c=$type==='ok'?'status-ok':($type==='warn'?'status-warn':'status-info');
echo "<div class='log-entry'><span class='timestamp'>[$t]</span><span class='$c'>".htmlspecialchars($msg)."</span></div>";
echo "<script>window.scrollTo(0,document.body.scrollHeight);</script>";
flush();
}
writeLog("Systeem geïnitialiseerd. Versie PHP: ".phpversion());
writeLog("Verbinding maken met Supabase: ".SUPABASE_URL);
$albs=supabaseRequest("album_settings",'GET');
if(!is_array($albs)){writeLog("FOUT: Kon album_settings niet ophalen.",'warn');exit;}
writeLog("Gevonden configuraties: ".count($albs)." albums.");
foreach($albs as $alb){
$slug=$alb['slug'];
$link=$alb['google_link'];
if(!$link){writeLog("SKIP: Album '$slug' heeft geen Google bronlink.",'warn');continue;}
$check=supabaseRequest("album_photos?category=eq.".rawurlencode($slug)."&limit=1",'GET');
if(empty($check)){
writeLog("INGEST: Nieuwe bron gedetecteerd voor '$slug'. Scraper starten...",'info');
$ch=curl_init();
curl_setopt($ch,CURLOPT_URL,$link);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0');
$html=curl_exec($ch);
curl_close($ch);
preg_match_all('/"(https:\/\/lh3\.googleusercontent\.com\/pw\/[a-zA-Z0-9\-_]+)"/',$html,$matches);
$urls=array_unique($matches[1]??[]);
$c=0;
foreach($urls as $gUrl){
if(strlen($gUrl)>60){
supabaseRequest("album_photos","POST",["category"=>$slug,"image_url"=>$gUrl."=w2400","thumbnail_url"=>$gUrl."=w500","owner_email"=>$alb['created_by']??"koen@lauwe.com","is_visible"=>true]);
$c++;
}}
writeLog("INGEST VOLTOOID: $c foto's toegevoegd aan de wachtrij voor '$slug'.",'ok');
}}
writeLog("START MIGRATIE: Foto's fysiek overzetten naar Supabase Storage...");
$limit=100;
for($p=0;$p<$limit;$p++){
$items=supabaseRequest("album_photos?or=(image_url.like.*googleusercontent*,thumbnail_url.like.*googleusercontent*)&limit=1",'GET');
if(empty($items)){writeLog("SYSTEEM: Alle media is vlijmscherp gesynchroniseerd.",'ok');break;}
$i=$items[0];$id=$i['id'];$cat=$i['category'];$upd=[];
writeLog("MIGRATIE: Verwerken item $id (Categorie: $cat)...");
$urls=['image_url'=>$i['image_url'],'thumbnail_url'=>$i['thumbnail_url']];
foreach($urls as $f=>$u){
if(strpos($u,'google')!==false){
$data=@file_get_contents($u);
if($data){
$ext=(strpos($u,'.mp4')!==false)?'.mp4':'.jpg';
$path=($f==='thumbnail_url'?"thumbs/":"")."$cat/$id$ext";
$nUrl=uploadToSupabase($path,$data,($ext=='.mp4'?'video/mp4':'image/jpeg'));
if($nUrl){$upd[$f]=$nUrl;writeLog(" -> Geüpload naar Storage: $path",'ok');}
}}}
if(!empty($upd)){supabaseRequest("album_photos?id=eq.$id","PATCH",$upd);}
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
echo "</div></div><script>document.getElementById('pulse').classList.remove('animate-pulse');document.getElementById('pulse').classList.add('bg-green-500');</script></body></html>";
?>