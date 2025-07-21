<?php
// File: CRM/Addresshistory/Upgrader/Base.php

use CRM_Addresshistory_ExtensionUtil as E;

/**
 * Base class which provides helpers to execute upgrade logic.
 */
class CRM_Addresshistory_Upgrader_Base {

  /**
   * @var CRM_Queue_TaskContext
   */
  protected $ctx;

  /**
   * @var string, eg 'com.example.myextension'
   */
  protected $extensionName;

  /**
   * @var string, full path to the extension's source tree
   */
  protected $extensionDir;

  /**
   * @var array(revisionNumber) sorted numerically
   */
  private $revisions;

  /**
   * @var bool
   *   Flag to clean up extension revision data in civicrm_extension table
   */
  private $revisionStorageIsDeprecated = FALSE;

  /**
   * Obtain a reference to the active upgrade handler.
   */
  public static function instance() {
    if (!self::$instance) {
      // FIXME auto-generate
      self::$instance = new CRM_Addresshistory_Upgrader(
        'com.moellman.addresshistory',
        E::path()
      );
    }
    return self::$instance;
  }

  /**
   * @var CRM_Addresshistory_Upgrader_Base
   */
  static $instance;

  /**
   * Adapter that lets you add normal (non-static) member functions to the queue.
   *
   * Note: Each upgrader instance should only be associated with one
   * task-context; otherwise, this will be non-reentrant.
   *
   * @code
   * CRM_Addresshistory_Upgrader_Base::_queueAdapter($ctx, 'methodName', 'arg1', 'arg2');
   * @endcode
   */
  public static function _queueAdapter() {
    $instance = self::instance();
    $args = func_get_args();
    $instance->ctx = array_shift($args);
    $instance->queue = $instance->ctx->queue;
    $method = array_shift($args);
    return call_user_func_array([$instance, $method], $args);
  }

  public function __construct($extensionName, $extensionDir) {
    $this->extensionName = $extensionName;
    $this->extensionDir = $extensionDir;
  }

  // ******** Task helpers ********

  /**
   * Run a CustomData file.
   *
   * @param string $relativePath the CustomData XML file path (relative to this extension's dir)
   * @return bool
   */
  public function executeCustomDataFile($relativePath) {
    $xml_file = $this->extensionDir . '/' . $relativePath;
    return $this->executeCustomDataFileByAbsPath($xml_file);
  }

  /**
   * Run a CustomData file
   *
   * @param string $xml_file  the CustomData XML file path (absolute path)
   *
   * @return bool
   */
  protected static function executeCustomDataFileByAbsPath($xml_file) {
    $import = new CRM_Utils_Migrate_Import();
    $import->run($xml_file);
    return TRUE;
  }

  /**
   * Run a SQL file.
   *
   * @param string $relativePath the SQL file path (relative to this extension's dir)
   *
   * @return bool
   */
  public function executeSqlFile($relativePath) {
    CRM_Utils_File::sourceSQLFile(
      CIVICRM_DSN,
      $this->extensionDir . DIRECTORY_SEPARATOR . $relativePath
    );
    return TRUE;
  }

  /**
   * Run one SQL query.
   *
   * This is just a wrapper for CRM_Core_DAO::executeSql, but it
   * provides syntactic sugar for queueing several tasks that
   * run different queries
   */
  public function executeSql($query, $params = []) {
    // FIXME verify that we raise an exception on error
    CRM_Core_DAO::executeQuery($query, $params);
    return TRUE;
  }

  /**
   * Syntactic sugar for enqueuing a task which calls a function in this class.
   *
   * The task is weighted so that it is processed
   * as part of the currently-pending revision.
   *
   * After passing the $funcName, you can also pass parameters that will go to
   * the function.  Note that all params must be serializable.
   */
  public function addTask($title) {
    $args = func_get_args();
    $title = array_shift($args);
    $task = new CRM_Queue_Task(
      [get_class($this), '_queueAdapter'],
      $args,
      $title
    );
    return $this->queue->createItem($task);
  }

  // ******** Revision-tracking helpers ********

  /**
   * Determine if there are any pending revisions.
   *
   * @return bool
   */
  public function hasPendingRevisions() {
    $revisions = $this->getRevisions();
    $currentRevision = $this->getCurrentRevision();

    if (empty($revisions)) {
      return FALSE;
    }
    if (empty($currentRevision)) {
      return TRUE;
    }

    return ($currentRevision < max($revisions));
  }

  /**
   * Add any pending revisions to the queue.
   */
  public function enqueuePendingRevisions(CRM_Queue_Queue $queue) {
    $this->queue = $queue;

    $currentRevision = $this->getCurrentRevision();
    foreach ($this->getRevisions() as $revision) {
      if ($revision > $currentRevision) {
        $title = ts('Upgrade %1 to revision %2', [
          1 => $this->extensionName,
          2 => $revision,
        ]);

        // note: don't use addTask() because it sets weight=-1

        $task = new CRM_Queue_Task(
          [get_class($this), '_queueAdapter'],
          ['upgrade_' . $revision],
          $title
        );
        $this->queue->createItem($task);

        $task = new CRM_Queue_Task(
          [get_class($this), '_queueAdapter'],
          ['setCurrentRevision', $revision],
          $title
        );
        $this->queue->createItem($task);
      }
    }
  }

  /**
   * Get a list of revisions.
   *
   * @return array(revisionNumbers) sorted numerically
   */
  public function getRevisions() {
    if (!is_array($this->revisions)) {
      $this->revisions = [];

      $clazz = new ReflectionClass(get_class($this));
      $methods = $clazz->getMethods();
      foreach ($methods as $method) {
        if (preg_match('/^upgrade_(.*)/', $method->name, $matches)) {
          $this->revisions[] = $matches[1];
        }
      }
      sort($this->revisions, SORT_NUMERIC);
    }

    return $this->revisions;
  }

  public function getCurrentRevision() {
    $revision = CRM_Core_BAO_Extension::getSchemaVersion($this->extensionName);
    if (!$revision) {
      $revision = $this->getCurrentRevisionDeprecated();
    }
    return $revision;
  }

  private function getCurrentRevisionDeprecated() {
    // For CiviCRM 6.4, we don't use the deprecated revision field
    // Instead, return null to indicate no previous revision
    return NULL;
  }

  public function setCurrentRevision($revision) {
    CRM_Core_BAO_Extension::setSchemaVersion($this->extensionName, $revision);
    return TRUE;
  }

  // ******** Hook delegates ********

  /**
   * @see https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
   */
  public function onInstall() {
    $files = glob($this->extensionDir . '/sql/*_install.sql');
    if (is_array($files)) {
      foreach ($files as $file) {
        CRM_Utils_File::sourceSQLFile(CIVICRM_DSN, $file);
      }
    }
    if (is_callable([$this, 'install'])) {
      $this->install();
    }
  }

  /**
   * @see https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
   */
  public function onPostInstall() {
    if (is_callable([$this, 'postInstall'])) {
      $this->postInstall();
    }
  }

  /**
   * @see https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
   */
  public function onUninstall() {
    if (is_callable([$this, 'uninstall'])) {
      $this->uninstall();
    }
    $files = glob($this->extensionDir . '/sql/*_uninstall.sql');
    if (is_array($files)) {
      foreach ($files as $file) {
        CRM_Utils_File::sourceSQLFile(CIVICRM_DSN, $file);
      }
    }
  }

  /**
   * @see https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
   */
  public function onEnable() {
    // stub for possible future use
    if (is_callable([$this, 'enable'])) {
      $this->enable();
    }
  }

  /**
   * @see https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
   */
  public function onDisable() {
    // stub for possible future use
    if (is_callable([$this, 'disable'])) {
      $this->disable();
    }
  }

  public function onUpgrade($op, CRM_Queue_Queue $queue = NULL) {
    switch ($op) {
      case 'check':
        return [$this->hasPendingRevisions()];

      case 'enqueue':
        return $this->enqueuePendingRevisions($queue);

      default:
    }
  }

}
