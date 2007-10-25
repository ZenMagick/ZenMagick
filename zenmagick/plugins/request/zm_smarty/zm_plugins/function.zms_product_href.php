<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {zm_product_href} function plugin
 *
 * Type:     function<br>
 * Name:     zm_product_href<br>
 * Purpose:  Wrapper
 * @author   DerManoMann
 * @param array
 * @param Smarty
 * @return string
 */
function smarty_function_zms_product_href($params, &$smarty) {
    return zm_product_href($params['id'], false);
}

/* vim: set expandtab: */

?>
