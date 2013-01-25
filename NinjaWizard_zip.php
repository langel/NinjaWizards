<?php

/*****************************************

  Ninja Wizard

  Z I P / U N Z I P

  v.0.23.090314

  code by puke7

  firteendesign.com

  Avoid the dreaded PECL extension!!

  Compatible with *NIX systems.

*****************************************/

##  http://linux.about.com/od/commands/l/blcmdl1_zip.htm
##  good reference on zip options



class NinjaWizard_zip {


  function Test() {
    exec('zip',$zip);
    exec('unzip',$unzip);
    if (count($zip)&&count($unzip))
      return TRUE;
    else
      return FALSE;
  }


  function GetZipHelp() {
    system('zip -h',$help);
    return $help;
  }


  function GetUnzipHelp() {
    system('unzip -h',$help);
    return $help;
  }


  function Create($filename,$working_dir='NinjaWizard_zip/') {
    $a = new NinjaWizard_zip();
    $a->filename = $filename;
    $a->files = array();
    $a->working_dir = $working_dir;
    return $a;
  }


  function AddFile($src,$des='') {
    $this->files[$src] = $des;
  }


  function Save($options='') {

    if (!count($this->files))
      return FALSE;

    $cwd = getcwd();
    $tmp_dir = $this->working_dir;
    if (!is_dir($tmp_dir))
      mkdir($tmp_dir);

    //print_r($this->files);

    foreach($this->files as $src => $des) {
      //echo $src.' ';
      if ($des!=''&&is_file($src)) {
        if (strstr($des,'/')) {
          $d = $tmp_dir.substr($des,0,strrpos($des,'/'));
          if (!is_dir($d))
            mkdir($d,0777,TRUE);
        }
        if (is_file($src))
          copy($src,$tmp_dir.$des);
        else
          $p[] = 'FILE :: '.$src.' NOT FOUND =0';
        if (is_file($this->filename))
          rename($this->filename,$tmp_dir.'temp.zip');
        chdir($tmp_dir);
        exec('zip '.$options.' temp.zip "'.$des.'"',$p);
        chdir($cwd);
        rename($tmp_dir.'temp.zip',$this->filename);
      }
      elseif (is_file($src))  {
        exec('zip '.$options.' '.$this->filename.' "'.$src.'"',$p);
      }
      else
        echo $src.' missing'.CR.BR;
    }

    return $p;
  }


  function CleanUp($d)  {
    if (is_dir($d)) {
      foreach(glob($d.'/*') as $f) {
        if (is_dir($f)&&!is_link($f)) {
          deltree($f);
        } else {
          unlink($f);
        }
      }
      rmdir($d);
    }
  }


}

?>