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


$customPageHeader = static function () { 
  ?>
  <h1><?php echo sprintf(__('Osclass %s'), OSCLASS_VERSION); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
  <?php
};

osc_add_hook('admin_page_header', $customPageHeader);


$customPageTitle = static function ($string) {
  return sprintf(__('Osclass %s - %s'), OSCLASS_VERSION, $string);
};

osc_add_filter('admin_title', $customPageTitle);


unset($customPageTitle, $customPageHeader);

osc_current_admin_theme_path('parts/header.php');
?>

<div class="row-wrapper">
  <div class="widget-box">
    <div class="widget-box-title">
      <h3><?php echo sprintf(__('Osclass changelog up to v%s'), OSCLASS_VERSION); ?></h3>
    </div>
    <div class="widget-box-content">
      <ul class="version-list">
        <?php
          $content = file_get_contents(ABS_PATH . 'CHANGELOG.txt');

          $data = explode(PHP_EOL, $content);
          $output = '';
          $li_ended = true;
          
          if(count($data) > 0) {
            foreach($data as $d) {
              // if(substr(trim($d), 0, 7) === "Osclass") {
              if(substr($d, 0, 7) === "Osclass") {
                $output .= ($li_ended === false ? '</li>' . PHP_EOL : '');
                $output .= '<li class="version-head"><span>' . trim(htmlentities($d)) . '</span></li>' . PHP_EOL;
              } else if(substr(trim($d), 0, 2) === "- ") {
                $output .= ($li_ended === false ? '</li>' . PHP_EOL : '');
                $output .= '<li>' . trim(substr(htmlentities($d), 2));
                $li_ended = false;
              } else if(substr(trim($d), 0, 6) === "------") {
                // do nothing, skip
              } else if (trim($d) != '') {
                $output .= ($li_ended === false ? '<br/>' : '');
                $output .= trim(htmlentities($d));
              }              
            }
            
            $output .= ($li_ended === false ? '</li>' : '');
          }
  
          echo $output;
        ?>
      </ul>
    </div>
  </div>
</div>

<?php osc_current_admin_theme_path('parts/footer.php'); ?>