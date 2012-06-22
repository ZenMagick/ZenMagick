<div id="search">
  <h3><?php _vzm("Quick Search") ?></h3>
  <?php echo $form->open('search', '', false, array('method' => 'get', 'id' => 'searchform')) ?>
    <div>
      <?php $onfocus = "if(this.value=='" . KEYWORD_DEFAULT . "') this.value='';" ?>
      <input type="text" name="keywords" id="keywords" size="15" value="<?php echo $html->encode($request->query->get('keywords', KEYWORD_DEFAULT)) ?>" onfocus="<?php echo $onfocus ?>" />
      <br />
      <input name="submit" type="submit" value="Search" id="submit" />
      <a class="clear" href="<?php echo $net->url('advanced_search') ?>"><?php _vzm("Advanced Search") ?></a>
    </div>
  </form>
</div>
