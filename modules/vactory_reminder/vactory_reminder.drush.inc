<?php

/**
 * @file
 * Contains vactory_reminder.module.
 */

/**
 * Implements hook_drush_command().
 */
function vactory_reminder_drush_command() {
  return [
    'reminder_queue' => [
      'aliases'     => ['_rrq'],
      'callback'    => '_run_reminder_queue_command',
      'description' => "Run reminder queue",
      'arguments'   => [],
    ],
  ];
}

function _run_reminder_queue_command() {
  $reminder_service = \Drupal::service('vactory_reminder.commander');
  $reminder_service->runReminderQueue();
}