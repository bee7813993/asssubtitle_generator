<?php
  require_once("readass.php");

  if(array_key_exists("filename",$_REQUEST)) {
      $filename=$_REQUEST["filename"];
  }

  if(array_key_exists("data",$_REQUEST)) {
      $data=$_REQUEST["data"];
  }

  if(array_key_exists("ass_structs_json",$_REQUEST)) {
      $ass_structs_json=urldecode($_REQUEST["ass_structs_json"]);
  }

  $kind = "clip";
  $stylelist = array();
  if(array_key_exists("style",$_REQUEST)) {
      $style=$_REQUEST["style"];

      for($i = 0; $i < count($style['beforewp_color']); $i++){
          $onestyle = array (
           'PrimaryColour' => $style['beforewp_color'][$i],
           'SecondaryColour' => $style['beforewp_color'][$i],
           'OutlineColour' =>$style['beforewpfuti_color'][$i],
           'BackColour' => $style['beforewpshadow_color'][$i],
           'Outline' => $style['Outline'][$i],
           'Shadow' => $style['Shadow'][$i],
           'Ruby_Outline' => $style['Ruby_Outline'][$i],
           'Ruby_Shadow' => $style['Ruby_Shadow'][$i],
           'blur' => $style['blur'][$i],
           'removeclip' => false
           );
           if(array_key_exists("removeclip",$style)) {
             $onestyle['removeclip'] = true;
           }
          $stylelist[] = $onestyle;
      }
  }

  
  if(empty($filename)){
      print "no filename<br />\n";
      die();
  }

  if(empty($data) && empty($ass_structs_json)){
      print "no data<br />\n";
      die();
  }

  // ass生成関数
  function ass_build($ass_structs_json, $stylelist) {
      $ass_structs = json_decode($ass_structs_json,true);
      
      if( empty($stylelist)) {
       // 一番外から
       $beforewp_color = '&H00FFFFFF';         	//文字中色 白
       $beforewpfuti_color = '&H00FFFFFF';     	//縁色 白
       $beforewpshadow_color = '&H00FFFFFF';   	//影色   白

       $changestyle = array (
           'PrimaryColour' => $beforewp_color,
           'SecondaryColour' => $beforewp_color,
           'OutlineColour' => $beforewpfuti_color,
           'BackColour' => $beforewpshadow_color,
           'Outline' => '15',
           'Shadow' => '0',
           'Ruby_Outline' => 13,
           'Ruby_Shadow' => 0,
           'blur' => '4',
           'removeclip' => false);
       $stylelist[] = $changestyle;

       $beforewp_color = '&H00FFFFFF';         	//文字色 白
       $beforewpfuti_color = '&H00000000';     	//縁色 黒
       $beforewpshadow_color = '&H00000000';   	//影色 黒

       $changestyle = array (
           'PrimaryColour' => $beforewp_color,
           'SecondaryColour' => $beforewp_color,
           'OutlineColour' => $beforewpfuti_color,
           'BackColour' => $beforewpshadow_color,
           'Outline' => '10',
           'Shadow' => '0',
           'Ruby_Outline' => 8,
           'Ruby_Shadow' => 0,
           'blur' => '0',
           'removeclip' => false);      
      $stylelist[] = $changestyle;
      }
      // var_dump($stylelist);
      $ass_structs = json_decode($ass_structs_json,true);
      if($ass_structs === NULL ) {
      print '-';
         print $ass_structs_json;
      print '-';
         return false;
      }else {
         return gen_new_ass($ass_structs,$stylelist);
      }
      
      
  }


  if(empty($data)) {
    $data = ass_build($ass_structs_json,$stylelist);
  } 
// print $data;
  header('Content-Type: application/octet-stream');
  header('Content-Disposition: attachment; filename="'.html_entity_decode($filename).'"');
  
  $stream = fopen('php://output', 'wb');
  fwrite($stream, pack('C*',0xEF,0xBB,0xBF));//BOM書き込み
  fwrite($stream, $data);
  fclose($stream);

?>
