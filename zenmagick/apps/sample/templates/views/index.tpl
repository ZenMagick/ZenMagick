<h1>Hello world!</h1>

<?php if (ZMMessages::instance()->hasMessages()) { ?>
    <ul id="messages">
    <?php foreach (ZMMessages::instance()->getMessages() as $message) { ?>
        <li class="<?php echo $message->getType() ?>"><?php echo $message->getText() ?></li>
    <?php } ?>
    </ul>
<?php } ?>

<!-- manually set up form validation -->
<script type="text/javascript">
  var mynameForm_validation_rules = new Array(
    new Array('required','myname','Please enter a name.')
  );
  // change messages
  validation.messages = {
    'alreadySubmitted': 'Please be patient!',
    'errors': "Oops, why such a hurry?\n\n"
  };
</script>

<form action="<?php echo $request->url() ?>" id="mynameForm" onsubmit="return validation.validate(this);" method="POST">
  <p>Tell me your name?</p>
  <p>
    <input type="text" name="myname" value="">
    <input type="submit" value="submit">
  </p>
</form>
<?php if (isset($name)) { ?>
  <p>Your name is: <?php echo $name ?>.</p>
<?php } ?>
<p>Context is: <?php echo $request->getContext() ?></p>


<p><a href="<?php echo $request->url(null, 'clear=true', true) ?>">Clear session</a></p>
<p><a href="<?php echo $request->url('about') ?>">About</a></p>
