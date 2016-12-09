<?php

namespace PhpIni\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Provides the console command.
 */
class SetCommand extends Command {

  use LockableTrait;

  /**
   * Path to the php.ini file.
   *
   * @var string
   */
  protected $phpIniFile;

  /**
   * Configuration options.
   *
   * @var array
   */
  protected $phpIniConfig;

  /**
   * Content of the php.ini file.
   *
   * @var string
   */
  protected $phpIniContents;

  /**
   * An OutputInterface instance.
   *
   * @var \Symfony\Component\Console\Output\OutputInterface
   */
  protected $output;

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('php_ini');
    $this->setDescription('Sets configuration options');
    $this->setHelp('This command allows to set (add or update) configuration options in specified or default php.ini file.');

    $this->addUsage('memory_limit=512M session.name=PHPSESSID');
    $this->addUsage('-f/etc/php/5.6/apache2/php.ini memory_limit=512M session.name=PHPSESSID');

    if (!$ini_path = php_ini_loaded_file()) {
      // The php_ini_loaded_file() may return FALSE. In this case replace
      // the value with NULL, meaning no default value for the file option.
      $ini_path = NULL;
    }

    $this->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'Path to php.ini file', $ini_path);
    $this->addArgument('config_option', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Configuration option in format "name=value"');
  }

  /**
   * Parses input options and arguments and sets object properties.
   *
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *   An InputInterface instance.
   */
  protected function parseInput(InputInterface $input) {
    $this->phpIniFile = $input->getOption('file');

    foreach ($input->getArgument('config_option') as $argument) {
      @list($key, $value) = explode('=', $argument, 2);
      $this->phpIniConfig[$key] = $value;
    }
  }

  /**
   * Validates input for purposes of the command.
   *
   * @throws \Symfony\Component\Console\Exception\RuntimeException
   *   If --file option is not provided and php_ini_loaded_file() returned
   *   FALSE or if provided option's value is not a file or it's neither
   *   readable nor writable.
   * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
   *   If any of arguments has invalid specification.
   */
  protected function validate() {
    if (!$this->phpIniFile) {
      throw new RuntimeException('A default php.ini file is not loaded. Rerun this command with --file option.');
    }
    if (!is_file($this->phpIniFile)) {
      throw new RuntimeException(sprintf('"%s" is not a file.', $this->phpIniFile));
    }
    if (!is_readable($this->phpIniFile)) {
      throw new RuntimeException(sprintf('"%s" is not readable.', $this->phpIniFile));
    }
    if (!is_writable($this->phpIniFile)) {
      throw new RuntimeException(sprintf('"%s" is not writable.', $this->phpIniFile));
    }

    foreach ($this->phpIniConfig as $key => $value) {
      if ($key === '') {
        throw new InvalidArgumentException('Undefined configuration option.');
      }
      if ($key{0} === '-') {
        throw new InvalidArgumentException(sprintf('Invalid configuration option name: "%s".', $key));
      }
      if (!isset($value)) {
        throw new InvalidArgumentException('Each configuration option must have value.');
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    if (!$this->lock('php_ini set')) {
      throw new RuntimeException('The command is already running in another process.');
    }

    $this->parseInput($input);
    $this->validate();

    $this->output = $output;
    $this->doExecute();
  }

  /**
   * Makes the main job.
   */
  protected function doExecute() {
    $this->createBackup();
    $this->readIni();

    foreach ($this->phpIniConfig as $key => $value) {
      $this->setDirective($key, $value);
    }

    $this->saveIni();
  }

  /**
   * Reads the php.ini contents into the object property.
   *
   * @throws \Symfony\Component\Console\Exception\RuntimeException
   *   If file reading operation failed.
   */
  protected function readIni() {
    if (!$this->phpIniContents = file_get_contents($this->phpIniFile)) {
      throw new RuntimeException(sprintf('Cannot read "%s".', $this->phpIniFile));
    }

    $this->output->writeln(sprintf('Loaded "%s".', $this->phpIniFile));
  }

  /**
   * Adds new configuration option or updates existing one with a new value.
   *
   * @param string $key
   *   The configuration option name.
   * @param string $value
   *   The new value for the option.
   */
  protected function setDirective($key, $value) {
    $pattern = '/^\s*' . preg_quote($key, '/') . '\s*=.*$/m';
    $replacement = $key . ' = ' . $value;

    $this->phpIniContents = preg_replace($pattern, $replacement, $this->phpIniContents, -1, $count);

    if (!$count) {
      $this->output->writeln(sprintf('The "%s" is missing from php.ini. Adding it now...', $key));
      $this->phpIniContents .= "\n" . $replacement . "\n";
    }
    elseif ($count > 1) {
      $this->output->writeln(sprintf('Found %d occurrences of "%s". Updating all of them...', $count, $key));
    }
  }

  /**
   * Saves php.ini file with updated contents.
   *
   * @throws \Symfony\Component\Console\Exception\RuntimeException
   *   If file writing operation failed.
   */
  protected function saveIni() {
    if (!file_put_contents($this->phpIniFile, $this->phpIniContents)) {
      throw new RuntimeException(sprintf('Cannot write to "%s".', $this->phpIniFile));
    }

    $this->output->writeln('php.ini updated successfully.');
  }

  /**
   * Creates a backup of php.ini.
   */
  protected function createBackup() {
    $target = $this->phpIniFile . '_backup_' . date('Ymd_His', filemtime($this->phpIniFile)) . 'UTC';

    $fs = new Filesystem();
    $fs->copy($this->phpIniFile, $target, TRUE);

    $this->output->writeln(sprintf('Backup created in %s', $target), OutputInterface::VERBOSITY_VERBOSE);
  }

}
