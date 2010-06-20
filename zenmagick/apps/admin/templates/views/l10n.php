<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
?>
<?php
  //TODO: move to controller or some central logic
  $currentLanguage = ZMLanguages::instance()->getLanguageForId($session->getValue('languages_id'));
  $selectedLanguageId = $request->getParameter('languageId', $currentLanguage->getId());
?>
<form action="<?php echo $admin2->url() ?>" method="POST">
  <h2>ZenMagick Language Tool (
          <select id="languageId" name="languageId">
            <?php foreach (ZMLanguages::instance()->getLanguages() as $lang) { ?>
              <?php $selected = $selectedLanguageId == $lang->getId() ? ' selected="selected"' : ''; ?>
              <option value="<?php echo $lang->getId() ?>"<?php echo $selected ?>><?php echo $lang->getName() ?></option>
            <?php } ?>
          </select>
        )
  </h2>

  <p>This tool helps you find language strings in your themes. Just select a theme and you will 
    get a full list of all strings and where they are used.</p>
  <p>The selected mapping can also be downloaded in a format that you can cut'n paste right into your <code>l10n.php</code> file.</p>
  <p>Inherited mappings are mappings defined in <code>l10n.php</code> files in themes further up the theme chain.</p>
  <p><strong>NOTE:</strong> '%s' and other strings starting with '%' are used as placeholders for things like order numbers, etc.</p>

  <fieldset>
    <legend>Select Theme to display the language mappings</legend>
    <select id="themeId" name="themeId" onchange="this.form.submit()">
      <option value="">Select Theme</option>
        <?php foreach ($themes as $theme) { ?>
        <?php $selected = $themeId == $theme->getThemeId() ? ' selected="selected"' : ''; ?>
        <option value="<?php echo $theme->getThemeId(); ?>"<?php echo $selected ?>><?php echo $theme->getName(); ?></option>
      <?php } ?>
    </select>
    <br>
    <input type="checkbox" id="includeDefaults" name="includeDefaults" value="true"<?php echo ($includeDefaults?' checked="checked"':'')?>><label for="includeDefaults">Add default theme</label><br>
    <input type="checkbox" id="mergeExisting" name="mergeExisting" value="true"<?php echo ($mergeExisting?' checked="checked"':'')?>><label for="mergeExisting">Add/merge with existing mappings</label><br>
    <input type="checkbox" id="scanShared" name="scanShared" value="true"<?php echo ($scanShared?' checked="checked"':'')?>><label for="scanShared">Add shared code strings</label><br>
    <input type="checkbox" id="scanPlugins" name="scanPlugins" value="true"<?php echo ($scanPlugins?' checked="checked"':'')?>><label for="scanPlugins">Add plugin(s) code strings</label><br>
    <input type="submit" value="Display Mapping">
  </fieldset>
</form>
<?php if (isset($translations)) { ?>
  <a href="<?php echo $admin2->url(null, 'download=full&amp;theme='.$themeId.'&amp;includeDefaults='.$includeDefaults.'&amp;mergeExisting='.$mergeExisting.'&amp;scanShared='.$scanShared) ?>">Download mapping</a>
  <?php foreach ($translations as $file => $strings) { ?>
    <h3><?php echo $file ?></h3>
    <?php foreach ($strings as $key => $value) { ?>
      &nbsp;&nbsp;'<?php echo $key ?>' =&gt; '<?php echo $value ?>';<br>
    <?php } ?>
  <?php } ?>
<?php } ?>
