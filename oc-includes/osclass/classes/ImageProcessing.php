<?php
if(!defined('ABS_PATH')) exit('ABS_PATH is not loaded. Direct access is not allowed.');

/*
 * Copyright 2014 Osclass
 * Copyright 2026 Osclass by OsclassPoint.com
 *
 * Osclass maintained & developed by OsclassPoint.com
 * You may not use this file except in compliance with the License.
 * You may download copy of Osclass at
 *
 *     https://osclass-classifieds.com/download
 *
 * Do not edit or add to this file if you wish to upgrade Osclass to newer
 * versions in the future. Software is distributed on an "AS IS" basis, without
 * warranties or conditions of any kind, either express or implied. Do not remove
 * this NOTICE section as it contains license information and copyrights.
 */


  /**
   * This class represents a utility to load, resize, rotate and process images easily.
   */
class ImageProcessing {
  private $im;
  private $img;
  private $image_info;
  private $ext;
  private $mime;
  private $_font;
  private $_color;
  private $_width;
  private $_height;
  private $_exif;
  private $_watermarked = false;
  private $_use_imagick = false;

  /**
   * ImageProcessing constructor.
   *
   * @param $imagePath
   *
   * @throws \Exception
   */
  private function __construct($imagePath) {
    if(!file_exists($imagePath)) {
      throw new RuntimeException(sprintf(__('%s does not exist!'), $imagePath));
    }

    if(!is_readable($imagePath)) {
    throw new RuntimeException(sprintf(__('%s is not readable!'), $imagePath));
    }

    if(filesize($imagePath) == 0) {
    throw new RuntimeException(sprintf(__('%s is corrupt or broken!'), $imagePath));
    }

    $this->image_info = @getimagesize($imagePath);

    if(extension_loaded('imagick') && osc_use_imagick()) {
      $this->_use_imagick = true;
    }

    if($this->_use_imagick) {
      $this->im = new Imagick($imagePath);
      // animated GIF set frame 0
      if($this->im->getNumberImages() > 1) {
        foreach($this->im as $frame) {
          $frame = $this->im;
          break;
        }
      }
      
      // remove exif data except ICC profile
      $profiles = $this->im->getImageProfiles('icc', true);
      $this->im->stripImage();

      if(!empty($profiles) && isset($profiles['icc'])) {
        $this->im->profileImage('icc', $profiles['icc']);
      }
    
      $geometry = $this->im->getImageGeometry();
      $this->_width = $geometry['width'];
      $this->_height = $geometry['height'];
      
    } else {
      $content = file_get_contents($imagePath);
      $this->im = imagecreatefromstring($content);
      $this->_width = imagesx($this->im);
      $this->_height = imagesy($this->im);

      $this->_exif = array();
      
      if(@$this->image_info['mime'] === 'image/jpeg' && function_exists('exif_read_data')) {
        $this->_exif = @exif_read_data($imagePath);
      }
    }

    switch (@$this->image_info['mime']) {
      case 'image/gif':
      case 'image/png':
        $this->ext = 'png';
        $this->mime = 'image/png';
        break;
        
      default:
        $this->ext = 'jpg';
        $this->mime = 'image/jpeg';
        
        if(!$this->_use_imagick) {
          $bg = imagecreatetruecolor($this->_width, $this->_height);
          imagefill($bg, 0, 0, $this->imageColor($bg));
          imagesavealpha($bg, true);
          imagealphablending($bg, true);
          imagecopy($bg, $this->im, 0, 0, 0, 0, $this->_width, $this->_height);
          
          if(PHP_VERSION_ID < 80500) {
            imagedestroy($this->im);
          }
          
          $this->im = null;
          $this->im = $bg;
        }
        
        break;
    }

    return $this;
  }

  /**
   * @param $imagePath
   *
   * @return \ImageProcessing
   * @throws \Exception
   */
  public static function fromFile($imagePath) {
    return new ImageProcessing($imagePath);
  }

  public function __destruct() {
    if($this->_use_imagick) {
      $this->im->destroy();
    } else {
      if(PHP_VERSION_ID < 80500) {
        imagedestroy($this->im);
      }
      
      $this->im = null;
    }
  }

  /**
   * @return string
   */
  public function getExt() {
    return $this->ext;
  }

  /**
   * @return string
   */
  public function getMime() {
    return $this->mime;
  }

  /**
   * @return int
   */
  public function getWidth() {
    return $this->_width;
  }

  /**
   * @return int
   */
  public function getHeight() {
    return $this->_height;
  }

  /**
   * @param    $width
   * @param    $height
   * @param null $force_aspect
   * @param bool $upscale
   *
   * @return $this
   */
  public function resizeTo($width, $height, $force_aspect = null, $upscale = true) {
    $target_width = $width;
    $target_height = $height;

    $best_fit = osc_best_fit_image();

    if($force_aspect == null) {
    $force_aspect = osc_force_aspect_image();
    } else if($best_fit) {
        $force_aspect = false;
      }

    if(($this->_width / $this->_height) >= ($width / $height)) {
      if($upscale) {
        $newW = $width;
      } else {
        $newW = ($this->_width > $width) ? $width : $this->_width;
      }
      
      $newH = ceil($this->_height * ($newW / $this->_width));
      if($force_aspect) {
        $height = $newH;
      }
    } else {
      if($upscale) {
        $newH = $height;
      } else {
        $newH = ($this->_height > $height) ? $height : $this->_height;
      }
      $newW = ceil($this->_width * ($newH / $this->_height));
      if($force_aspect) {
        $width = $newW;
      }
    }

    if($this->_use_imagick) {
      $bg = new Imagick();
      if($this->ext === 'jpg') {
        $bg->newImage($width, $height, osc_canvas_background());  // white or black
      } else {
        $bg->newImage($width, $height, 'none');
      }

      if($best_fit) {
        $this->img = $this->centeredThumbnail($this->im, $target_width, $target_height);

      } else {
        $this->im->thumbnailImage($width, $height, true);
        $bg->compositeImage($this->im, imagick::COMPOSITE_OVER, floor(($width - $newW) / 2), floor(($height - $newH) / 2));
        $this->im = $bg;
      }
    } else {
      if($best_fit) {
        $newIm = imagecreatetruecolor($target_width, $target_height);
        imagealphablending($newIm, false);
        $colorTransparent = $this->imageColor($newIm);
        imagefill($newIm, 0, 0, $colorTransparent);
        imagesavealpha($newIm, true);

        $w = $this->_width;
        $h = $this->_height;

        $original_aspect = $w / $h;
        $thumb_aspect = $target_width / $target_height;

        if($original_aspect >= $thumb_aspect) {
          // If image is wider than thumbnail (in aspect ratio sense)
          $new_height = $target_height;
          $new_width = $w / ($h / $target_height);
        } else {
          // If the thumbnail is wider than the image
          $new_width = $target_width;
          $new_height = $h / ($w / $target_width);
        }

        imagecopyresampled($newIm, $this->im, floor(0 - ($new_width - $target_width) / 2), floor(0 - ($new_height - $target_height) / 2), 0, 0, $new_width, $new_height, $w, $h);
       
      } else {
        $newIm = imagecreatetruecolor($width, $height);
        imagealphablending($newIm, false);
        $colorTransparent = $this->imageColor($newIm);
        imagefill($newIm, 0, 0, $colorTransparent);
        imagesavealpha($newIm, true);
        
        imagecopyresampled($newIm, $this->im, floor(($width - $newW) / 2), floor(($height - $newH) / 2), 0, 0, $newW, $newH, $this->_width, $this->_height);
      }

      if(PHP_VERSION_ID < 80500) {
        imagedestroy($this->im);
      }
      
      $this->im = null;
      $this->im = $newIm;
    }
    
    $this->_width = $width;
    $this->_height = $height;

    return $this;
  }


  public function imageColor($im) {
    if(osc_best_fit_image() || osc_force_aspect_image()) {
      return imagecolorallocatealpha($im, 255, 255, 255, 127);
    } else if(osc_canvas_background() == 'black') {
      return imagecolorallocatealpha($im, 0, 0, 0, 127);
    }
    
    return imagecolorallocatealpha($im, 255, 255, 255, 127);
  }


  public function centeredThumbnail($image, $new_w, $new_h, $focus = 'center') {
    $w = $image->getImageWidth();
    $h = $image->getImageHeight();

    $r = $w/$h;
    $new_r = $new_w/$new_h;

    if($r > $new_r) {
      $resize_w = $w * $new_h / $h;
      $resize_h = $new_h;
    } else {
      $resize_w = $new_w;
      $resize_h = $h * $new_w / $w;
    }
    
    // Convert to integer values
    $resize_w = intval($resize_w);
    $resize_h = intval($resize_h);
    $new_w = intval($new_w);
    $new_h = intval($new_h);
   

    $image->resizeImage($resize_w, $resize_h, Imagick::FILTER_LANCZOS, 0.9);

    switch ($focus) {
      case 'northwest':
        $image->cropImage($new_w, $new_h, 0, 0);
        break;

      case 'center':
        $image->cropImage($new_w, $new_h, intval(($resize_w - $new_w) / 2), intval(($resize_h - $new_h) / 2));
        break;

      case 'northeast':
        $image->cropImage($new_w, $new_h, $resize_w - $new_w, 0);
        break;

      case 'southwest':
        $image->cropImage($new_w, $new_h, 0, $resize_h - $new_h);
        break;

      case 'southeast':
        $image->cropImage($new_w, $new_h, $resize_w - $new_w, $resize_h - $new_h);
        break;
    }
  }


  /**
   * @param    $imagePath
   * @param null $ext
   *
   * @throws \Exception
   */
  public function saveToFile($imagePath, $ext = null) {
    if(file_exists($imagePath) && !is_writable($imagePath)) {
      throw new RuntimeException("$imagePath is not writable!");
    }

    if($ext == null) {
      $ext = $this->ext;
    }

    if($ext !== 'png' && $ext !== 'gif') {
      $ext = 'jpeg';
    }

    if($this->_use_imagick) {
      if($ext === 'jpeg' && ($this->ext !== 'jpeg' && $this->ext !== 'jpg')) {
        $bg = new Imagick();
        $bg->newImage($this->_width, $this->_height, osc_canvas_background());
        $this->im->thumbnailImage($this->_width, $this->_height, true);
        $bg->compositeImage($this->im, imagick::COMPOSITE_OVER, 0, 0);
        $this->im = $bg;
        $this->ext = 'jpeg';
      }
      
      $this->im->setImageDepth(8);
      $this->im->setImageFilename($imagePath);
      $this->im->setImageFormat($ext);
      $this->im->writeImage($imagePath);
      
    } else {
      switch ($ext) {
        case 'gif':
        case 'png':
          imagepng($this->im, $imagePath, 0);
          break;
          
        default:
          if(($ext === 'jpeg' && ($this->ext !== 'jpeg' && $this->ext !== 'jpg')) || $this->_watermarked) {
            $this->ext = 'jpeg';
          }
          
          imagejpeg($this->im, $imagePath);
          break;
      }
    }
  }

  public function resetOrientation() {
    if($this->_use_imagick) {
      $this->im->setImageOrientation(1);
    } else {
      $this->_exif['Orientation'] = 1;
    }
  }

  /**
   * @return $this
   */
  public function autoRotate() {
    if($this->_use_imagick) {
      switch ($this->im->getImageOrientation()) {
        case imagick::ORIENTATION_TOPRIGHT:
          $this->im->flopImage();
          break;

        case imagick::ORIENTATION_BOTTOMRIGHT:
          $this->im->rotateimage(new ImagickPixel('none'), 180); // rotate 180 degrees
          break;

        case imagick::ORIENTATION_BOTTOMLEFT:
          $this->im->flopImage();
          $this->im->rotateImage(new ImagickPixel('none'), 180);
          break;

        case imagick::ORIENTATION_LEFTTOP:
          $this->im->flopImage();
          $this->im->rotateImage(new ImagickPixel('none'), - 90);
          break;

        case imagick::ORIENTATION_RIGHTTOP:
        $this->im->rotateimage(new ImagickPixel('none'), 90); // rotate 90 degrees CW
        break;

        case imagick::ORIENTATION_RIGHTBOTTOM:
          $this->im->flopImage();
          $this->im->rotateImage(new ImagickPixel('none'), 90);
          break;

        case imagick::ORIENTATION_LEFTBOTTOM:
          $this->im->rotateimage(new ImagickPixel('none'), - 90); // rotate 90 degrees CCW
          break;
          
        default:
          // DO NOTHING, THE IMAGE IS OK OR WE DON'T KNOW IF IT'S ROTATED
          break;
      }
      
    } else {
      if(isset($this->_exif['Orientation'])) {
        switch ($this->_exif['Orientation']) {
          case 1:
          default:
            // DO NOTHING, THE IMAGE IS OK OR WE DON'T KNOW IF IT'S ROTATED
            break;
            
          case 2:
            imageflip($this->im, IMG_FLIP_HORIZONTAL);
            break;
            
          case 3:
            $this->im = imagerotate($this->im, 180, 0);
            break;
            
          case 4:
            $this->im = imagerotate($this->im, 180, 0);
            imageflip($this->im, IMG_FLIP_HORIZONTAL);
            break;
            
          case 5:
            $this->im = imagerotate($this->im, 270, 0);
            imageflip($this->im, IMG_FLIP_HORIZONTAL);
            $aux = $this->_height;
            $this->_height = $this->_width;
            $this->_width = $aux;
            break;
            
          case 6:
            $this->im = imagerotate($this->im, - 90, 0);
            $aux = $this->_height;
            $this->_height = $this->_width;
            $this->_width = $aux;
            break;
            
          case 7:
            $this->im = imagerotate($this->im, 90, 0);
            imageflip($this->im, IMG_FLIP_HORIZONTAL);
            $aux = $this->_height;
            $this->_height = $this->_width;
            $this->_width = $aux;
            break;
            
          case 8:
            $this->im = imagerotate($this->im, 90, 0);
            $aux = $this->_height;
            $this->_height = $this->_width;
            $this->_width = $aux;
            break;
          }
          
        $this->_exif['Orientation'] = 1;
      }
    }

    return $this;
  }


  public function show() {
    header('Content-Disposition: Attachment;filename=image.' . $this->ext);
    header('Content-type: ' . $this->mime);
    
    if($this->_use_imagick) {
    } else {
      switch($this->ext) {
        case 'gif':
        case 'png':
          imagepng($this->im);
          break;
          
        default:
          imagejpeg($this->im);
          break;
      }
    }
  }


  /**
   * @param    $text
   * @param string $color
   * @param string $fontsize
   *
   * @return $this
   */
  public function doWatermarkText($text, $color = 'ff0000', $fontsize = '30') {
    $this->_watermarked = true;
    $this->_font = osc_apply_filter('watermark_font_path', LIB_PATH . 'osclass/assets/fonts/Arial.ttf');
    $text = osc_apply_filter('watermark_text_value', $text);
    $fontsize = osc_apply_filter('watermark_font_size', $fontsize);
    
    if($this->_use_imagick) {
      $draw = new ImagickDraw();
      $draw->setFillColor('#' . $color);
      $draw->setFont($this->_font);
      $draw->setFontSize($fontsize);
      $metrics = $this->im->queryFontMetrics($draw, $text);
      
      switch (osc_watermark_place()) {
        case 'tl':
        $offset['x'] = 1;
        $offset['y'] = $metrics['ascender'] + 1;
        break;
        
        case 'tr':
        $offset['x'] = $this->_width - $metrics['textWidth'] - 1;
        $offset['y'] = $metrics['ascender'] + 1;
        break;
        
        case 'bl':
        $offset['x'] = 1;
        $offset['y'] = $this->_height - 1;
        break;
        
        case 'br':
        $offset['x'] = $this->_width - $metrics['textWidth'] - 1;
        $offset['y'] = $this->_height - 1;
        break;
        
        default:
        $offset['x'] = ($this->_width / 2) - ($metrics['textWidth'] / 2);
        $offset['y'] = ($this->_height / 2) - ($metrics['ascender'] / 2);
        break;
      }
      
      $this->im->annotateImage($draw, $offset['x'], $offset['y'], 0, $text);
      
    } else {
      imagealphablending($this->im, true);
      imagesavealpha($this->im, true);
      
      if($this->ext !== 'jpg') {
        $white = $this->imageColor($this->im);
        imagefill($this->im, 0, 0, $white);
      }
      
      $color = $this->_imageColorAllocateHex($color);
      $offset = $this->_calculateOffset($text);
      imagettftext($this->im, 20, 0, $offset['x'], $offset['y'], $color, $this->_font, html_entity_decode($text, null, 'UTF-8'));
    }

    return $this;
  }


  /**
   * @param $hexstr
   *
   * @return int
   */
  private function _imageColorAllocateHex($hexstr) {
    $red = hexdec(substr($hexstr, 0, 2));
    $green = hexdec(substr($hexstr, 2, 2));
    $blue = hexdec(substr($hexstr, 4, 2));

    return imagecolorallocate($this->im, $red, $green, $blue);
  }


  /**
   * @param $text
   *
   * @return array
   */
  private function _calculateOffset($text) {
    $offset = array ('x' => 0, 'y' => 0);
    $bbox = $this->_calculateBBox($text);

    switch (osc_watermark_place()) {
    case 'tl':
      $offset['x'] = $bbox['height'];
      $offset['y'] = $bbox['height'] * 1.5;
      break;
      
    case 'tr':
      $offset['x'] = $this->_width - ($bbox['width'] + $bbox['height']);
      $offset['y'] = $bbox['height'] * 1.5;
      break;
      
    case 'bl':
      $offset['x'] = $bbox['height'];
      $offset['y'] = $this->_height - $bbox['height'];
      break;
      
    case 'br':
      $offset['x'] = $this->_width - ($bbox['width'] + $bbox['height']);
      $offset['y'] = $this->_height - $bbox['height'];
      break;
      
    default:
      $offset['x'] = ($this->_width / 2) - ($bbox['top_right']['x'] / 2);
      $offset['y'] = ($this->_height / 2) - ($bbox['top_right']['y'] / 2);
      break;
    }

    return $offset;
  }


  /**
   * @param $text
   *
   * @return array
   */
  private function _calculateBBox($text) {
    $bbox = imagettfbbox(
      20,
      0,
      $this->_font,
      $text
    );

    $bbox = array (
      'bottom_left' => array(
        'x' => $bbox[0],
        'y' => $bbox[1]
      ),
      'bottom_right' => array(
        'x' => $bbox[2],
        'y' => $bbox[3]
      ),
      'top_right' => array(
        'x' => $bbox[4],
        'y' => $bbox[5]
      ),
      'top_left' => array(
        'x' => $bbox[6],
        'y' => $bbox[7]
      )
    );

    $bbox['width'] = $bbox['top_right']['x'] - $bbox['top_left']['x'];
    $bbox['height'] = $bbox['bottom_left']['y'] - $bbox['top_left']['y'];

    return $bbox;
  }


  /**
   * @return $this
   */
  public function doWatermarkImage() {
    $this->_watermarked = true;
    $path_watermark = osc_uploads_path() . 'watermark.png';
    
    if($this->_use_imagick) {
      $wm = new Imagick($path_watermark);
      $wgeo = $wm->getImageGeometry();

      switch (osc_watermark_place()) {
        case 'tl':
        $dest_x = 0;
        $dest_y = 0;
        break;
        
        case 'tr':
        $dest_x = $this->_width - $wgeo['width'];
        $dest_y = 0;
        break;
        
        case 'bl':
        $dest_x = 0;
        $dest_y = $this->_height - $wgeo['height'];
        break;
        
        case 'br':
        $dest_x = $this->_width - $wgeo['width'];
        $dest_y = $this->_height - $wgeo['height'];
        break;
        
        default:
        $dest_x = ($this->_width - $wgeo['width']) / 2;
        $dest_y = ($this->_height - $wgeo['height']) / 2;
        break;
      }

      $this->im->compositeImage($wm, imagick::COMPOSITE_OVER, $dest_x, $dest_y);
      $wm->destroy();
    } else {
      $watermark = imagecreatefrompng($path_watermark);

      $watermark_width = imagesx($watermark);
      $watermark_height = imagesy($watermark);

      switch (osc_watermark_place()) {
        case 'tl':
        $dest_x = 0;
        $dest_y = 0;
        break;
        
        case 'tr':
        $dest_x = $this->_width - $watermark_width;
        $dest_y = 0;
        break;
        
        case 'bl':
        $dest_x = 0;
        $dest_y = $this->_height - $watermark_height;
        break;
        
        case 'br':
        $dest_x = $this->_width - $watermark_width;
        $dest_y = $this->_height - $watermark_height;
        break;
        
        default:
        $dest_x = ($this->_width - $watermark_width) / 2;
        $dest_y = ($this->_height - $watermark_height) / 2;
        break;
      }

      $this->_imagecopymerge_alpha($this->im, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height, 100);
      
      if(PHP_VERSION_ID < 80500) {
        imagedestroy($watermark);
      }
      
      unset($watermark);
    }

    return $this;
  }


  /**
   * @param    $dst_im
   * @param    $src_im
   * @param    $dst_x
   * @param    $dst_y
   * @param    $src_x
   * @param    $src_y
   * @param    $src_w
   * @param    $src_h
   * @param    $pct
   * @param null $trans
   *
   * @return bool
   */
  private function _imagecopymerge_alpha(&$dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct, $trans = null) {
    imagealphablending($dst_im, false);
    imagesavealpha($dst_im, true);

    $dst_w = imagesx($dst_im);
    $dst_h = imagesy($dst_im);

    $src_x = max($src_x, 0);
    $src_y = max($src_y, 0);
    $dst_x = max($dst_x, 0);
    $dst_y = max($dst_y, 0);
    
    if($dst_x + $src_w > $dst_w) {
      $src_w = $dst_w - $dst_x;
    }
    
    if($dst_y + $src_h > $dst_h) {
      $src_h = $dst_h - $dst_y;
    }

    for ($x_offset = 0; $x_offset < $src_w; $x_offset ++) {
      for ($y_offset = 0; $y_offset < $src_h; $y_offset ++) {
        $srccolor = imagecolorsforindex($src_im, imagecolorat($src_im, $src_x + $x_offset, $src_y + $y_offset));
        $dstcolor = imagecolorsforindex($dst_im, imagecolorat($dst_im, $dst_x + $x_offset, $dst_y + $y_offset));

        if(null === $trans || ($srccolor !== $trans)) {
          $src_a = $srccolor['alpha'] * $pct / 100;
          // blend
          $src_a = 127 - $src_a;
          $dst_a = 127 - $dstcolor['alpha'];
          
          // Update os812 to convert values into integer
          $dst_r = intval(($srccolor['red'] * $src_a + $dstcolor['red'] * $dst_a * (127 - $src_a) / 127) / 127);
          $dst_g = intval(($srccolor['green'] * $src_a + $dstcolor['green'] * $dst_a * (127 - $src_a) / 127) / 127);
          $dst_b = intval(($srccolor['blue'] * $src_a + $dstcolor['blue'] * $dst_a * (127 - $src_a) / 127) / 127);
          $dst_a = intval(127 - ($src_a + $dst_a * (127 - $src_a) / 127));
          $color = imagecolorallocatealpha($dst_im, $dst_r, $dst_g, $dst_b, $dst_a);

          if(!imagesetpixel($dst_im, $dst_x + $x_offset, $dst_y + $y_offset, $color)) {
            return false;
          }
          
          imagecolordeallocate($dst_im, $color);
        }
      }
    }

    return true;
  }
}


if(!function_exists('imageflip')) {
  /**
   * @param    $image
   * @param int  $x
   * @param int  $y
   * @param null $width
   * @param null $height
   *
   * @return mixed
   */
  function imageflip(&$image, $x = 0, $y = 0, $width = null, $height = null) {
    if($width < 1) {
      $width = imagesx($image);
    }
    
    if($height < 1) {
      $height = imagesy($image);
    }
    
    // Truecolor provides better results, if possible.
    if(function_exists('imageistruecolor') && imageistruecolor($image)) {
      $tmp = imagecreatetruecolor(1, $height);
    } else {
      $tmp = imagecreate(1, $height);
    }
    
    $x2 = $x + $width - 1;
    
    for($i = (int) floor(($width - 1) / 2); $i >= 0; $i --) {
      imagecopy($tmp, $image, 0, 0, $x2 - $i, $y, 1, $height);              // Backup right stripe.
      imagecopy($image, $image, $x2 - $i, $y, $x + $i, $y, 1, $height);     // Copy left stripe to the right.
      imagecopy($image, $tmp, $x + $i, $y, 0, 0, 1, $height);               // Copy backuped right stripe to the left.
    }
    
    if(PHP_VERSION_ID < 80500) {
      imagedestroy($tmp);
    }
    
    unset($tmp);

    return $image;
  }

  define('IMG_FLIP_HORIZONTAL', 0);
}