<?php

/**
 * Implementation of hook_drush_command().
 */
function php_repl_drush_command() {
  $items = array();

  $items['php-repl'] = array(
    'callback' => 'drush_php_repl_callback',
    'description' => "Open a PHP command-line interface following a Drupal bootstrap.",
    'aliases' => array('repl', 'cli'),
  );

  return $items;
}

/**
 * Implementation of hook_drush_help().
 */
function php_repl_drush_help($section) {
  switch ($section) {
    case 'drush:php-repl':
      return dt("Opens an interactive PHP shell following a Drupal bootstrap.");
  }
}

/**
 * Callback for php-cli command.
 *
 * This loads a file which opens a php shell.
 */
function drush_php_repl_callback() {
  $argv[1] = 'drepl>';
  include "phprepl.php";
}