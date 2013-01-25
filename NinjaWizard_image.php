<?php
/**********************************************************

  IMAGE JACK - a Ninja Wizard class
  v.110709

  Langel Bookbinder
  firteendesign.com

**********************************************************/



class Image {


  function Load($file)  {
    $a = new Image();
    $b = @getimagesize($file);
    $a->width = $b[0];
    $a->height = $b[1];
    $a->type = $b[2];
    if (!Image::GetType($a->type)) $a->error .= 'Unfound or unsupported image type.';
    else  {
      if ($a->type==1)  $a->image = @imagecreatefromgif($file);
      if ($a->type==2)  $a->image = @imagecreatefromjpeg($file);
      if ($a->type==3)  $a->image = @imagecreatefrompng($file);
      if (!$a->image) $a->error .= 'Unreadable image file.  :( ';
    }
    return $a;
  }

  function SaveGIF($file)  {
    $this->SetType('gif');
    imagegif($this->image, $file);
  }
  function SaveJPG($file,$q=90)  {
    $this->SetType('gif');
    imagejpeg($this->image, $file, $q);
  }
  function SavePNG($file,$q=9)  {
    $this->SetType('png');
    imagepng($this->image, $file, $q);
  }

  function Destroy()  {
    imagedestroy($this->image);
  }

  function GetType($typeID) {
    $types = array(1=>'gif', 2=>'jpeg', 3=>'png');
    if ($types[$typeID]=='')  return false;
    else return $types[$typeID];
  }

  function SetType($string) {
    $str = strtolower($string);
    if ($str=='gif')  $this->type = 1;
    if ($str=='jpeg'||$str=='jpg')  $this->type = 2;
    if ($str=='png')  $this->type = 3;
  }

  function CreateThumbnail($width,$height)  {
    $a = new Image();
    $a->image = imagecreatetruecolor($width,$height);
    $a->width = $width;
    $a->height = $height;
    $ratio = $this->width/$this->height;

    if ($width/$height > $ratio) {
      $width = round($height*$ratio);
    }
    else {
      $height = round($width/$ratio);
    }
    echo $ratio.' '.$width.' '.$height;
    imagecopyresampled($a->image, $this->image, 0,0, 0,0, $width,$height, $this->width,$this->height);
    return $a;
  }

  function PixelResize($multi=2)  {
    $a = new Image();
    $a->width = $this->width*$multi;
    $a->height = $this->height*$multi;
    $a->image = imagecreatetruecolor($a->width,$a->height);
    /*
    // works for pngs?
    imagealphablending($a->image, false);
    imagesavealpha($a->image,true);
    $transparent = imagecolorallocatealpha($a->image, 255, 255, 255, 127);
    imagefilledrectangle($a->image, 0, 0, $a->width, $a->height, $transparent);
    */
    // give the image a transparent bg!!!
    $bg = imagecolorallocate($a->image,0,255,0);
    imagecolortransparent($a->image,$bg);
    imagefilledrectangle($a->image, 0, 0, $a->width, $a->height, $bg);
    imagecopyresized($a->image,$this->image, 0,0,0,0, $a->width,$a->height, $this->width,$this->height);

    return $a;
  }

}

?>