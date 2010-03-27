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

<?php if (!$session->isRegistered()) { ?>
    <?php $redirectUrl = $request->url('affiliate_signup', '', true); ?>
    <p>Interested in signing up for our referral program? Please begin by 
      <a href="<?php echo $request->url('login', 'redirect='.$redirectUrl, true)?>">logging in to your account</a>.
      If you don't already have one, you can <a href="<?php echo $request->url('create_account', 'redirect='.$redirectUrl, true) ?>">create it here</a>.</p>
<?php } else { ?>
    <p>Interested in signing up for our referral program? 
    Enter the URL you'd like to promote us from.  We'll take a look and let you know what we think.</p>

    <?php echo $form->open('affiliate_signup', '', true, array('id'=>'affiliateSignup')) ?>
      <fieldset>
        <legend><?php zm_l10n("Signup URL") ?></legend>
        <div>
          <label for="url"><?php zm_l10n("Website URL") ?></label>
          <input type="text" id="url" name="url" /> 
        </div>
      </fieldset>
      <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Submit") ?>" /></div>
    </form>
<?php } ?>

<?php include 'affiliate_terms.php' ?>
