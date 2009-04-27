<?php
var_dump($affiliateDetails);
?>

<?php if ($affiliateDetails->referrer_banned) { ?>
    <p><em>Your referrer account has been suspended. If you believe this to be an error, please 
    <a href="<?php $net->url('contact_us') ?>">contact us</a>.</em>
<?php } ?>

<p>Orders and Payments | <a href="<?php $net->url('affiliate_tools', '', true) ?>">Marketing Tools</a> | 
  <a href="<?php $net->url('affiliate_terms', '', false) ?>">Referrer Terms</a></p>

<h3><?php zm_l10n('My Affiliate Information') ?></h3>
<table>
  <tr>
    <td><?php zm_l10n('Affiliate ID') ?></td>
    <td><?php echo $affiliateDetails->referrer_key ?></td>
  </tr>
  <tr>
    <td><?php zm_l10n('Last payment made on') ?></td>
    <td><?php echo $affiliateDetails->referrer_key ?></td>
  </tr>
  <tr>
    <td><?php zm_l10n('Current commission rate') ?></td>
    <td><?php echo $affiliateDetails->referrer_commission*100 ?>%</td>
  </tr>
</table>
