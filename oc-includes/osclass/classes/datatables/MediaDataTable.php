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
 * MediaDataTable class
 *
 * @since 3.1
 * @package Osclass
 * @subpackage classes
 * @author Osclass
 */
class MediaDataTable extends DataTable {
  private $order_by;
  private $resourceID;
  private $keyword;
  private $extension;

  /**
   * @param $params
   *
   * @return array
   */
  public function table($params) {

    $this->addTableHeader();
    $this->getDBParams($params);

    $media = ItemResource::newInstance()->searchResources($this->resourceID, $this->start, $this->limit, $this->order_by['column_name'], $this->order_by['type'], $this->keyword, $this->extension);
    $this->processData($media['resources']);
    $this->total = $media['rows'];
    $this->totalFiltered = $media['total_results'];

    return $this->getData();
  }

  private function addTableHeader() {
    Rewrite::newInstance()->init();
    $page  = (int)Params::getParam('iPage');
    if($page==0) { $page = 1; }
    Params::setParam('iPage', $page);
    $url_base = preg_replace('|&direction=([^&]*)|', '', preg_replace('|&sort=([^&]*)|', '', osc_base_url() . Rewrite::newInstance()->get_raw_request_uri()));
    $sort = Params::getParam('sort');
    $direction = Params::getParam('direction');

    $this->clearSortColumns();
    $this->clearSourceColumns();
    $this->setDefaultSort('id', 'desc');
    // List of sortable columns in datatable
    $this->addSortColumn('id', 'r.pk_i_id', true);
    $this->addSortColumn('file', 'r.s_path', true);
    $this->addSortColumn('extension', 'r.s_extension', true);
    $this->addSortColumn('item', 'r.fk_i_item_id', true);
    $this->addSortColumn('order', 'r.i_order', true);

    // Source columns used by data-source-col in table header
    $this->addSourceColumn('file', 's_path|s_extension');
    $this->addSourceColumn('extension', 's_extension');
    $this->addSourceColumn('item', 'fk_i_item_id');
    $this->addSourceColumn('order', 'i_order');
    $this->addSourceColumn('variants', 'filesystem:normal|thumbnail|preview|original');
    $this->addSourceColumn('upload_date', 'filesystem:ctime');

    // Table header columns rendered in admin
    $this->addColumn('bulkactions', '<input id="check_all" type="checkbox" />');
    $this->addColumn('item', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('item', $sort, $direction)) . '">' . __('Item') . '</a>');
    $this->addColumn('file', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('file', $sort, $direction)) . '">' . __('File') . '</a>');
    $this->addColumn('extension', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('extension', $sort, $direction)) . '">' . __('Extension') . '</a>');
    $this->addColumn('order', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('order', $sort, $direction)) . '">' . __('Order') . '</a>');
    $this->addColumn('variants', __('Variants'));
    $this->addColumn('upload_date', __('Upload date'));

    $dummy = &$this;
    osc_run_hook( 'admin_media_table' , $dummy);
  }

  /**
   * @param $media
   */
  private function processData($media) {
    if(!empty($media)) {
      $csrf_token_url = osc_csrf_token_url();

      foreach($media as $aRow) {
        $row = array();
        $item = osc_get_item_row($aRow['fk_i_item_id']);
        $itemTitle = ($item !== false && isset($item['s_title']) ? $item['s_title'] : __('Item'));
        $resourcePath = osc_apply_filter('resource_path', osc_base_url() . $aRow['s_path']) . $aRow['pk_i_id'] . '.' . $aRow['s_extension'];
        $thumbnailPath = osc_apply_filter('resource_thumbnail_url', osc_apply_filter('resource_path', osc_base_url() . $aRow['s_path']) . $aRow['pk_i_id'] . '_thumbnail.' . $aRow['s_extension']);
        $editItemUrl = osc_admin_base_url(true) . '?page=items&action=item_edit&id=' . $aRow['fk_i_item_id'];
        $viewItemUrl = osc_item_url_ns($aRow['fk_i_item_id']);
        $showItemMediaUrl = osc_admin_base_url(true) . '?page=media&itemId=' . $aRow['fk_i_item_id'];
        $deleteItemMediaUrl = osc_admin_base_url(true) . '?page=media&action=delete_item_media&itemId=' . $aRow['fk_i_item_id'] . '&' . $csrf_token_url;
        $resourceFullPath = $this->getResourceFullPath($aRow);
        $resourceMeta = $this->getResourceMeta($resourceFullPath);

        $rowWebp = $aRow;
        $rowWebp['s_extension'] = 'webp';

        $thumbFullPath = $this->getResourceFullPath($aRow, '_thumbnail');
        $thumbWebpFullPath = $this->getResourceFullPath($rowWebp, '_thumbnail');
        $normalFullPath = $this->getResourceFullPath($aRow);
        $normalWebpFullPath = $this->getResourceFullPath($rowWebp);

        if(!is_file($thumbFullPath)) {
          if(is_file($thumbWebpFullPath)) {
            $thumbnailPath = osc_apply_filter('resource_thumbnail_url', osc_apply_filter('resource_path', osc_base_url() . $aRow['s_path']) . $aRow['pk_i_id'] . '_thumbnail.webp');
          } else if(is_file($normalFullPath)) {
            $thumbnailPath = osc_apply_filter('resource_thumbnail_url', osc_apply_filter('resource_path', osc_base_url() . $aRow['s_path']) . $aRow['pk_i_id'] . '.' . $aRow['s_extension']);
          } else if(is_file($normalWebpFullPath)) {
            $thumbnailPath = osc_apply_filter('resource_thumbnail_url', osc_apply_filter('resource_path', osc_base_url() . $aRow['s_path']) . $aRow['pk_i_id'] . '.webp');
          }
        }

        if(!is_file($normalFullPath) && is_file($normalWebpFullPath)) {
          $resourcePath = osc_apply_filter('resource_path', osc_base_url() . $aRow['s_path']) . $aRow['pk_i_id'] . '.webp';
        }

        $options = array();
        $options[] = '<a onclick="return delete_dialog(\'' . $aRow['pk_i_id'] . '\');" href="#">' . __('Delete') . '</a>';
        $options[] = '<a href="' . osc_esc_html($resourcePath) . '" target="_blank">' . __('View') . '</a>';
        $options[] = '<a href="' . osc_esc_html($editItemUrl) . '" target="_blank">' . __('Edit item') . '</a>';
        $options[] = '<a href="' . osc_esc_html($viewItemUrl) . '" target="_blank">' . __('View item') . '</a>';
        $options[] = '<a href="' . osc_esc_html($showItemMediaUrl) . '">' . __('View all item media') . '</a>';
        $options[] = '<a href="' . osc_esc_html($deleteItemMediaUrl) . '" onclick="return confirm(\'' . osc_esc_js(__('Are you sure you want to delete all media of this item?')) . '\');">' . __('Delete all item media') . '</a>';
        $auxOptions = '<ul>' . PHP_EOL;
        foreach($options as $actual) {
          $auxOptions .= '<li>' . $actual . '</li>' . PHP_EOL;
        }
        $actions = '<div class="actions">' . $auxOptions . '</ul>' . PHP_EOL . '</div>' . PHP_EOL;
        $fileLabel = basename($resourcePath);
        $row['bulkactions'] = '<input type="checkbox" name="id[]" value="' . $aRow['pk_i_id'] . '" />';
        $row['item'] = '<a target="_blank" href="' . osc_esc_html($viewItemUrl) . '">' . osc_highlight($itemTitle, 40) . '<span class="icon-new-window"></span></a>';
        $row['file'] = '<div class="img-wrap"><a href="' . osc_esc_html($resourcePath) . '" target="_blank" title="' . osc_esc_html($fileLabel) . '"><img src="' . osc_esc_html($thumbnailPath) . '" width="60" height="50" alt="' . osc_esc_html($fileLabel) . '"/></a></div>' . $actions;
        $extensionValue = trim((string)$aRow['s_extension']);
        if($extensionValue != '') {
          $hasWebp = false;
          $aConfiguredExt = osc_parse_allowed_image_extensions(osc_allowed_extension());
          $webpUploadEnabled = in_array('webp', $aConfiguredExt, true);

          if(!$webpUploadEnabled && strtolower($extensionValue) != 'webp') {
            if(is_file($thumbWebpFullPath) || is_file($normalWebpFullPath) || is_file($this->getResourceFullPath($rowWebp, '_preview')) || is_file($this->getResourceFullPath($rowWebp, '_original'))) {
              $hasWebp = true;
            }
          }

          $row['extension'] = '<a href="' . osc_esc_html(osc_admin_base_url(true) . '?page=media&extension=' . rawurlencode($extensionValue)) . '">' . osc_esc_html($extensionValue) . '</a>';
          if($hasWebp) {
            $row['extension'] .= ' / webp';
          }
        } else {
          $row['extension'] = '-';
        }
        $row['order'] = (isset($aRow['i_order']) && $aRow['i_order'] !== '' ? (int)$aRow['i_order'] : 0);
        $row['variants'] = $this->getResourceVariantsMeta($aRow);
        $row['upload_date'] = $resourceMeta['upload_date'];

        $row = osc_apply_filter('media_processing_row', $row, $aRow);

        $this->addRow($row);
        $this->rawRows[] = $aRow;
      }

    }
  }

  /**
   * @param array $params
   */
  private function getDBParams($params) {

    foreach($params as $k => $v) {
      if(($k === 'itemId') && !empty($v)) {
        $this->resourceID = (int) $v;
      }

      if(($k === 'resourceId') && !empty($v) && $this->resourceID <= 0) {
        $this->resourceID = (int) $v;
      }
      if($k === 'iDisplayStart') {
        $this->start = (int) $v;
      }
      if($k === 'iDisplayLength') {
        $this->limit = (int) $v;
      }
      if($k === 'sSearch') {
        $this->keyword = trim((string)$v);
      }
      if($k === 'extension') {
        $this->extension = trim((string)$v);
      }
    }

    $sortData = $this->resolveSort($params);
    $this->order_by['column_name'] = ($sortData['column'] != '' ? $sortData['column'] : 'r.pk_i_id');
    $this->order_by['type'] = strtoupper($sortData['direction']);
    Params::setParam('sort', $sortData['key']);
    Params::setParam('direction', $sortData['direction']);

    // set start and limit using iPage param
    $start = ((int)Params::getParam('iPage')-1) * $params['iDisplayLength'];

    $this->start = (int) $start;
    $this->limit = (int)$params['iDisplayLength'];

  }

  private function getResourceFullPath($row, $suffix = '') {
    $relativePath = (isset($row['s_path']) ? (string)$row['s_path'] : '');
    $fileName = (isset($row['pk_i_id']) ? (int)$row['pk_i_id'] : 0) . $suffix . '.' . (isset($row['s_extension']) ? (string)$row['s_extension'] : '');
    $urlPath = osc_apply_filter('resource_path', osc_base_url() . $relativePath) . $fileName;
    $relative = ltrim(str_replace(osc_base_url(), '', $urlPath), '/');
    return rtrim(str_replace('\\', '/', ABS_PATH), '/') . '/' . $relative;
  }

  private function getResourceVariantsMeta($row) {
    $variants = array(
      '' => __('Normal'),
      '_thumbnail' => __('Thumbnail'),
      '_preview' => __('Preview'),
      '_original' => __('Original')
    );
    $output = '';
    $ext = (isset($row['s_extension']) ? strtolower(trim((string)$row['s_extension'])) : '');

    foreach($variants as $suffix => $label) {
      $fullPath = $this->getResourceFullPath($row, $suffix);
      $metaPath = $fullPath;
      $metaLabel = $label;

      if(!is_file($metaPath) && $ext != '' && $ext != 'webp') {
        $webpPath = preg_replace('/\.' . preg_quote($ext, '/') . '$/i', '.webp', $fullPath);
        if(is_string($webpPath) && $webpPath != '' && is_file($webpPath)) {
          $metaPath = $webpPath;
          $metaLabel = $label . ' (webp)';
        }
      }

      if(!is_file($metaPath)) {
        $output .= '<p><strong>' . osc_esc_html($label) . ':</strong> ' . osc_esc_html(__('File does not exist or is not stored in local storage')) . '</p>';
        continue;
      }

      $meta = $this->getResourceMeta($metaPath);
      $line = '<strong>' . osc_esc_html($metaLabel) . ':</strong> ' . osc_esc_html($meta['size']) . ' / ' . osc_esc_html($meta['dimension']) . ' / ' . osc_esc_html($meta['last_mod_date']);
      $output .= '<p>' . $line . '</p>';
    }

    return $output;
  }

  private function getResourceMeta($fullPath) {
    $meta = array(
      'size' => '-',
      'dimension' => '-',
      'upload_date' => '-',
      'last_mod_date' => '-'
    );

    if(!is_file($fullPath)) {
      return $meta;
    }

    $size = @filesize($fullPath);
    if($size !== false) {
      $meta['size'] = number_format(((float)$size / 1024), 2, '.', '') . 'kb';
    }

    $imageData = @getimagesize($fullPath);
    if(is_array($imageData) && isset($imageData[0]) && isset($imageData[1])) {
      $meta['dimension'] = (int)$imageData[0] . 'x' . (int)$imageData[1] . 'px';
    }

    $ctime = @filectime($fullPath);
    if($ctime !== false) {
      $meta['upload_date'] = date('Y-m-d H:i:s', $ctime);
    }

    $mtime = @filemtime($fullPath);
    if($mtime !== false) {
      $meta['last_mod_date'] = date('Y-m-d H:i:s', $mtime);
      if($meta['upload_date'] === '-') {
        $meta['upload_date'] = $meta['last_mod_date'];
      }
    }

    return $meta;
  }
}
