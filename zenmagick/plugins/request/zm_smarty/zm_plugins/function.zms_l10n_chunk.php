<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {zms_l10n_chunk} function plugin
 *
 * Type:     function<br>
 * Name:     zms_l10n_chunk<br>
 * Purpose:  Wrapper
 * @author   DerManoMann
 * @param array
 * @param Smarty
 * @return string
 */
function smarty_function_zms_l10n_chunk($params, &$smarty) {
    $args = array();
    foreach ($params as $key => $value) {
        if (is_numeric($key)) {
            $args[$key] = $value;
        }
    }
    return zm_l10n_chunk_get($params['text'], $params['text'], $args);
}

/* vim: set expandtab: */

?>
