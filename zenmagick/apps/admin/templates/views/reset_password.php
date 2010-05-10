<h1>Reset Password</h1>


<form action="<?php echo $admin2->url() ?>" method="POST">
<input type="hidden" name="<?php echo ZMRequest::SESSION_TOKEN_NAME ?>" value="<?php echo $session->getToken() ?>">

<p>
<label for="email">Email</label><br>
<input type="text" name="email" id="email">
</p>

<p><input type="submit" value="Reset"></p>
</form>
