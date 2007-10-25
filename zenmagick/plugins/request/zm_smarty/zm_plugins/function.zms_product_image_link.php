<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {zms_product_image_link} function plugin
 *
 * Type:     function<br>
 * Name:     zms_product_image_link<br>
 * Purpose:  Wrapper
 * @author   DerManoMann
 * @param array
 * @param Smarty
 * @return string
 */
function smarty_function_zms_product_image_link($params, &$smarty) {
    return zm_product_image_link($params['product'], isset($params['format']) ? $params['format'] : PRODUCT_IMAGE_SMALL, false);
}

/* vim: set expandtab: */

?>
