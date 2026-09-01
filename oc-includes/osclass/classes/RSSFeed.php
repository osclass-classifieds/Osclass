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
 * This class takes items descriptions and generates a RSS feed from that information.
 * @author Osclass
 */
class RSSFeed {
  private $title;
  private $link;
  private $description;
  private $items;

  public function __construct() {
    $this->items = array();
  }

  /**
   * @param $title
   */
  public function setTitle($title) {
    $this->title = $title;
  }

  /**
   * @param $link
   */
  public function setLink($link) {
    $this->link = $link;
  }

  /**
   * @param $description
   */
  public function setDescription($description) {
    $this->description = $description;
  }

  /**
   * @param $item
   */
  public function addItem($item) {
    $this->items[] = osc_apply_filter('rss_add_item', $item);
  }

  public function dumpXML() {
    $items = osc_apply_filter('rss_items', $this->items);
    
    echo '<?xml version="1.0" encoding="UTF-8"?>', PHP_EOL;
    echo '<rss version="2.0">', PHP_EOL;
    echo '<channel>', PHP_EOL;
    echo '<title>', $this->title, '</title>', PHP_EOL;
    echo '<link>', $this->link, '</link>', PHP_EOL;
    echo '<description>', $this->description, '</description>', PHP_EOL;
    
    osc_run_hook('rss_before');
    
    foreach ($items as $item) {
      echo '<item>', PHP_EOL;
      echo '<title><![CDATA[', $item['title'], ']]></title>', PHP_EOL;
      echo '<link>', $item['link'], '</link>', PHP_EOL;
      echo '<guid>', $item['link'], '</guid>', PHP_EOL;
      echo '<pubDate>', date('r',strtotime($item['dt_pub_date'])) , '</pubDate>', PHP_EOL;

      echo '<description><![CDATA[';
      
      if(isset($item['images'])) {
        if(is_array($item['images']) && count($item['images']) > 0) {
          foreach($item['images'] as $img) {
            echo '<a href="'.$img['link'].'" title="'.$img['title'].'" rel="nofollow">';
            echo '<img style="float:left;border:0px;" src="'.$img['thumbnail_url'].'" alt="'.$img['title'].'"/>';
            echo '</a>';
          }
        }
      } else if(isset($item['image'])) {
        echo '<a href="'.$item['image']['link'].'" title="'.$item['image']['title'].'" rel="nofollow">';
        echo '<img style="float:left;border:0px;" src="'.$item['image']['url'].'" alt="'.$item['image']['title'].'"/>';
        echo '</a>';
      }
      
      echo $item['description'], ']]>';
      echo '</description>', PHP_EOL;
      
      if(isset($item['image'])) {
        $image_url = str_replace('_thumbnail', '', $item['image']['url']); // remove '_thumbnail' from image URL
        echo '<image>';
        echo '<url>'.$image_url.'</url>', PHP_EOL;
        echo '</image>', PHP_EOL;
      }

      echo '<country><![CDATA[', $item['country'], ']]></country>', PHP_EOL;
      echo '<region><![CDATA[', $item['region'], ']]></region>', PHP_EOL;
      echo '<city><![CDATA[', $item['city'], ']]></city>', PHP_EOL;
      echo '<cityArea><![CDATA[', $item['city_area'], ']]></cityArea>', PHP_EOL;
      echo '<category><![CDATA[', $item['category'], ']]></category>', PHP_EOL;
      echo '<price><![CDATA[', $item['price_formatted'], ']]></price>', PHP_EOL;
      echo '<priceRaw><![CDATA[', $item['price'], ']]></priceRaw>', PHP_EOL;
      echo '<currency><![CDATA[', $item['currency'], ']]></currency>', PHP_EOL;

      // Uncomment if you want to add to RSS
      // echo '<contactName><![CDATA[', $item['contact_name'], ']]></contactName>', PHP_EOL;
      // echo '<contactEmail><![CDATA[', $item['contact_email'], ']]></contactEmail>', PHP_EOL;
      
      if(osc_enable_comment_rating()) {
        echo '<rating><![CDATA[', $item['rating'], ']]></rating>', PHP_EOL;
      }
      
      if(isset($item['images'])) {
        if(is_array($item['images']) && count($item['images']) > 0) {
          echo '<imagesThumbnail>';
          
          foreach($item['images'] as $img) {
            echo '<url>'.$img['thumbnail_url'].'</url>', PHP_EOL;
          }
          
          echo '</imagesThumbnail>', PHP_EOL;
        }
      } 

      // preview images
      if(isset($item['images'])) {
        if(is_array($item['images']) && count($item['images']) > 0) {
          echo '<imagesPreview>';
          
          foreach($item['images'] as $img) {
            echo '<url>'.$img['preview_url'].'</url>', PHP_EOL;
          }
          
          echo '</imagesPreview>', PHP_EOL;
        }
      } 
      
      // normal images
      if(isset($item['images'])) {
        if(is_array($item['images']) && count($item['images']) > 0) {
          echo '<imagesNormal>';
          
          foreach($item['images'] as $img) {
            echo '<url>'.$img['normal_url'].'</url>', PHP_EOL;
          }
          
          echo '</imagesNormal>', PHP_EOL;
        }
      } 

      // original
      if(osc_keep_original_image()) {
        if(isset($item['images'])) {
          echo '<imagesOriginal>';
          
          if(is_array($item['images']) && count($item['images']) > 0) {
            foreach($item['images'] as $img) {
              echo '<url>'.$img['original_url'].'</url>', PHP_EOL;
            }
          }
          
          echo '</imagesOriginal>', PHP_EOL;
          
        } else if(isset($item['image'])) {
          $image_url = str_replace('_thumbnail', '_original', $item['image']['url']); // remove '_thumbnail' and replace with '_original' from image URL
          echo '<imagesOriginal>';
          echo '<url>'.$image_url.'</url>', PHP_EOL;
          echo '</imagesOriginal>', PHP_EOL;
        }
      }
      
      osc_run_hook('rss_item', $item['id']);
      
      echo '</item>', PHP_EOL;
    }
    
    osc_run_hook('rss_after');
    
    echo '</channel>', PHP_EOL;
    echo '</rss>', PHP_EOL;
  }
}