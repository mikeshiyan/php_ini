# php_ini.sh
Linux shell script to update php.ini configuration options.

## Installation
```
git clone https://github.com/mikeshiyan/php_ini.sh.git
cd php_ini.sh/
chmod +x php_ini.sh
sudo ln -s /absolute-path-to-this-script/php_ini.sh /usr/local/bin/php_ini
```

## Examples
Set *memory_limit* and *session.name* options:
```
sudo php_ini memory_limit=512M session.name=PHPSESSID
```
Do the same with another php.ini (not currently loaded):
```
sudo php_ini -f=/etc/php/5.6/apache2/php.ini memory_limit=512M session.name=PHPSESSID
```
