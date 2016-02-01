<?php
setlocale(LC_ALL, 'ja_JP.UTF-8');
mb_internal_encoding('UTF-8');

$assall[] = file_get_contents($_FILES['assfile']['tmp_name']);
//$tmp = fopen($_FILES['assfile']['tmp_name'], "r");
//while ($assline[] = fgets($tmp, "4096") ) {}
// 配列 $csv の文字コードをSJIS-winからUTF-8に変換
$from_chacode = "UTF-16,UTF-8,Shift_JIS,EUC-JP,JIS";
mb_language("Japanese");
mb_convert_variables("UTF-8", "UTF-16", $assall);

$assall = str_replace(array('\r\n','\r','\n'), '\n', $assall[0]);
$assline = explode("\n", $assall);
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


<?php

function checksection($line)
{
if(strlen($line) <2 ) return false;
$pos = strlen($line)-2;
//print "DEBUG : $line :";
//print "DEBUG : $line[0] ".bin2hex($line[$pos])." <br>";

   if($line[0] == '['){
       if($line[strlen($line)-1] == ']'){
           return true;
       }
   }
   return false;
}

echo "<pre>\n";
print_r(mb_get_info());
echo "</pre>\n";

//var_dump($assline);

$ass_analyzed = array();
$sectionname = null;

foreach($assline as $value)
{
    if( strlen($value) <= 0 ) continue;
    
    if( checksection($value) ){
        $sectionname = $value;
        continue;
    }
//print "DEBUG : $sectionname $value <br>";
    //if(empty($sectionname) continue;
    $ass_analyzed[$sectionname][] = $value;
    
}

var_dump($ass_analyzed);

?>

</body>
</html>