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


$comments    = __get("comments");
$max       = __get("max");
$latest_comments = __get("latest_comments");
$type      = Params::getParam('type_stat');

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
  echo '<p>' . __('See how many comments the listings published on your site have received.') . '</p>';
}

osc_add_hook('help_box','addHelp');


osc_add_hook('admin_page_header','customPageHeader');
function customPageHeader(){ 
  ?>
  <h1><?php _e('Statistics'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
  <?php
}

function customPageTitle($string) {
  return sprintf(__('Comment Statistics - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');


function customHead() {
  $comments    = __get("comments");
  $max       = __get("max");
  $latest_comments = __get("latest_comments");
  ?>
  <script type="text/javascript" src="https://www.google.com/jsapi"></script>
  
  <?php if( count($comments) > 0 ) { ?>
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
        data.addColumn('number', '<?php echo osc_esc_js(__('Comments')); ?>');
        <?php $k = 0;
        echo "data.addRows(" . count($comments) . ");";
        foreach($comments as $date => $num) {
          echo "data.setValue(" . $k . ", 0, \"" . $date . "\");";
          echo "data.setValue(" . $k . ", 1, " . $num . ");";
          $k++;
        }
        ?>

        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.LineChart(document.getElementById('placeholder'));
        var options = 
          {
          colors:['<?php echo omg_current_color_scheme_chart(); ?>','<?php echo omg_current_color_scheme_chart(); ?>'],
            areaOpacity: 0.15,
            lineWidth:2,
            hAxis: {
            viewWindow:'explicit',
            showTextEvery: 2,
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
            height:"84%"
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
      <h2 class="render-title"><?php _e('Comment Statistics'); ?></h2>
    </div>
  </div>
  <div class="grid-row grid-50">
    <div class="row-wrapper">
      <a id="monthly" class="btn float-right <?php if($type=='month') echo 'btn-green';?>" href="<?php echo osc_admin_base_url(true); ?>?page=stats&amp;action=comments&amp;type_stat=month"><?php _e('Last 10 months'); ?></a>
      <a id="weekly"  class="btn float-right <?php if($type=='week') echo 'btn-green';?>" href="<?php echo osc_admin_base_url(true); ?>?page=stats&amp;action=comments&amp;type_stat=week"><?php _e('Last 10 weeks'); ?></a>
      <a id="daily"   class="btn float-right <?php if($type==''||$type=='day') echo 'btn-green';?>" href="<?php echo osc_admin_base_url(true); ?>?page=stats&amp;action=comments&amp;type_stat=day"><?php _e('Last 10 days'); ?></a>
    </div>
  </div>
  <div class="grid-row grid-50 clear">
    <div class="row-wrapper">
      <div class="widget-box">
        <div class="widget-box-title">
          <h3><?php _e('Comments'); ?></h3>
        </div>
        <div class="widget-box-content">
          <b class="stats-title"></b>
          <div id="placeholder" class="graph-placeholder">
            <?php if( count($comments) == 0 ) {
              _e("There are no statistics yet");
            } ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="grid-row grid-50">
    <div class="row-wrapper">
      <div class="widget-box">
        <div class="widget-box-title"><h3><?php _e('Latest comments on the web'); ?></h3></div>
        <div class="widget-box-content">
          <?php if( count($latest_comments) > 0 ) { ?>
          <table class="table" cellpadding="0" cellspacing="0">
            <thead>
            <tr>
              <th>ID</th>
              <th class="col-title"><?php _e('Title'); ?></th>
              <th><?php _e('Author'); ?></th>
              <th><?php _e('Comment'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach($latest_comments as $c) { ?>
            <tr>
              <td><a href="<?php echo osc_admin_base_url(true); ?>?page=comments&amp;action=comment_edit&amp;id=<?php echo $c['pk_i_id']; ?>"><?php echo $c['pk_i_id']; ?></a></td>
              <td><a href="<?php echo osc_admin_base_url(true); ?>?page=comments&amp;action=comment_edit&amp;id=<?php echo $c['pk_i_id']; ?>"><?php echo $c['s_title']; ?></a></td>
              <td><a href="<?php echo osc_admin_base_url(true); ?>?page=comments&amp;action=comment_edit&amp;id=<?php echo $c['pk_i_id']; ?>"><?php echo $c['s_author_name'] . " - " . $c['s_author_email']; ?></a></td>
              <td><a href="<?php echo osc_admin_base_url(true); ?>?page=comments&amp;action=comment_edit&amp;id=<?php echo $c['pk_i_id']; ?>"><?php echo $c['s_body']; ?></a></td>
            </tr>
            <?php }; ?>
            </tbody>
          </table>
          <?php } else { ?>
            <p><?php _e("There're no statistics yet"); ?></p>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>
  <div class="clear"></div>
</div>
<?php osc_current_admin_theme_path( 'parts/footer.php' ); ?>