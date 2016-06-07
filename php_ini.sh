#!/usr/bin/env php
<?php

/**
 * @file
 * Linux shell script to update php.ini configuration options.
 */

/**
 * Executes the script.
 */
function go () {
  // First check that php.ini is found, loaded and is readable.
  ini();

  // Do not spoil global $argv, copy its value to local variable.
  $argv = $GLOBALS['argv'];
  array_shift($argv);

  foreach ($argv as $argument) {
    if ($pair = get_pair($argument)) {
      list($key, $value) = $pair;
      set_directive($key, $value);
    }
  }

  save_ini();
}

/**
 * Reads the php.ini contents into static variable.
 *
 * @return string
 *   Contents of the php.ini.
 */
function &ini () {
  static $ini_content;

  if (!isset($ini_content)) {
    if ($ini_path = php_ini_loaded_file()) {
      if ($ini_content = file_get_contents($ini_path)) {
        print "Loaded $ini_path\n";
      }
      else {
        print "Cannot read $ini_path\n";
        exit;
      }
    }
    else {
      print "A php.ini file is not loaded.\n";
      exit;
    }
  }

  return $ini_content;
}

/**
 * Explodes the given string into key/value pair.
 *
 * @param string $string
 *   User input.
 *
 * @return string[]|false
 *   Array with 2 string elements in case of valid provided input, FALSE
 *   otherwise.
 */
function get_pair ($string) {
  $pair = explode('=', $string, 2);
  return count($pair) == 2 && $pair[0] !== '' ? $pair : FALSE;
}

/**
 * Adds new configuration option or updates existing one with a new value.
 *
 * @param string $key
 *   The configuration option name.
 * @param string $value
 *   The new value for the option.
 */
function set_directive ($key, $value) {
  $ini = &ini();

  $pattern = '/^\s*' . preg_quote($key, '/') . '\s*=.*$/m';
  $replacement = $key . ' = ' . $value;

  $ini = preg_replace($pattern, $replacement, $ini, -1, $count);

  if (!$count) {
    print "The \"$key\" is missing from php.ini. Adding it now...\n";

    $ini .= "\n" . $replacement . "\n";
  }
  elseif ($count > 1) {
    print "Found $count occurrences of \"$key\". Updating all of them...\n";
  }
}

/**
 * Saves php.ini file with updated contents.
 */
function save_ini () {
  $ini_path = php_ini_loaded_file();
  $result = file_put_contents($ini_path, ini());

  if ($result === FALSE) {
    print "Cannot write to $ini_path\n";
    exit;
  }
  else {
    print "php.ini updated successfully.\n";
  }
}

go();
