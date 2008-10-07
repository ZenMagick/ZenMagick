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
      <label style="display:inline;" for="autoLogin"><?php zm_l10n("Remember Me") ?></label>
    </div>



create_account:

    <tr>
        <td></td>
        <td><input type="checkbox" id="autoLogin" name="autoLogin" value="1" /><label for="autoLogin"><?php zm_l10n("Remember Me") ?></label></td>
    </tr>


Token support
=============
If the zm_token plugin is installed, it is possible to issue token rather than storing the original password hash
in the cookie.

NOTE: It is important to have the token plugin a lower sort order than this plugin in order for the token service being
available when required.


Cookie name
===========
The code uses a different cookie name compared to the original zen-cart mod. If you require full compatibility,
you can do so by adding the following line to your local.php file (and token support disabled):

  define('ZM_AUTO_LOGIN_COOKIE', 'zencart_cookie_permlogin');
