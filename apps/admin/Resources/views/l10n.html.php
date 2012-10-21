<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
 */ $admin->title(_zm('Translation Helper')) ?>
<form action="<?php echo $net->url() ?>" method="POST">
  <h2>ZenMagick Language Tool (
          <select id="languageId" name="languageId">
            <?php foreach ($this->container->get('languageService')->getLanguages() as $lang) { ?>
              <?php $selected = $currentLanguage->getId() == $lang->getId() ? ' selected="selected"' : ''; ?>
              <option value="<?php echo $lang->getId() ?>"<?php echo $selected ?>><?php echo $lang->getName() ?></option>
            <?php } ?>
          </select>
        )
  </h2>

  <p>This tool helps you find language strings in your themes and everywhere else in the app. Just select a theme and you will
    get a full list of all strings and where they are used.</p>
  <p>The selected mapping can also be downloaded in a format that you can cut'n paste right into your <code>l10n.yaml</code> file.</p>
  <p>Inherited mappings are mappings defined in <code>l10n.yaml</code> files in themes further up the theme chain.</p>
  <p><strong>NOTE:</strong> '%s' and other strings starting with '%' are used as placeholders for things like order numbers, etc.</p>

  <fieldset>
    <legend>Select theme and other options to display the language mappings</legend>
    <select id="themeId" name="themeId">
      <option value="">Select Theme</option>
        <?php foreach ($themes as $theme) { ?>
        <?php $selected = $themeId == $theme->getId() ? ' selected="selected"' : ''; ?>
        <option value="<?php echo $theme->getId(); ?>"<?php echo $selected ?>><?php echo $theme->getName(); ?></option>
      <?php } ?>
    </select>
    <br>
    <select id="source" name="source">
      <option value="">-- Preset --</option>
        <?php foreach ($sources as $value => $text) { ?>
        <?php $selected = $source == $value ? ' selected="selected"' : ''; ?>
        <option value="<?php echo $value ?>"<?php echo $selected ?>><?php echo $text ?></option>
      <?php } ?>
    </select>
    <br>
    <p>Chose individual settings</p>
    <input type="checkbox" id="includeDefaults" name="includeDefaults" value="true"<?php echo ($includeDefaults?' checked="checked"':'')?>><label for="includeDefaults">Add default theme</label><br>
    <input type="checkbox" id="mergeExisting" name="mergeExisting" value="true"<?php echo ($mergeExisting?' checked="checked"':'')?>><label for="mergeExisting">Add/merge with existing mappings</label><br>
    <input type="checkbox" id="scanShared" name="scanShared" value="true"<?php echo ($scanShared?' checked="checked"':'')?>><label for="scanShared">Add shared code strings</label><br>
    <input type="checkbox" id="scanPlugins" name="scanPlugins" value="true"<?php echo ($scanPlugins?' checked="checked"':'')?>><label for="scanPlugins">Add plugin(s) code strings</label><br>
    <input type="checkbox" id="scanAdmin" name="scanAdmin" value="true"<?php echo ($scanAdmin?' checked="checked"':'')?>><label for="scanAdmin">Add admin code strings</label><br>
<?php /*
*/ ?>
    <input type="submit" class="<?php echo $buttonClasses ?>" value="Display Mapping">
  </fieldset>
</form>
<?php if (isset($translations)) { ?>
  <strong>Download: </strong>
  <a href="<?php echo $net->url(null, $downloadParamsPo) ?>">.po</a>
  <a href="<?php echo $net->url(null, $downloadParamsPot) ?>">.pot</a>
  <?php foreach ($translations as $file => $strings) { ?>
    <h3><?php echo $file ?></h3>
    <?php foreach ($strings as $key => $info) { ?>
      &nbsp;&nbsp;'<?php echo $key ?>' =&gt; '<?php echo $info['msg'] ?>';<br>
    <?php } ?>
  <?php } ?>
<?php } ?>
