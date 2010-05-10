<h1>Login</h1>


<form action="<?php echo $admin2->url() ?>" method="POST">
<input type="hidden" name="<?php echo ZMRequest::SESSION_TOKEN_NAME ?>" value="<?php echo $session->getToken() ?>">

<p>
<label for="name">User Name</label><br>
<input type="text" name="name" id="name">
</p>

<p>
<label for="password">Password</label><br>
<input type="password" name="password" id="password">
</p>

<p><input type="submit" value="Login"></p>
</form>
<p><a href="<?php echo $admin2->url('reset_password') ?>">Reset Password</a></p>
