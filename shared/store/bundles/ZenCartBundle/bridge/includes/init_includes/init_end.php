<?php // holding pen for stuff we want to move elsewhere!
if ($spider_flag == false) {
// counter and counter history
  require(DIR_WS_INCLUDES . 'counter.php');
}
// get customers unique IP that paypal does not touch
$customers_ip_address = $_SERVER['REMOTE_ADDR'];
if (!isset($_SESSION['customers_ip_address'])) {
  $_SESSION['customers_ip_address'] = $customers_ip_address;
}
