<?php
/**
 * functions_email.php
 * Processes all outbound email from Zen Cart
 * Hooks into phpMailer class for actual email encoding and sending
 *
 * @package functions
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Tue Aug 14 14:56:11 2012 +0100 Modified in v1.5.1 $
 * 2007-09-30 added encryption support for Gmail Chuck Redman
 */

/**
 * Set email system debugging off or on
 * 0=off
 * 1=show SMTP status errors
 * 2=show SMTP server responses
 * 4=show SMTP readlines if applicable
 * 5=maximum information
 * 'preview' to show HTML-emails on-screen while sending
 */
  if (!defined('EMAIL_SYSTEM_DEBUG')) define('EMAIL_SYSTEM_DEBUG','0');
  if (!defined('EMAIL_ATTACHMENTS_ENABLED')) define('EMAIL_ATTACHMENTS_ENABLED', true);
/**
 * enable embedded image support
 */
  if (!defined('EMAIL_ATTACH_EMBEDDED_IMAGES')) define('EMAIL_ATTACH_EMBEDDED_IMAGES', 'Yes');

/**
 * If using authentication protocol, enter appropriate option here: 'ssl' or 'tls' or 'starttls'
 * If using 'starttls', you must add a define for 'SMTPAUTH_EMAIL_CERTIFICATE_CONTEXT' in the extra_datafiles folder to supply your certificate-context
 */
  if (!defined('SMTPAUTH_EMAIL_PROTOCOL')) define('SMTPAUTH_EMAIL_PROTOCOL', 'none');

/**
 * Send email (text/html) using MIME. This is the central mail function.
 * If using "PHP" transport method, the SMTP Server or other mail application should be configured correctly in server's php.ini
 *
 * @param string $to_name           The name of the recipient, e.g. "Jim Johanssen"
 * @param string $to_email_address  The email address of the recipient, e.g. john.smith@hzq.com
 * @param string $email_subject     The subject of the eMail
 * @param string $email_text        The text of the email, may contain HTML entities
 * @param string $from_email_name   The name of the sender, e.g. Shop Administration
 * @param string $from_email_adrdess The email address of the sender, e.g. info@myzenshop.com
 * @param array  $block             Array containing values to be inserted into HTML-based email template
 * @param string $module            The module name of the routine calling zen_mail. Used for HTML template selection and email archiving.
 *                                  This is passed to the archive function denoting what module initiated the sending of the email
 * @param array  $attachments_list  Array of attachment names/mime-types to be included  (this portion still in testing, and not fully reliable)
**/
  function zen_mail_org($to_name, $to_address, $email_subject, $email_text, $from_email_name, $from_email_address, $block=array(), $module='default', $attachments_list='' ) {
    global $db, $messageStack, $zco_notifier;
    if (!defined('DEVELOPER_OVERRIDE_EMAIL_STATUS') || (defined('DEVELOPER_OVERRIDE_EMAIL_STATUS') && DEVELOPER_OVERRIDE_EMAIL_STATUS == 'site'))
      if (SEND_EMAILS != 'true') return false;  // if sending email is disabled in Admin, just exit

    if (defined('DEVELOPER_OVERRIDE_EMAIL_ADDRESS') && DEVELOPER_OVERRIDE_EMAIL_ADDRESS != '') $to_address = DEVELOPER_OVERRIDE_EMAIL_ADDRESS;

    // ignore sending emails for any of the following pages
    // (The EMAIL_MODULES_TO_SKIP constant can be defined in a new file in the "extra_configures" folder)
    if (defined('EMAIL_MODULES_TO_SKIP') && in_array($module,explode(",",constant('EMAIL_MODULES_TO_SKIP')))) return false;

    // check for injection attempts. If new-line characters found in header fields, simply fail to send the message
    foreach(array($from_email_address, $to_address, $from_email_name, $to_name, $email_subject) as $key=>$value) {
      if (preg_match("/\r/i",$value) || preg_match("/\n/i",$value)) return false;
    }

    // if no text or html-msg supplied, exit
    if (trim($email_text) == '' && (!zen_not_null($block) || (isset($block['EMAIL_MESSAGE_HTML']) && $block['EMAIL_MESSAGE_HTML'] == '')) ) return false;

    // Parse "from" addresses for "name" <email@address.com> structure, and supply name/address info from it.
    if (preg_match("/ *([^<]*) *<([^>]*)> */i",$from_email_address,$regs)) {
      $from_email_name = trim($regs[1]);
      $from_email_address = $regs[2];
    }
    // if email name is same as email address, use the Store Name as the senders 'Name'
    if ($from_email_name == $from_email_address) $from_email_name = STORE_NAME;

    // loop thru multiple email recipients if more than one listed  --- (esp for the admin's "Extra" emails)...
    foreach(explode(',',$to_address) as $key=>$value) {
      if (preg_match("/ *([^<]*) *<([^>]*)> */i",$value,$regs)) {
        $to_name = str_replace('"', '', trim($regs[1]));
        $to_email_address = $regs[2];
      } elseif (preg_match("/ *([^ ]*) */i",$value,$regs)) {
        $to_email_address = trim($regs[1]);
      }
      if (!isset($to_email_address)) $to_email_address=trim($to_address); //if not more than one, just use the main one.

      // ensure the address is valid, to prevent unnecessary delivery failures
      if (!zen_validate_email($to_email_address)) {
        @error_log(sprintf(EMAIL_SEND_FAILED . ' (failed validation)', $to_name, $to_email_address, $email_subject));
        continue;
      }

      //define some additional html message blocks available to templates, then build the html portion.
      if (!isset($block['EMAIL_TO_NAME']) || $block['EMAIL_TO_NAME'] == '')       $block['EMAIL_TO_NAME'] = $to_name;
      if (!isset($block['EMAIL_TO_ADDRESS']) || $block['EMAIL_TO_ADDRESS'] == '') $block['EMAIL_TO_ADDRESS'] = $to_email_address;
      if (!isset($block['EMAIL_SUBJECT']) || $block['EMAIL_SUBJECT'] == '')       $block['EMAIL_SUBJECT'] = $email_subject;
      if (!isset($block['EMAIL_FROM_NAME']) || $block['EMAIL_FROM_NAME'] == '')   $block['EMAIL_FROM_NAME'] = $from_email_name;
      if (!isset($block['EMAIL_FROM_ADDRESS']) || $block['EMAIL_FROM_ADDRESS'] == '') $block['EMAIL_FROM_ADDRESS'] = $from_email_address;
      $email_html = (!is_array($block) && substr($block, 0, 6) == '<html>') ? $block : zen_build_html_email_from_template($module, $block);
      if (!is_array($block) && $block == '' || $block == 'none') $email_html = '';

      // Build the email based on whether customer has selected HTML or TEXT, and whether we have supplied HTML or TEXT-only components
      // special handling for XML content
      if ($email_text == '') {
        $email_text = str_replace(array('<br>','<br />'), "<br />\n", $block['EMAIL_MESSAGE_HTML']);
        $email_text = str_replace('</p>', "</p>\n", $email_text);
        $email_text = ($module != 'xml_record') ? htmlspecialchars(stripslashes(strip_tags($email_text)), ENT_COMPAT, CHARSET, TRUE) : $email_text;
      } else {
        $email_text = ($module != 'xml_record') ? strip_tags($email_text) : $email_text;
      }

      if ($module != 'xml_record') {
        if (defined('EMAIL_DISCLAIMER') && EMAIL_DISCLAIMER != '' && !strstr($email_text, sprintf(EMAIL_DISCLAIMER, STORE_OWNER_EMAIL_ADDRESS)) && $to_email_address != STORE_OWNER_EMAIL_ADDRESS && !defined('EMAIL_DISCLAIMER_NEW_CUSTOMER')) $email_text .= "\n" . sprintf(EMAIL_DISCLAIMER, STORE_OWNER_EMAIL_ADDRESS);
        if (defined('EMAIL_SPAM_DISCLAIMER') && EMAIL_SPAM_DISCLAIMER != '' && !strstr($email_text, EMAIL_SPAM_DISCLAIMER) && $to_email_address != STORE_OWNER_EMAIL_ADDRESS) $email_text .= "\n\n" . EMAIL_SPAM_DISCLAIMER;
      }

      // bof: body of the email clean-up
      // clean up &amp; and && from email text
      while (strstr($email_text, '&amp;&amp;')) $email_text = str_replace('&amp;&amp;', '&amp;', $email_text);
      while (strstr($email_text, '&amp;')) $email_text = str_replace('&amp;', '&', $email_text);
      while (strstr($email_text, '&&')) $email_text = str_replace('&&', '&', $email_text);

      // clean up currencies for text emails
      $zen_fix_currencies = preg_split("/[:,]/" , CURRENCIES_TRANSLATIONS);
      $size = sizeof($zen_fix_currencies);
      for ($i=0, $n=$size; $i<$n; $i+=2) {
        $zen_fix_current = $zen_fix_currencies[$i];
        $zen_fix_replace = $zen_fix_currencies[$i+1];
        if (strlen($zen_fix_current)>0) {
          while (strpos($email_text, $zen_fix_current)) $email_text = str_replace($zen_fix_current, $zen_fix_replace, $email_text);
        }
      }

      // fix double quotes
      while (strstr($email_text, '&quot;')) $email_text = str_replace('&quot;', '"', $email_text);
      // prevent null characters
      while (strstr($email_text, chr(0))) $email_text = str_replace(chr(0), ' ', $email_text);

      // fix slashes
      $text = stripslashes($email_text);
      $email_html = stripslashes($email_html);

      // eof: body of the email clean-up

      //determine customer's email preference type: HTML or TEXT-ONLY  (HTML assumed if not specified)
      $sql = "select customers_email_format from " . TABLE_CUSTOMERS . " where customers_email_address= :custEmailAddress:";
      $sql = $db->bindVars($sql, ':custEmailAddress:', $to_email_address, 'string');
      $result = $db->Execute($sql);
      $customers_email_format = ($result->RecordCount() > 0) ? $result->fields['customers_email_format'] : '';
      if ($customers_email_format == 'NONE' || $customers_email_format == 'OUT') return; //if requested no mail, then don't send.
//      if ($customers_email_format == 'HTML') $customers_email_format = 'HTML'; // if they opted-in to HTML messages, then send HTML format

      // handling admin/"extra"/copy emails:
      if (ADMIN_EXTRA_EMAIL_FORMAT == 'TEXT' && substr($module,-6)=='_extra') {
        $email_html='';  // just blank out the html portion if admin has selected text-only
      }
      //determine what format to send messages in if this is an admin email for newsletters:
      if ($customers_email_format == '' && ADMIN_EXTRA_EMAIL_FORMAT == 'HTML' && in_array($module, array('newsletters', 'product_notification')) && isset($_SESSION['admin_id'])) {
        $customers_email_format = 'HTML';
      }

      // special handling for XML content
      if ($module == 'xml_record') {
        $email_html = '';
        $customers_email_format ='TEXT';
      }

      //notifier intercept option
      $zco_notifier->notify('NOTIFY_EMAIL_AFTER_EMAIL_FORMAT_DETERMINED');

      // now lets build the mail object with the phpmailer class
      $mail = new PHPMailer();
      $lang_code = strtolower(($_SESSION['languages_code'] == '' ? 'en' : $_SESSION['languages_code'] ));
      $mail->SetLanguage($lang_code, DIR_FS_CATALOG . DIR_WS_CLASSES . 'support/');
      $mail->CharSet =  (defined('CHARSET')) ? CHARSET : "iso-8859-1";
      $mail->Encoding = (defined('EMAIL_ENCODING_METHOD')) ? EMAIL_ENCODING_METHOD : "7bit";
      if ((int)EMAIL_SYSTEM_DEBUG > 0 ) $mail->SMTPDebug = (int)EMAIL_SYSTEM_DEBUG;
      $mail->WordWrap = 76;    // set word wrap to 76 characters
      // set proper line-endings based on switch ... important for windows vs linux hosts:
      $mail->LE = (EMAIL_LINEFEED == 'CRLF') ? "\r\n" : "\n";

      switch (EMAIL_TRANSPORT) {
        case 'smtp':
          $mail->IsSMTP();
          $mail->Host = trim(EMAIL_SMTPAUTH_MAIL_SERVER);
          if (EMAIL_SMTPAUTH_MAIL_SERVER_PORT != '25' && EMAIL_SMTPAUTH_MAIL_SERVER_PORT != '') $mail->Port = trim(EMAIL_SMTPAUTH_MAIL_SERVER_PORT);
          $mail->LE = "\r\n";
          break;
        case 'smtpauth':
          $mail->IsSMTP();
          $mail->SMTPAuth = true;
          $mail->Username = (zen_not_null(EMAIL_SMTPAUTH_MAILBOX)) ? trim(EMAIL_SMTPAUTH_MAILBOX) : EMAIL_FROM;
          $mail->Password = trim(EMAIL_SMTPAUTH_PASSWORD);
          $mail->Host = trim(EMAIL_SMTPAUTH_MAIL_SERVER);
          if (EMAIL_SMTPAUTH_MAIL_SERVER_PORT != '25' && EMAIL_SMTPAUTH_MAIL_SERVER_PORT != '') $mail->Port = trim(EMAIL_SMTPAUTH_MAIL_SERVER_PORT);
          $mail->LE = "\r\n";
          //set encryption protocol to allow support for Gmail or other secured email protocols
          if (EMAIL_SMTPAUTH_MAIL_SERVER_PORT == '465' || EMAIL_SMTPAUTH_MAIL_SERVER_PORT == '587' || EMAIL_SMTPAUTH_MAIL_SERVER == 'smtp.gmail.com') $mail->Protocol = 'ssl';
          if (defined('SMTPAUTH_EMAIL_PROTOCOL') && SMTPAUTH_EMAIL_PROTOCOL != 'none') {
            $mail->Protocol = SMTPAUTH_EMAIL_PROTOCOL;
            if (SMTPAUTH_EMAIL_PROTOCOL == 'starttls' && defined('SMTPAUTH_EMAIL_CERTIFICATE_CONTEXT')) {
              $mail->Starttls = true;
              $mail->Context = SMTPAUTH_EMAIL_CERTIFICATE_CONTEXT;
            }
          }
          break;
        case 'PHP':
          $mail->IsMail();
          break;
        case 'Qmail':
          $mail->IsQmail();
          break;
        case 'sendmail':
        case 'sendmail-f':
          $mail->LE = "\n";
        default:
          $mail->IsSendmail();
          if (defined('EMAIL_SENDMAIL_PATH')) $mail->Sendmail = trim(EMAIL_SENDMAIL_PATH);
          break;
      }

      $mail->Subject  = $email_subject;
      $mail->From     = $from_email_address;
      $mail->FromName = $from_email_name;
      $mail->AddAddress($to_email_address, $to_name);
      //$mail->AddAddress($to_email_address);    // (alternate format if no name, since name is optional)
      //$mail->AddBCC(STORE_OWNER_EMAIL_ADDRESS, STORE_NAME);

      // set the reply-to address.  If none set yet, then use Store's default email name/address.
      // If sending from contact-us or tell-a-friend page, use the supplied info
      $email_reply_to_address = (isset($email_reply_to_address) && $email_reply_to_address != '') ? $email_reply_to_address : (in_array($module, array('contact_us') ? $from_email_address : EMAIL_FROM);
      $email_reply_to_name    = (isset($email_reply_to_name) && $email_reply_to_name != '')    ? $email_reply_to_name    : (in_array($module, array('contact_us')) ? $from_email_name    : STORE_NAME);
      $mail->AddReplyTo($email_reply_to_address, $email_reply_to_name);

      // if mailserver requires that all outgoing mail must go "from" an email address matching domain on server, set it to store address
      if (EMAIL_SEND_MUST_BE_STORE=='Yes') $mail->From = EMAIL_FROM;

      if (EMAIL_TRANSPORT=='sendmail-f' || EMAIL_SEND_MUST_BE_STORE=='Yes') {
        $mail->Sender = EMAIL_FROM;
      }

      if (EMAIL_USE_HTML == 'true') $email_html = processEmbeddedImages($email_html, $mail);

      // PROCESS FILE ATTACHMENTS
      if ($attachments_list == '') $attachments_list = array();
      if (is_string($attachments_list)) {
        if (file_exists($attachments_list)) {
          $attachments_list = array(array('file' => $attachments_list));
        } elseif (file_exists(DIR_FS_CATALOG . $attachments_list)) {
          $attachments_list = array(array('file' => DIR_FS_CATALOG . $attachments_list));
        } else {
          $attachments_list = array();
        }
      }
      global $newAttachmentsList;
      $zco_notifier->notify('NOTIFY_EMAIL_BEFORE_PROCESS_ATTACHMENTS', array('attachments'=>$attachments_list, 'module'=>$module));
      if (isset($newAttachmentsList) && is_array($newAttachmentsList)) $attachments_list = $newAttachmentsList;
      if (defined('EMAIL_ATTACHMENTS_ENABLED') && EMAIL_ATTACHMENTS_ENABLED && is_array($attachments_list) && sizeof($attachments_list) > 0) {
        foreach($attachments_list as $key => $val) {
          $fname = (isset($val['name']) ? $val['name'] : null);
          $mimeType = (isset($val['mime_type']) && $val['mime_type'] != '' && $val['mime_type'] != 'application/octet-stream') ? $val['mime_type'] : '';
          switch (true) {
            case (isset($val['raw_data']) && $val['raw_data'] != ''):
              $fdata = $val['raw_data'];
              if ($mimeType != '') {
                $mail->AddStringAttachment($fdata, $fname, "base64", $mimeType);
              } else {
                $mail->AddStringAttachment($fdata, $fname);
              }
              break;
            case (isset($val['file']) && file_exists($val['file'])): //'file' portion must contain the full path to the file to be attached
              $fdata = $val['file'];
              if ($mimeType != '') {
                $mail->AddAttachment($fdata, $fname, "base64", $mimeType);
              } else {
                $mail->AddAttachment($fdata, $fname);
              }
              break;
          } // end switch
        } //end foreach attachments_list
      } //endif attachments_enabled
      $zco_notifier->notify('NOTIFY_EMAIL_AFTER_PROCESS_ATTACHMENTS', sizeof($attachments_list));

      // prepare content sections:
      if (EMAIL_USE_HTML == 'true' && trim($email_html) != '' &&
      ($customers_email_format == 'HTML' || (ADMIN_EXTRA_EMAIL_FORMAT != 'TEXT' && substr($module,-6)=='_extra'))) {
        $mail->IsHTML(true);           // set email format to HTML
        $mail->Body    = $email_html;  // HTML-content of message
        $mail->AltBody = $text;        // text-only content of message
      }  else {                        // use only text portion if not HTML-formatted
        $mail->Body    = $text;        // text-only content of message
      }

      $oldVars = array(); $tmpVars = array('REMOTE_ADDR', 'HTTP_X_FORWARDED_FOR', 'PHP_SELF', 'SERVER_NAME');
      foreach ($tmpVars as $key) {
        if (isset($_SERVER[$key])) {
          $oldVars[$key] = $_SERVER[$key];
          $_SERVER[$key]='';
        }
        if ($key == 'REMOTE_ADDR') $_SERVER[$key] = HTTP_SERVER;
        if ($key == 'PHP_SELF') $_SERVER[$key] = '/obf'.'us'.'cated';
      }
/**
 * Send the email. If an error occurs, trap it and display it in the messageStack
 */
      $ErrorInfo = '';
      $zco_notifier->notify('NOTIFY_EMAIL_READY_TO_SEND', $mail);
      if (!($result = $mail->Send())) {
        if (IS_ADMIN_FLAG === true) {
          $messageStack->add_session(sprintf(EMAIL_SEND_FAILED . '&nbsp;'. $mail->ErrorInfo, $to_name, $to_email_address, $email_subject),'error');
        } else {
          $messageStack->add('header',sprintf(EMAIL_SEND_FAILED . '&nbsp;'. $mail->ErrorInfo, $to_name, $to_email_address, $email_subject),'error');
        }
        $ErrorInfo .= ($mail->ErrorInfo != '') ? $mail->ErrorInfo . '<br />' : '';
      }
      $zco_notifier->notify('NOTIFY_EMAIL_AFTER_SEND');
      foreach($oldVars as $key => $val) {
        $_SERVER[$key] = $val;
      }

      $zco_notifier->notify('NOTIFY_EMAIL_AFTER_SEND_WITH_ALL_PARAMS', array($to_name, $to_email_address, $from_email_name, $from_email_address, $email_subject, $email_html, $text, $module, $ErrorInfo));
      // Archive this message to storage log
      // don't archive pwd-resets and CC numbers
      if (EMAIL_ARCHIVE == 'true'  && $module != 'password_forgotten_admin' && $module != 'cc_middle_digs' && $module != 'no_archive') {
        zen_mail_archive_write($to_name, $to_email_address, $from_email_name, $from_email_address, $email_subject, $email_html, $text, $module, $ErrorInfo );
      } // endif archiving
    } // end foreach loop thru possible multiple email addresses
    $zco_notifier->notify('NOTIFY_EMAIL_AFTER_SEND_ALL_SPECIFIED_ADDRESSES');

    if (EMAIL_FRIENDLY_ERRORS=='false' && $ErrorInfo != '') die('<br /><br />Email Error: ' . $ErrorInfo);

    return $ErrorInfo;
  }  // end function

/**
 * zen_mail_archive_write()
 *
 * this function stores sent emails into a table in the database as a log record of email activity.  This table CAN get VERY big!
 * To disable this function, set the "Email Archives" switch to 'false' in ADMIN!
 *
 * See zen_mail() function description for more details on the meaning of these parameters
 * @param string $to_name
 * @param string $to_email_address
 * @param string $from_email_name
 * @param string $from_email_address
 * @param string $email_subject
 * @param string $email_html
 * @param array $email_text
 * @param string $module
**/
  function zen_mail_archive_write($to_name, $to_email_address, $from_email_name, $from_email_address, $email_subject, $email_html, $email_text, $module, $error_msgs) {
    global $db, $zco_notifier;
    $zco_notifier->notify('NOTIFY_EMAIL_BEGIN_ARCHIVE_WRITE', array($to_name, $to_email_address, $from_email_name, $from_email_address, $email_subject, $email_html, $email_text, $module, $error_msgs));
    $to_name = zen_db_prepare_input($to_name);
    $to_email_address = zen_db_prepare_input($to_email_address);
    $from_email_name = zen_db_prepare_input($from_email_name);
    $from_email_address = zen_db_prepare_input($from_email_address);
    $email_subject = zen_db_prepare_input($email_subject);
    $email_html = (EMAIL_USE_HTML=='true') ? zen_db_prepare_input($email_html) : zen_db_prepare_input('HTML disabled in admin');
    $email_text = zen_db_prepare_input($email_text);
    $module = zen_db_prepare_input($module);
    $error_msgs = zen_db_prepare_input($error_msgs);

    $db->Execute("insert into " . TABLE_EMAIL_ARCHIVE . "
                  (email_to_name, email_to_address, email_from_name, email_from_address, email_subject, email_html, email_text, date_sent, module)
                  values ('" . zen_db_input($to_name) . "',
                          '" . zen_db_input($to_email_address) . "',
                          '" . zen_db_input($from_email_name) . "',
                          '" . zen_db_input($from_email_address) . "',
                          '" . zen_db_input($email_subject) . "',
                          '" . zen_db_input($email_html) . "',
                          '" . zen_db_input($email_text) . "',
                          now() ,
                          '" . zen_db_input($module) . "')");
    return $db;
  }

  //DEFINE EMAIL-ARCHIVABLE-MODULES LIST // this array will likely be used by the email archive log VIEWER module in future
  $emodules_array = array();
  $emodules_array[] = array('id' => 'newsletters', 'text' => 'Newsletters');
  $emodules_array[] = array('id' => 'product_notification', 'text' => 'Product Notifications');
  $emodules_array[] = array('id' => 'direct_email', 'text' => 'One-Time Email');
  $emodules_array[] = array('id' => 'contact_us', 'text' => 'Contact Us');
  $emodules_array[] = array('id' => 'coupon', 'text' => 'Send Coupon');
  $emodules_array[] = array('id' => 'coupon_extra', 'text' => 'Send Coupon');
  $emodules_array[] = array('id' => 'gv_queue', 'text' => 'Send-GV-Queue');
  $emodules_array[] = array('id' => 'gv_mail', 'text' => 'Send-GV');
  $emodules_array[] = array('id' => 'gv_mail_extra', 'text' => 'Send-GV-Extra');
  $emodules_array[] = array('id' => 'welcome', 'text' => 'New Customer Welcome');
  $emodules_array[] = array('id' => 'welcome_extra', 'text' => 'New Customer Welcome-Extra');
  $emodules_array[] = array('id' => 'password_forgotten', 'text' => 'Password Forgotten');
  $emodules_array[] = array('id' => 'password_forgotten_admin', 'text' => 'Password Forgotten');
  $emodules_array[] = array('id' => 'checkout', 'text' => 'Checkout');
  $emodules_array[] = array('id' => 'checkout_extra', 'text' => 'Checkout-Extra');
  $emodules_array[] = array('id' => 'order_status', 'text' => 'Order Status');
  $emodules_array[] = array('id' => 'order_status_extra', 'text' => 'Order Status-Extra');
  $emodules_array[] = array('id' => 'low_stock', 'text' => 'Low Stock Notices');
  $emodules_array[] = array('id' => 'cc_middle_digs', 'text' => 'CC - Middle-Digits');
  $emodules_array[] = array('id' => 'purchase_order', 'text' => 'Purchase Order');
  $emodules_array[] = array('id' => 'payment_modules', 'text' => 'Payment Modules');
  $emodules_array[] = array('id' => 'payment_modules_extra', 'text' => 'Payment Modules-Extra');
  /////////////////////////////////////////////////////////////////////////////////////////
  ////////END SECTION FOR EMAIL FUNCTIONS//////////////////////////////////////////////////
  /////////////////////////////////////////////////////////////////////////////////////////


/**
 * select email template based on 'module' (supplied as param to function)
 * selectively go thru each template tag and substitute appropriate text
 * finally, build full html content as "return" output from class
**/
  function zen_build_html_email_from_template_org($module='default', $content='') {
    global $messageStack, $current_page_base;
    $block = array();
    if (is_array($content)) {
      $block = $content;
    } else {
      $block['EMAIL_MESSAGE_HTML'] = $content;
    }
    // Identify and Read the template file for the type of message being sent
    $langfolder = (strtolower($_SESSION['languages_code']) == 'en') ? '' : strtolower($_SESSION['languages_code']) . '/';
    $template_filename_base = DIR_FS_EMAIL_TEMPLATES . $langfolder . "email_template_";
    $template_filename = DIR_FS_EMAIL_TEMPLATES . $langfolder . "email_template_" . $current_page_base . ".html";

    if (!file_exists($template_filename)) {
      if (isset($block['EMAIL_TEMPLATE_FILENAME']) && $block['EMAIL_TEMPLATE_FILENAME'] != '' && file_exists($block['EMAIL_TEMPLATE_FILENAME'] . '.html')) {
        $template_filename = $block['EMAIL_TEMPLATE_FILENAME'] . '.html';
      } elseif (file_exists($template_filename_base . str_replace(array('_extra','_admin'),'',$module) . '.html')) {
        $template_filename = $template_filename_base . str_replace(array('_extra','_admin'),'',$module) . '.html';
      } elseif (file_exists($template_filename_base . 'default' . '.html')) {
        $template_filename = $template_filename_base . 'default' . '.html';
      } else {
        if(isset($messageStack)) $messageStack->add('header','ERROR: The email template file for (' . $template_filename_base . ') or (' . $template_filename . ') cannot be found.','caution');
        return ''; // couldn't find template file, so return an empty string for html message.
      }
    }

    if (!$fh = fopen($template_filename, 'rb')) {   // note: the 'b' is for compatibility with Windows systems
      if (isset($messageStack)) $messageStack->add('header','ERROR: The email template file (' . $template_filename_base . ') or (' . $template_filename . ') cannot be opened', 'caution');
    }

    $file_holder = fread($fh, filesize($template_filename));
    fclose($fh);

    //strip linebreaks and tabs out of the template
//  $file_holder = str_replace(array("\r\n", "\n", "\r", "\t"), '', $file_holder);
    $file_holder = str_replace(array("\t"), ' ', $file_holder);


    if (!defined('HTTP_CATALOG_SERVER')) define('HTTP_CATALOG_SERVER', HTTP_SERVER);
    //check for some specifics that need to be included with all messages
    if (!isset($block['EMAIL_STORE_NAME']) || $block['EMAIL_STORE_NAME'] == '')     $block['EMAIL_STORE_NAME']  = STORE_NAME;
    if (!isset($block['EMAIL_STORE_URL']) || $block['EMAIL_STORE_URL'] == '')       $block['EMAIL_STORE_URL']   = '<a href="'.HTTP_CATALOG_SERVER . DIR_WS_CATALOG.'">'.STORE_NAME.'</a>';
    if (!isset($block['EMAIL_STORE_OWNER']) || $block['EMAIL_STORE_OWNER'] == '')   $block['EMAIL_STORE_OWNER'] = STORE_OWNER;
    if (!isset($block['EMAIL_FOOTER_COPYRIGHT']) || $block['EMAIL_FOOTER_COPYRIGHT'] == '') $block['EMAIL_FOOTER_COPYRIGHT'] = EMAIL_FOOTER_COPYRIGHT;
    if (!isset($block['EMAIL_DISCLAIMER']) || $block['EMAIL_DISCLAIMER'] == '')     $block['EMAIL_DISCLAIMER']  = sprintf(EMAIL_DISCLAIMER, '<a href="mailto:' . STORE_OWNER_EMAIL_ADDRESS . '">'. STORE_OWNER_EMAIL_ADDRESS .' </a>');
    if (!isset($block['EMAIL_SPAM_DISCLAIMER']) || $block['EMAIL_SPAM_DISCLAIMER'] == '')   $block['EMAIL_SPAM_DISCLAIMER']  = EMAIL_SPAM_DISCLAIMER;
    if (!isset($block['EMAIL_DATE_SHORT']) || $block['EMAIL_DATE_SHORT'] == '')     $block['EMAIL_DATE_SHORT']  = zen_date_short(date("Y-m-d"));
    if (!isset($block['EMAIL_DATE_LONG']) || $block['EMAIL_DATE_LONG'] == '')       $block['EMAIL_DATE_LONG']   = zen_date_long(date("Y-m-d"));
    if (!isset($block['BASE_HREF']) || $block['BASE_HREF'] == '') $block['BASE_HREF'] = HTTP_SERVER . DIR_WS_CATALOG;
    if (!isset($block['CHARSET']) || $block['CHARSET'] == '') $block['CHARSET'] = CHARSET;
    //  if (!isset($block['EMAIL_STYLESHEET']) || $block['EMAIL_STYLESHEET'] == '')      $block['EMAIL_STYLESHEET']       = str_replace(array("\r\n", "\n", "\r"), "",@file_get_contents(DIR_FS_EMAIL_TEMPLATES.'stylesheet.css'));

    if (!isset($block['EXTRA_INFO']))  $block['EXTRA_INFO']  = '';
    if (substr($module,-6) != '_extra' && $module != 'contact_us')  $block['EXTRA_INFO']  = '';

    $block['COUPON_BLOCK'] = '';
    if (isset($block['COUPON_TEXT_VOUCHER_IS']) && $block['COUPON_TEXT_VOUCHER_IS'] != '' && isset($block['COUPON_TEXT_TO_REDEEM']) && $block['COUPON_TEXT_TO_REDEEM'] != '') {
      $block['COUPON_BLOCK'] = '<div class="coupon-block">' . $block['COUPON_TEXT_VOUCHER_IS'] . $block['COUPON_DESCRIPTION'] . '<br />' . $block['COUPON_TEXT_TO_REDEEM'] . '<span class="coupon-code">' . $block['COUPON_CODE'] . '</span></div>';
    }

    $block['GV_BLOCK'] = '';
    if (isset($block['GV_WORTH']) && $block['GV_WORTH'] != '' && isset($block['GV_REDEEM']) && $block['GV_REDEEM'] != '' && isset($block['GV_CODE_URL']) && $block['GV_CODE_URL'] != '') {
      $block['GV_BLOCK'] = '<div class="gv-block">' . $block['GV_WORTH'] . '<br />' . $block['GV_REDEEM'] . $block['GV_CODE_URL'] . '<br />' . $block['GV_LINK_OTHER'] . '</div>';
    }

    //prepare the "unsubscribe" link:
    if (IS_ADMIN_FLAG === true) { // is this admin version, or catalog?
      $block['UNSUBSCRIBE_LINK'] = str_replace("\n",'',TEXT_UNSUBSCRIBE) . ' <a href="' . zen_catalog_href_link(FILENAME_UNSUBSCRIBE, "addr=" . $block['EMAIL_TO_ADDRESS']) . '">' . zen_catalog_href_link(FILENAME_UNSUBSCRIBE, "addr=" . $block['EMAIL_TO_ADDRESS']) . '</a>';
    } else {
      $block['UNSUBSCRIBE_LINK'] = str_replace("\n",'',TEXT_UNSUBSCRIBE) . ' <a href="' . zen_href_link(FILENAME_UNSUBSCRIBE, "addr=" . $block['EMAIL_TO_ADDRESS']) . '">' . zen_href_link(FILENAME_UNSUBSCRIBE, "addr=" . $block['EMAIL_TO_ADDRESS']) . '</a>';
    }

    //now replace the $BLOCK_NAME items in the template file with the values passed to this function's array
    foreach ($block as $key=>$value) {
      $file_holder = str_replace('$' . $key, $value, $file_holder);
    }

    //DEBUG -- to display preview on-screen
    if (EMAIL_SYSTEM_DEBUG=='preview') echo $file_holder;

    return $file_holder;
  }


/**
 * Function to build array of additional email content collected and sent on admin-copies of emails:
 *
 */
  function email_collect_extra_info($from, $email_from, $login, $login_email, $login_phone='', $login_fax='') {
    // get host_address from either session or one time for both email types to save server load
    if (!$_SESSION['customers_host_address']) {
      if (SESSION_IP_TO_HOST_ADDRESS == 'true') {
        $email_host_address = @gethostbyaddr($_SERVER['REMOTE_ADDR']);
      } else {
        $email_host_address = OFFICE_IP_TO_HOST_ADDRESS;
      }
    } else {
      $email_host_address = $_SESSION['customers_host_address'];
    }

    // generate footer details for "also-send-to" emails
    $extra_info=array();
    $extra_info['TEXT'] =
      OFFICE_USE . "\t" . "\n" .
      OFFICE_FROM . "\t" . $from . "\n" .
      OFFICE_EMAIL. "\t" . $email_from . "\n" .
      (trim($login) !='' ? OFFICE_LOGIN_NAME . "\t" . $login . "\n"  : '') .
      (trim($login_email) !='' ? OFFICE_LOGIN_EMAIL . "\t" . $login_email . "\n"  : '') .
      ($login_phone !='' ? OFFICE_LOGIN_PHONE . "\t" . $login_phone . "\n" : '') .
      ($login_fax !='' ? OFFICE_LOGIN_FAX . "\t" . $login_fax . "\n" : '') .
      OFFICE_IP_ADDRESS . "\t" . $_SESSION['customers_ip_address'] . ' - ' . $_SERVER['REMOTE_ADDR'] . "\n" .
      OFFICE_HOST_ADDRESS . "\t" . $email_host_address . "\n" .
      OFFICE_DATE_TIME . "\t" . date("D M j Y G:i:s T") . "\n\n";

    $extra_info['HTML'] = '<table class="extra-info">' .
      '<tr><td class="extra-info-bold" colspan="2">' . OFFICE_USE . '</td></tr>' .
      '<tr><td class="extra-info-bold">' . OFFICE_FROM . '</td><td>' . $from . '</td></tr>' .
      '<tr><td class="extra-info-bold">' . OFFICE_EMAIL. '</td><td>' . $email_from . '</td></tr>' .
      ($login !='' ? '<tr><td class="extra-info-bold">' . OFFICE_LOGIN_NAME . '</td><td>' . $login . '</td></tr>' : '') .
      ($login_email !='' ? '<tr><td class="extra-info-bold">' . OFFICE_LOGIN_EMAIL . '</td><td>' . $login_email . '</td></tr>' : '') .
      ($login_phone !='' ? '<tr><td class="extra-info-bold">' . OFFICE_LOGIN_PHONE . '</td><td>' . $login_phone . '</td></tr>' : '') .
      ($login_fax !='' ? '<tr><td class="extra-info-bold">' . OFFICE_LOGIN_FAX . '</td><td>' . $login_fax . '</td></tr>' : '') .
      '<tr><td class="extra-info-bold">' . OFFICE_IP_ADDRESS . '</td><td>' . $_SESSION['customers_ip_address'] . ' - ' . $_SERVER['REMOTE_ADDR'] . '</td></tr>' .
      '<tr><td class="extra-info-bold">' . OFFICE_HOST_ADDRESS . '</td><td>' . $email_host_address . '</td></tr>' .
      '<tr><td class="extra-info-bold">' . OFFICE_DATE_TIME . '</td><td>' . date('D M j Y G:i:s T') . '</td></tr>' . '</table>';
    return $extra_info;
  }


/**
 * validates an email address
 *
 * Sample Valid Addresses:
 *
 *     first.last@host.com
 *     firstlast@host.to
 *     "first last"@host.com
 *     "first@last"@host.com
 *     first-last@host.com
 *     first's-address@email.host.4somewhere.com
 *     first.last@[123.123.123.123]
 *
 *     hosts with either external IP addresses or from 2-6 characters will pass (e.g. .jp or .museum)
 *
 *     Invalid Addresses:
 *
 *     first last@host.com
 *     'first@host.com
 * @param string The email address to validate
 * @return boolean true if valid else false
**/
  function zen_validate_email($email) {
    global $zco_notifier;
    $valid_address = TRUE;

    // fail if contains no @ symbol or more than one @ symbol
    if (substr_count($email,'@') != 1) return false;

    // split the email address into user and domain parts
    // this method will most likely break in that case
    list( $user, $domain ) = explode( "@", $email );
    $valid_ip_form = '[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}';
    $valid_email_pattern = '^([\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+\.)*[\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+@((((([a-z0-9]{1}[a-z0-9\-]{0,62}[a-z0-9]{1})|[a-z])\.)+[a-z]{2,6})|(\d{1,3}\.){3}\d{1,3}(\:\d{1,5})?)$';
    $space_check = '[ ]';

    // strip beginning and ending quotes, if and only if both present
    if( (preg_match('/^["]/', $user) && preg_match('/["]$/', $user)) ){
      $user = preg_replace ( '/^["]/', '', $user );
      $user = preg_replace ( '/["]$/', '', $user );
      $user = preg_replace ( '/'.$space_check.'/', '', $user ); //spaces in quoted addresses OK per RFC (?)
      $email = $user."@".$domain; // contine with stripped quotes for remainder
    }

    // fail if contains spaces in domain name
    if (strstr($domain,' ')) return false;

    // if email domain part is an IP address, check each part for a value under 256
    if (preg_match('/'.$valid_ip_form.'/', $domain)) {
      $digit = explode( ".", $domain );
      for($i=0; $i<4; $i++) {
        if ($digit[$i] > 255) {
          $valid_address = false;
          return $valid_address;
          exit;
        }
        // stop crafty people from using internal IP addresses
        if (($digit[0] == 192) || ($digit[0] == 10)) {
          $valid_address = false;
          return $valid_address;
          exit;
        }
      }
    }

    if (!preg_match('/'.$valid_email_pattern.'/i', $email)) { // validate against valid email patterns
      $valid_address = false;
      return $valid_address;
      exit;
    }

    $zco_notifier->notify('NOTIFY_EMAIL_VALIDATION_TEST', array($email, $valid_address));

    return $valid_address;
  }


  /**
   * PROCESS EMBEDDED IMAGES
   * attach and properly embed any embedded images marked as 'embed="yes"'
   *
   * @param string $email_html
   * return string
   */
  function processEmbeddedImages ($email_html, & $mail)
  {
    if (defined('EMAIL_ATTACH_EMBEDDED_IMAGES') && EMAIL_ATTACH_EMBEDDED_IMAGES == 'Yes')
    {
      $imageFiles = array();
      $imagesToProcess = array();
      if (preg_match_all('#<img.*src=\"(.*?)\".*?\/>#', $email_html, $imagesToProcess))
      {
        for ($i = 0, $n = count($imagesToProcess[0]); $i < $n; $i ++)
        {
          $exists = strpos($imagesToProcess[0][$i], 'embed="yes"');
          if ($exists !== false)
          {
            // prevent duplicate attachments - if already processed, remember it
            if (array_key_exists($imagesToProcess[1][$i], $imageFiles))
            {
              $substitute = $imageFiles[$imagesToProcess[1][$i]];

              // if not a duplicate, and file can be located on filesystem, add it as an attachment, and replace its SRC attribute with the embedded code
            } elseif (file_exists(DIR_FS_CATALOG . $imagesToProcess[1][$i]))
            {
              $rpos = strrpos($imagesToProcess[1][$i], '.');
              $ext = substr($imagesToProcess[1][$i], $rpos + 1);
              $name = basename($imagesToProcess[1][$i], '.'.$ext);
              switch (strtolower($ext)) {
                case 'gif':
                  $mimetype = 'image/gif';
                  break;
                case 'jpg':
                case 'jpeg':
                  $mimetype = 'image/jpeg';
                  break;
                case 'png':
                default:
                  $mimetype = 'image/png';
                  break;
              }
              $substitute = $name . $i;
              $mail->AddEmbeddedImage(DIR_FS_CATALOG . $imagesToProcess[1][$i], $substitute, $name . '.' . $ext, "base64", $mimetype);
              $imageFiles[$imagesToProcess[1][$i]] = $substitute;
            }
            $email_html = str_replace($imagesToProcess[1][$i], 'cid:'.$substitute, $email_html);
          }
        }
      }
    }
    return $email_html;
  }

