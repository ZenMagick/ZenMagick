<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <!-- Theme created by Erwin Aligam - styleshout.com -->
    <?php echo $this->fetch('head.php') ?>
    <?php $resourceManager->cssFile('css/screen.css', array('media' => 'screen')) ?>
    <?php $resourceManager->cssFile('css/theme.css', array('media' => 'screen')) ?>
    <?php $pageCSS = "css/".$request->getRequestId().".css"; ?>
    <?php /* page specific CSS */ ?>
    <?php if ($this->exists($pageCSS)) { ?>
        <?php $resourceManager->cssFile($pageCSS, array('media' => 'screen')) ?>
    <?php } ?>
  </head>
  <body>
    <!-- header starts-->
    <div id="header-wrap"><div id="header" class="container_16">

      <h1 id="logo-text"><a href="<?php echo $net->url('index') ?>" title="">ZenMagick</a></h1>
      <p id="intro">As simple as that!</p>

      <!-- navigation -->
      <div  id="nav">
        <?php echo $this->fetch('top-menu.php') ?>
      </div>

      <div id="header-image"></div>

      <?php echo $form->open('search', '', false, array('method' => 'get', 'id' => 'quick-search')) ?>
        <p>
        <label for="qsearch">Search:</label>
        <?php define('KEYWORD_DEFAULT', _zm("search ...")); ?>
        <?php $onfocus = "if(this.value=='" . KEYWORD_DEFAULT . "') this.value='';" ?>
        <input class="tbox" id="qsearch" type="text" name="keywords" value="<?php echo $html->encode($request->getParameter('keywords', KEYWORD_DEFAULT)) ?>" onfocus="<?php echo $onfocus ?>" title="Start typing and hit ENTER" />
        <input class="btn" alt="Search" type="image" name="searchsubmit" title="Search" src="<?php echo $this->asUrl("images/search.gif") ?>" />
        </p>
      </form>

    <!-- header ends here -->
    </div></div>

    <!-- content starts -->
    <div id="content-outer"><div id="content-wrapper" class="container_16">

      <!-- main -->
      <div id="main" class="grid_8">
          <div id="crumbtrail"><?php echo $this->fragment('crumbtrail') ?></div>

          <?php if ($messageService->hasMessages()) { ?>
              <ul id="messages">
              <?php foreach ($messageService->getMessages() as $message) { ?>
                  <li class="<?php echo $message->getType() ?>"><?php echo $message->getText() ?></li>
              <?php } ?>
              </ul>
          <?php } ?>

          <?php echo $this->fetch($viewTemplate); ?>
          <?php $this->fragment('crumbtrail', $macro->buildCrumbtrail($crumbtrail, " &gt; ")) ?>
      <!-- main ends -->
      </div>

      <!-- left-columns starts -->
      <div id="left-columns" class="grid_8">

        <div class="grid_4 alpha">

        <div id="sidebar" >
          <?php echo $this->fetchBlockGroup('leftColumn', array('format' => '<div class="sidebox">%s</div>')) ?>
        </div>

        </div>

        <div class="grid_4 omega">
          <?php echo $this->fetchBlockGroup('rightColumn') ?>
        </div>

      <!-- end left-columns -->
      </div>

    <!-- contents end here -->
    </div></div>

    <!-- footer starts here -->
    <div id="footer-wrapper" class="container_16">

      <div id="footer-bottom">

        <p class="bottom-left">
        &nbsp; &copy;2008 ZenMagick&nbsp; &nbsp;
        Design by : <a href="http://www.styleshout.com/">styleshout</a>
        </p>

        <p class="bottom-right">
            <?php $first = true; foreach ($container->get('ezPageService')->getPagesForFooter($session->getLanguageId()) as $page) { ?>
                <?php if (!$first) { ?>| <?php } $first = false; ?><?php echo $html->ezpageLink($page->getId()) ?>
            <?php } ?>
        </p>

      </div>

    </div>
    <!-- footer ends here -->

  </body>
</html>
