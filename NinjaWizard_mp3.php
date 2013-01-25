<?php
/*****************************************

  Ninja Wizard

  M P 3    T A G S + M O A R Z

  v.0.75.080505

  code by puke7

  firteendesign.com

*****************************************/


function str2bin ($string) {
  while (strlen($string) > 0 )  {
    $byte = "";
    $i = 0;
    $byte = substr($string, 0, 1);
    while ($byte!=chr($i))
      $i++;
    $byte = base_convert($i, 10, 2);
    $byte = str_repeat("0", (8-strlen($byte))).$byte;
    $string = substr($string, 1);
    $binary .= $byte." ";
  }
  return $binary;
}


class mp3 {
                                                          ##   LOOK-UP TABLES!!  =D
    static  $bitnums  = array(1,2,4,8,16,32,64,128);
                                                      // header bits AAAAAAAA AAABBCCD EEEEFFGH IIJJKLMM
    static  $id       = array(                                    // B
      0 => 'MPEG-2.5',
      1 => '',
      2 => 'MPEG-2',
      3 => 'MPEG-1');
        static  $id2bitr = array(
          0 => 2,
          2 => 2,
          3 => 1);
        static  $id2freq = array(
          0 => 2,
          2 => 1,
          3 => 0
          );
    static  $layer    = array('Not defined',                      // C
                      'Layer III',
                      'Layer II',
                      'Layer I'
                      );
    static  $bitrate  = array(                                    // E abreviation*1000
    //            MPEG-1,  MPEG-1,   MPEG-1,    MPEG-2,   MPEG-2,   MPEG-2,
    //bits        layer I  layer II  layer III  layer I   layer II  layer III
       0 => array(),
       1 => array(32,        32,        32,        32,        32,        8),
       2 => array(64,        48,        40,        64,        48,        16),
       3 => array(96,        56,        48,        96,        56,        24),
       4 => array(128,       64,        56,        128,       64,        32),
       5 => array(160,       80,        64,        160,       80,        64),
       6 => array(192,       96,        80,        192,       96,        80),
       7 => array(224,       112,       96,        224,       112,       56),
       8 => array(256,       128,       112,       256,       128,       64),
       9 => array(288,       160,       128,       288,       160,       128),
      10 => array(320,       192,       160,       320,       192,       160),
      11 => array(352,       224,       192,       352,       224,       112),
      12 => array(384,       256,       224,       384,       256,       128),
      13 => array(416,       320,       256,       416,       320,       256),
      14 => array(448,       384,       320,       448,       384,       320),
      15 => array()
      );
    static  $frequency= array(                                    // F abbreviation
    //bits       MPEG-1   MPEG-2   MPEG-2.5
      0 => array('44kHz', '22kHz', '11kHz'),
      1 => array('48kHz', '24kHz', '12kHz'),
      2 => array('32kHz', '16kHz',  '8kHz'),
      3 => array()
      );
    static  $samplerate= array(                                   // F
    //bits       MPEG-1   MPEG-2  MPEG-2.5
      0 => array('44100', '22050', '11025'),
      1 => array('48000', '24000', '12000'),
      2 => array('32000', '16000',  '8000'),
      3 => array()
      );
    static  $mode     = array('Stereo',                           // I
                      'Joint Stereo',
                      'Dual Channel',
                      'Mono',
                      'Forced Stereo',    // found using mode extension bits?? lol
                      'Auto',
                      'Intensity Stereo',
                      'other'
                      );
    static  $samples   = array(
    //Layer      MPEG-1   MPEG-2  MPEG-2.5
      1 => array(  '48',    '48',   '48'),
      2 => array( '144',   '144',  '144'),
      3 => array( '144',    '72',   '72')
      );


  function Open($filename) {
    $a = new mp3;
    $a->filename = $filename;
    $a->filesize = filesize($filename);
    $a->file = fopen($a->filename,'r+b');
    $a->FetchID3v1();
    $a->FetchID3v2();
    $a->FetchInfos();
    $a->newTags = array();
    print_r($a);
    return $a;
  }

  function Close() {
    fclose($this->file);
  }


  function FetchID3v1() {
    fseek($this->file,filesize($this->filename)-128);                                         ## ID3v1 @ EOF
    $tag = fread($this->file,128);
    if (substr($tag,0,3)=="TAG")  {
      $id3v1 = array();
      (substr($tag,125,1)==chr(0)&&substr($tag,126,1)!=chr(0)) ? $id3v1['version'] = 1.1 : $id3v1['version'] = 1.0;
      $id3v1['title']  = trim(substr($tag,3,30),chr(0));
      $id3v1['artist'] = trim(substr($tag,33,30),chr(0));
      $id3v1['album']  = trim(substr($tag,63,30),chr(0));
      $id3v1['year']   = trim(substr($tag,93,4),chr(0));
      if ($id3v1['version']==1.1) {
        $id3v1['comment'] = trim(substr($tag,97,28),chr(0));
        $id3v1['track'] = ord(substr($tag,126,1));
      }
      else
        $id3v1['comment'] = trim(substr($tag,97,30),chr(0));
      $id3v1['genre'] = ord(substr($tag,127,1));
      (isset($id3v1['track'])) ? $id3v1['version'] = 1.1 : $id3v1['version'] = 1.0;
      $this->id3v1 = $id3v1;
      return TRUE;
    }
    return FALSE;
  }


  function FetchID3v2() {
    rewind($this->file);
    $head = fread($this->file,10);
    if (strstr($head,'ID3'))  {                                                         ## ID3v2 @ head of file
      $id3v2 = array();
      $id3v2['version'] = '2.'.ord(substr($head,3,1)).'.'.ord(substr($head,4,1));
      $id3v2['size'] = (ord(substr($head,6,1))<<21) + (ord(substr($head,7,1))<<14) + (ord(substr($head,8,1))<<7) + ord(substr($head,9,1));
      $Dbit = substr($head,5,1);
      if ($Dbit&$bitnums[7]) $id3v2['unsynchronisation'] = 1;
      if ($Dbit&$bitnums[6]) $id3v2['extended header'] = 1;
      if ($Dbit&$bitnums[5]) $id3v2['experimental indicator'] = 1;
 //    print intval($Dbit).':bits:'.str2bin(chr($Dbits));
      $id3v2['payload'] = fread($this->file,$id3v2['size']);
      $this->id3v2 = $id3v2;
      return TRUE;
    }
    else
      return FALSE;
  }



  function FetchInfos() {

    print $this->filename.'<br>';

    if (is_array($this->id3v2))
      fseek($this->file,$this->id3v2['size']+10);
    else
      rewind($this->file);

    $info = $this->ParseFrameHead();
    $this->version =    $info['version'];
    $this->layer =      $info['layer'];
    $this->frequency =  $info['frequency'];
    $this->samplerate = $info['samplerate'];
    $this->mode =       $info['mode'];
    $bitcount = $info['bitcount'];

    $FrameCount = 1;
    $BitrtTotal = $info['bitcount'];

    while (!strstr($info['head'],'TAG')&&$info['bytes'][0]==255)  {
      $FrameCount++;
      $BitrtTotal += $info['bitcount'];
      $info = $this->ParseFrameHead();
    }
    $abr = $BitrtTotal/$FrameCount;
    $this->bitcount = intval($abr);
    if ($this->bitcount==$bitcount)
      $this->CBR = round($abr/1000).'kbps';
    else
      $this->VBR = round($abr/1000).'kbps';
    $this->frames = $FrameCount;
    $this->length = round(($this->filesize-$this->id3v2['size']-(is_array($this->id3v1)?128:0))/($this->bitcount/8));
  }



  function ParseFrameHead()  {
        ##  FILE POINTER MUST ALREADY BE IN CORRECT POSITION
    $head = fread($this->file,4);

    $bytes = array();
    for ($i=0; $i<4; $i++)
      $bytes[] = ord(substr($head,$i,1));

    $frame = array();
    if ($bytes[1]&mp3::$bitnums[3]) $frame['b'] = 1;
      else $frame['b'] = 0;
    if ($bytes[1]&mp3::$bitnums[4]) $frame['b'] = $frame['b']+2;
    if (($bytes[1]&mp3::$bitnums[2])<<1) $frame['c'] = 2;
      else $frame['c'] = 0;
    if ($bytes[1]&mp3::$bitnums[1]) $frame['c']++;
    $frame['e'] = $bytes[2]>>4;
    if ($bytes[2]&mp3::$bitnums[3]) $frame['f'] = 2;
      else $frame['f'] = 0;
    if ($bytes[2]&mp3::$bitnums[2]) $frame['f']++;
    if ($bytes[2]&mp3::$bitnums[1]) $frame['g'] = 1;
      else $frame['g'] = 0;
    if ($bytes[3]&mp3::$bitnums[7]) $frame['i'] = 2;
      else $frame['i'] = 0;
    if ($bytes[3]&mp3::$bitnums[6]) $frame['i']++;
    if ($frame['i']==3) {
      if ($bytes[3]&mp3::$bitnums[5]) $frame['i'] = $frame['i']+2;
      if ($bytes[3]&mp3::$bitnums[4]) $frame['i']++;
    }

    $info['version']    = mp3::$id[$frame['b']];
    $info['layer']      = mp3::$layer[$frame['c']];
    $info['bitrate']    = mp3::$bitrate[$frame['e']][(4-$frame['c'])*mp3::$id2bitr[$frame['b']]-1].'kbps';
    $info['bitcount']   = mp3::$bitrate[$frame['e']][(4-$frame['c'])*mp3::$id2bitr[$frame['b']]-1]*1000;
    $info['frequency']  = mp3::$frequency[$frame['f']][mp3::$id2freq[$frame['b']]];
    $info['samplerate'] = mp3::$samplerate[$frame['f']][mp3::$id2freq[$frame['b']]];
    // ( (Samples Per Frame / 8 * Bitrate) / Sampling Rate) + Padding Size
    $info['samples']    = mp3::$samples[4-$frame['c']][mp3::$id2freq[$frame['b']]];
    $info['FrameSize']  = intval($info['samples']*$info['bitcount']/$info['samplerate'] + $frame['g']);
    $info['mode']       = mp3::$mode[$frame['i']];
    $info['bytes']      = $bytes;
//print_r($info);

    fseek($this->file,ftell($this->file)+$info['FrameSize']-4);
    return $info;
  }



  function CopyStripTags($dest)  {                      // copies mp3 stripping tags
    //copy($this->filename,$dest);
    $b = fopen($dest,'wb');
    if (isset($this->id3v2['version']))
      fseek($this->file,$this->id3v2['size']+10);
    else
      rewind($this->file);
    while (!feof($this->file))
        fwrite($b,fgets($this->file, 4096));
    fclose($b);
    $size = filesize($dest);
    $b = fopen($dest,'r+b');
    fseek($b,$size-128);
    while (fread($b,3)=='TAG')  {
      ## recursively remove duplicate ID3v1 tags - *untested* beyond single tag
      ftruncate($b,$size-128);
      $size = $size-128;
      fseek($b,$size-128);  //print 'boobsa';
    }
    fclose($b);
  }


  function SetTag($tag,$val)  {
    $this->newTags['$tag'] = $val;
  }



  function frame($tag,$t) {
    $a = $tag;
    $s = strlen($t);
    $s1 = $s>>24;
    $s2 = ($s-($s1<<24))>>16;
    $s3 = ($s-($s1<<24)-($s2<<16))>>8;
    $s4 = $s-($s1<<24)-($s2<<16)-($s3<<8);
    $a .= chr($s1).chr($s2).chr($s3).chr($s4);
    $a .= chr(0).chr(0).$t;
    return $a;
  }

  function UpdateTags($newfilename='')  {
    //setup id3 tags

    //create v1
    $v1 = 'TAG';
    $v1 .= str_pad(substr(utf8_decode($this->newTags['TIT2']),0,30),30,chr(0));
    $v1 .= str_pad(substr(utf8_decode($this->newTags['TPE1']),0,30),30,chr(0));
    $v1 .= str_pad(substr(utf8_decode($this->newTags['TALB']),0,30),30,chr(0));
    $v1 .= str_pad(substr(utf8_decode($this->newTags['TYER']),0,4),4,chr(0));
    if (isset($this->newTags['TRCK']))  {
      $v1 .= str_pad(substr(utf8_decode($this->newTags['COMM']),0,28),28,chr(0));
      $v1 .= chr(0);
      $track = explode('/',$this->newTags['TRCK']);
      $v1 .= chr(intval($track[0]));
    }
    else
      $v1 .= str_pad(substr(utf8_decode($this->newTags['COMM']),0,30),30,chr(0));
    if (isset($this->newTags['genre']))
      $v1 .= chr(intval($this->newTags['genre']));
    else
      $v1 .= chr(23);

    //create v2.3
    $v2 = '';
    foreach ($this->newTags as $tag => $val)  {
      if (substr($tag,0,1)=='T'&&$tag!='TXXX')  {
        $v2 .= mp3::frame($tag,chr(0).utf8_decode($val).chr(0));
      }
      if (substr($tag,0,1)=='W'&&$tag!='WXXX')  {
        $v2 .= mp3::frame($tag,utf8_decode($val).chr(0));
      }
      if ($tag=='COMM') {
        $v2 .= mp3::frame($tag,chr(0).'eng'.chr(0).utf8_decode($val).chr(0));
      }
      if ($tag=='APIC') {
        $i = getimagesize($val);
        $fo = fopen($val,'r');
        while (!feof($fo))
          $j = fread($fo, 8192);
        fclose($fo);
        $v2 .= mp3::frame($tag,chr(0).$i['mime'].chr(0).chr(17).'a picture'.chr(0).$j);
        unset($j);
      }
    }
    $v2size = array();
    $v2size['bin'] = base_convert(strlen($v2),10,2);
    //print strlen($v2).' => '.$v2size['bin'];
    $v2size['binlen'] = strlen($v2size['bin']);
    /*
    $v2size[4] = 0;
    $v2size[3] = 0;
    $v2size[2] = 0;
    $v2size[1] = 0;*/
    for ($i=0; $i<$v2size['binlen']; $i++ ) {
      $j = substr($v2size['bin'],$i,1);           // binary value
      $k = ceil(($v2size['binlen']-$i)/7);        // which byte
      $l = $v2size['binlen']-$i-(($k-1)*7)-1;     // which bit
      if ($j=='1')
        $v2size[$k] += mp3::$bitnums[$l];
 //     print '<br>bin:'.$j.' byte:'.$k.' bit:'.$l;
    }
 //   print_r($v2size);
 //   print $v2size[2].','.$v2size[1].'; ';
    $v2head = 'ID3'.chr(3).chr(0).chr(0).chr($v2size[4]).chr($v2size[3]).chr($v2size[2]).chr($v2size[1]);
    for ($i=0; $i<strlen($v2head); $i++)
      print ord(substr($v2head,$i,1)).',';
    $v2 = $v2head.$v2;
    print strlen($v2);
    print '<br>'.$v2;
    $this->CopyStripTags('temp.mp3');
    if ($newfilename=='') $newfilename=='new'.$this->filename;
    $f = fopen($newfilename,'w');
   // echo 'new'.$this->filename;
    fwrite($f,$v2);
    $fo = fopen('temp.mp3','r');
    while (!feof($fo))
      fwrite($f,fread($fo, 8192));
    fclose($fo);
    unlink('temp.mp3');
    fwrite($f,$v1);
    fclose($f);
  }

}
/*
?><HTML><head>
<META http-equiv="Content-Type" content="text/html; charset=ascii">
</head><body><pre>
<?

$dir = dir("mp3z");
while (false!==($f=$dir->read()))  {
  //if (is_file('mp3z/'.$f)) print $f.'<br>';
  if (@strpos($f,'.mp3',strlen($f)-4)) {
    $mp3 = mp3::Open('mp3z/'.$f);

    $mp3->Close();
    print '<br><br>';
  }
}
$dir->close;

?></body></html><?
*/
/*
?><HTML><head>
<META http-equiv="Content-Type" content="text/html; charset=ascii">
</head><body>
<font face="system">
<?

$dir = dir("mp3z");
//print '<pre>';
while (false!==($f=$dir->read()))  {
  if (is_file('mp3z/'.$f)) print $f.'<br>';
  if (@strpos($f,'.mp3',strlen($f)-4)) {
    $mp3 = mp3::Open('mp3z/'.$f);
    //$mp3->CopyStripTags('_'.$f);
    $mp3->newTags['TIT2'] = 'poops';
    $mp3->newTags['TYER'] = '1994';
    $mp3->newTags['TALB'] = 'mighty ASS!!!!';
    $mp3->newTags['TPE1'] = 'nut';
    $mp3->newTags['COMM'] = "kljdshf alsfdh alfh wif lirg2 kaldfu3 08y0 fh sdifh0h4f0 hf 08 af0h 0fh23 0f8ha e0fh ae0f8g ae08fg ae0f8g wa9eufg9g 9eg 9wegfi awe 9c7t3rc9 auwgrc 9egf9 ugzsdi fg9tf9 8xg ud icf9732tr uwegfa c9stf9 9c gef9c a9f8tawe 9fgz isdf 98r hwef zhsdf08eyf98egf ud 8 3r gli2fg iufg9723g laiwef i73tr iaufgl a9873 li3f olgglisaugdf723";
//    $mp3->newTags['APIC'] = '070318.jpg';
    $mp3->UpdateTags();
    $mp3->Close();
    print '<br><br>';
  }
}
$dir->close;

?></body></html>
*/
/*
$d = "J:/firteenrecords.net/data/albums/002/";
$dir = dir($d);
//print $dir;
print '<pre>';
$c = 1; $cc = 0;
chdir($d);
foreach (glob("*.mp3") as $f )  {
  //$fi = $d.$f;
    print $f.'<br>';
    $new = ucwords(substr_replace($f,'Cringen Tingency -',3,11+$cc));
    if (!is_file($new)) {
      $mp3 = mp3::Open($f);
      print $new.'<br>';
      //$mp3->CopyStripTags('_'.$f);
      $mp3->newTags['TIT2'] = ucwords(substr($f,15+$cc,strlen($f)-19-$cc));
      $mp3->newTags['TYER'] = '1999';
      $mp3->newTags['TALB'] = 'Sport Utility Vehicle (the industrial training take-along home audio tape)';
      $mp3->newTags['TPE1'] = 'Cringen Tingency';
      $mp3->newTags['COMM'] = "firteenrecords.net #002";
      $mp3->newTags['TRCK'] = substr($f,0,2);
//    $mp3->newTags['APIC'] = '070318.jpg';
      $mp3->UpdateTags($new);
      $mp3->Close();
      unlink($f);
      print '<br><br>';
      $c++;
      if ($c>9) $cc = 1;
  }
}
$dir->close;
*/