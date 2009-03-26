<?php
// +----------------------------------------------------------------------+
// |Snap Affiliates for Zen Cart                                          |
// +----------------------------------------------------------------------+
// | Copyright (c) 2009 Michael Burke                                     |
// |                                                                      |
// | http://www.filterswept.com                                           |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license.       |
// +----------------------------------------------------------------------+
?>

<?php if ($referrer->referrer_banned) { ?>
    <p><em>Your referrer account has been suspended. If you beleive this to be an error, please 
    <a href="<?php $net->url('contact_us') ?>">contact us</a>.</em>
<?php } ?>

<p>Orders and Payments | <a href="<?php $net->url('affiliate_tools', '', true) ?>">Marketing Tools</a> | 
  <a href="<?php $net->url('affiliate_terms', '', false) ?>">Referrer Terms</a></p>

