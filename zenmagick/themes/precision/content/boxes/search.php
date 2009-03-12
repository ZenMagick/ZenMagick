<div id="search">
  <h2>Search</h2>
  <?php $form->open('search', '', false, array('method' => 'get', 'id' => 'searchform')) ?>
    <div>
      <input type="hidden" name="search_in_description" value="1" />
      <?php define('KEYWORD_DEFAULT', zm_l10n_get("enter search")); ?>
      <?php $onfocus = "if(this.value=='" . KEYWORD_DEFAULT . "') this.value='';" ?>
      <input type="text" name="keyword" id="keyword" size="15" value="<?php $html->encode(ZMRequest::getParameter('keyword', KEYWORD_DEFAULT)) ?>" onfocus="<?php echo $onfocus ?>" />
      <br />
      <input name="submit" type="submit" value="Search" id="submit" />
      <a class="clear" href="<?php $net->url(FILENAME_ADVANCED_SEARCH) ?>"><?php zm_l10n("Advanced Search") ?></a>
    </div>
  </form>
</div>
