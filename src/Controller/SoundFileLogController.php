<?php

namespace Drupal\sound_file_logger\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\Entity\DateFormat;

class SoundFileLogController extends ControllerBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a SoundFileLogController object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * Displays the sound file access log.
   *
   * @return array
   *   A render array.
   */
  public function viewLog() {
    $header = [
      'user' => $this->t('User'),
      'file' => $this->t('File'),
      'time' => $this->t('Access Time'),
      'ip' => $this->t('IP Address'),
    ];

    $query = $this->database->select('sound_file_access_log', 'l')
      ->fields('l')
      ->orderBy('timestamp', 'DESC')
      ->extend('Drupal\Core\Database\Query\PagerSelectExtender')
      ->limit(50);

    $results = $query->execute();

    $rows = [];
    foreach ($results as $record) {
      $user = $this->entityTypeManager()->getStorage('user')->load($record->uid);
      $rows[] = [
        'user' => $user ? $user->getAccountName() : $this->t('Unknown'),
        'file' => $record->file_uri,
        'time' => strtotime('d-m-Y', $record->timestamp),
        'ip' => $record->ip_address,
      ];
    }

    return [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No sound file access logs found.'),
      '#pager' => [
        '#type' => 'pager',
      ],
    ];
  }
}
