<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {zms_fmt_price} function plugin
 *
 * Type:     function<br>
 * Name:     zms_fmt_price<br>
 * Purpose:  Wrapper
 * @author   DerManoMann
 * @param array
 * @param Smarty
 * @return string
 */
function smarty_function_zms_fmt_price($params, &$smarty) {
    return zm_fmt_price($params['product'], false);
}

/* vim: set expandtab: */

?>
