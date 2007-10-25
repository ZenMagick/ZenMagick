<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {zms_add_product_form} function plugin
 *
 * Type:     function<br>
 * Name:     zms_add_product_form<br>
 * Purpose:  Wrapper
 * @author   DerManoMann
 * @param array
 * @param Smarty
 * @return string
 */
function smarty_function_zms_add_product_form($params, &$smarty) {
    return zm_add_product_form($params['id'], false);
}

/* vim: set expandtab: */

?>
