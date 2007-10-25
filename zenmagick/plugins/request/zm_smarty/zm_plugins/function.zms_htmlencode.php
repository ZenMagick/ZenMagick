<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {zms_htmlencode} function plugin
 *
 * Type:     function<br>
 * Name:     zms_htmlencode<br>
 * Purpose:  Wrapper
 * @author   DerManoMann
 * @param array
 * @param Smarty
 * @return string
 */
function smarty_function_zms_htmlencode($params, &$smarty) {
    return zm_htmlencode($params['text'], false);
}

/* vim: set expandtab: */

?>
