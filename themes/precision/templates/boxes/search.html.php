<div id="search">
  <h2>Search</h2>
  <?php echo $form->open('search', '', false, array('method' => 'get', 'id' => 'searchform')) ?>
    <div>
      <?php $onfocus = "if(this.value=='" . KEYWORD_DEFAULT . "') this.value='';" ?>
      <input type="text" name="keywords" id="keywords" size="15" value="<?php echo $html->encode($request->query->get('keywords', KEYWORD_DEFAULT)) ?>" onfocus="<?php echo $onfocus ?>" />
      <br />
      <input name="submit" type="submit" value="Search" id="submit" />
      <a class="clear" href="<?php echo $net->generate('advanced_search') ?>"><?php _vzm("Advanced Search") ?></a>
    </div>
  </form>
</div>
