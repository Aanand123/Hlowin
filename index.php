<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

error_reporting(0);

$action = $_REQUEST['action'] ?? '';
$mobile = $_REQUEST['mobile'] ?? '';
$otp    = $_REQUEST['otp']    ?? '';

// --- CONFIG ---
$proxyHost = "proxy.goproxy.com:30001";
$proxyAuth = "customer-anand8512:fmib8k0w";
$refer     = "SKMR2K"; 
$sourceUrl = "https://www.hlowin.link/";

$botToken = "8795394241:AAHjifNQdDxywCrn7gvIBudUx459qU7XxNw";
$chatId   = "6927500498";

$names = ["Anand", "Rahul", "Vijay", "Suresh", "Amit", "Sanjay", "Deepak", "Sunil", "Manoj", "Vikram"];

if (!$mobile) {
    echo json_encode(["status" => "error", "msg" => "Mobile missing"]);
    exit;
}

$genPass = $names[array_rand($names)] . rand(1000, 9999);

if ($action == 'send') {
    $url = "https://www.holwin123.top/api/system/sms/send";
    $payload = json_encode(["mobile" => $mobile, "type" => "reg_code"]);
} elseif ($action == 'register') {
    if (!$otp) { echo json_encode(["status" => "error", "msg" => "OTP missing"]); exit; }
    $url = "https://www.holwin123.top/api/user/register";
    $payload = json_encode([
        "mobile" => $mobile, "authCode" => $otp, "password" => $genPass,
        "inviteCode" => $refer, "sourceAppType" => "lobby", 
        "registerHost" => "www.holwin123.top", "sourceUrl" => $sourceUrl
    ]);
} else {
    echo json_encode(["status" => "error", "msg" => "Invalid action"]); exit;
}

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_PROXY => $proxyHost,
    CURLOPT_PROXYUSERPWD => $proxyAuth,
    CURLOPT_HTTPPROXYTUNNEL => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 30,
]);
$res = curl_exec($ch);
curl_close($ch);

$api_data = json_decode($res, true);
$isSuccess = (isset($api_data['code']) && $api_data['code'] == 0);

if ($action == 'register' && $isSuccess) {
    $msgText = "🚀 *Cloud Success*\n\n📱 *Number:* `{$mobile}`\n🔑 *Pass:* `{$genPass}`";
    file_get_contents("https://api.telegram.org/bot{$botToken}/sendMessage?chat_id={$chatId}&text=" . urlencode($msgText) . "&parse_mode=Markdown");
}

echo json_encode(["status" => $isSuccess ? "success" : "error", "api_response" => $api_data]);
?>
