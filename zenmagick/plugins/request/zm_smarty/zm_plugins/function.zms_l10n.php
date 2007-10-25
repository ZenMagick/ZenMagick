<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {zms_l10n} function plugin
 *
 * Type:     function<br>
 * Name:     zms_l10n<br>
 * Purpose:  Wrapper
 * @author   DerManoMann
 * @param array
 * @param Smarty
 * @return string
 */
function smarty_function_zms_l10n($params, &$smarty) {
    $args = array();
    foreach ($params as $key => $value) {
        if (is_numeric($key)) {
            $args[$key] = $value;
        }
    }
    return _zm_l10n_lookup($params['text'], $params['text'], $args);
}

/* vim: set expandtab: */

?>
