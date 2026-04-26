<?php
// Cloudflare/Vercel CORS Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// Error reporting off taaki output clean rahe
error_reporting(0);

// Parameters input
$action = $_REQUEST['action'] ?? '';
$mobile = $_REQUEST['mobile'] ?? '';
$otp    = $_REQUEST['otp']    ?? '';

// --- CONFIGURATION ---
$proxyHost = "proxy.goproxy.com:30001";
$proxyAuth = "customer-anand8512:fmib8k0w";
$refer     = "SKMR2K"; 
$sourceUrl = "https://www.hlowin.link/";

// Telegram Bot Details
$botToken = "8795394241:AAHjifNQdDxywCrn7gvIBudUx459qU7XxNw";
$chatId   = "6927500498";

// Random Indian Names for Passwords
$names = ["Anand", "Rahul", "Vijay", "Suresh", "Amit", "Sanjay", "Deepak", "Sunil", "Manoj", "Vikram", "Aaryan", "Abhishek", "Aditya", "Akash", "Aniket", "Ansh", "Arjun", "Ayush", "Bhavin", "Chirag", "Darshan", "Dev", "Dhruv", "Gaurav", "Harsh", "Ishaan", "Jatin", "Karan", "Kunal", "Lucky", "Manish", "Mayur", "Mohit", "Naveen", "Nikhil", "Nitin", "Pankaj", "Prateek", "Priyanshu", "Raj", "Rajat", "Rishi", "Rohan", "Sahil", "Sameer", "Shivam", "Shubham", "Siddharth", "Sumit", "Tushar", "Umesh", "Varun", "Vishal", "Yash", "Yuvraj"];

if (!$mobile) {
    echo json_encode(["status" => "error", "msg" => "Mobile missing"]);
    exit;
}

$genPass = $names[array_rand($names)] . rand(1000, 9999);

// --- API ACTIONS ---
if ($action == 'send') {
    $url = "https://www.holwin123.top/api/system/sms/send";
    $payload = json_encode(["mobile" => $mobile, "type" => "reg_code"]);
} elseif ($action == 'register') {
    if (!$otp) { echo json_encode(["status" => "error", "msg" => "OTP missing"]); exit; }
    $url = "https://www.holwin123.top/api/user/register";
    $payload = json_encode([
        "mobile" => $mobile, 
        "authCode" => $otp, 
        "password" => $genPass,
        "inviteCode" => $refer, 
        "sourceAppType" => "lobby", 
        "registerHost" => "www.holwin123.top", 
        "sourceUrl" => $sourceUrl
    ]);
} else {
    echo json_encode(["status" => "error", "msg" => "Invalid action"]); 
    exit;
}

// 1. Proxy IP Check (Registration se pehle check karte hain)
$ch_ip = curl_init("http://ipinfo.io/json");
curl_setopt_array($ch_ip, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_PROXY          => $proxyHost,
    CURLOPT_PROXYUSERPWD   => $proxyAuth,
    CURLOPT_PROXYTYPE      => CURLPROXY_HTTP,
    CURLOPT_HTTPPROXYTUNNEL => true,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_CONNECTTIMEOUT => 5,
]);
$ipRes = curl_exec($ch_ip);
$ipData = json_decode($ipRes, true);
$regIP = $ipData['ip'] ?? 'Proxy Connected';
curl_close($ch_ip);

// 2. Main API Request (SMS/Register)
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_PROXY          => $proxyHost,
    CURLOPT_PROXYUSERPWD   => $proxyAuth,
    CURLOPT_HTTPPROXYTUNNEL => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT        => 30,
]);
$res = curl_exec($ch);
curl_close($ch);

$api_data = json_decode($res, true);
$isSuccess = (isset($api_data['code']) && $api_data['code'] == 0);

// --- TELEGRAM LOGIC ---
if ($action == 'register' && $isSuccess) {
    $msgText = "🚀 *Hlowin Registration Success*\n\n"
             . "📱 *Number:* `{$mobile}`\n"
             . "🔑 *Password:* `{$genPass}`\n"
             . "🌍 *Proxy IP:* `{$regIP}`\n"
             . "🎟️ *Refer Code:* `{$refer}`\n"
             . "📄 *API Msg:* " . ($api_data['msg'] ?? 'Success');
    
    file_get_contents("https://api.telegram.org/bot{$botToken}/sendMessage?chat_id={$chatId}&text=" . urlencode($msgText) . "&parse_mode=Markdown");
}

// Final Output to Browser
echo json_encode([
    "status" => $isSuccess ? "success" : "error",
    "proxy_ip" => $regIP,
    "api_response" => $api_data
]);
?>
