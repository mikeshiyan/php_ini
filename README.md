# php_ini.sh
Linux shell script to update php.ini configuration options.

## Installation
```
git clone https://github.com/mikeshiyan/php_ini.sh.git
cd php_ini.sh/
chmod +x php_ini.sh
sudo ln -s /absolute-path-to-this-script/php_ini.sh /usr/sbin/php_ini
```

## Examples
```
sudo php_ini memory_limit=512M session.name=PHPSESSID
```
