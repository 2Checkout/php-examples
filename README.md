2Checkout Payment API PHP Tutorial
----------------------------------------

In this tutorial we will walk through integrating the 2Checkout Payment API using the PHP library. You will need a 2Checkout sandbox account to complete the tutorial so if you have not already, [signup for an account](https://sandbox.2checkout.com/sandbox/signup) and [generate your Payment API keys](https://www.2checkout.com/documentation/sandbox/payment-api-testing).

Application Setup
----------------------------------

The first thing that you will need to do is create a new directory to host this application, as well as two PHP files. We recommend calling the directory "payment-api" and the files "index.html" and "payment.php". For the remainder of this tutorial, we will be referring to the files and directory using this naming convention.

2Checkouts PHP library provides us with a simple bindings to the API, INS and Checkout process so that we can integrate each feature with only a few lines of code. In this example, we will only be using the Payment API functionality of the library, but for an example of the other features.you can view this tutorial: [https://github.com/2Checkout/2checkout-ci-tutorial](https://github.com/2Checkout/2checkout-ci-tutorial)

Download or clone the 2Checkout PHP library at [https://github.com/2checkout/2checkout-php](https://github.com/2checkout/2checkout-php).

Including the library is as easy as copying contents of the **lib** directory into the 'payment-api' directory that we created earlier. 

We will need to reference this library in the payment.php file to be able to use it.

```
require_once("lib/Twocheckout.php");
```



Creating a Token
--------
Open the 'index.html' file that we created, and add a basic credit card form that allows our buyer to enter in their card number, expiration month and year and CVC.

```
<form id="myCCForm" action="payment.php" method="post">
    <input id="token" name="token" type="hidden" value="">
    <div>
        <label>
            <span>Card Number</span>
        </label>
        <input id="ccNo" type="text" size="20" value="" autocomplete="off" required />
    </div>
    <div>
        <label>
            <span>Expiration Date (MM/YYYY)</span>
        </label>
        <input type="text" size="2" id="expMonth" required />
        <span> / </span>
        <input type="text" size="2" id="expYear" required />
    </div>
    <div>
        <label>
            <span>CVC</span>
        </label>
        <input id="cvv" size="4" type="text" value="" autocomplete="off" required />
    </div>
    <input type="submit" value="Submit Payment">
</form>
```

Notice that we have a no 'name' attributes on the input elements that collect the credit card information. This will ensure that no sensitive card data touches your server when the form is submitted. Also, we include a hidden input element for the token which we will submit to our server to make the authorization request.

Now we can add our JavaScript to make the token request call. Replace 'sandbox-seller-id' and 'sandbox-publishable-key' with your credentials.

```
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script src="https://www.2checkout.com/checkout/api/2co.min.js"></script>

<script>
    // Called when token created successfully.
    var successCallback = function(data) {
        var myForm = document.getElementById('myCCForm');

        // Set the token as the value for the token input
        myForm.token.value = data.response.token.token;

        // IMPORTANT: Here we call `submit()` on the form element directly instead of using jQuery to prevent and infinite token request loop.
        myForm.submit();
    };

    // Called when token creation fails.
    var errorCallback = function(data) {
        if (data.errorCode === 200) {
            tokenRequest();
        } else {
            alert(data.errorMsg);
        }
    };

    var tokenRequest = function() {
        // Setup token request arguments
        var args = {
            sellerId: "sandbox-seller-id",
            publishableKey: "sandbox-publishable-key",
            ccNo: $("#ccNo").val(),
            cvv: $("#cvv").val(),
            expMonth: $("#expMonth").val(),
            expYear: $("#expYear").val()
        };

        // Make the token request
        TCO.requestToken(successCallback, errorCallback, args);
    };

    $(function() {
        // Pull in the public encryption key for our environment
        TCO.loadPubKey('sandbox');

        $("#myCCForm").submit(function(e) {
            // Call our token request function
            tokenRequest();

            // Prevent form from submitting
            return false;
        });
    });
</script>
```

Let's take a second to look at what we did here. First we pulled in a jQuery library to help us with manipulating the document.
(The 2co.js library does NOT require jQuery.)

Next we pulled in the 2co.js library so that we can make our token request with the card details.

```	
<script src="https://www.2checkout.com/checkout/api/2co.min.js"></script>
```

This library provides us with 2 functions, one to load the public encryption key, and one to make the token request.

The `TCO.loadPubKey(String environment, Function callback)` function must be used to asynchronously load the public encryption key for the 'production' or 'sandbox' environment. In this example, we are going to call this as soon as the document is ready so it is not necessary to provide a callback.

```
TCO.loadPubKey('sandbox');
```

The the 'TCO.requestToken(Function callback, Function callback, Object arguments)' function is used to make the token request. This function takes 3 arguments:

* Your success callback function which accepts one argument and will be called when the request is successful.
* Your error callback function which accepts one argument and will be called when the request results in an error.
* An object containing the credit card details and your credentials.
    * **sellerId** : 2Checkout account number
    * **publishableKey** : Payment API publishable key
    * **ccNo** : Credit Card Number
    * **expMonth** : Card Expiration Month
    * **expYear** : Card Expiration Year
    * **cvv** : Card Verification Code

```
TCO.requestToken(successCallback, errorCallback, args);
```

In our example we created 'tokenRequest' function to setup our arguments by pulling the values entered on the credit card form and we make the token request.

```
var tokenRequest = function() {
    // Setup token request arguments
    var args = {
        sellerId: "sandbox-seller-id",
        publishableKey: "sandbox-publishable-key,
        ccNo: $("#ccNo").val(),
        cvv: $("#cvv").val(),
        expMonth: $("#expMonth").val(),
        expYear: $("#expYear").val()
    };

    // Make the token request
    TCO.requestToken(successCallback, errorCallback, args);
};
```

We then call this function from a submit handler function that we setup on the form.

```
$("#myCCForm").submit(function(e) {
    // Call our token request function
    tokenRequest();

    // Prevent form from submitting
    return false;
});
```

The 'successCallback' function is called if the token request is successful. In this function we set the token as the value for our 'token' hidden input element and we submit the form to our server.

```
var successCallback = function(data) {
    var myForm = document.getElementById('myCCForm');

    // Set the token as the value for the token input
    myForm.token.value = data.response.token.token;

    // IMPORTANT: Here we call `submit()` on the form element directly instead of using jQuery to prevent and infinite token request loop.
    myForm.submit();
};
```

The 'errorCallback' function is called if the token request fails. In our example function, we check for error code 200, which indicates that the ajax call has failed. If the error code was 200, we automatically re-attempt the tokenization, otherwise, we alert with the error message.

```
var errorCallback = function(data) {
    if (data.errorCode === 200) {
        tokenRequest();
    } else {
        alert(data.errorMsg);
    }
};
```

Create the Sale
--------

Once the credit card information has been tokenized and passed to the correct page, we can make the authorization call. For this example, this will take place in the payment.php file.

First thing is to include the PHP library and set your credentials. Replace 'sandbox-seller-id' and 'sandbox-private-key' with your credentials.

```
require_once("2checkout-php/lib/Twocheckout.php");

Twocheckout::privateKey('sandbox-private-key'); 
Twocheckout::sellerId('sandbox-seller-id); 
Twocheckout::sandbox(true);
```

These values are the credentials from your sandbox test account. To use with your production account, you would need to use your production credentials, and set the sandbox value to false.

Then, we'll need to create the array with our sale parameters, and submit it for authorization. For this example, we are creating an intangible order for $10.

```
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
```

Notice that we are using the token that is being posted to this page. Most likely, you would also want to populate the rest of these parameters from your customer's information but for the sake of this tutorial, we have hard coded the values in.

Once the authorization call has been made, we check if the authorization is successful. If it is, we are outputting a message to the buyer stating this fact. In this example we are also outputting all of the return parameters from the sale so you can view the return structure.

2Checkout also can throw errors from the authorization call, so the entire call is included in a try-catch block, to find and handle any authorization errors.

**Important note: a token can only be used for one authorization call, and will expire after 30 minutes if not used.**

And that's it! Implement the code as shown, and you should have a working Payment API example.


Run the example application
----

In your browser, navigate to the 'payment-api' directory in either your server or your localhost, and you should see a payment form where you can enter credit card information.

For your testing, you can use these values for a successful authorization
>Credit Card Number: 4000000000000002

>Expiration date: 10/2020

>cvv: 123

And these values for a failed authorization:

>Credit Card Number: 4333433343334333

>Expiration date: 10/2020

>cvv:123

If you have any questions, feel free to send them to techsupport@2co.com

