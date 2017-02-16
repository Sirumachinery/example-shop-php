# SiruMobile Example Shop

## Prerequisites
- You should have received your API credentials from SiruMobile. If not, please contact SiruMobile.
- PHP 5.4 or higher
- [composer](http://getcomposer.org)

## Setting up the example shop
1. Download and unpack or clone the example shop
2. Copy the file `configuration.php.dist` to `configuration.php` and add your API credentials and phone number there
3. Install required dependencies using `composer install`
4. Open the example shop in your browser

## Using PHP built-in webserver
If you don't have a webserver installed such as Apache or Nginx, you can use the PHP built in webserver.
- `cd <project_path>`
- `cd public`
- `php -S localhost:9999`

Use `http://localhost:9999` via browser to see the example shop in action
