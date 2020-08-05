<?php
use Siru\Signature;
use Siru\API;
use Siru\DemoShop\Products;

/**
 * Siru Reference DemoShop - feel free to modify!
 *
 * NOTICE!
 * If you want SiruMobile to be able to notify your application after a successful/failed/cancelled purchase,
 * you must provide public URL as notifyAfterX arguments.
 */

if(file_exists('../configuration.php') == false) {
    die("<h1>Missing configuration.php</h1>Please copy configuration.php.dist to configuration.php and edit your credentials and phone number there.");
}

if(file_exists('../vendor/autoload.php') == false) {
    die('<h1>Missing autoloader</h1>Please install <a href="https://getcomposer.org">Composer</a> and run <code>composer install</code> to install required dependencies.');
}

require_once('../configuration.php');
require_once('../vendor/autoload.php');

$signature = new Signature($merchantId, $merchantSecret);
$api = new API($signature);

$products = new Products();

$redirectUrl = ($_SERVER['SERVER_PORT'] === '443' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

$id = isset($_GET['id']) ? $_GET['id'] : null;
$product = $id ? $products->getProduct($id) : null;

if ($product) {

    $notifyUrl = str_replace('index.php', 'notify.php', $redirectUrl);
    $signedFields = $signature->signMessage([
        'variant' => 'variant1',
        'purchaseCountry' => 'FI',
        'basePrice' => number_format($product['price'], 2),
        'taxClass' => 3,
        'serviceGroup' => 4,
        'customerNumber' => $msisdn,
        'purchaseReference' => $product['id'],
        'customerReference' => 'john.doe@tunk.io',
        'notifyAfterSuccess' => $notifyUrl,
        'notifyAfterFailure' => $notifyUrl,
        'notifyAfterCancel' => $notifyUrl,
    ], [], Signature::SORT_FIELDS);

}

?>

<html>
    <head>
        <style>
            body { font-family: Helvetica, Arial, sans-serif; }
            .status { padding: 10px; border: 1px solid black; }
            label { display: block; width: 100px; margin: 10px; }
            .success { background-color: green; color: white; }
            .failure { background-color: red;   color: white; }
            .cancelled { background-color: yellow; color: black; }
            .product { display: inline-block; margin: 10px; border: 1px solid gray; padding: 10px; }
        </style>
        <title>SiruMobile Reference DemoShop</title>
    </head>
    <body>

        <h1><a href="/">Siru Mobile example shop</a></h1>

        <h2>Variant1</h2>

        <?php if ($product): ?>

            <form action="https://staging.sirumobile.com/payment.html" method="post">
                <input type="hidden" name="variant" value="<?= $signedFields['variant']; ?>">
                <input type="hidden" name="merchantId" value="<?= $merchantId; ?>">
                <input type="hidden" name="purchaseCountry" value="<?= $signedFields['purchaseCountry']; ?>">
                <input type="hidden" name="redirectAfterSuccess" value="<?= $redirectUrl; ?>">
                <input type="hidden" name="redirectAfterFailure" value="<?= $redirectUrl; ?>">
                <input type="hidden" name="redirectAfterCancel" value="<?= $redirectUrl; ?>">
                <?php foreach (['notifyAfterSuccess', 'notifyAfterFailure', 'notifyAfterCancel'] as $status): ?>
                    <input type="hidden" name="<?= $status; ?>" value="<?= $signedFields[$status]; ?>">
                <?php endforeach; ?>
                <input type="hidden" name="purchaseReference" value="<?= $signedFields['purchaseReference']; ?>">
                <input type="hidden" name="customerReference" value="<?= $signedFields['customerReference']; ?>">

                <input type="hidden" name="serviceGroup" value="<?= $signedFields['serviceGroup']; ?>">
                <input type="hidden" name="basePrice" value="<?= $signedFields['basePrice']; ?>">
                <input type="hidden" name="customerNumber" value="<?= $signedFields['customerNumber']; ?>">
                <input type="hidden" name="taxClass" value="<?= $signedFields['taxClass']; ?>">

                <input type="hidden" name="signature" value="<?= $signedFields['signature']; ?>">

                <div class="product">
                    <strong><?= $product['name']; ?></strong>
                    <p><?= $product['description']; ?></p>
                    <p>Price: <?= $signedFields['basePrice']; ?> &euro;</p>
                    <button>Confirm purchase</button>
                </div>
            </form>

        <?php else: ?>

            <div class="products">
                <?php foreach ($products->getProducts() as $product): ?>
                    <div class="product">
                        <strong><?= $product['name']; ?></strong>
                        <p><?= $product['description']; ?></p>
                        <p>Price: <?= number_format($product['price'], 2); ?> &euro;</p>
                        <a href="?id=<?= $product['id']; ?>">Buy</a>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

        <?php

        // If user was redirected here from Siru payment flow, the payment status can be found in GET parameters
        if(isset($_GET['siru_signature'])) {

            if(!$signature->isNotificationAuthentic($_GET)) {
                // The GET parameters were not signed correct which can mean someone is trying to scam your shop
                echo '<div class="status failure">Unable to determine purchase status. Signature is not valid</div>';
            
            } elseif ($_GET['siru_event'] === 'success') {
                echo '<div class="status success">Successful purchase</div>';

            } elseif ($_GET['siru_event'] === 'failure') {
                echo '<div class="status failure">Failed purchase</div>';

            } elseif ($_GET['siru_event'] === 'cancel') {
                echo '<div class="status cancelled">Cancelled purchase</div>';
            }

        }

        ?>

    </body>
</html>
