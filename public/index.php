<?php

/**
 * Siru Reference DemoShop - feel free to modify!
 *
 * NOTICE!
 * If you wan't SiruMobile to be able to notify your application after a succesful/failed/cancelled purchase,
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

$signature = new \Siru\Signature($merchantId, $merchantSecret);
$api = new \Siru\API($signature);

$products = new DemoShop\Products();

if (isset($_GET['siru_signature']) && !$signature->isNotificationAuthentic($_GET)) {
    die('Signature does not match!');
}

if (isset($_GET['notify'])) {
    $requestBody = file_get_contents('php://input');

    file_put_contents(
        '../data/logs/notifications.log',
        (new DateTime())->format('d.m.Y H:i:s') . " - RECEIVED POST:\n$requestBody\n",
        FILE_APPEND
    );

    $requestJson = json_decode($requestBody, true);

    if ($api->getPaymentApi()->isNotificationAuthentic($requestJson)) {
        $event = $requestJson['siru_event'];

        switch ($event) {
            case 'success':

                $products->confirmAndLogPurchase($requestJson);
                break;

            case 'cancel':

                // TODO: cancel purchase
                break;

            case 'failure':

                // TODO: fail purchase
                break;

            default:
                throw new Exception("Unknown event: '$event'");
        }

        header("HTTP/1.1 200 OK");
    }

    die;
}

$baseUrl = ($_SERVER['SERVER_PORT'] === '443' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];

$id = isset($_GET['id']) ? $_GET['id'] : null;

$product = $id ? $products->getProduct($id) : null;

if ($product) {

    $signedFields = $signature->signMessage([
        'variant' => 'variant1',
        'purchaseCountry' => 'FI',
        'basePrice' => number_format($product['price'], 2),
        'taxClass' => 3,
        'serviceGroup' => 4,
        'customerNumber' => $msisdn,
        'purchaseReference' => $product['id'],
        'customerReference' => 'john.doe@tunk.io',
        'notifyAfterSuccess' => $baseUrl . '/?notify=success',
        'notifyAfterFailure' => $baseUrl . '/?notify=failure',
        'notifyAfterCancel' => $baseUrl . '/?notify=cancel',
    ], [], \Siru\Signature::SORT_FIELDS);

}

$statusNotSuccess = !isset($_GET['status']) || (isset($_GET['status']) && $_GET['status'] !== 'success');

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
<!--        <a href="/">
            <img src="https://staging.sirumobile.com/bundles/sirudemoshop/images/payment-siru.png?c1e839d5030fe002a43947c3ac531e415928d805">
        </a>
-->
        <h1><a href="/">Siru Mobile example shop</a></h1>

        <h2>Variant1</h2>

        <?php if ($product): ?>

            <form action="https://staging.sirumobile.com/payment.html" method="post">
                <input type="hidden" name="variant" value="<?= $signedFields['variant']; ?>">
                <input type="hidden" name="merchantId" value="<?= $merchantId; ?>">
                <input type="hidden" name="purchaseCountry" value="<?= $signedFields['purchaseCountry']; ?>">
                <input type="hidden" name="redirectAfterSuccess" value="<?= $baseUrl; ?>/?status=success">
                <input type="hidden" name="redirectAfterFailure" value="<?= $baseUrl; ?>/?status=failure">
                <input type="hidden" name="redirectAfterCancel" value="<?= $baseUrl; ?>/?status=cancel">
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

        <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>

            <div class="status success">Successful purchase</div>

        <?php elseif (isset($_GET['status']) && $_GET['status'] === 'failure'): ?>

            <div class="status failure">Failed purchase</div>

        <?php elseif (isset($_GET['status']) && $_GET['status'] === 'cancel'): ?>

            <div class="status cancelled">Cancelled purchase</div>

        <?php endif; ?>
    </body>
</html>
