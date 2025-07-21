<?php
// File: addresshistory.civix.php
// AUTO-GENERATED FILE -- Civix may overwrite any changes made to this file

/**
 * The ExtensionUtil class provides small stubs for accessing resources of this
 * extension.
 */
class CRM_Addresshistory_ExtensionUtil {
  const SHORT_NAME = 'addresshistory';
  const LONG_NAME = 'com.moellman.addresshistory';
  const CLASS_PREFIX = 'CRM_Addresshistory';

  /**
   * Translate a string using the extension's domain.
   */
  public static function ts($text, $params = []) {
    if (!array_key_exists('domain', $params)) {
      $params['domain'] = [self::LONG_NAME, NULL];
    }
    return ts($text, $params);
  }

  /**
   * Get the URL of a resource file (in this extension).
   */
  public static function url($file = NULL) {
    if ($file === NULL) {
      return rtrim(CRM_Core_Resources::singleton()->getUrl(self::LONG_NAME), '/');
    }
    return CRM_Core_Resources::singleton()->getUrl(self::LONG_NAME, $file);
  }

  /**
   * Get the path of a resource file (in this extension).
   */
  public static function path($file = NULL) {
    return __DIR__ . ($file === NULL ? '' : (DIRECTORY_SEPARATOR . $file));
  }

  /**
   * Get the name of a class within this extension.
   */
  public static function findClass($suffix) {
    return self::CLASS_PREFIX . '_' . str_replace('\\', '_', $suffix);
  }
}

use CRM_Addresshistory_ExtensionUtil as E;

/**
 * (Delegated) Implements hook_civicrm_config().
 */
function _addresshistory_civix_civicrm_config(&$config = NULL) {
  static $configured = FALSE;
  if ($configured) {
    return;
  }
  $configured = TRUE;

  $template =& CRM_Core_Smarty::singleton();
  $extRoot = dirname(__FILE__) . DIRECTORY_SEPARATOR;
  $extDir = $extRoot . 'templates';

  if (is_array($template->template_dir)) {
    array_unshift($template->template_dir, $extDir);
  }
  else {
    $template->template_dir = [$extDir, $template->template_dir];
  }

  $include_path = $extRoot . PATH_SEPARATOR . get_include_path();
  set_include_path($include_path);
}

/**
 * (Delegated) Implements hook_civicrm_xmlMenu().
 */
function _addresshistory_civix_civicrm_xmlMenu(&$files) {
  foreach (_addresshistory_civix_glob(__DIR__ . '/xml/Menu/*.xml') as $file) {
    $files[] = $file;
  }
}

/**
 * Implements hook_civicrm_install().
 */
function _addresshistory_civix_civicrm_install() {
  _addresshistory_civix_civicrm_config();
  if ($upgrader = _addresshistory_civix_upgrader()) {
    $upgrader->onInstall();
  }
}

/**
 * Implements hook_civicrm_postInstall().
 */
function _addresshistory_civix_civicrm_postInstall() {
  _addresshistory_civix_civicrm_config();
  if ($upgrader = _addresshistory_civix_upgrader()) {
    if (is_callable([$upgrader, 'onPostInstall'])) {
      $upgrader->onPostInstall();
    }
  }
}

/**
 * Implements hook_civicrm_uninstall().
 */
function _addresshistory_civix_civicrm_uninstall() {
  _addresshistory_civix_civicrm_config();
  if ($upgrader = _addresshistory_civix_upgrader()) {
    $upgrader->onUninstall();
  }
}

/**
 * (Delegated) Implements hook_civicrm_enable().
 */
function _addresshistory_civix_civicrm_enable() {
  _addresshistory_civix_civicrm_config();
  if ($upgrader = _addresshistory_civix_upgrader()) {
    if (is_callable([$upgrader, 'onEnable'])) {
      $upgrader->onEnable();
    }
  }
}

/**
 * (Delegated) Implements hook_civicrm_disable().
 */
function _addresshistory_civix_civicrm_disable() {
  _addresshistory_civix_civicrm_config();
  if ($upgrader = _addresshistory_civix_upgrader()) {
    if (is_callable([$upgrader, 'onDisable'])) {
      $upgrader->onDisable();
    }
  }
}

/**
 * (Delegated) Implements hook_civicrm_upgrade().
 */
function _addresshistory_civix_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  if ($upgrader = _addresshistory_civix_upgrader()) {
    return $upgrader->onUpgrade($op, $queue);
  }
}

/**
 * @return CRM_Addresshistory_Upgrader
 */
function _addresshistory_civix_upgrader() {
  if (!file_exists(__DIR__ . '/CRM/Addresshistory/Upgrader.php')) {
    return NULL;
  }
  else {
    return CRM_Addresshistory_Upgrader_Base::instance();
  }
}

/**
 * Search directory tree for files which match a glob pattern.
 */
function _addresshistory_civix_find_files($dir, $pattern) {
  if (is_callable(['CRM_Utils_File', 'findFiles'])) {
    return CRM_Utils_File::findFiles($dir, $pattern);
  }

  $todos = [$dir];
  $result = [];
  while (!empty($todos)) {
    $subdir = array_shift($todos);
    foreach (_addresshistory_civix_glob("$subdir/$pattern") as $match) {
      if (!is_dir($match)) {
        $result[] = $match;
      }
    }
    if ($dh = opendir($subdir)) {
      while (FALSE !== ($entry = readdir($dh))) {
        $path = $subdir . DIRECTORY_SEPARATOR . $entry;
        if ($entry[0] == '.') {
          // Skip dot files
        }
        elseif (is_dir($path)) {
          $todos[] = $path;
        }
      }
      closedir($dh);
    }
  }
  return $result;
}

/**
 * (Delegated) Implements hook_civicrm_managed().
 */
function _addresshistory_civix_civicrm_managed(&$entities) {
  $mgdFiles = _addresshistory_civix_find_files(__DIR__, '*.mgd.php');
  sort($mgdFiles);
  foreach ($mgdFiles as $file) {
    $es = include $file;
    foreach ($es as $e) {
      if (empty($e['module'])) {
        $e['module'] = E::LONG_NAME;
      }
      if (empty($e['params']['version'])) {
        $e['params']['version'] = '3';
      }
      $entities[] = $e;
    }
  }
}

/**
 * (Delegated) Implements hook_civicrm_caseTypes().
 */
function _addresshistory_civix_civicrm_caseTypes(&$caseTypes) {
  if (!is_dir(__DIR__ . '/xml/case')) {
    return;
  }

  foreach (_addresshistory_civix_glob(__DIR__ . '/xml/case/*.xml') as $file) {
    $name = preg_replace('/\.xml$/', '', basename($file));
    if ($name != CRM_Case_XMLProcessor::mungeCaseType($name)) {
      $errorMessage = sprintf("Case-type file name is malformed (%s vs %s)", $name, CRM_Case_XMLProcessor::mungeCaseType($name));
      throw new CRM_Core_Exception($errorMessage);
    }
    $caseTypes[$name] = [
      'module' => E::LONG_NAME,
      'name' => $name,
      'file' => $file,
    ];
  }
}

/**
 * (Delegated) Implements hook_civicrm_angularModules().
 */
function _addresshistory_civix_civicrm_angularModules(&$angularModules) {
  if (!is_dir(__DIR__ . '/ang')) {
    return;
  }

  $files = _addresshistory_civix_glob(__DIR__ . '/ang/*.ang.php');
  foreach ($files as $file) {
    $name = preg_replace(':\.ang\.php$:', '', basename($file));
    $module = include $file;
    if (empty($module['ext'])) {
      $module['ext'] = E::LONG_NAME;
    }
    $angularModules[$name] = $module;
  }
}

/**
 * (Delegated) Implements hook_civicrm_themes().
 */
function _addresshistory_civix_civicrm_themes(&$themes) {
  $files = _addresshistory_civix_glob(__DIR__ . '/*.theme.php');
  foreach ($files as $file) {
    $themeMeta = include $file;
    if (empty($themeMeta['name'])) {
      $themeMeta['name'] = preg_replace(':\.theme\.php$:', '', basename($file));
    }
    if (empty($themeMeta['ext'])) {
      $themeMeta['ext'] = E::LONG_NAME;
    }
    $themes[$themeMeta['name']] = $themeMeta;
  }
}

/**
 * Glob wrapper which is guaranteed to return an array.
 */
function _addresshistory_civix_glob($pattern) {
  $result = glob($pattern);
  return is_array($result) ? $result : [];
}

/**
 * Inserts a navigation menu item at a given place in the hierarchy.
 */
function _addresshistory_civix_insert_navigation_menu(&$menu, $path, $item) {
  // If we are done going down the path, insert menu
  if (empty($path)) {
    $menu[] = [
      'attributes' => array_merge([
        'label'      => CRM_Utils_Array::value('name', $item),
        'active'     => 1,
      ], $item),
    ];
    return TRUE;
  }
  else {
    // Find an recurse into the next level down
    $found = FALSE;
    $path = explode('/', $path);
    $first = array_shift($path);
    foreach ($menu as $key => &$entry) {
      if ($entry['attributes']['name'] == $first) {
        if (!isset($entry['child'])) {
          $entry['child'] = [];
        }
        $found = _addresshistory_civix_insert_navigation_menu($entry['child'], implode('/', $path), $item);
      }
    }
    return $found;
  }
}

/**
 * (Delegated) Implements hook_civicrm_navigationMenu().
 */
function _addresshistory_civix_civicrm_navigationMenu(&$menu) {
  if ($upgrader = _addresshistory_civix_upgrader()) {
    $upgrader->appendNavigationMenu($menu);
  }
}

/**
 * (Delegated) Implements hook_civicrm_alterSettingsFolders().
 */
function _addresshistory_civix_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  $settingsDir = __DIR__ . DIRECTORY_SEPARATOR . 'settings';
  if (is_dir($settingsDir) && !in_array($settingsDir, $metaDataFolders)) {
    $metaDataFolders[] = $settingsDir;
  }
}

/**
 * (Delegated) Implements hook_civicrm_entityTypes().
 */
function _addresshistory_civix_civicrm_entityTypes(&$entityTypes) {
  $entityFiles = _addresshistory_civix_find_files(__DIR__, '*.entityType.php');
  foreach ($entityFiles as $file) {
    $es = include $file;
    foreach ($es as $e) {
      if (empty($e['module'])) {
        $e['module'] = E::LONG_NAME;
      }
      $entityTypes[] = $e;
    }
  }
}
