<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {zms_image} function plugin
 *
 * Type:     function<br>
 * Name:     zms_image<br>
 * Purpose:  Wrapper
 * @author   DerManoMann
 * @param array
 * @param Smarty
 * @return string
 */
function smarty_function_zms_image($params, &$smarty) {
    return zm_image($params['imageInfo'], _zms_dv($params['size']), _zms_ad($params['parameter']), false);
}

/* vim: set expandtab: */

?>
