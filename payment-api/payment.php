<?php
require_once("lib/Twocheckout.php");

Twocheckout::privateKey('sandbox-private-key'); //Private Key
Twocheckout::sellerId('sandbox-seller-id'); // 2Checkout Account Number
Twocheckout::sandbox(true); // Set to false for production accounts.

try {
    $charge = Twocheckout_Charge::auth(array(
        "merchantOrderId" => "123",
        "token"      => $_POST['token'],
        "currency"   => 'USD',
        "total"      => '10.00',
        "billingAddr" => array(
            "name" => 'Testing Tester',
            "addrLine1" => '123 Test St',
            "city" => 'Columbus',
            "state" => 'OH',
            "zipCode" => '43123',
            "country" => 'USA',
            "email" => 'example@2co.com',
            "phoneNumber" => '555-555-5555'
        )
    ));

    if ($charge['response']['responseCode'] == 'APPROVED') {
        echo "Thanks for your Order!";
        echo "<h3>Return Parameters:</h3>";
        echo "<pre>";
        print_r($charge);
        echo "</pre>";

    }
} catch (Twocheckout_Error $e) {
    print_r($e->getMessage());
}