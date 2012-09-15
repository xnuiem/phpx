<?php
/**
* This is just an array that is required to change the SQL as the versions change.
* 
* 0.1 -> 0.2
* 0.2 -> 0.3
* 0.3 -> 0.4
* 0.4 -> 0.5
* 0.5 -> 0.6
* 0.6 -> 1.0
* 1.0 -> 1.1
* 
* @var mixed
*/

$upgradeArray = array();
$upgradeArray["0.6"][] = "ALTER TABLE `" . $this->wpdb->prefix . "bx_item` ADD `bx_item_no_update_desc` TINYINT( 1 ) NOT NULL DEFAULT '0', CHANGE `bx_item_name` `bx_item_name` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `bx_item_author` `bx_item_author` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL , CHANGE `bx_item_format` `bx_item_format` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL , CHANGE `bx_item_publisher` `bx_item_publisher` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL, CHANGE `bx_item_date` `bx_item_date` INT( 10 ) NULL DEFAULT NULL , CHANGE `bx_item_date_added` `bx_item_date_added` INT( 10 ) NULL DEFAULT NULL, CHANGE `bx_item_image` `bx_item_image` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL , CHANGE `bx_item_link` `bx_item_link` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL";
$upgradeArray["1.4"] = "ALTER TABLE `" . $this->wpdb->prefix . "_bx_item` CHANGE `bx_item_pages` `bx_item_pages` INT( 10 ) NULL DEFAULT '0', CHANGE `bx_item_price` `bx_item_price` FLOAT( 4, 2 ) NULL DEFAULT '0.00'";
?>
