<?php
require_once("readass.php");
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>多重縁ジェネレーター for ass from Txt2ass</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
<?php

/*** 
print "<code>";
var_dump($_REQUEST);
var_dump($_FILES);
print "</code>";
***/
if(array_key_exists("uploaddfilename",$_REQUEST)) {
    $filename_base=basename($_REQUEST["uploaddfilename"]);
    // print '移動予定先ファイル:'.$filename;
}
if(array_key_exists("assInputFile",$_FILES)){
    $filename=$_FILES['assInputFile']['tmp_name'];
    $filename_base=$_FILES['assInputFile']['name'];
    $assbasename_info = pathinfo($filename_base);
    $assbasename = $assbasename_info['filename'];    
}

if(empty($filename)){
    print("no file information");
    die();
}
?>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>

<?php

// file読み込み
$assfile=file_get_contents($filename);
if(empty($assfile)){
    print("Cannot read file $$filename");
    die();
}


// ass解析
$ass_structs=analyze_ass($assfile);

//****************** ２重縁clip部分生成
// style複製
$tmp_ass_structs = copy_style($ass_structs, 'songInfo2' , 'songInfo2_uti');
$tmp_ass_structs = copy_event($tmp_ass_structs, ' songInfo2' , 'songInfo2_uti');
$tmp_ass_structs = copy_style($tmp_ass_structs, 'songInfo1' , 'songInfo1_uti');
$tmp_ass_structs = copy_event($tmp_ass_structs, ' songInfo1' , 'songInfo1_uti');
$tmp_ass_structs = copy_style($tmp_ass_structs, 'songInfo' , 'songInfo_uti');
$tmp_ass_structs = copy_event($tmp_ass_structs, ' songInfo' , 'songInfo_uti');
$tmp_ass_structs = copy_style($tmp_ass_structs, 'Kanji1' , 'Kanji1_uti');
$tmp_ass_structs = copy_event($tmp_ass_structs, ' Kanji1' , 'Kanji1_uti');
$tmp_ass_structs = copy_style($tmp_ass_structs, 'Ruby1' , 'Ruby1_uti');
$tmp_ass_structs = copy_event($tmp_ass_structs, ' Ruby1' , 'Ruby1_uti');
$tmp_ass_structs = copy_style($tmp_ass_structs, 'Kanji2' , 'Kanji2_uti');
$tmp_ass_structs = copy_event($tmp_ass_structs, ' Kanji2' , 'Kanji2_uti');
$tmp_ass_structs = copy_style($tmp_ass_structs, 'Ruby2' , 'Ruby2_uti');
$tmp_ass_structs = copy_event($tmp_ass_structs, ' Ruby2' , 'Ruby2_uti');
$tmp_ass_structs = copy_style($tmp_ass_structs, 'Kanji3' , 'Kanji3_uti');
$tmp_ass_structs = copy_event($tmp_ass_structs, ' Kanji3' , 'Kanji3_uti');
$tmp_ass_structs = copy_style($tmp_ass_structs, 'Ruby3' , 'Ruby3_uti');
$tmp_ass_structs = copy_event($tmp_ass_structs, ' Ruby3' , 'Ruby3_uti');
$tmp_ass_structs = copy_style($tmp_ass_structs, 'whosing1' , 'whosing1_uti');
$tmp_ass_structs = copy_event($tmp_ass_structs, ' whosing1' , 'whosing1_uti');

//色＆縁サイズ指定
//内縁：メイン歌詞＆曲情報：曲名
$beforewp_color = '&H00FFFFFF';         	//文字色 白
$beforewpfuti_color = '&H00253378';     	//内縁色 茶
$beforewpshadow_color = '&H00253378';   	//影色   茶
$beforewpsotofuti_color = '&H00FFFFFF'; 	//外縁色 白

$changestyle = array (
    'PrimaryColour' => $beforewp_color,
    'SecondaryColour' => $beforewp_color,
    'OutlineColour' => $beforewpfuti_color,
    'BackColour' => $beforewpshadow_color,
    'Outline' => '4',
    'Shadow' => '1'
    );

$tmp_ass_structs = mod_style($tmp_ass_structs, 'Kanji1_uti' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'songInfo1_uti' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'Kanji2_uti' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'Kanji3_uti' , $changestyle);

//内縁： ルビ、曲情報曲名以外、曲中曲情報
$changestyle = array (
    'PrimaryColour' => $beforewp_color,
    'SecondaryColour' => $beforewp_color,
    'OutlineColour' => $beforewpfuti_color,
    'BackColour' => $beforewpshadow_color,
    'Outline' => '3',
    'Shadow' => '1'
    );

$tmp_ass_structs = mod_style($tmp_ass_structs, 'Ruby1_uti' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'Ruby2_uti' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'Ruby3_uti' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'songInfo2_uti' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'songInfo_uti' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'whosing1_uti' , $changestyle);

//外縁：メイン歌詞＆曲情報：曲名
$changestyle = array (
    'PrimaryColour' => $beforewp_color,
    'SecondaryColour' => $beforewp_color,
    'OutlineColour' => $beforewpsotofuti_color,
    'BackColour' => $beforewpsotofuti_color,
    'Outline' => '15',
    'Shadow' => '0'
    );
$tmp_ass_structs = mod_style($tmp_ass_structs, 'Kanji1' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'songInfo1' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'Kanji2' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'Kanji3' , $changestyle);

//外縁： ルビ、曲情報曲名以外、曲中曲情報
$changestyle = array (
    'PrimaryColour' => $beforewp_color,
    'SecondaryColour' => $beforewp_color,
    'OutlineColour' => $beforewpsotofuti_color,
    'BackColour' => $beforewpsotofuti_color,
    'Outline' => '13',
    'Shadow' => '0'
    );
$tmp_ass_structs = mod_style($tmp_ass_structs, 'Ruby1' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'Ruby2' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'Ruby3' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'songInfo2' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'songInfo' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'whosing1' , $changestyle);

//外縁ブラー化
$tmp_ass_structs = mod_event_text($tmp_ass_structs, 'Kanji1' , '/^/' , '{\\blur4}');
$tmp_ass_structs = mod_event_text($tmp_ass_structs, 'Ruby1' , '/^/' , '{\\blur4}');
$tmp_ass_structs = mod_event_text($tmp_ass_structs, 'Kanji2' , '/^/' , '{\\blur4}');
$tmp_ass_structs = mod_event_text($tmp_ass_structs, 'Ruby2' , '/^/' , '{\\blur4}');
$tmp_ass_structs = mod_event_text($tmp_ass_structs, 'Kanji3' , '/^/' , '{\\blur4}');
$tmp_ass_structs = mod_event_text($tmp_ass_structs, 'Ruby3' , '/^/' , '{\\blur4}');
$tmp_ass_structs = mod_event_text($tmp_ass_structs, 'songInfo1' , '/^/' , '{\\blur4}');
$tmp_ass_structs = mod_event_text($tmp_ass_structs, 'songInfo2' , '/^/' , '{\\blur4}');
$tmp_ass_structs = mod_event_text($tmp_ass_structs, 'songInfo' , '/^/' , '{\\blur4}');
$tmp_ass_structs = mod_event_text($tmp_ass_structs, 'whosing1' , '/^/' , '{\\blur4}');

// 内縁ルビLayer変更
$tmp_ass_structs = mod_event($tmp_ass_structs, 'Ruby1_uti' , array('Layer' => '120'));
$tmp_ass_structs = mod_event($tmp_ass_structs, 'Ruby2_uti' , array('Layer' => '120'));
$tmp_ass_structs = mod_event($tmp_ass_structs, 'Ruby3_uti' , array('Layer' => '120'));

$ass_structs_clip = $tmp_ass_structs;   // 確定
$outbuf_clip = outputass($ass_structs_clip);

//****************** ワイプ後部分ass生成

$beforewp_color = '&H00253378';
$beforewpfuti_color = '&H00253378';
$beforewpshadow_color = '&H00FFFFFF';
$beforewpsotofuti_color = '&H00F8E3FF';

$changestyle = array (
    'PrimaryColour' => $beforewp_color,
    'SecondaryColour' => $beforewp_color,
    'OutlineColour' => $beforewpshadow_color,
    'BackColour' => $beforewpshadow_color,
    'Outline' => '6',
    'Shadow' => '1'
    );
    
$tmp_ass_structs = mod_style($tmp_ass_structs, 'Kanji1_uti' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'Kanji2_uti' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'Kanji3_uti' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'songInfo1_uti' , $changestyle);
$changestyle = array (
    'PrimaryColour' => $beforewp_color,
    'SecondaryColour' => $beforewp_color,
    'OutlineColour' => $beforewpshadow_color,
    'BackColour' => $beforewpshadow_color,
    'Outline' => '4',
    'Shadow' => '1'
    );
$tmp_ass_structs = mod_style($tmp_ass_structs, 'Ruby1_uti' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'Ruby2_uti' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'Ruby3_uti' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'songInfo2_uti' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'songInfo_uti' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'whosing1_uti' , $changestyle);

$changestyle = array (
    'PrimaryColour' => $beforewp_color,
    'SecondaryColour' => $beforewp_color,
    'OutlineColour' => $beforewpsotofuti_color,
    'BackColour' => $beforewpsotofuti_color,
    'Outline' => '15',
    'Shadow' => '0'
    );
$tmp_ass_structs = mod_style($tmp_ass_structs, 'Kanji1' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'Kanji2' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'Kanji3' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'songInfo1' , $changestyle);
$changestyle = array (
    'PrimaryColour' => $beforewp_color,
    'SecondaryColour' => $beforewp_color,
    'OutlineColour' => $beforewpsotofuti_color,
    'BackColour' => $beforewpsotofuti_color,
    'Outline' => '13',
    'Shadow' => '0'
    );
$tmp_ass_structs = mod_style($tmp_ass_structs, 'Ruby1' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'Ruby2' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'Ruby3' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'songInfo2' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'songInfo' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'whosing1' , $changestyle);

$preg = '/{\\\\t\(\d{1,},\d{1,},\\\\clip.+?\\\\clip.+?}/';
$tmp_ass_structs=mod_event_text($tmp_ass_structs, 'Kanji1' , $preg , '');
$tmp_ass_structs=mod_event_text($tmp_ass_structs, 'Ruby1' , $preg , '');
$tmp_ass_structs=mod_event_text($tmp_ass_structs, 'Kanji2' , $preg , '');
$tmp_ass_structs=mod_event_text($tmp_ass_structs, 'Ruby2' , $preg , '');
$tmp_ass_structs=mod_event_text($tmp_ass_structs, 'Kanji3' , $preg , '');
$tmp_ass_structs=mod_event_text($tmp_ass_structs, 'Ruby3' , $preg , '');
$tmp_ass_structs=mod_event_text($tmp_ass_structs, 'Kanji1_uti' , $preg , '');
$tmp_ass_structs=mod_event_text($tmp_ass_structs, 'Ruby1_uti' , $preg , '');
$tmp_ass_structs=mod_event_text($tmp_ass_structs, 'Kanji2_uti' , $preg , '');
$tmp_ass_structs=mod_event_text($tmp_ass_structs, 'Ruby2_uti' , $preg , '');
$tmp_ass_structs=mod_event_text($tmp_ass_structs, 'Kanji3_uti' , $preg , '');
$tmp_ass_structs=mod_event_text($tmp_ass_structs, 'Ruby3_uti' , $preg , '');

$tmp_ass_structs = mod_event($tmp_ass_structs, 'songInfo1' , array('Format' => 'Comment'));
$tmp_ass_structs = mod_event($tmp_ass_structs, 'songInfo2' , array('Format' => 'Comment'));
$tmp_ass_structs = mod_event($tmp_ass_structs, 'songInfo' , array('Format' => 'Comment'));
$tmp_ass_structs = mod_event($tmp_ass_structs, 'songInfo2_uti' , array('Format' => 'Comment'));
$tmp_ass_structs = mod_event($tmp_ass_structs, 'songInfo1_uti' , array('Format' => 'Comment'));
$tmp_ass_structs = mod_event($tmp_ass_structs, 'songInfo_uti' , array('Format' => 'Comment'));
$tmp_ass_structs = mod_event($tmp_ass_structs, 'whosing1_uti' , array('Format' => 'Comment'));
$tmp_ass_structs = mod_event($tmp_ass_structs, 'whosing1' , array('Format' => 'Comment'));


$ass_structs_base = $tmp_ass_structs;
$outbuf_base = outputass($ass_structs_base);

//****************** mask用ass生成
$beforewp_color = '&H00FFFFFF';
$beforewpfuti_color = '&H00000000';
$beforewpshadow_color = '&H00FFFFFF';
$beforewpsotofuti_color = '&H00000000';
$changestyle = array (
    'PrimaryColour' => $beforewp_color,
    'SecondaryColour' => $beforewp_color,
    'OutlineColour' => $beforewpsotofuti_color,
    'BackColour' => $beforewpshadow_color,
    'Outline' => '1',
    'Shadow' => '0'
    );
$tmp_ass_structs = mod_style($ass_structs, 'Kanji1' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'Ruby1' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'Kanji2' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'Ruby2' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'Kanji3' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'Ruby3' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'songInfo1' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'songInfo2' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'songInfo' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'whosing1' , $changestyle);

$ass_structs_mask = $tmp_ass_structs;
$outbuf_mask = outputass($ass_structs_mask);

?>



    <h1>多重縁ジェネレーター for ass from Txt2ass</h1>
    

    <div class="container">
    <h2>変換結果</h2>
<?php
if(!empty($outbuf_base)){
    $fn=$assbasename."_base.ass";
    print '<form enctype="multipart/form-data" action="assdownload.php" method="POST" >';
    print '<div class="form-group">';
    print '    <input type="hidden" name="filename" value="'.$fn.'" />';
    print '    <input type="hidden" name="data" value="'.htmlentities($outbuf_base).'" />';
    print '    <button type="submit" class="btn btn-default">Wipe後base部ダウンロード</button>';
    print '</div>';
    print '</form>';
}
if(!empty($outbuf_clip)){
    $fn=$assbasename."_clip.ass";
    print '<form enctype="multipart/form-data" action="assdownload.php" method="POST" >';
    print '<div class="form-group">';
    print '    <input type="hidden" name="filename" value="'.$fn.'" />';
    print '    <input type="hidden" name="data" value="'.htmlentities($outbuf_clip).'" />';
    print '    <button type="submit" class="btn btn-default">Wipe前Clip部ダウンロード</button>';
    print '</div>';
    print '</form>';
}

if(!empty($outbuf_mask)){
    $fn=$assbasename."_mask.ass";
    print '<form enctype="multipart/form-data" action="assdownload.php" method="POST" >';
    print '<div class="form-group">';
    print '    <input type="hidden" name="filename" value="'.$fn.'" />';
    print '    <input type="hidden" name="data" value="'.htmlentities($outbuf_mask).'" />';
    print '    <button type="submit" class="btn btn-default">ワイプ前文字色maskダウンロード</button>';
    print '</div>';
    print '</form>';
}
?>    
   <h2>さらに変換</h2> 
      <form enctype="multipart/form-data" action="multioutlinegen_result.php"  method="POST" onsubmit="saveFilename();" >
          <div class="form-group">
            <input type="hidden" name="MAX_FILE_SIZE" value="900000000" />
            <input type="hidden" name="uploaddfilename" id="upldfilename" />
            <label for="assInputFile">ass file</label>
            <input type="file" id="assInputFile" class="form-control" name="assInputFile" />
            <p class="help-block">Txt2assで作成したassファイル.</p>
          </div>
          <button type="submit" class="btn btn-default">Submit</button>
      </form>
    </div>
    
<hr />
    <div class="container">
        <h2>パラメータ指定変換</h2>
<?php
$fn=$assbasename."_mod.ass";
?>
        <form enctype="multipart/form-data" action="assdownload.php" method="POST" >
        <div class="form-group">
            <input type="hidden" name="filename" value="<?php echo $fn; ?>" />
            <input type="hidden" name="ass_structs_json" value='<?php echo urlencode(json_encode($ass_structs)); ?>' />
        <div>
            <label> 縁１(1番外側) </label>
            <div class="form-group form-inline">
                <label> 文字中色 </label>
                <input class="" type="color" value="#ffffff" name="style[beforewp_color][]" />
                <label> 縁色 </label>
                <input class="" type="color" value="#ffffff" name="style[beforewpfuti_color][]" />
                <label>縁サイズ</label> <input type="text" class="form-control" value="12" name="style[Outline][]" size="2" />
                <label> 影色 </label>
                <input class="" type="color" value="#ffffff" name="style[beforewpshadow_color][]" />
                <label>影サイズ</label> <input type="text" class="form-control" value="0" name="style[Shadow][]" size="2" />
                <label>ルビ縁サイズ</label> <input type="text" class="form-control" value="10" name="style[Ruby_Outline][]" size="2" />
                <label>ルビ影サイズ</label> <input type="text" class="form-control" value="0" name="style[Ruby_Shadow][]" size="2" />
                <label>ぼかし量</label> <input type="text" class="form-control" value="4" name="style[blur][]" size="2" />
                <label class="checkbox-inline">
                     <input type="checkbox" name="style[removeclip][]" value="1" /> ワイプ後（Clip削除）
                </label>
            </div>
        </div>
        <div>
            <label> 縁２ </label>
            <div class="form-group form-inline">
                <label> 文字中色 </label>
                <input class="" type="color" value="#ffffff" name="style[beforewp_color][]" />
                <label> 縁色 </label>
                <input class="" type="color" value="#000000" name="style[beforewpfuti_color][]" />
                <label>縁サイズ</label> <input type="text" class="form-control" value="6" name="style[Outline][]" size="2" />
                <label> 影色 </label>
                <input class="" type="color" value="#000000" name="style[beforewpshadow_color][]" />
                <label>影サイズ</label> <input type="text" class="form-control" value="2" name="style[Shadow][]" size="2" />
                <label>ルビ縁サイズ</label> <input type="text" class="form-control" value="4" name="style[Ruby_Outline][]" size="2" />
                <label>ルビ影サイズ</label> <input type="text" class="form-control" value="1" name="style[Ruby_Shadow][]" size="2" />
                <label>ぼかし量</label> <input type="text" class="form-control" value="0" name="style[blur][]" size="2" />
                <label class="checkbox-inline">
                     <input type="checkbox" name="style[removeclip][]" value="1" /> ワイプ後（Clip削除）
                </label>
            </div>
        </div>
            
            <button type="submit" class="btn btn-default">ダウンロード</button>
        </div>
        </form>
    </div>

    
  </body>
</html>