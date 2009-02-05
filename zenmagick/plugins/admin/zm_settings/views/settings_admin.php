<h1>Settings</h1>

<script type="text/javascript">
    var $widgetTemplates = [];
    // use jquery to toggle value element
</script>

<?php $toolbox->form->open('', 'fkt=settings_admin', false, array('id'=>'settings_form')) ?>
    <fieldset>
        <legend>Settings</legend>
        <?php foreach ($plugin->getConfigValues(false) as $value) { ?>
            <?php if ($value instanceof ZMWidget) { ?>
                <p><?php echo $value->getTitle() ?>: <?php echo $value->render(); ?></p>
            <?php } ?>
        <?php } ?>
    </fieldset>
    <fieldset>
        <legend>New Setting</legend>
        <p>Type: <select name="type">
            <option value="TextFormWidget">Text</option>
            <option value="BooleanFormWidget">Boolean</option>
        </select></p>
        <p>Value: <input type="text" name="value"></p>
        <p>Title: <input type="text" name="title"></p>
        <p>Descritpion: <input type="text" name="description"></p>
        <input type="submit" value="create">
    </fieldset>
</form>
