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
 * Reusable position/order helpers for admin sortable rows (pages, categories, ...).
 */
class PositionOrder {

  // Sort rows by position and assign sequential values starting at $minPos
  public static function normalize($items, $idKey = 'pk_i_id', $posKey = 'i_order', $minPos = 1) {
    if(!is_array($items) || count($items) == 0) {
      return array();
    }

    usort($items, function($a, $b) use ($posKey, $idKey) {
      $pa = (int)(isset($a[$posKey]) ? $a[$posKey] : 0);
      $pb = (int)(isset($b[$posKey]) ? $b[$posKey] : 0);

      if($pa == $pb) {
        return ((int)$a[$idKey] < (int)$b[$idKey] ? -1 : 1);
      }

      return ($pa < $pb ? -1 : 1);
    });

    $pos = (int)$minPos;
    if($pos < 1) {
      $pos = 1;
    }

    foreach($items as $i => $item) {
      $items[$i][$posKey] = $pos;
      $pos++;
    }

    return $items;
  }

  // Move one row up/down and shift neighbors (before final normalize)
  public static function move($items, $id, $direction, $idKey = 'pk_i_id', $posKey = 'i_order', $minPos = 1) {
    if(!is_array($items) || count($items) == 0) {
      return array();
    }

    $items = self::normalize($items, $idKey, $posKey, $minPos);
    $index = -1;

    foreach($items as $i => $item) {
      if((int)$item[$idKey] === (int)$id) {
        $index = $i;
        break;
      }
    }

    if($index < 0) {
      return $items;
    }

    if($direction === 'up' && $index > 0) {
      $oldPos = (int)$items[$index][$posKey];
      $newPos = $oldPos - 1;

      foreach($items as $j => $item) {
        if($j == $index) {
          continue;
        }

        $p = (int)$item[$posKey];
        if($p >= $newPos && $p < $oldPos) {
          $items[$j][$posKey] = $p + 1;
        }
      }

      $items[$index][$posKey] = $newPos;
    } else if($direction === 'down' && $index < count($items) - 1) {
      $oldPos = (int)$items[$index][$posKey];
      $newPos = $oldPos + 1;

      foreach($items as $j => $item) {
        if($j == $index) {
          continue;
        }

        $p = (int)$item[$posKey];
        if($p > $oldPos && $p <= $newPos) {
          $items[$j][$posKey] = $p - 1;
        }
      }

      $items[$index][$posKey] = $newPos;
    }

    return $items;
  }

  // Highest position value in list (at least $minPos)
  public static function getMaxPosition($items, $posKey = 'i_order', $minPos = 1) {
    $minPos = (int)$minPos;
    if($minPos < 1) {
      $minPos = 1;
    }

    if(!is_array($items) || count($items) == 0) {
      return $minPos;
    }

    $max = $minPos;
    foreach($items as $item) {
      $p = (int)(isset($item[$posKey]) ? $item[$posKey] : 0);
      if($p < $minPos) {
        $p = $minPos;
      }
      if($p > $max) {
        $max = $p;
      }
    }

    return $max;
  }

  // Whether row can move up/down within bounds
  public static function canMoveUp($position, $minPos = 1) {
    return ((int)$position > (int)$minPos);
  }

  public static function canMoveDown($position, $maxPosition) {
    return ((int)$position < (int)$maxPosition);
  }
}

/* file end: ./oc-includes/osclass/classes/PositionOrder.php */
