<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * $Id$
 */
?>

<?php define('KEYWORD_DEFAULT', zm_l10n_get("enter search")); ?>
<?php $form->open('search', '', false, array('method' => 'get')) ?>
    <div>
        <?php define('KEYWORD_DEFAULT', zm_l10n_get("enter search")); ?>
        <?php $onfocus = "if(this.value=='" . KEYWORD_DEFAULT . "') this.value='';" ?>
        <input type="text" id="keyword" name="keyword" value="<?php $html->encode(ZMRequest::getParameter('keyword', KEYWORD_DEFAULT)) ?>" onfocus="<?php echo $onfocus ?>" />
    </div>
    <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Search") ?>" /></div>
    <a class="clear" href="<?php $net->url(FILENAME_ADVANCED_SEARCH, '&keyword='.$zm_searchCriteria->getKeywords()) ?>"><?php zm_l10n("Advanced Search") ?></a>
</form>

<?php if (isset($zm_resultList)) { ?>
    <?php if ($zm_resultList->hasResults()) { ?>
        <div class="rnblk">
            <?php include('resultlist/nav.php') ?>
            <?php include('resultlist/options.php') ?>
        </div>

        <?php $form->open('compare_products', '', false, array('method' => 'get')) ?>
            <div class="rlist">
                <table cellspacing="0" cellpadding="0"><tbody>
                    <?php $first = true; $odd = true; foreach ($zm_resultList->getResults() as $product) { ?>
                      <?php include('resultlist/product.php') ?>
                    <?php $first = false; $odd = !$odd; } ?>
                </tbody></table>
            </div>
            <div class="rnblk">
                <?php include('resultlist/compare.php') ?>
                <?php include('resultlist/nav.php') ?>
            </div>
        </form>
    <?php } else { ?>
        <h2><?php zm_l10n("No products found") ?></h2>
    <?php } ?>
<?php } ?>
