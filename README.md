# Siru Mobile Example Shop

This is an example shop for Siru Mobile payment gateway. If you are familiar with Symfony, the project structure should be easy to understand but if you are not, you can look just at `src/Controller/DefaultController.php` where most of the magic happens.

## Prerequisites

- Your staging API credentials from Siru Mobile. If you don't have those, please contact Siru Mobile.
- PHP 7.1.3 or higher
- [Composer](http://getcomposer.org)
- [Yarn](https://yarnpkg.com)

## Setting up the example shop

1. Download and unpack or clone the example shop
2. Run `./composer.phar install` or `composer install` if you have composer installed globally
3. Install javascript modules using `yarn install --pure-lockfile`
4. Install assets using `yarn run encore production`
5. Copy the `.env.dist` file to `.env` and put your merchantId and merchant secret there
6. Start the demoshop by running `bin/console server:start` or configure a virtual host to your webserver

### Testing notifications

Siru payment gateway uses notifications to a callback URL to make sure shop is notified of successful transactions even if end-user does not return to the shop. For this to work, your shop must be accessible from the internet. If you are running example shop under localhost, notifications will not be available.
