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


$reports = __get("reports");
$type  = Params::getParam('type_stat');

switch($type){
  case 'week':
    $type_stat = __('Last 10 weeks');
    break;
  case 'month':
    $type_stat = __('Last 10 months');
    break;
  default:
    $type_stat = __('Last 10 days');
}


function render_offset(){
  return 'row-offset';
}

osc_add_filter('render-wrapper','render_offset');


function addHelp() {
  echo '<p>' . __('See how many listings from your site have been reported as spam, expired, duplicate, etc.') . '</p>';
}

osc_add_hook('help_box','addHelp');


function customPageHeader(){ 
  ?>
  <h1><?php _e('Statistics'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return sprintf(__('Report Statistics - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');


function customHead() {
  $reports = __get("reports");
  ?>
  <script type="text/javascript" src="https://www.google.com/jsapi"></script>
  <?php if(count($reports)>0) { ?>
    <script type="text/javascript">
    // Load the Visualization API and the piechart package.
    google.load('visualization', '1', {'packages':['corechart']});

    // Set a callback to run when the Google Visualization API is loaded.
    google.setOnLoadCallback(drawChart);

    // Callback that creates and populates a data table,
    // instantiates the pie chart, passes in the data and
    // draws it.
    function drawChart() {
      var data = new google.visualization.DataTable();
      data.addColumn('string', '<?php echo osc_esc_js(__('Date')); ?>');
      data.addColumn('number', '<?php echo osc_esc_js(__('Spam')); ?>');
      data.addColumn('number', '<?php echo osc_esc_js(__('Duplicated')); ?>');
      data.addColumn('number', '<?php echo osc_esc_js(__('Bad category')); ?>');
      data.addColumn('number', '<?php echo osc_esc_js(__('Offensive')); ?>');
      data.addColumn('number', '<?php echo osc_esc_js(__('Expired')); ?>');
      <?php $k = 0;
      echo "data.addRows(".count($reports).");";
      foreach($reports as $date => $data) {
        echo "data.setValue(" . $k . ', 0, "' . $date . '");';
        echo "data.setValue(" . $k . ", 1, " . $data['spam'] . ");";
        echo "data.setValue(" . $k . ", 2, " . $data['repeated'] . ");";
        echo "data.setValue(" . $k . ", 3, " . $data['bad_classified'] . ");";
        echo "data.setValue(" . $k . ", 4, " . $data['offensive'] . ");";
        echo "data.setValue(" . $k . ", 5, " . $data['expired'] . ");";
        $k++;
      }
      ?>

      // Instantiate and draw our chart, passing in some options.
      var chart = new google.visualization.ColumnChart(document.getElementById('placeholder'));
      var options = 
        {
        colors:['<?php echo omg_current_color_scheme_chart(); ?>','<?php echo omg_current_color_scheme_chart(); ?>'],
          areaOpacity: 0.15,
          lineWidth:2,
          hAxis: {
          viewWindow:'explicit',
          slantedText: false,
          color: 'none',
          baselineColor: 'none',
          textStyle: {
            color: '#8C8C8C',
            fontName: 'Calibri',
            fontSize: 12
          }
          },
          vAxis: {
          color: 'none',
          baselineColor: 'none',
          textStyle: {
            color: '#8C8C8C',
            fontName: 'Calibri',
            fontSize: 12
          },      
          gridlines: {
            color: '#ddd',
            count: 4
          }
          },
          legend: 'none',
          pointSize: 10,
          animation: {
          duration: 500,
          easing: 'out',
          startup: true
          },
          chartArea:{
          left:10,
          top:10,
          width:"95%",
          height:"88%"
          }
        };
      chart.draw(data, options);
    }
    </script>
  <?php } ?>
  <?php 
}

osc_add_hook('admin_header', 'customHead', 10);

osc_current_admin_theme_path( 'parts/header.php' ); 
?>

<div class="grid-system" id="stats-page">
  <div class="grid-row grid-50">
    <div class="row-wrapper">
      <h2 class="render-title"><?php _e('Report Statistics'); ?></h2>
    </div>
  </div>
  <div class="grid-row grid-50">
    <div class="row-wrapper">
      <a id="monthly" class="btn float-right <?php if($type=='month') echo 'btn-green';?>" href="<?php echo osc_admin_base_url(true); ?>?page=stats&amp;action=reports&amp;type_stat=month"><?php _e('Last 10 months'); ?></a>
      <a id="weekly"  class="btn float-right <?php if($type=='week') echo 'btn-green';?>" href="<?php echo osc_admin_base_url(true); ?>?page=stats&amp;action=reports&amp;type_stat=week"><?php _e('Last 10 weeks'); ?></a>
      <a id="daily"   class="btn float-right <?php if($type==''||$type=='day') echo 'btn-green';?>" href="<?php echo osc_admin_base_url(true); ?>?page=stats&amp;action=reports&amp;type_stat=day"><?php _e('Last 10 days'); ?></a>
    </div>
  </div>
  <div class="grid-row grid-100 clear">
    <div class="row-wrapper">
      <div class="widget-box">
        <div class="widget-box-title">
          <h3><?php _e('Total number of reports'); ?></h3>
        </div>
        <div class="widget-box-content">
          <b class="stats-title"></b>
          <div id="placeholder" class="graph-placeholder">
            <?php if( count($reports) == 0 ) {
              _e("There are no statistics yet");
            } ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="clear"></div>
</div>
<?php osc_current_admin_theme_path( 'parts/footer.php' ); ?>