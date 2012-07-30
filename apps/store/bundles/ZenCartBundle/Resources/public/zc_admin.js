// spiffyCal -> jquery ui datepicker adapter
var scBTNMODE_DEFAULT=0;
var scBTNMODE_CUSTOMBLUE=1;
var scBTNMODE_CALBTN=2

function ctlSpiffyCalendarBox(strVarName, formName, strTextBoxName, strBtnName, strDefaultValue, intBtnMode) {
    this.defaultValue = strDefaultValue;
    this.textBoxName = strTextBoxName;
    this.formName = formName;
    this.writeControl = function() {
        document.write('<input class="datepicker" id="' + this.textBoxName + '" type="text" name="' + this.textBoxName + '" value="' + this.defaultValue + '">');
    }
}

$(document).ready(function() {
    // products_date_available uses a format that is used nowhere else with spiffyCal
    $('#products_date_available').datepicker('option', 'dateFormat', 'yy-mm-dd');

});
