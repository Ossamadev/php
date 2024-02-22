<?php

// استقبال البيانات من الريكويست
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// تشفير كلمة المرور
$current_time = time();
$encrypted_password = '#PWD_INSTAGRAM_BROWSER:0:' . $current_time . ':' . $password;

// بيانات الطلب
$data = array(
    'enc_password' => $encrypted_password,
    'optIntoOneTap' => false,
    'queryParams' => array(),
    'trustedDeviceRecords' => array(),
    'username' => $username
);

// تحويل البيانات إلى تنسيق x-www-form-urlencoded
$post_data = http_build_query($data);

// روابط الريكويست
$url = 'https://www.instagram.com/api/v1/web/accounts/login/ajax/';

// إعداد الـ cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true); // تمكين طباعة الرؤوس

// إعداد الهيدرات
$headers = array(
    'Accept: */*',
    'Accept-Encoding: gzip, deflate, br',
    'Accept-Language: ar-MA,ar;q=0.9,en-GB;q=0.8,en;q=0.7,en-US;q=0.6,fr;q=0.5',
    'Content-Type: application/x-www-form-urlencoded',
    'Dpr: 1',
    'Origin: https://www.instagram.com',
    'Referer: https://www.instagram.com/',
    'Sec-Ch-Prefers-Color-Scheme: light',
    'Sec-Ch-Ua: "Not_A Brand";v="8", "Chromium";v="120", "Google Chrome";v="120"',
    'Sec-Ch-Ua-Full-Version-List: "Not_A Brand";v="8.0.0.0", "Chromium";v="120.0.6099.225", "Google Chrome";v="120.0.6099.225"',
    'Sec-Ch-Ua-Mobile: ?0',
    'Sec-Ch-Ua-Model: ""',
    'Sec-Ch-Ua-Platform: "Windows"',
    'Sec-Ch-Ua-Platform-Version: "3.0.0"',
    'Sec-Fetch-Dest: empty',
    'Sec-Fetch-Mode: cors',
    'Sec-Fetch-Site: same-origin',
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Viewport-Width: 912',
    'X-Asbd-Id: 129477',
    'X-Csrftoken: HJHvrNHcHgUeOolh6pM4Iell3MA2CCZ2',
    'X-Ig-App-Id: 936619743392459',
    'X-Ig-Www-Claim: hmac.AR18JMsXZ9XZ5S-uexs8bTMUaEHdcs9pDvqGx6HUY8IxPwla',
    'X-Instagram-Ajax: 1010936309',
    'X-Requested-With: XMLHttpRequest'
);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// إرسال الريكويست والحصول على الرد
$response = curl_exec($ch);

// جلب الكوكيز
$headerLines = explode("\r\n", $response);
$cookies = array();

foreach ($headerLines as $header) {
    if (strpos($header, 'set-cookie:') !== false) {
        $cookieLine = trim(str_replace('set-cookie:', '', $header));
        // اقتصاص المحتوى قبل علامة الفاصلة وتحويله إلى JSON
        $cookieValue = strtok($cookieLine, ';');
        list($key, $value) = explode("=", $cookieValue, 2);
        if (in_array($key, ['csrftoken', 'ds_user_id', 'sessionid'])) {
            $cookies[$key] = $value;
        }
    }
}

// تحديد الكوكيز المطلوبة
$filteredCookies = array(
    'csrftoken' => isset($cookies['csrftoken']) ? $cookies['csrftoken'] : '',
    'ds_user_id' => isset($cookies['ds_user_id']) ? $cookies['ds_user_id'] : '',
    'sessionid' => isset($cookies['sessionid']) ? $cookies['sessionid'] : ''
);

// تحديد قيمة الـ status
$status = !empty($filteredCookies['csrftoken']) && !empty($filteredCookies['ds_user_id']) && !empty($filteredCookies['sessionid']);

// إضافة العنصر status إلى المصفوفة
$filteredCookies['status'] = $status;

// إعداد المصفوفة النهائية
$result = array(
    'status' => $status,
    'csrftoken' => $filteredCookies['csrftoken'],
    'ds_user_id' => $filteredCookies['ds_user_id'],
    'sessionid' => $filteredCookies['sessionid']
);

// طباعة المصفوفة بتنسيق JSON
echo json_encode($result);

// إغلاق الاتصال
curl_close($ch);



