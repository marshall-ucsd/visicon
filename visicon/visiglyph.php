<?php

define ("VISIUPLOAD",'glyph/');

class visiglyph {

  private $visiseed = 'foobar';
  public  $visisize = 24;
  public  $visip    = "default";
  private $visihash;

  private $resize      = 0;
  private $minvisisize = 24;

  private $blockone;  // first block
  private $blocktwo;  // second block
  private $blockcen;  // centre block

  private $rotone;    // rotation first block
  private $rottwo;    // rotation second block

  private $fgr;  // foreground color red
  private $fgg;  // foreground color green
  private $fgb;  // foreground color blue

  private $fgr2;  // foreground color 2 red
  private $fgg2;  // foreground color 2 green
  private $fgb2;  // foreground color 2 blue

  private $bgr;  // background color red
  private $bgg;  // background color green
  private $bgb;  // background color blue

  // imagin vars
  private $imgsize;
  private $quarter;
  private $quarter3;
  private $half;
  private $third;
  private $centre;

  private $glyph;  // the image
  private $glyphcol1;
  private $glyphcol2;

  private function initvars(){
    $this->visihash = md5($this->visip.$this->visiseed);

    $this->blockone = hexdec(substr($this->visihash,0,1));
    $this->blocktwo = hexdec(substr($this->visihash,1,1));
    $this->blockcen = hexdec(substr($this->visihash,2,1))&7;
    $this->rotone   = hexdec(substr($this->visihash,3,1))&3;
    $this->rottwo   = hexdec(substr($this->visihash,4,1))&3;
    $this->fgr      = hexdec(substr($this->visihash,5,2))&239;
    $this->fgg      = hexdec(substr($this->visihash,7,2))&239;
    $this->fgb      = hexdec(substr($this->visihash,9,2))&239;
    $this->fgr2     = hexdec(substr($this->visihash,11,2))&239;
    $this->fgg2     = hexdec(substr($this->visihash,13,2))&239;
    $this->fgb2     = hexdec(substr($this->visihash,15,2))&239;
    $this->bgr      = 255;
    $this->bgg      = 255;
    $this->bgb      = 255;

    if ($this->visisize < $this->minvisisize){
      $this->resize = $this->visisize;
      $this->visisize = $this->minvisisize;
    }
    $this->imgsize  = $this->visisize*3;
    $this->quarter  = $this->visisize/4;
    $this->quarter3 = $this->quarter*3;
    $this->half     = $this->visisize/2;
    $this->third    = $this->visisize/3;
    $this->centre   = $this->imgsize/2;
  }

  public function visicreate(){
    $this->initvars();

    $this->glyph     = imagecreate($this->imgsize,$this->imgsize);
    // imagealphablending($this->glyph, true);
    // imagesavealpha($this->glyph, true);
    $backgroundcolor = imagecolorallocate($this->glyph, $this->bgr, $this->bgg, $this->bgb);

    $this->glyphcol1 = imagecolorallocate($this->glyph, $this->fgr, $this->fgg, $this->fgb);
    $this->glyphcol2 = imagecolorallocate($this->glyph, $this->fgr2, $this->fgg2, $this->fgb2);

    $this->visicorners();
    $this->visisides();
    $this->visicentre();
  }

  public function visidisplay(){

    header('Content-type: image/png');
    if ($this->resize > 0){ // if we need to resample down
      $this->visisize = $this->resize;
      $imgsizeR  = $this->visisize*3;

      $imresize  = imagecreatetruecolor($imgsizeR,$imgsizeR);
      $backgroundcolor = imagecolorallocate($imresize, $this->bgr, $this->bgg, $this->bgb);
      imagecopyresampled ( $imresize, $this->glyph, 0, 0, 0, 0, $imgsizeR, $imgsizeR, $this->imgsize, $this->imgsize );
      // ImageColorTransparent($imresize,$backgroundcolor); // FIXME transparency feature not finished.
      #imagepng($imresize);
      imagepng($imresize,VISIUPLOAD.$this->visip.'.png'); // FIXME remove comments to generate file cache
    } else {
      #imagepng($this->glyph);
      imagepng($this->glyph,VISIUPLOAD.$this->visip.'.png');// FIXME remove comments to generate file cache
    }
    // header('Content-type: image/png');
    // imagepng($this->glyph);
  }

  private function visicorners(){
    $corner[0]['x'] = 0;
    $corner[0]['y'] = 0;
    $corner[1]['x'] = 0;
    $corner[1]['y'] = 2*$this->visisize;
    $corner[2]['x'] = 2*$this->visisize;
    $corner[2]['y'] = 2*$this->visisize;
    $corner[3]['x'] = 2*$this->visisize;
    $corner[3]['y'] = 0;

    for ($i=0; $i<4; $i++){
      $rotation = $this->rotone + $i;
      $this->getglyph($this->blockone,$rotation,$corner[$i],$this->glyphcol1);
    }
  }

  private function visicentre(){
    $sides[0]['x'] = $this->visisize;
    $sides[0]['y'] = $this->visisize;

    $rotation = 0;
    $this->getglyph($this->blockcen,$rotation,$sides[0],$this->glyphcol1,false);
  }

  private function visisides(){
    $sides[0]['x'] = $this->visisize;
    $sides[0]['y'] = 0;
    $sides[1]['x'] = 0;
    $sides[1]['y'] = $this->visisize;
    $sides[2]['x'] = $this->visisize;
    $sides[2]['y'] = 2*$this->visisize;
    $sides[3]['x'] = 2*$this->visisize;
    $sides[3]['y'] = $this->visisize;

    for ($i=0; $i<4; $i++){
      $rotation = $this->rottwo + $i;
      $this->getglyph($this->blocktwo,$rotation,$sides[$i],$this->glyphcol2);
    }
  }

  private function getglyph($block,$initrot,$modifier,$color,$outer=true){
    if ($outer){ 
      switch($block){
        case 1: // #1 mountains
          $points = array(
            0, 0,
            $this->quarter, $this->visisize,
            $this->half, 0
          );
          $points = $this->rotatepoints($points,$initrot,$modifier);
          $num = count($points) / 2;
          imagefilledpolygon($this->glyph, $points, $num, $color);

          $points = array(
            $this->half, 0,
            $this->quarter3, $this->visisize,
            $this->visisize, 0
          );
          $points = $this->rotatepoints($points,$initrot,$modifier);
          $num = count($points) / 2;
          imagefilledpolygon($this->glyph, $points, $num, $color);
          break;

        case 2: // #2 half triangle
          $points = array(
            0, 0,
            $this->visisize, 0,
            0, $this->visisize
          );
          $points = $this->rotatepoints($points,$initrot,$modifier);
          $num = count($points) / 2;
          imagefilledpolygon($this->glyph, $points, $num, $color);
          break;

        case 3: // #3 centre triangle
          $points = array(
            0,0,
            $this->half, $this->visisize,
            $this->visisize, 0
          );
          $points = $this->rotatepoints($points,$initrot,$modifier);
          $num = count($points) / 2;
          imagefilledpolygon($this->glyph, $points, $num, $color);
          break;

        case 4: // #4 half block
          $points = array(
            0,0,
            0,$this->visisize,
            $this->half, $this->visisize,
            $this->half, 0
          );
          $points = $this->rotatepoints($points,$initrot,$modifier);
          $num = count($points) / 2;
          imagefilledpolygon($this->glyph, $points, $num, $color);
          break;

        case 5: // #5 half diamond
          $points = array(
            $this->quarter, 0,
            0, $this->half,
            $this->quarter, $this->visisize,
            $this->half, $this->half
          );
          $points = $this->rotatepoints($points,$initrot,$modifier);
          $num = count($points) / 2;
          imagefilledpolygon($this->glyph, $points, $num, $color);
          break;

        case 6: // #6 spike
          $points = array(
            0,0,
            $this->visisize, $this->half,
            $this->visisize, $this->visisize,
            $this->half, $this->visisize
          );
          $points = $this->rotatepoints($points,$initrot,$modifier);
          $num = count($points) / 2;
          imagefilledpolygon($this->glyph, $points, $num, $color);
          break;

        case 7: // #7 quarter triangle
          $points = array(
            0, 0,
            $this->half, $this->visisize,
            0, $this->visisize
          );
          $points = $this->rotatepoints($points,$initrot,$modifier);
          $num = count($points) / 2;
          imagefilledpolygon($this->glyph, $points, $num, $color);
          break;

        case 8: // #8 diag triangle
          $points = array(
            0, 0,
            $this->visisize, $this->half,
            $this->half, $this->visisize
          );
          $points = $this->rotatepoints($points,$initrot,$modifier);
          $num = count($points) / 2;
          imagefilledpolygon($this->glyph, $points, $num, $color);
          break;

        case 9: // #9 centre mini triangle
          $points = array(
            $this->quarter, $this->quarter,
            $this->quarter3, $this->quarter,
            $this->quarter, $this->quarter3
          );
          $points = $this->rotatepoints($points,$initrot,$modifier);
          $num = count($points) / 2;
          imagefilledpolygon($this->glyph, $points, $num, $color);
          break;

        case 10: // #10 diag mountains
          $points = array(
            0, 0,
            $this->half, 0,
            $this->half, $this->half
          );
          $points = $this->rotatepoints($points,$initrot,$modifier);
          $num = count($points) / 2;
          imagefilledpolygon($this->glyph, $points, $num, $color);
          $points = array(
            $this->half, $this->half,
            $this->visisize, $this->half,
            $this->visisize, $this->visisize
          );
          $points = $this->rotatepoints($points,$initrot,$modifier);
          $num = count($points) / 2;
          imagefilledpolygon($this->glyph, $points, $num, $color);
          break;

        case 11: // #11 quarter block
          $points = array(
            0, 0,
            0,$this->half,
            $this->half, $this->half,
            $this->half,0
          );
          $points = $this->rotatepoints($points,$initrot,$modifier);
          $num = count($points) / 2;
          imagefilledpolygon($this->glyph, $points, $num, $color);
          break;

        case 12: // #12 point out triangle
          $points = array(
            0, $this->half,
            $this->half, $this->visisize,
            $this->visisize, $this->half
          );
          $points = $this->rotatepoints($points,$initrot,$modifier);
          $num = count($points) / 2;
          imagefilledpolygon($this->glyph, $points, $num, $color);
          break;

        case 13: // #13 point in triangle
          $points = array(
            0, 0,
            $this->half, $this->half,
            $this->visisize, 0
          );
          $points = $this->rotatepoints($points,$initrot,$modifier);
          $num = count($points) / 2;
          imagefilledpolygon($this->glyph, $points, $num, $color);
          break;

        case 14: // #14 diag point in
          $points = array(
            $this->half, $this->half,
            0, $this->half,
            $this->half, $this->visisize
          );
          $points = $this->rotatepoints($points,$initrot,$modifier);
          $num = count($points) / 2;
          imagefilledpolygon($this->glyph, $points, $num, $color);
          break;

        case 15: // #15 diag point out
          $points = array(
            0, 0,
            $this->half, 0,
            0, $this->half
          );
          $points = $this->rotatepoints($points,$initrot,$modifier);
          $num = count($points) / 2;
          imagefilledpolygon($this->glyph, $points, $num, $color);
          break;

        case 16:  // #16 diag side point out
        default:
          $points = array(
            0, 0,
            $this->half, 0,
            $this->half, $this->half
          );
          $points = $this->rotatepoints($points,$initrot,$modifier);
          $num = count($points) / 2;
          imagefilledpolygon($this->glyph, $points, $num, $color);
      } // end switch
    } else {
    // centre options:
      switch($block){
        case 1: // circle
          imagefilledellipse($this->glyph, $this->centre, $this->centre, 
                                           $this->quarter3, $this->quarter3, $color);
          // $num = count($points) / 2;
          // imagefilledpolygon($this->glyph, $points, $num, $color);
          break;

        case 2: // quarter square
          $points = array(
            $this->quarter, $this->quarter,
            $this->quarter, $this->quarter3,
            $this->quarter3, $this->quarter3,
            $this->quarter3, $this->quarter
          );
          $points = $this->rotatepoints($points,$initrot,$modifier);
          $num = count($points) / 2;
          imagefilledpolygon($this->glyph, $points, $num, $color);
          // imagefilledrectangle ( $im, $originx+$quarter, $originy+$quarter, $originx+$quarter3, $originy+$quarter3, $red);
          break;

        case 3: // full square
          $points = array(
            0, 0,
            0, $this->visisize,
            $this->visisize, $this->visisize,
            $this->visisize, 0
          );
          $points = $this->rotatepoints($points,$initrot,$modifier);
          $num = count($points) / 2;
          imagefilledpolygon($this->glyph, $points, $num, $color);
          // imagefilledrectangle ( $im, $originx, $originy, $originx+$blocksize, $originy+$blocksize, $red);
          break;

        case 4: // quarter diamond
          $points = array(
            $this->half, $this->quarter,
            $this->quarter3, $this->half,
            $this->half, $this->quarter3,
            $this->quarter, $this->half
          );
          $points = $this->rotatepoints($points,$initrot,$modifier);
          $num = count($points) / 2;
          imagefilledpolygon($this->glyph, $points, $num, $color);
          break;
    
        case 5: // diamond
          $points = array(
            $this->half, 0,
            0, $this->half,
            $this->half, $this->visisize,
            $this->visisize, $this->half
          );
          $points = $this->rotatepoints($points,$initrot,$modifier);
          $num = count($points) / 2;
          imagefilledpolygon($this->glyph, $points, $num, $color);
          break;
        default:
        // empty space
      }
    }
  }

  private function rotatepoints($pointarray,$rotation,$modifier){
    // rotation = 0,1,2,3 max
    $rotation = fmod($rotation,4);
    switch($rotation){
      case 1:
        ###
        for ($i=0; $i<count($pointarray);$i = $i+2){
          $tmp1 = $i;   $val1 = $pointarray[$tmp1];
          $tmp2 = $i+1; $val2 = $pointarray[$tmp2];
          $pointarray[$tmp1]  = $val2 + $modifier['x'];
          $pointarray[$tmp2]  = ($this->visisize - $val1) + $modifier['y'];
        }
        break;
      case 2:
        for ($i=0; $i<count($pointarray);$i = $i+2){
          $tmp1 = $i;   $val1 = $pointarray[$tmp1];
          $tmp2 = $i+1; $val2 = $pointarray[$tmp2];
          $pointarray[$tmp1]  = $this->visisize - $val1 + $modifier['x'];;
          $pointarray[$tmp2]  = $this->visisize - $val2 + $modifier['y'];
        }
        break;
      case 3:
        ###
        for ($i=0; $i<count($pointarray);$i = $i+2){
          $tmp1 = $i;   $val1 = $pointarray[$tmp1];
          $tmp2 = $i+1; $val2 = $pointarray[$tmp2];
          $pointarray[$tmp1]  = $this->visisize - $val2 + $modifier['x'];;
          $pointarray[$tmp2]  = $val1 + $modifier['y'];
        }
        break;
      default:
        for ($i=0; $i<count($pointarray);$i = $i+2){
          $tmp1 = $i;   $val1 = $pointarray[$tmp1];
          $tmp2 = $i+1; $val2 = $pointarray[$tmp2];
          $pointarray[$tmp1]  = $val1 + $modifier['x'];
          $pointarray[$tmp2]  = $val2 + $modifier['y'];
        }
    }
    return $pointarray;
  }
}

$glyph = new visiglyph();
$glyph->visip = 'ab';
// $glyph->visisize = 48;
$glyph->visicreate();
$glyph->visidisplay();

?>
