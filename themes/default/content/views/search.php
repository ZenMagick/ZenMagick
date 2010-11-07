<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
 */
?>

<?php define('KEYWORD_DEFAULT', _zm("enter search")); ?>
<?php echo $form->open('search', '', false, array('method' => 'get')) ?>
    <fieldset>
    <legend><?php _vzm("Search again") ?></legend>
        <div>
            <?php define('KEYWORD_DEFAULT', _zm("enter search")); ?>
            <?php $onfocus = "if(this.value=='" . KEYWORD_DEFAULT . "') this.value='';" ?>
            <input type="text" id="keywords" name="keywords" value="<?php echo $html->encode($searchCriteria->getKeywords(KEYWORD_DEFAULT)) ?>" onfocus="<?php echo $onfocus ?>" />
        </div>
        <div class="btn"><input type="submit" class="btn" value="<?php _vzm("Search") ?>" /></div>
        <a class="clear" href="<?php echo $net->url(FILENAME_ADVANCED_SEARCH, '&keywords='.$searchCriteria->getKeywords()) ?>"><?php _vzm("Advanced Search") ?></a>
    </fieldset>
</form>

<?php if (isset($resultList)) { ?>
    <?php if ($resultList->hasResults()) { ?>
        <div class="rnblk">
            <?php echo $this->fetch('views/resultlist/nav.php') ?>
            <?php echo $this->fetch('views/resultlist/options.php') ?>
        </div>

        <?php echo $form->open('compare_products', '', false, array('method' => 'get')) ?>
            <div class="rlist">
                <table cellspacing="0" cellpadding="0"><tbody>
                    <?php $first = true; $odd = true; foreach ($resultList->getResults() as $product) {
                          $this->assign(array('product' => $product, 'first' => $first, 'odd' => $odd)); ?>
                      <?php echo $this->fetch('views/resultlist/product.php') ?>
                    <?php $first = false; $odd = !$odd; } ?>
                </tbody></table>
            </div>
            <div class="rnblk">
                <?php echo $this->fetch('views/resultlist/nav.php') ?>
            </div>
        </form>
    <?php } else { ?>
        <h2><?php _vzm("No products found") ?></h2>
    <?php } ?>
<?php } ?>
