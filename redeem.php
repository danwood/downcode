<?php
$iOSDevice = false;       // or set to a non-false text value
if (preg_match("/(\\(iPod|\\(iPhone|\\(iPad)/", $_SERVER['HTTP_USER_AGENT'], $matches)) {
    $iOSDevice = substr($matches[1], 1);
}
$code = isset($_POST['code']) ? htmlspecialchars($_POST['code']) : '';
$email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';

echo "<h1>submit code</h1><p>$code with email address: $email</p>";

?>