<h1><?php _vzm('Hello world!') ?></h1>

<?php if (ZMMessages::instance()->hasMessages()) { ?>
    <ul id="messages">
    <?php foreach (ZMMessages::instance()->getMessages() as $message) { ?>
        <li class="<?php echo $message->getType() ?>"><?php echo $message->getText() ?></li>
    <?php } ?>
    </ul>
<?php } ?>

<!-- manually set up form validation -->
<script type="text/javascript">
  // change messages
  // NOTE: these change the JS messages only
  zmFormValidation.messages = {
    'alreadySubmitted': '<?php _vzm('Please be patient!') ?>',
    'errors': "<?php _vzm("Oopsey, why such a hurry?") ?>\n\n"
  };
</script>

<!-- semi automatic -->
<?php echo ZMValidator::instance()->toJSString('mynameForm'); ?>

<form action="<?php echo $request->url() ?>" id="mynameForm" onsubmit="return zmFormValidation.validate(this);" method="POST">
  <p><?php _vzm('Tell me your name?') ?></p>
  <p>
    <input type="text" name="myname" value="">
    <input type="submit" value="<?php _vzm('Submit') ?>">
  </p>
</form>
<?php if (isset($name)) { ?>
  <p><?php _vzm('Your name is: %s.', $name) ?></p>
<?php } ?>
<p><?php echo sprintf(_vzm('Context is: %s', $request->getContext())) ?></p>

<p><a href="<?php echo $request->url(null, 'clear=true', true) ?>"><?php _vzm('Clear session') ?></a></p>
<p>
  <?php foreach ($languages as $locale => $name) { if ($locale == $currentLocale) { continue; } ?>
    <a href="<?php echo $request->url(null, 'locale='.$locale) ?>"><?php echo $name ?></a> 
  <?php } ?>
</p>
<p><a href="<?php echo $request->url('about') ?>"><?php _vzm('About') ?></a></p>
