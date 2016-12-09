# php_ini
PHP script to update php.ini configuration options.

## Requirements
* [Composer](https://getcomposer.org)

## Installation
Preferred way (prevents potential Composer dependencies conflict):
```
mkdir php-ini && cd php-ini
composer require -o mikeshiyan/php-ini:@stable
sudo ln -s $PWD/vendor/mikeshiyan/php-ini/bin/php_ini /usr/local/bin/
```

Of course you can
[search for](http://stackoverflow.com/search?q=how+to+install+composer+package)
and use any other known way to install this Composer package. Just make sure
to make *bin/php_ini* an executable file and to create its symlink in any
directory from the PATH environment variable.

## Examples
Set *memory_limit* and *session.name* options:
```
sudo php_ini memory_limit=512M session.name=PHPSESSID
```
Do the same with another php.ini (not currently loaded):
```
sudo php_ini -f/etc/php/5.6/apache2/php.ini memory_limit=512M session.name=PHPSESSID
```
