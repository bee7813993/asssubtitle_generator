<?php

if (setlocale(LC_ALL,  'ja_JP.UTF-8', 'Japanese_Japan.932') === false) {
    logtocmd('Locale not found: ja_JP.UTF-8');
    exit(1);
}

date_default_timezone_set('Asia/Tokyo');

function delete_bom($str)
{
    if (ord($str{0}) == 0xef && ord($str{1}) == 0xbb && ord($str{2}) == 0xbf) {
        $str = substr($str, 3);
    }
    return $str;
}

function is_section($line){
    if(preg_match("/^\[.*\]$/",$line) ){
        return trim($line, '[]');
    }
    return false;

}

function analyze_ass_line_style($line){

    $line_with_key = array();

    $format = 'Format: Name, Fontname, Fontsize, PrimaryColour, SecondaryColour, OutlineColour, BackColour, Bold, Italic, Underline, StrikeOut, ScaleX, ScaleY, Spacing, Angle, BorderStyle, Outline, Shadow, Alignment, MarginL, MarginR, MarginV, Encoding';
    $format_info = preg_split('/[:,]/',$format);
    $format_info = array_map('trim',$format_info);
    $format_count = count($format_info);

    $line_val_1=preg_split('/[,]/',$line, $format_count - 1);
    $line_val=array_merge(array_map('trim', preg_split('/[:]/',$line_val_1[0])),array_slice($line_val_1,1));
    if(count($line_val) == $format_count ){
        $line_with_key=array_combine($format_info,$line_val);
    }
    return $line_with_key;
    


}

function analyze_ass_line_event($line){

    $line_with_key = array();
    $format = 'Format: Layer, Start, End, Style, Name, MarginL, MarginR, MarginV, Effect, Text';
    $format_info_1 = preg_split('/[,]/',$format);
    $format_info = array_merge(preg_split('/[:]/',$format_info_1[0]),array_slice($format_info_1,1));
    $format_info = array_map('trim',$format_info);
    $format_count = count($format_info);
    
    

    $line_val_1=preg_split('/[,]/',$line, $format_count-1);
    $line_val=array_merge(array_map('trim', preg_split('/[:]/',$line_val_1[0])),array_slice($line_val_1,1));
//    var_dump($format_info);
//    var_dump($line_val);
    if(count($line_val) == $format_count ){
        $line_with_key=array_combine($format_info,$line_val);
    }
    return $line_with_key;
    

}


function analyze_ass($rawass){
  $cr = array("\r\n", "\r");
  $ass_struct = array();
  
  $current_section = "";
  
  $rawass = delete_bom( $rawass);

  
  $rawass = str_replace($cr, "\n", $rawass); 
  $ass_lines =  explode("\n", $rawass);


  
  foreach($ass_lines as $line){
    // print 'Check line: '.mb_convert_encoding($line, "SJIS-win","UTF-8" )."\r\n";
    if( $section_name = is_section($line) ){
        $current_section = $section_name;
    }else if(empty($current_section) ){
        $ass_struct[]=$line;
    }else{
        if(preg_match("/Styles/i",$current_section)){
            $ass_struct[$current_section][]=analyze_ass_line_style($line);
        }else if(preg_match("/Events/i",$current_section)){
            $ass_struct[$current_section][]=analyze_ass_line_event($line);
        }else{
            $ass_struct[$current_section][]=$line;
        }
    }
  }
  
  return $ass_struct;
  
}

function build_style_line($stylestruct){
    $returnline = "";
    
    foreach($stylestruct as $line_1){
      if(count($line_1) <= 1){
      }else {
        $lastkind = 0;
        foreach($line_1 as $key => $value){
        
          if($lastkind == 1){
              $returnline = $returnline.': ';
          }else if($lastkind == 2){
              $returnline = $returnline.',';
          }
          $returnline = $returnline.$value;
          if(strcmp($key,'Format') === 0) {
              $lastkind = 1;
          }else{
              $lastkind = 2;
          }
        }
      }
      $returnline = $returnline."\r\n";
    }
    
    return $returnline;
}

function build_event_line($stylestruct){
    return build_style_line($stylestruct);
}

function outputass($ass_struct){

    $outputstr = "";
    $globalstr = "";
    $stylestr = "";
    $eventstr = "";
    
    foreach($ass_struct as $key => $value ){
        if(preg_match("/Styles/i",$key)){
            $stylestr = $stylestr."[$key]\r\n";
            $stylestr = $stylestr.build_style_line($value);
        }else if(preg_match("/Events/i",$key)){
            $eventstr = $eventstr."[$key]\r\n";
            $eventstr = $eventstr.build_event_line($value);
        }else{
            $globalstr = $globalstr."[$key]\r\n";
            foreach($value as $globalvalue ){
                $globalstr = $globalstr.$globalvalue."\r\n";
            }
        }
    }
    
    $outputstr = $globalstr.$stylestr.$eventstr;
    
    return $outputstr ;
}

function get_sectionlist($ass_structs){
    
    $sectionlist = array();
//    var_dump($ass_structs); 
    foreach($ass_structs as $key => $value){
        $sectionlist[] = $key;
    }
    
    return $sectionlist;
}

function get_stylesection($ass_structs){

    $stylesectionname = "";
    $sectionlist = get_sectionlist($ass_structs);
    foreach($sectionlist as $value){
        if(preg_match("/Styles/i",$value)){
            $stylesectionname = $value;
            break;
        }
    }
    return $stylesectionname;
}

function get_stylelist($ass_structs){

    $stylesectionname = get_stylesection($ass_structs);
    
    $stylelist = "";
    if(!empty($stylesectionname)){
        foreach($ass_structs[$stylesectionname] as $styleinfo){
            if(count($styleinfo) <= 1) continue;
            if($styleinfo['Format'] === 'Format' ) continue;
            if(!empty($styleinfo['Name']))
                $stylelist[] = $styleinfo['Name'];
        }
    }
    return $stylelist;
}

function copy_style($ass_structs, $fromstylename ,$tostylename){
    
    $newstyle = array();
    $stylesectionname=get_stylesection($ass_structs);
    
    foreach($ass_structs[$stylesectionname] as $styleinfo ){
        if(count($styleinfo) <= 1) continue;
        if($styleinfo["Name"] === $fromstylename ){
            $newstyle = $styleinfo;
            $newstyle["Name"] = $tostylename;
            $ass_structs[$stylesectionname][] = $newstyle;
            break;
        }
    }
    
    return $ass_structs;
}

function mod_style($ass_structs, $stylename, $style_struct ){
    // print " mod_style(ass_structs, $stylename , style_struct)";
    $newstyle = array();
    $stylesectionname=get_stylesection($ass_structs);
    
    for($i = 0 ; $i < count($ass_structs[$stylesectionname]) ; $i++  ){
        if(count($ass_structs[$stylesectionname][$i]) <= 1) continue;
// print "compair stylename ".$ass_structs[$stylesectionname][$i]['Name']." :  $stylename";
        if($ass_structs[$stylesectionname][$i]["Name"] === $stylename ){
            foreach( $style_struct as $key => $value ){
                // print "key :$key value:$value \r\n";

                if(array_key_exists($key,$ass_structs[$stylesectionname][$i])){
                // print $ass_structs[$stylesectionname][$i][$key];
                    $ass_structs[$stylesectionname][$i][$key] = $value;
                }
            }
//            var_dump($ass_structs[$stylesectionname]);
        }
    }
    // var_dump($ass_structs);
    return $ass_structs;
}

function copy_event($ass_structs, $fromstylename ,$tostylename){

    $newevents = array();
    
    foreach($ass_structs["Events"] as $eventinfo ){
        if(count($eventinfo) <= 1) continue;
        if(trim($eventinfo["Style"]) === trim($fromstylename) ){
            $newevents = $eventinfo;
            $newevents["Style"] = trim($tostylename);
            $ass_structs["Events"][] = $newevents;
        }
    }
    
    return $ass_structs;

}

function mod_event_text($ass_structs, $stylename ,$preg_string,$replacement ){

    $newevents = array();
    
    for($i = 0 ; $i < count($ass_structs["Events"]) ; $i++  ){
        if(count($ass_structs["Events"][$i]) <= 1) continue;

        if(trim($ass_structs["Events"][$i]["Style"]) === trim($stylename) ){
           $ass_structs["Events"][$i]["Text"] = preg_replace($preg_string,$replacement, $ass_structs["Events"][$i]["Text"]);
           // print($preg_string);
           // var_dump($ass_structs["Events"][$i]);
        }
    }
    
    return $ass_structs;

}

function mod_event($ass_structs, $stylename, $event_struct ){
    
    $newstyle = array();
    $sectionname='Events';
    
    for($i = 0 ; $i < count($ass_structs[$sectionname]) ; $i++  ){
        if(count($ass_structs[$sectionname][$i]) <= 1) continue;

        if($ass_structs[$sectionname][$i]["Style"] === $stylename ){
            foreach( $event_struct as $key => $value ){
                // print "key :$key value:$value \r\n";

                if(array_key_exists($key,$ass_structs[$sectionname][$i])){
                    $ass_structs[$sectionname][$i][$key] = $value;
                }
            }
           // var_dump($ass_structs[$sectionname][$i]);
        }
    }
    return $ass_structs;
}

/***
$propertyarray
 futistyle[] = array (
    'PrimaryColour' => --,
    'SecondaryColour' => --,
    'OutlineColour' => --,
    'BackColour' => --,
    'Outline' => --,
    'Shadow' => --,
    'Ruby_Outline' => --,
    'Ruby_Shadow' => --,
    'blur' => '4',
    'removeclip' => false,
 );

***/

function gen_new_ass($ass_structs, $propertyarray){
    $stylelist_main = array ("songInfo1", "Kanji1", "Kanji2", "Kanji3");
    $stylelist_ruby = array ("songInfo2", "songInfo", "Ruby1", "Ruby2", "Ruby3");


    $futicount = 0;
        $tmp_ass_structs = $ass_structs;
    foreach ($propertyarray as $changestyle) {
        if($futicount == 0 ){
            $style_footer = "";
        }else {
            $style_footer = '_uti'.$futicount;

            foreach ($stylelist_main as $usestyle ){
                $tmp_ass_structs = copy_style($tmp_ass_structs, $usestyle , $usestyle.$style_footer);
                $tmp_ass_structs = copy_event($tmp_ass_structs, $usestyle , $usestyle.$style_footer);
            }
            foreach ($stylelist_ruby as $usestyle ){
                $tmp_ass_structs = copy_style($tmp_ass_structs, $usestyle , $usestyle.$style_footer);
                $tmp_ass_structs = copy_event($tmp_ass_structs, $usestyle , $usestyle.$style_footer);
            }
        }
        
        foreach ($stylelist_main as $usestyle ){
            $tmp_ass_structs = mod_style($tmp_ass_structs, $usestyle.$style_footer , $changestyle);
        }
        
        $changestyle['Outline'] = $changestyle['Ruby_Outline'];
        $changestyle['Shadow'] =  $changestyle['Ruby_Shadow'];

        foreach ($stylelist_ruby as $usestyle ){
            $tmp_ass_structs = mod_style($tmp_ass_structs, $usestyle.$style_footer , $changestyle);
        }
        
        if($changestyle['blur'] >= 0 ){
            $blurstr = '{\\blur'.$changestyle['blur'].'}';
            //外縁ブラー化
            foreach ($stylelist_main as $usestyle ){
                $tmp_ass_structs = mod_event_text($tmp_ass_structs, $usestyle.$style_footer , '/{\\\\blur\\d}/' , '');
                $tmp_ass_structs = mod_event_text($tmp_ass_structs, $usestyle.$style_footer , '/^/' , $blurstr);
            }
            foreach ($stylelist_ruby as $usestyle ){
                $tmp_ass_structs = mod_event_text($tmp_ass_structs, $usestyle.$style_footer , '/{\\\\blur\\d}/' , '');
                $tmp_ass_structs = mod_event_text($tmp_ass_structs, $usestyle.$style_footer , '/^/' , $blurstr);
            }
        }
        
        if($changestyle['removeclip'] == true){
            $preg = '/{\\\\t\(\d{1,},\d{1,},\\\\clip.+?\\\\clip.+?}/';
            foreach ($stylelist_main as $usestyle ){
                $tmp_ass_structs=mod_event_text($tmp_ass_structs, $usestyle.$style_footer , $preg , '');
            }
            foreach ($stylelist_ruby as $usestyle ){
                $tmp_ass_structs=mod_event_text($tmp_ass_structs, $usestyle.$style_footer , $preg , '');
            }            
            
        }
        $futicount++;
    }
    return outputass($tmp_ass_structs);
}


/***
memo ass変更情報

struct modassinfo {
    struct targetstyle['stylename']
}

対象となるStyle ['stylename']
-> [重数]-> { p_color, sec_coler, { OutlineColor, size} , {ShadowColer, size} , additional_tag}



***/

if(php_sapi_name()=='cli') {  // コマンドライン

// とりあえず argv[1]のファイル名の2重縁、縁ワイプ、ワイプ前文字ないブラシ用maskに対応した3つのassを出力するように作成する。

if( (count($argv) <= 0) || ! file_exists($argv[1])){
 print "file not found";
 die();
}

$filename=$argv[1];

$assfile=file_get_contents($filename);
 
$ass_structs=analyze_ass($assfile);


//******************** ワイプ二重縁生成
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
$tmp_ass_structs = copy_style($tmp_ass_structs, 'whosing1' , 'whosing1_uti');
$tmp_ass_structs = copy_event($tmp_ass_structs, ' whosing1' , 'whosing1_uti');

$beforewp_color = '&H00FFFFFF';
$beforewpfuti_color = '&H00253378';
$beforewpshadow_color = '&H00253378';
$beforewpsotofuti_color = '&H00FFFFFF';


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
$changestyle = array (
    'PrimaryColour' => $beforewp_color,
    'SecondaryColour' => $beforewp_color,
    'OutlineColour' => $beforewpfuti_color,
    'BackColour' => $beforewpshadow_color,
    'Outline' => '3',
    'Shadow' => '1'
    );
$tmp_ass_structs = mod_style($tmp_ass_structs, 'Ruby1_uti' , $changestyle);
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
$tmp_ass_structs = mod_style($tmp_ass_structs, 'songInfo2' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'songInfo' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'whosing1' , $changestyle);

$tmp_ass_structs = mod_event_text($tmp_ass_structs, 'Kanji1' , '/^/' , '{\\blur4}');
$tmp_ass_structs = mod_event_text($tmp_ass_structs, 'Ruby1' , '/^/' , '{\\blur4}');
$tmp_ass_structs = mod_event_text($tmp_ass_structs, 'songInfo1' , '/^/' , '{\\blur4}');
$tmp_ass_structs = mod_event_text($tmp_ass_structs, 'songInfo2' , '/^/' , '{\\blur4}');
$tmp_ass_structs = mod_event_text($tmp_ass_structs, 'songInfo' , '/^/' , '{\\blur4}');
$tmp_ass_structs = mod_event_text($tmp_ass_structs, 'whosing1' , '/^/' , '{\\blur4}');

$tmp_ass_structs = mod_event($tmp_ass_structs, 'Ruby1_uti' , array('Layer' => '120'));

$ass_structs_clip = $tmp_ass_structs;
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
$tmp_ass_structs = mod_style($tmp_ass_structs, 'songInfo2' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'songInfo' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'whosing1' , $changestyle);

$preg = '/{\\\\t\(\d{1,},\d{1,},\\\\clip.+?\\\\clip.+?}/';
$tmp_ass_structs=mod_event_text($tmp_ass_structs, 'Kanji1' , $preg , '');
$tmp_ass_structs=mod_event_text($tmp_ass_structs, 'Ruby1' , $preg , '');
$tmp_ass_structs=mod_event_text($tmp_ass_structs, 'Kanji1_uti' , $preg , '');
$tmp_ass_structs=mod_event_text($tmp_ass_structs, 'Ruby1_uti' , $preg , '');

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

// mask用ass生成
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
$tmp_ass_structs = mod_style($tmp_ass_structs, 'songInfo1' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'songInfo2' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'songInfo' , $changestyle);
$tmp_ass_structs = mod_style($tmp_ass_structs, 'whosing1' , $changestyle);

$ass_structs_mask = $tmp_ass_structs;
$outbuf_mask = outputass($ass_structs_mask);

// 二重縁内側生成
// $new_ass_structs=copy_style($ass_structs, ' Kanji1' , ' Kanji1_futi');
// $new_ass_structs=copy_event($ass_structs, ' Kanji1' , ' Kanji1_futi');

// スタイル変更
// $new_ass_structs=mod_style($ass_structs, ' Kanji1' , array('PrimaryColour' => '&H000000FF', 'Outline' => '10'));

// 縁ぼかし \blur4
// $new_ass_structs=mod_event_text($ass_structs, ' Kanji1' , '/^/' , '{\\blur4}');
// Ruby Layer変更
// $new_ass_structs = mod_event($ass_structs, 'Ruby1' , array('Layer' => '120', 'MarginL' => '1'));
// clip外し
// $preg = '/{\\\\t\(\d{1,},\d{1,},\\\\clip.+?\\\\clip.+?}/';
// $new_ass_structs=mod_event_text($ass_structs, 'Kanji1' , $preg , '');

$assbasename_info = pathinfo($filename);
$assbasename = $assbasename_info['filename'];

file_put_contents($assbasename."_base.ass", $outbuf_base);
file_put_contents($assbasename."_clip.ass", $outbuf_clip);
file_put_contents($assbasename."_mask.ass", $outbuf_mask);


//mb_convert_variables("SJIS-win","UTF-8",$ass_structs);
//var_dump(get_stylelist($ass_structs));
}
?>
