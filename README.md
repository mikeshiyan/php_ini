# php_ini
PHP script to update php.ini configuration options.

## Installation
```
git clone https://github.com/mikeshiyan/php_ini.git
cd php_ini/
chmod +x php_ini.php
sudo ln -s /absolute-path-to-this-script/php_ini.php /usr/local/bin/php_ini
```

## Examples
Set *memory_limit* and *session.name* options:
```
sudo php_ini memory_limit=512M session.name=PHPSESSID
```
Do the same with another php.ini (not currently loaded):
```
sudo php_ini -f/etc/php/5.6/apache2/php.ini memory_limit=512M session.name=PHPSESSID
```
