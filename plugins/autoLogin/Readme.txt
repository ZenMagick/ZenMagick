Automatically login returning customers
=======================================

This is a ZenMagick implementation of the zen-cart mod AutomaticLogin,
http://www.zen-cart.com/index.php?main_page=product_contrib_info&products_id=489.


Installation
============
1) Unzip the plugin package into the zenmagick/plugins directory.
2) Install plugin using the ZenMagick Plugin Manager.
3) Configure as required using the Plugin Manager


Required template changes
=========================
To allow users to opt in (if configured), the following should be added to the login and
create_account template files, respectivley (adjust layout as required):

login:

    <div>
      <input type="checkbox" id="autoLogin" name="autoLogin" value="1" /> 
      <label style="display:inline;" for="autoLogin"><?php _vzm("Remember Me") ?></label>
    </div>



create_account:

    <tr>
        <td></td>
        <td><input type="checkbox" id="autoLogin" name="autoLogin" value="1" /><label for="autoLogin"><?php _vzm("Remember Me") ?></label></td>
    </tr>


Security
========
In contrast to the original mod this plugin uses the ZenMagick token service to avoid having to put the account id into the cookie.
