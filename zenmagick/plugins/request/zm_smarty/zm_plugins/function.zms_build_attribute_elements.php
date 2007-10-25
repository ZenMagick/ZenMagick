<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {zms_build_attribute_elements} function plugin
 *
 * Type:     function<br>
 * Name:     zms_build_attribute_elements<br>
 * Purpose:  Wrapper
 * @author   DerManoMann
 * @param array
 * @param Smarty
 * @return string
 */
function smarty_function_zms_build_attribute_elements($params, &$smarty) {
    $smarty->assign($params['var'], zm_build_attribute_elements($params['product']));
}

/* vim: set expandtab: */

?>
