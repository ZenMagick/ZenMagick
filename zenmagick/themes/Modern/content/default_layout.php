<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php echo HTML_PARAMS; ?>>
  <head>
    <title><?php echo META_TAG_TITLE; ?></title>
    <meta http-equiv="content-type" content="text/html; charset=<?php echo ZMSettings::get('zenmagick.mvc.html.charset') ?>" />
    <meta name="generator" content="ZenMagick <?php echo ZMSettings::get('zenmagick.version') ?>" />
    <meta name="keywords" content="<?php echo $metaTags->getKeywords()?>" />
    <meta name="description" content="<?php echo $metaTags->getDescription()?>" />
    <meta http-equiv="imagetoolbar" content="no" />
    <meta name="author" content="The Zen Cart&trade; Team and others" />
    <?php if (defined('ROBOTS_PAGES_TO_SKIP') && in_array($current_page_base,explode(",",constant('ROBOTS_PAGES_TO_SKIP'))) || $current_page_base=='down_for_maintenance' || $robotsNoIndex === true) { ?>
      <meta name="robots" content="noindex, nofollow" />
    <?php } ?>
    <?php $utils->cssFile('css/print_stylesheet.css', array('media' => 'print')) ?>
    <?php $utils->cssFile('css/style_imagehover.css') ?>
    <?php $utils->cssFile('css/stylesheet.css') ?>
    <?php $utils->cssFile('css/stylesheet_boxes.css') ?>
    <?php $utils->cssFile('css/stylesheet_css_buttons.css') ?>
    <?php //echo $this->fetch('css/stylesheet.php'); ?>
    <?php if (defined('FAVICON')) { ?>
      <link rel="icon" href="<?php echo FAVICON; ?>" type="image/x-icon" />
      <link rel="shortcut icon" href="<?php echo FAVICON; ?>" type="image/x-icon" />
    <?php } //endif FAVICON ?>
    <base href="<?php echo $request->getPageBase() ?>" />
  </head>

  <?php 
    $this_is_home_page = 'index' == $request->getRequestId();
    $body_id = ($this_is_home_page) ? 'indexHome' : str_replace('_', '', $request->getRequestId());
  ?>

  <body id="<?php echo $body_id . 'Body'; ?>">
    <?php if (null != ($bannerBox = ZMBanners::instance()->getBannerForSet('header1'))) { ?>
        <div id="bannerOne" class="banners"><?php echo $macro->showBanner($bannerBox); ?></div>
    <?php } ?>

    <!--bof main_wrapper-->
    <div id="mainWrapper">
      <?php
       /**
        * prepares and displays header output
        *
        */
        if (CUSTOMERS_APPROVAL_AUTHORIZATION == 1 && CUSTOMERS_AUTHORIZATION_HEADER_OFF == 'true' and ($_SESSION['customers_authorization'] != 0 or $_SESSION['customer_id'] == '')) {
          $flag_disable_header = true;
        }
        echo $this->fetch('header.php');
        ?>
        <div id="contentMainWrapper">
       
          <!-- bof  breadcrumb -->
          <?php if (DEFINE_BREADCRUMB_STATUS == '1' || (DEFINE_BREADCRUMB_STATUS == '2' && !$this_is_home_page) ) { ?>
              <div id="navBreadCrumb"><?php echo $macro->buildCrumbtrail($crumbtrail, " &gt; "); ?></div>
          <?php } ?>
          <!-- eof breadcrumb -->
         
          <div id="contentWrapper">
          <?php
          if (COLUMN_LEFT_STATUS == 0 || (CUSTOMERS_APPROVAL == '1' and $_SESSION['customer_id'] == '') || (CUSTOMERS_APPROVAL_AUTHORIZATION == 1 && CUSTOMERS_AUTHORIZATION_COLUMN_LEFT_OFF == 'true' and ($_SESSION['customers_authorization'] != 0 or $_SESSION['customer_id'] == ''))) {
            // global disable of column_left
            $flag_disable_left = true;
          }
          if (!isset($flag_disable_left) || !$flag_disable_left) {
          ?>

           <div id="navColumnOne" class="columnLeft back" style="width: <?php echo COLUMN_WIDTH_LEFT; ?>">
            <?php /** prepares and displays left column sideboxes */ ?>
            <div id="navColumnOneWrapper" style="width: <?php echo BOX_WIDTH_LEFT; ?>">
              <?php foreach (ZMTemplateManager::instance()->getLeftColBoxNames() as $box) { ?>
                <?php if ($this->exists('boxes/'.$box)) { ?>
                  <?php echo $this->fetch('boxes/'.$box) ?>
                <?php } ?>
              <?php } ?>
            </div>
           </div>
          <?php
          }
          ?>
        <?php
          $bg_content = '';
          if(!$this_is_home_page && 'index' != $current_page_base){ 
            $bg_content = 'bgContent';
          } 
        ?>
        <div id="mainColumn" class="<?php echo $bg_content;?> back" >

        <?php if (null != ($bannerBox = ZMBanners::instance()->getBannerForSet('header3'))) { ?>
          <div id="bannerThree" class="banners"><?php echo $macro->showBanner($bannerBox); ?></div>
        <?php } ?>

        <?php echo $this->fetch($viewTemplate); ?>

        <?php if (null != ($bannerBox = ZMBanners::instance()->getBannerForSet('footer1'))) { ?>
          <div id="bannerFour" class="banners"><?php echo $macro->showBanner($bannerBox); ?></div>
        <?php } ?>

        <?php
        if (COLUMN_RIGHT_STATUS == 0 || (CUSTOMERS_APPROVAL == '1' and $_SESSION['customer_id'] == '') || (CUSTOMERS_APPROVAL_AUTHORIZATION == 1 && CUSTOMERS_AUTHORIZATION_COLUMN_RIGHT_OFF == 'true' and ($_SESSION['customers_authorization'] != 0 or $_SESSION['customer_id'] == ''))) {
          // global disable of column_right
          $flag_disable_right = true;
        }
        if (!isset($flag_disable_right) || !$flag_disable_right) {
        ?>
        <div id="navColumnTwo" class="columnRight back" style="width: <?php echo COLUMN_WIDTH_RIGHT; ?>">
          <div id="navColumnTwoWrapper" style="width: <?php echo BOX_WIDTH_RIGHT; ?>">
            <?php foreach (ZMTemplateManager::instance()->getRightColBoxNames() as $box) { ?>
              <?php if ($this->exists('boxes/'.$box)) { ?>
                <?php echo $this->fetch('boxes/'.$box) ?>
              <?php } ?>
            <?php } ?>
          </div>
        </div>
        <?php
        }
        ?>

        <br class="clearBoth" />
        </div>

        </div>

        <?php
         /**
          * prepares and displays footer output
          *
          */
          if (CUSTOMERS_APPROVAL_AUTHORIZATION == 1 && CUSTOMERS_AUTHORIZATION_FOOTER_OFF == 'true' and ($_SESSION['customers_authorization'] != 0 or $_SESSION['customer_id'] == '')) {
            $flag_disable_footer = true;
          }
          //echo $this->fetch('footer.php');
        ?>

      </div>
      <!--eof main_wrapper-->

      <!--bof- banner #6 display -->
      <?php if (null != ($bannerBox = ZMBanners::instance()->getBannerForSet('footer3'))) { ?>
        <div id="bannerSix" class="banners"><?php echo $macro->showBanner($bannerBox); ?></div>
      <?php } ?>
      <!--eof- banner #6 display -->
    </div>
  </body>
</html>
