<?php

setlocale(LC_ALL, 'ja_JP.UTF-8');
mb_internal_encoding('UTF-8');
mb_language("Japanese");

if(!empty($_FILES['tagfile']['tmp_name'])){
    $tagfileall = file_get_contents($_FILES['tagfile']['tmp_name']);
    mb_convert_variables("UTF-8","ASCII,JIS,UTF-8,EUC-JP,SJIS",$tagfileall);

    $taginfo = filetotaginfo($tagfileall);
}

if(array_key_exists("tagformat", $_REQUEST)) {
    $tagformat = $_REQUEST["tagformat"];
}

if(array_key_exists("before_msec", $_REQUEST)) {
    $before_msec = $_REQUEST["before_msec"];
}else {
    $before_msec = 1000;
}

if(array_key_exists("after_msec", $_REQUEST)) {
    $after_msec = $_REQUEST["after_msec"];
}else {
    $after_msec = 1000;
}


function mb_split_array($str)
    {
    //print "$str <br>";
        $charList = array();

        $len = mb_strlen($str);
        $i = 0;
        while ($i < $len)
        {
            $charList[] = mb_substr($str, $i++, 1);
        }
        return $charList;
    }

// [mm:ss:100ms] to array
function analyzetimetag($tag){
   $tmpresultarray = array();
   $resultarray = array();
   $loc = 0;
   $tmpnum = null;
   $endflg = false;
   //print "DEBUG : analyzetimetag tag : $tag<br>\n";
   for($i = 0; $i < strlen($tag) ; $i++) {
      if($endflg ) break;
      //print "$tag[$i]<br>\n";
      if(is_numeric($tag[$i])){ 
          if(is_null($tmpnum)){
             $tmpnum = $tag[$i];
          }else {
             $tmpnum = $tmpnum*10 + $tag[$i];
          }
          //print "$tmpnum<br>\n";
      } else if($tag[$i] == ':'){
          $tmpresultarray[] = $tmpnum;
          $tmpnum = null;
          $loc++;
      } else if($tag[$i] == '.'){
          $tmpresultarray[] = $tmpnum;
          $tmpnum = null;
          $loc++;
      } else if($tag[$i] == ']'){
          $tmpresultarray[] = $tmpnum;
          $tmpnum = null;
          $loc++;
          $endflg = true;
      } else {
          // skip this word
      }
   }
   
   //var_dump($tmpresultarray);
   if($loc == 3) {
       $resultarray['sec'] = $tmpresultarray[0]*60 + $tmpresultarray[1];
       $resultarray['msec'] = $tmpresultarray[2];
   } else if ($loc == 2) {
       $resultarray['sec'] = $tmpresultarray[0]*60 + $tmpresultarray[1];
       $resultarray['msec'] = 0;
   } 
   
   return $resultarray;
   
}

//1行内のタグを調査する関数
function findtimetag($line){
    $linetag = array();
    $partlocstart = 0;
    $partlocend = 0;
    $partkind = 0; // 0:本文 1:タグ
    $nofirsttag = false; // 行頭にタグがあったかどうか
    
    $mb_line = mb_split_array($line);
    //var_dump($mb_line);

    for($i = 0; $i < count($mb_line) ; $i++) {
        if($mb_line[$i] == '['){
            if($partlocend > $partlocstart ) {
                if( $partkind == 0 ){
                    $linetag['lyrics'][] =mb_substr($line, $partlocstart,($partlocend - $partlocstart));
                }
                // 直前がタグの場合何もしない
            }
                    $partlocstart = $i;
                    $partlocend = $i;
                    $partkind = 1; //以降の文字列をタグと認識する。
        } else  if($mb_line[$i] == ']'){
            if($partlocend > $partlocstart ) {
                if( $partkind == 1 ){
                    $timetagarray = analyzetimetag(mb_substr($line, $partlocstart, ($i - $partlocstart + 1) ));
                    if($nofirsttag) {
                        $linetag['tag'][0] = $timetagarray;
                        $nofirsttag = false;
                    }
                    $linetag['tag'][] = $timetagarray;
                    $partlocstart = $i+1;
                    $partlocend = $i+1;
                    $partkind = 0; //以降の文字列を本文と認識する。
                }
            }
        } else {
            if(! isset($linetag['tag'][0]) && $partkind == 0 ){
                // 行頭にタグがないときは直後のタグを行頭につける
                $nofirsttag = true;
            }
            $partlocend++;
        }
    }
    return($linetag);
}

function filetotaginfo($allstring){
    $arr =  explode(PHP_EOL, $allstring);
    //var_dump($arr);
    $taginfo = array();
    
    foreach ($arr as $line){
        //print "DEBUG : LINE $line <br>\n";
        $lineinfo = findtimetag($line);
        if(!empty($lineinfo))
        $taginfo[] = $lineinfo;
    }
    
    return $taginfo;
}

function gen_assline($lineinfo, $beforetime, $aftertime, $template = 'none')
{
    $startsec_base = $lineinfo['tag'][0]['sec'];
    $startsec = $startsec_base % 60;
    $startmin = ($startsec_base - $startsec) / 60;
    $startmsec = $lineinfo['tag'][0]['msec'];
    $startmsectotal = $startsec_base*1000 + $startmsec*10;
    
    $startmsectotal_b = $startmsectotal - $beforetime;
    $startmsec_b = $startmsectotal_b % 1000;
    $startsectotal_b = ( $startmsectotal_b - $startmsec_b ) /1000;
    $startsec_b = $startsectotal_b % 60;
    $startmin_b = ($startsectotal_b - $startsec_b) / 60;
    $startmsec_b = $startmsec_b / 10;


    $endsec_base = $lineinfo['tag'][(count( $lineinfo['tag'])-1)]['sec'];
    $endsec = $endsec_base % 60;
    $endmin = ($endsec_base - $endsec) / 60;
    $endmsec = $lineinfo['tag'][(count( $lineinfo['tag'])-1)]['msec'];
    $endmsectotal = $endsec_base*1000 + $endmsec*10;

    $endmsectotal_b = $endmsectotal + $aftertime;
    $endmsec_b = $endmsectotal_b % 1000;
    $endsectotal_b = ( $endmsectotal_b - $endmsec_b ) /1000;
    $endsec_b = $endsectotal_b % 60;
    $endmin_b = ($endsectotal_b - $endsec_b) / 60;
    $endmsec_b = $endmsec_b / 10;

    
    $lyrics_oneline = null;
    foreach ( $lineinfo['lyrics'] as $str ){
        $lyrics_oneline = $lyrics_oneline.$str;
    }
    
    $displaytime = $endmsectotal - $startmsectotal;
    //print "DEBUG : $displaytime, $endmsectotal, $startmsectotal, $endsec, $endmsec, $startsec, $startmsec <br>\n";
    
    $taginfoline = sprintf("Dialogue: 110,0:%02d:%02d.%02d,".
                           "0:%02d:%02d.%02d,Kanji1,,0000,0000,0000,".
                           "Karaoke,{\\q2}{\\pos(285,450)}{\\fad(%d,%d)}{\\org(10000,450)}{\\fr361}".
                           "{\\t(0,%d,\\fr360.1)}".
                           "{\\t(%d,%d,\\fr359.9)}".
                           "{\\t(%d,%d,\\fr359)}%s\n"
               ,$startmin_b,($startsec_b),$startmsec_b  //1
               ,$endmin_b,($endsec_b),$endmsec_b  //2
               ,$beforetime, $aftertime //3
               ,$beforetime //4
               ,$beforetime,($displaytime+$beforetime) //5
               ,($displaytime+$beforetime) ,($displaytime+$beforetime+$aftertime) //6
               ,$lyrics_oneline
               );
    return $taginfoline;
}


if(!empty($_FILES['tagfile']['tmp_name'])){
    $tagfileall = file_get_contents($_FILES['tagfile']['tmp_name']);
    mb_convert_variables("UTF-8","ASCII,JIS,UTF-8,EUC-JP,SJIS",$tagfileall);

    $taginfo = filetotaginfo($tagfileall);
}



?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />


<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="cache-control" content="no-cache">
<meta http-equiv="expires" content="0">
<meta name="viewport" content="width=width,initial-scale=1.0,minimum-scale=1.0">
<title>ASSファイル表示画面</title>
<link type="text/css" rel="stylesheet" href="css/style.css" />
</head>
<body>

<form method="post" enctype="multipart/form-data">
タグ付きtxtファイル
<input type="file" name="tagfile" accept="text/kra,text/txt,text/lyc" />
開始前表示時間(msec)
<input type="text" size="8" name="before_msec" value="1000" />
終了後表示時間(msec)
<input type="text" size="8" name="after_msec" value="1000" />

<br>付与タグ
<input type="text" size="512" name="tagformat" value="Dialogue: 110,0:%02d:%02d.%02d,0:%02d:%02d.%02d,Kanji1,,0000,0000,0000,Karaoke,{\\q2}{\\pos(285,450)}{\\fad(1000,1000)}{\\org(10000,450)}{\\fr361}{\\t(0,1000,\\fr360.1)}{\\t(1000,%d,\\fr359.9)}{\\t(%d,%d,\\fr359)}%s\n" />

<input type="submit" value="Send" />  
</form>

<pre>
<?php
if(!empty($taginfo)){
//var_dump($taginfo);
foreach ($taginfo as $taginfoline){
    print gen_assline( $taginfoline, $before_msec, $after_msec);
}
}
?>
</pre>

</body>
</html>
