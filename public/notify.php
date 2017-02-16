<?php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Siru\Signature;

/**
 * Siru Reference DemoShop - feel free to modify!
 *
 * If this script is publicly accessible, this file will receive the payment
 * status notification from Siru API.
 */

if(file_exists('../configuration.php') == false) {
    die("<h1>Missing configuration.php</h1>Please copy configuration.php.dist to configuration.php and edit your credentials and phone number there.");
}

if(file_exists('../vendor/autoload.php') == false) {
    die('<h1>Missing autoloader</h1>Please install <a href="https://getcomposer.org">Composer</a> and run <code>composer install</code> to install required dependencies.');
}

require_once('../configuration.php');
require_once('../vendor/autoload.php');
$products = new DemoShop\Products();

// Create a log channel
$log = new Logger('name');
$log->pushHandler(new StreamHandler('../data/logs/notifications.log', \Monolog\Logger::DEBUG));

// We need Siru\Signature to verify that notification is authentic
$signature = new Signature($merchantId, $merchantSecret);

// Get JSON data from POST body
$requestBody = file_get_contents('php://input');
$requestJson = (array)json_decode($requestBody, true);

$log->info('Received notification: ' . $requestBody);

// If notification is authentic, we could update purchase status as completed, canceled or failed
if ($signature->isNotificationAuthentic($requestJson)) {

    $event = $requestJson['siru_event'];
    $product = $products->getProduct($requestJson['siru_purchaseReference']);

    switch ($event) {
        case 'success':
            $logger->info("Notification of successful payment: {$product['name']} ({$product['id']}) was sold for {$product['price']} euros");
            break;

        case 'cancel':
            $logger->info("Notification of canceled payment: {$product['name']} ({$product['id']})");
            break;

        case 'failure':
            $logger->warning("Notification of failed payment: {$product['name']} ({$product['id']})");
            break;

        default:
            $logger->error("Received an unknown notification type!");
    }

}

// Remember to respond with HTTP 200 to notifications
header("HTTP/1.1 200 OK");
