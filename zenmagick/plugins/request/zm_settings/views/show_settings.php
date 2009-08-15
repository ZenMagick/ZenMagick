<h1>Show Settings</h1>

<?php 

foreach ($settingDetails as $group => $groupDetails) { 
    echo '<h2>',$group,'</h2>';
    foreach ($groupDetails as $sub => $subDetails) {
        echo '<h3>',$sub,'</h3>';
        echo '<table width="98%" border="1">';
        foreach ($subDetails as $details) {
            echo '<tr>';
            echo '<td width="32%">', $details['desc'], '</td>';
            echo '<td width="28%">', $details['fullkey'], '</td>';
            echo '<td>', $details['value'], '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}

?>
