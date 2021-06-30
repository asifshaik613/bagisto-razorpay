### Laravel eCommerce RazorPay Payment Gateway

### 1. Introduction:

Laravel Razorpay payment – An easy way for online payment. Today we are going to introduce Razorpay payment for Laravel.

Razorpay make it easy for customers to pay by accepting the payments they prefer, including major credit cards, signature debit cards.

* Enable/disable payment method from admin panel.
* Provide payment directly to the admin account.
* Accept all the cards that Razorpay supports.

### 2. Requirements:

* **Bagisto**: v1.3.0 to v1.3.1

### 3. Installation:

* Unzip the respective extension zip and then merge "packages" folder into project root directory.

* Goto config/app.php file and add following line under 'providers'

~~~
Razorpay\Providers\RazorpayServiceProvider::class
~~~

* Goto composer.json file and add following line under psr-4

~~~
"Razorpay\\": "packages/Razorpay/src"
~~~

* Run these commands below to complete the setup

~~~
composer dump-autoload
~~~
~~~
composer require razorpay/razorpay:2.*
~~~
~~~
php artisan route:cache
~~~
~~~
php artisan vendor:publish

-> Press 0 and then press enter to publish all assets and configurations.

~~~

> That's it, now just execute the project on your specified domain.



