<?php
if(!defined('ABS_PATH')) exit('ABS_PATH is not loaded. Direct access is not allowed.');

osc_enqueue_script('tabber');
osc_enqueue_script('jquery-nested');

$categories = __get('categories');

function addHelp() {
  echo '<p>' . __('Add, edit or delete the categories or subcategories in which users can post listings. Reorder sections by dragging and dropping, or nest a subcategory in an expanded category. <strong>Be careful</strong>: If you delete a category, all listings associated will also be deleted!') . '</p>';
}
osc_add_hook('help_box','addHelp');

function customPageHeader() {
  ?>
  <h1><?php _e('Categories'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
    <a href="<?php echo osc_admin_base_url(true); ?>?page=categories&amp;action=add_post_default&amp;<?php echo osc_csrf_token_url(); ?>" class="btn btn-green ico ico-add-white float-right"><?php _e('Add category'); ?></a>
    <a href="<?php echo osc_admin_base_url(true); ?>?page=categories" class="btn btn-white float-right"><?php _e('Manage categories'); ?></a>
  </h1>
  <?php
}
osc_add_hook('admin_page_header','customPageHeader');

function customPageTitle($string) {
  return sprintf(__('Quick management - %s'), $string);
}
osc_add_filter('admin_title', 'customPageTitle');

function customHead() {
  $max_levels = intval(osc_num_category_levels() > 0 ? osc_num_category_levels() : 4);
  ?>
  <style>
    .tabber{display:none;}
    .placeholder {background-color: #cfcfcf;}
    .footest .category_div {opacity: 0.8;}
    .list-categories li {opacity: 1 !important;}
    .category_div {background: #ffffff;}
    .alert-custom {background-color: #FDF5D9;border-bottom: 1px solid #EEDC94;color: #404040;}
    .ui-widget.ui-widget-content {border:none;}
    .cat-hover, .cat-hover .category_row{background-color:#fffccc !important;background:#fffccc !important;}
    .content_list_loading {padding:15px;text-align:center;}
    .list-categories > .ui-sortable > <?php for($k=1; $k<$max_levels; $k++) { ?>li > ul > <?php } ?>.category_row {padding-left:49px}
    .list-categories > .ui-sortable > <?php for($k=1; $k<$max_levels; $k++) { ?>li > ul > <?php } ?>.subcategory .toggle {display:none}
    .list-categories <?php for($k=1; $k<$max_levels; $k++) { ?>.subcategory <?php } ?>.ico-childrens {display:none}
  </style>
  <script type="text/javascript">
    var quickEditLabel = '<?php echo osc_esc_js(__('Quick edit')); ?>';
    var cancelLabel = '<?php echo osc_esc_js(__('Cancel')); ?>';

    $(function() {
      $('.category_div').on('mouseenter',function(){
        $(this).addClass('cat-hover');
      }).on('mouseleave',function(){
        $(this).removeClass('cat-hover');
      });
      var list_original = '';

      $('.sortable').nestedSortable({
        disableNesting: 'no-nest',
        forcePlaceholderSize: true,
        handle: '.handle',
        helper: 'clone',
        listType: 'ul',
        items: 'li',
        maxLevels: <?php echo $max_levels; ?>,
        opacity: .6,
        placeholder: 'placeholder',
        revert: 250,
        tabSize: 25,
        tolerance: 'pointer',
        toleranceElement: '> div',
        start: function(event, ui) {
          list_original = $('.sortable').nestedSortable('serialize');
          $(ui.helper).addClass('footest');
          $(ui.helper).prepend('<div style="opacity: 1 !important; padding:5px;" class="alert-custom"><?php echo osc_esc_js(__('Note: You must expand the category in order to make it a subcategory.')); ?></div>');
        },
        stop: function(event, ui) {
          $(".jsMessage").fadeIn("fast");
          $(".jsMessage p").attr('class', '');
          $(".jsMessage p").html('<img height="16" width="16" src="<?php echo osc_current_admin_theme_url('images/loading.gif');?>"> <?php echo osc_esc_js(__('This action could take a while.')); ?>');

          var list = $('.sortable').nestedSortable('serialize');
          var array_list = $('.sortable').nestedSortable('toArray');
          var l = array_list.length;

          for(var k = 0; k < l; k++ ) {
            if(array_list[k].item_id == $(ui.item).find('.category_div').attr('category_id') ) {
              if(array_list[k].parent_id == 'root' ) {
                $(ui.item).closest('.toggle').show();
              }
              break;
            }
          }
          if(!$(ui.item).parent().hasClass('sortable') ) {
            $(ui.item).parent().addClass('subcategory');
          }
          if(list_original != list) {
            var plist = array_list.reduce(function ( total, current, index ) {
              total[index] = {'c' : current.item_id, 'p' : current.parent_id};
              return total;
            }, {});
            $.ajax({
              type: 'POST',
              url: "<?php echo osc_admin_base_url(true) . "?page=ajax&action=categories_order&" . osc_csrf_token_url(); ?>",
              data: {'list' : JSON.stringify(plist)},
              success: function(res){
                var ret = eval( "(" + res + ")");
                var message = "";
                if(ret.error ) {
                  $(".jsMessage p").attr('class', 'error');
                  message += ret.error;
                }
                if(ret.ok ){
                  $(".jsMessage p").attr('class', 'ok');
                  message += ret.ok;
                }
                $(".jsMessage").show();
                $(".jsMessage p").html(message);
              },
              error: function(){
                $(".jsMessage").fadeIn("fast");
                $(".jsMessage p").attr('class', '');
                $(".jsMessage p").html('<?php echo osc_esc_js(__('Ajax error, please try again.')); ?>');
              }
            });
            list_original = list;
          }
        }
      });

      $(".toggle").bind("click", function(e) {
        var list = $(this).parents('li').first().find('ul');
        var lili = $(this).closest('li').find('ul').find('li').find('ul');
        var li   = $(this).closest('li').first();
        if($(this).hasClass('status-collapsed') ) {
          $(li).removeClass('no-nest');
          $(list).show();
          $(lili).hide();
          $(this).removeClass('status-collapsed').addClass('status-expanded');
          $(this).html('-');
        } else {
          $(li).addClass('no-nest');
          $(list).hide();
          $(this).removeClass('status-expanded').addClass('status-collapsed');
          $(this).html('+');
        }
      });

      $("#dialog-delete-category").dialog({ autoOpen: false, modal: true });
      $("#category-delete-submit").click(function() {
        var id  = $("#dialog-delete-category").attr('data-category-id');
        var url  = '<?php echo osc_admin_base_url(true); ?>?page=ajax&action=delete_category&<?php echo osc_csrf_token_url(); ?>&id=' + id;
        $.ajax({
          url: url,
          success: function(res) {
            var ret = eval( "(" + res + ")");
            if(ret.error ) {
              $(".jsMessage p").attr('class', 'error').html(ret.error);
            }
            if(ret.ok ) {
              $(".jsMessage p").attr('class', 'ok').html(ret.ok);
              $('#list_'+id).fadeOut("slow", function(){ $(this).remove(); });
            }
            $(".jsMessage").show();
          }
        });
        $('#dialog-delete-category').dialog('close');
        return false;
      });
    });

    function toggleQuickEdit(btn, class_name, id) {
      var $btn = $(btn);
      if($('.content_list_'+id+' .iframe-category').length > 0) {
        $('.iframe-category').remove();
        $btn.text(quickEditLabel);
        return false;
      }
      show_iframe($btn, class_name, id);
      return false;
    }

    function show_iframe($btn, class_name, id) {
      $('.iframe-category').remove();
      var $container = $('div.' + class_name);
      $container.html('<div class="content_list_loading"><img height="16" width="16" src="<?php echo osc_current_admin_theme_url('images/loading.gif');?>" /> <?php echo osc_esc_js(__('Loading...')); ?></div>').fadeIn('fast');
      var url  = '<?php echo osc_admin_base_url(true); ?>?page=ajax&action=category_edit_iframe&id=' + id;
      $.ajax({
        url: url,
        success: function(res){
          $container.html(res).fadeIn('fast');
          if($btn) { $btn.text(cancelLabel); }
        },
        error: function(){
          $container.html('<p class="error"><?php echo osc_esc_js(__('Ajax error, please try again.')); ?></p>');
          if($btn) { $btn.text(quickEditLabel); }
        }
      });
      return false;
    }

    function delete_category(id) {
      $("#dialog-delete-category").attr('data-category-id', id);
      $("#dialog-delete-category").dialog('open');
      return false;
    }

    function enable_cat(id) {
      var enabled = ($('div[category_id=' + id + ']').hasClass('disabled') ? 1 : 0);
      $(".jsMessage").fadeIn("fast");
      $(".jsMessage p").attr('class', '').html('<img height="16" width="16" src="<?php echo osc_current_admin_theme_url('images/loading.gif');?>"> <?php echo osc_esc_js(__('This action could take a while.')); ?>');
      var url  = '<?php echo osc_admin_base_url(true); ?>?page=ajax&action=enable_category&<?php echo osc_csrf_token_url(); ?>&id=' + id + '&enabled=' + enabled;
      $.ajax({
        url: url,
        success: function(res) {
          var ret = eval( "(" + res + ")");
          if(ret.error) {
            $(".jsMessage p").attr('class', 'error').html(ret.error);
          } else if(ret.ok) {
            $(".jsMessage p").attr('class', 'ok').html(ret.ok);
            location.reload();
          }
          $(".jsMessage").show();
        }
      });
    }
  </script>
  <?php
}
osc_add_hook('admin_header','customHead', 10);

function drawCategory($category){
  if(count($category['categories']) > 0 ) { $has_subcategories = true; } else { $has_subcategories = false; }
  $catId = (int)$category['pk_i_id'];
?>
<li id="list_<?php echo $catId; ?>" class="category_li <?php echo ( $category['b_enabled'] == 1 ? 'enabled' : 'disabled' ); ?> " >
  <div class="category_div <?php echo ( $category['b_enabled'] == 1 ? 'enabled' : 'disabled' ); ?>" category_id="<?php echo $catId; ?>" >
    <div class="category_row">
      <div class="handle ico ico-32 ico-droppable"></div>
      <div class="ico-childrens">
        <?php if($has_subcategories ) {
          echo '<span class="toggle status-collapsed">+</span>';
        } else {
          echo '<span class="toggle status-expanded">-</span>';
        } ?>
      </div>
      <div class="name-cat">
        <span class="name" title="<?php echo osc_esc_html(sprintf(__('Category ID: %s'), $catId)); ?>"><?php echo osc_esc_html(osc_category_row_name($category)); ?></span>
      </div>
      <div class="actions-cat">
        <a href="javascript:void(0);" class="quick-edit-btn" onclick="return toggleQuickEdit(this, 'content_list_<?php echo $catId; ?>', '<?php echo $catId; ?>');"><?php _e('Quick edit'); ?></a>
        &middot;
        <a href="<?php echo osc_admin_base_url(true); ?>?page=categories&amp;action=edit&amp;id=<?php echo $catId; ?>"><?php _e('Edit'); ?></a>
        &middot;
        <a class="enable" onclick="enable_cat('<?php echo $catId; ?>')"><?php $category['b_enabled'] == 1 ? _e('Disable') : _e('Enable'); ?></a>
        &middot;
        <a onclick="delete_category(<?php echo $catId; ?>)"><?php _e('Delete'); ?></a>
      </div>
    </div>
    <div class="edit content_list_<?php echo $catId; ?>"></div>
  </div>
  <?php if($has_subcategories) { ?>
    <ul class="subcategory subcategories-<?php echo $catId; ?> " style="display: none;">
      <?php foreach($category['categories'] as $subcategory) {
        drawCategory($subcategory);
      } ?>
    </ul>
  <?php } ?>
</li>
<?php
}
?>

<?php osc_current_admin_theme_path('parts/header.php'); ?>

<div class="right">
  <div class="categories">
    <h2 class="render-title"><?php _e('Quick management'); ?></h2>
    <div class="flashmessage flashmessage-info">
      <p class="info"><?php _e('Drag&drop the categories to reorder them the way you like. Use Quick edit for inline changes or Edit for the full form.'); ?></p>
    </div>
    <?php if(!osc_selectable_parent_categories()) { ?>
      <div class="flashmessage flashmessage-warning">
        <p class="info"><?php echo sprintf(__('Parent category cannot be selected when publishing a new listing. Each category that has at least 1 child category is considered as parent. You can change this setting in %s, "Parent categories" section.'), '<a href="' . osc_admin_base_url(true) . '?page=settings">' . __('Settings > General') . '</a>'); ?></p>
      </div>
    <?php } ?>
    <div class="list-categories">
      <ul class="sortable">
      <?php foreach($categories as $category) {
        drawCategory($category);
      } ?>
      </ul>
    </div>
    <div class="clear"></div>
  </div>
</div>

<div id="dialog-delete-category" title="<?php echo osc_esc_html(__('Delete category')); ?>" class="has-form-actions hide" data-category-id="">
  <div class="form-horizontal">
    <div class="form-row">
      <?php _e('<strong>WARNING</strong>: This will also delete the listings under that category. This action cannot be undone. Are you sure you want to continue?'); ?>
    </div>
    <div class="form-actions">
      <div class="wrapper">
        <a id="category-delete-submit" href="javascript:void(0);" class="btn btn-submit"><?php echo osc_esc_html(__('Delete')); ?></a>
        <a class="btn" href="javascript:void(0);" onclick="$('#dialog-delete-category').dialog('close');"><?php _e('Cancel'); ?></a>
      </div>
    </div>
  </div>
</div>

<?php osc_current_admin_theme_path('parts/footer.php');
