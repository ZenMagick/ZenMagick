<?php
var_dump($affiliateDetails);
?>

<?php if ($affiliateDetails->referrer_banned) { ?>
    <p><em>Your referrer account has been suspended. If you believe this to be an error, please 
    <a href="<?php echo $request->url('contact_us') ?>">contact us</a>.</em>
<?php } ?>

<p>Orders and Payments | <a href="<?php echo $request->url('affiliate_tools', '', true) ?>">Marketing Tools</a> | 
  <a href="<?php echo $request->url('affiliate_terms', '', false) ?>">Referrer Terms</a></p>

<h3><?php _vzm('My Affiliate Information') ?></h3>
<table>
  <tr>
    <td><?php _vzm('Affiliate ID') ?></td>
    <td><?php echo $affiliateDetails->referrer_key ?></td>
  </tr>
  <tr>
    <td><?php _vzm('Last payment made on') ?></td>
    <td><?php echo $affiliateDetails->referrer_key ?></td>
  </tr>
  <tr>
    <td><?php _vzm('Current commission rate') ?></td>
    <td><?php echo $affiliateDetails->referrer_commission*100 ?>%</td>
  </tr>
</table>
