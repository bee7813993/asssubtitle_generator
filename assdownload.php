<?php

  if(array_key_exists("filename",$_REQUEST)) {
      $filename=$_REQUEST["filename"];
  }

  if(array_key_exists("data",$_REQUEST)) {
      $data=$_REQUEST["data"];
  }
  
  if(empty($filename)){
      print "no filename<br />\n";
      die();
  }

  if(empty($data)){
      print "no data<br />\n";
      die();
  }

  header('Content-Type: application/octet-stream');
  header('Content-Disposition: attachment; filename="'.html_entity_decode($filename).'"');
  
  $stream = fopen('php://output', 'w');
  fwrite($fp, pack('C*',0xEF,0xBB,0xBF));//BOM‘‚«ž‚Ý
  fwrite($stream, $data);

?>
