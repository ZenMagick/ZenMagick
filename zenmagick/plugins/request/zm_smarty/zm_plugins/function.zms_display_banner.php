<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {zms_display_banner} function plugin
 *
 * Type:     function<br>
 * Name:     zms_display_banner<br>
 * Purpose:  Wrapper
 * @author   DerManoMann
 * @param array
 * @param Smarty
 * @return string
 */
function smarty_function_zms_display_banner($params, &$smarty) {
    return zm_display_banner($params['box'], false);
}

/* vim: set expandtab: */

?>
