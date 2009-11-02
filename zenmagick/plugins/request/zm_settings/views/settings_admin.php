<h1>Manage Settings</h1>

<script type="text/javascript">
    var $widgetTemplates = [];
    // use jquery to toggle value element
</script>

<?php $toolbox->form->open('', 'fkt=settings_admin', false, array('id'=>'settings_form_create')) ?>
    <fieldset>
        <legend>Create New Setting</legend>
        <p>
            <input type="hidden" name="fkt" value="ZMSettingsAdminController">
            <input type="hidden" name="action" value="create">
            Title: <input type="text" name="title">
            Key: <input type="text" name="key">
            Value: <input type="text" name="value">
            Type: <select name="type">
                <option value="TextFormWidget#">Text</option>
                <option value="PasswordFormWidget#">Password</option>
                <option value="TextAreaFormWidget#cols=80&rows=5">Text Area</option>
                <option value="TextAreaFormWidget#cols=35&rows=2">Text Area (small)</option>
                <option value="BooleanFormWidget#style=select">Boolean (dropdown)</option>
                <option value="BooleanFormWidget#style=radio">Boolean (radio)</option>
                <option value="BooleanFormWidget#style=checkbox">Boolean (checkbox)</option>
                <option value="SelectFormWidget#">Generic Select (dropdown)</option>
                <option value="SelectFormWidget#multiple=true&size=3">Generic Select (dropdown, multiple)</option>
                <option value="ManufacturerSelectFormWidget#">Manufacturer (dropdown)</option>
                <option value="ManufacturerSelectFormWidget#title=None&options=0= --- ">Manufacturer (dropdown, incl. empty default)</option>
                <option value="OrderStatusSelectFormWidget#">Order Status (dropdown)</option>
                <option value="OrderStatusSelectFormWidget#title=None&options=0= --- ">Order Status (dropdown, incl. empty default)</option>
                <option value="CountrySelectFormWidget#">Country (dropdown)</option>
                <option value="CountrySelectFormWidget#title=None&options=0= --- ">Country (dropdown, incl. empty default)</option>
            </select>
        </p>
        <p><input type="submit" value="create"></p>
    </fieldset>
</form>

<?php $toolbox->form->open('', 'fkt=settings_admin', false, array('id'=>'settings_form_update')) ?>
    <fieldset>
        <legend>Current Settings</legend>
        <table>
            <thead>
                <tr>
                    <th>title</th>
                    <th>key</th>
                    <th>value</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($plugin->getConfigValues() as $value) { ?>
                    <?php if ($value instanceof ZMWidget) { ?>
                        <tr>
                            <td><?php echo $value->getTitle() ?></td>
                            <td><?php echo $value->getName() ?></td>
                            <td><?php echo $value->render($request); ?></td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </tbody>
        </table>
        <input type="hidden" name="fkt" value="ZMSettingsAdminController">
        <input type="hidden" name="action" value="update">
        <p><input type="submit" value="update"></p>
    </fieldset>
</form>

