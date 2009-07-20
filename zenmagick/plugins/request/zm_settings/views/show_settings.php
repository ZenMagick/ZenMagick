<h1>Show Settings</h1>

<?php 

foreach ($settingDetails as $group => $groupDetails) { 
    echo '<h2>',$group,'</h2>';
    foreach ($groupDetails as $sub => $subDetails) {
        echo '<h3>',$sub,'</h3>';
        echo '<table width="98%" border="1">';
        foreach ($subDetails as $details) {
            echo '<tr>';
            echo '<td width="30%">', $details['fullkey'], '</td>';
            echo '<td width="15%">', $details['value'], '</td>';
            echo '<td>', $details['desc'], '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}

?>
