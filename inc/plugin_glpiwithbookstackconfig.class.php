<?php
if (!defined('GLPI_ROOT')) { define('GLPI_ROOT', realpath(__DIR__ . '/../..')); }

class PluginGlpiwithbookstackConfig extends CommonGLPI {
   static protected $notable = true;

   /**
    * Return plugin configuration as associative array
    * @param string $context (e.g. 'plugin:Glpiwithbookstack')
    * @return array
    */
   public static function getConfigurationValues($context = 'plugin:Glpiwithbookstack') {
      global $DB;
      $config = [];
      $res = $DB->request([
         'FROM' => 'glpi_plugin_glpiwithbookstack_configs',
         'WHERE' => []
      ]);
      if ($res && count($res)) {
         foreach ($res as $row) {
            $config[$row['name']] = $row['value'];
         }
      }
      return $config;
   }
}
