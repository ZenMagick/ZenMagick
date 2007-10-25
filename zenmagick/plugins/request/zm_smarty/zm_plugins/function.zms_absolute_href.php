<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {zms_absolute_href} function plugin
 *
 * Type:     function<br>
 * Name:     zms_absolute_href<br>
 * Purpose:  Wrapper
 * @author   DerManoMann
 * @param array
 * @param Smarty
 * @return string
 */
function smarty_function_zms_absolute_href($params, &$smarty) {
    return zm_absolute_href($params['uri'], false);
}

/* vim: set expandtab: */

?>
