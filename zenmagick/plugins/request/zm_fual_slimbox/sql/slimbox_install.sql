/**
 * Fual Slimbox for Zen v0.1.5
 *
 * @author Brian Tyler (btyler@math.ucl.ac.uk)
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */

SET @t4=0;
SELECT (@t4:=configuration_group_id) as t4 
FROM configuration_group
WHERE configuration_group_title= 'Fual Slimbox';
DELETE FROM configuration WHERE configuration_group_id = @t4;
DELETE FROM configuration_group WHERE configuration_group_id = @t4;

INSERT INTO configuration_group VALUES (NULL, 'Fual Slimbox', 'Configure Slimbox options', '1', '1');
UPDATE configuration_group SET sort_order = last_insert_id() WHERE configuration_group_id = last_insert_id();

SET @t4=0;
SELECT (@t4:=configuration_group_id) as t4 
FROM configuration_group
WHERE configuration_group_title= 'Fual Slimbox';

INSERT INTO configuration (configuration_id, configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) 

VALUES

(NULL, 'Enable Fual Slimbox?', 'FUAL_SLIMBOX', 'true', '<br />If true, all product images on the following pages will be displayed within a lightbox:<br /><br />- document_general_info<br />- document_product_info<br />- product_free_shipping_info<br />- product_info<br />- product_music_info<br />- product_reviews<br />- product_reviews_info<br />- product_reviews_write<br /><br />Please note: To completely remove Fual Slimbox please see the documentation provided.<br /><br /><b>Zen Lightbox overrides this when installed</b>. This means that if you have the ZL mod and Slimbox, Slimbox will do nothing. I have done this specifically to stop people messing their installations up. I have provided a modified version of ZL for ZL users, unfortunately switching ZL off doesnt really switch it off (all the javascript is still loaded), the modified version actually switches ZL off when you switch it off.<br /><br />
The big advantage of Slimbox over lightbox is that it takes only 30kb of javasript rather than well over 100kb for lightbox<br /><br />Unlike (Zen) Lightbox the Slimbox is completely generated using CSS, so any tweeks that you want to make to the look can be made from the CSS file.<br /><br />Props to: <a href="http://www.digitalia.be/software/slimbox">Christophe Beyls</a> for writing Slimbox, <a href="http://homepage.mac.com/yukikun/software/slimbox_ex/">Yukio Arita</a> for extending it to work with iframes (this is the extended version so you can put whole webpages in the Slimbox, dont get carried away though!) and Alex Clarke for the original ZL plugin which inspired me to write this.<br /><br /><b>Default: true</b><br />', @t4, 1, NOW(), NOW(), NULL, 'zen_cfg_select_option( array(''true'', ''false''), '), 

(NULL, 'Paranoia Mode', 'FUAL_SLIMBOX_NERVOUS', '1', '<br />Controls whether to wait until the DOM loads to display the image. This stops the problem of being redirected to the image rather than displaying the image in the lightbox if a user with a slow connection clicks on the image before the slimbox script has been initialised.<br /><br />0 = Relaxed: Dont wait until the DOM loads.<br />1 = Conservative: Waits until the DOM has loaded to make the image visible.<br />2 = Funky: Waits until the DOM has loaded to reveal the image with a funky transition. <strong>Depending on your template this may cause problems in IE6, so if you want to use the funky transition please test it in older browsers before going live.</strong><br /><br /><b>Default: 1 (Conservative)</b><br />', @t4, 10, NOW(), NOW(), NULL, 'zen_cfg_select_option(array(''0'', ''1'', ''2''), '),

(NULL, 'Safe Navigation', 'FUAL_SLIMBOX_DISPLAY_VAR', 'true', '<br />With safe navigation enabled the Next and Prev links are displayed permenantly in the lightbox when there are additional images to display. Safe Navigation is recommended because without it the navigation links fail to show in IE6.<br /><br /><b>Default: true</b><br />', @t4, 15, NOW(), NOW(), NULL, 'zen_cfg_select_option(array(''true'', ''false''), '),

(NULL, 'Page of Text', 'FUAL_SLIMBOX_PAGEOF', 'Page #1 of #2', '<br />Sets the "Page #1 of #2" text when there is more than one image.<br /><br />The text is replaced with a regular expression: #1 is the number of the current page, #2 is the total number of pages.<br /><br /><b>Examples:</b><br />Page #1 of #2 - default English<br />Page #1 (of #2) - variation English<br />Pagina #1 di #2 - Itallian<br /><br /><b>Default: Page #1 of #2</b><br />', @t4, 17, NOW(), NOW(), NULL, NULL),

(NULL, 'Transition Duration', 'FUAL_SLIMBOX_DURATION', '800', '<br />Controls the duration of the transition in milliseconds. (note that 1000 milliseconds = 1 second, so the default value of 800 is just under a second)<br /><br /><b>Default: 800</b><br />', @t4, 20, NOW(), NOW(), NULL, NULL),

(NULL, 'Transition Type', 'FUAL_SLIMBOX_TRANSITION_TYPE', 'Sine', '<br />Controls the type of the transition. See <a href="http://docs.mootools.net/Effects/Fx-Transitions.js">Mootools</a> for what the options mean.<br /><br /><b>Default: Sine</b><br />', @t4, 30, NOW(), NOW(), NULL, 'zen_cfg_select_option(array(''linear'', ''Quad'', ''Cubic'', ''Quart'', ''Quint'', ''Expo'', ''Circ'', ''Sine'', ''Back'', ''Bounce'', ''Elastic''), '),

(NULL, 'Transition Ease', 'FUAL_SLIMBOX_EASE', 'easeInOut', '<br />Controls the easing of the transition. See <a href="http://docs.mootools.net/Effects/Fx-Transitions.js">Mootools</a> for what the options mean.<br /><br /><b>Default: easeInOut</b><br />', @t4, 40, NOW(), NOW(), NULL, 'zen_cfg_select_option(array(''easeIn'', ''easeOut'', ''easeInOut''), '),

(NULL, 'Transition Amplitude', 'FUAL_SLIMBOX_AMPLITUDE', '5', '<br />Controls the amplitude of the transition.<br /><br /><b>Default: 5</b><br />', @t4, 50, NOW(), NOW(), NULL, 'zen_cfg_select_option(array(''1'', ''2'', ''3'',''4'',''5'',''6'',''7'',''8'',''9''), '),

(NULL, 'FPS', 'FUAL_SLIMBOX_FPS', '60', '<br />Controls the frames per second of the motion.<br /><br /><em>Should be an integer value between 1 - 200.</em><br /><br /><b>Default: 60</b><br />', @t4, 60, NOW(), NOW(), NULL, NULL),

(NULL, 'Width', 'FUAL_SLIMBOX_WIDTH', '400', '<br />Controls the initial width of the lightbox.<br /><br /><b>Default: 400</b><br />', @t4, 70, NOW(), NOW(), NULL, NULL),

(NULL, 'Height', 'FUAL_SLIMBOX_HEIGHT', '300', '<br />Controls the initial height of the lightbox. Have a play!<br /><br /><b>Default: 300</b><br />', @t4, 80, NOW(), NOW(), NULL, NULL),

(NULL, 'iFrame Width', 'FUAL_SLIMBOX_IWIDTH', '500', '<br />Controls the initial width of the lightbox when it contains an iFrame. Have a play!<br /><br /><b>Default: 500</b><br />', @t4, 90, NOW(), NOW(), NULL, NULL),

(NULL, 'iFrame Height', 'FUAL_SLIMBOX_IHEIGHT', '300', '<br />Controls the initial width of the lightbox when it contains an iFrame. Have a play!<br /><br /><b>Default: 300</b><br />', @t4, 100, NOW(), NOW(), NULL, NULL),

(NULL, 'Animate Caption', 'FUAL_SLIMBOX_CAPTION', 'true', '<br />Animates the caption. Have a play!<br /><br /><b>Default: true</b><br />', @t4, 110, NOW(), NOW(), NULL, 'zen_cfg_select_option(array(''true'', ''false''), '),

(NULL, 'Class to Hide', 'FUAL_SLIMBOX_HIDE_ME', 'zenLightboxHideMe', '<br />Any content wrapped in with this class will be hidden when the lightbox is displayed (this extends the behaviour of Zen Lightbox which only hides DIV tags).<br /><br /><b>Default: zenLightboxHideMe</b> (This coincides with the Zen Lightbox default class.)<br />', @t4, 120, NOW(), NOW(), NULL, NULL);