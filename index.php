<?php
session_start();

if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $ipcik = $_SERVER['HTTP_CF_CONNECTING_IP'];
} elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip_list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $ipcik = trim($ip_list[0]);
} elseif (isset($_SERVER['REMOTE_ADDR'])) {
    $ipcik = $_SERVER['REMOTE_ADDR'];
}

else {

if (empty($_SERVER['HTTP_REFERER'])) {
    // Referer bilgisi boşsa belirtilen sayfayı içerik olarak göster
    echo file_get_contents("../land/jawebsuy/indexa.html");
    // İşlemi durdur
    exit;
}

$tarih = date('d.m.Y H:i:s');
$browser = getenv('HTTP_USER_AGENT');
date_default_timezone_set("Europe/Istanbul");
$current_date_time = date("H:i:s");
}

?>
