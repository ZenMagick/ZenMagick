<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {zms_format_currency} function plugin
 *
 * Type:     function<br>
 * Name:     zms_format_currency<br>
 * Purpose:  Wrapper
 * @author   DerManoMann
 * @param array
 * @param Smarty
 * @return string
 */
function smarty_function_zms_format_currency($params, &$smarty) {
    return zm_format_currency($params['value'], true, false);
}

/* vim: set expandtab: */

?>
