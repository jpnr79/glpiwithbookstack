
if (!defined('GLPI_ROOT')) { define('GLPI_ROOT', realpath(__DIR__ . '/../..')); }

/**
 * -------------------------------------------------------------------------
 * Example plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of Example.
 *
 * Example is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Example is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Example. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2006-2022 by Example plugin team.
 * @license   GPLv2 https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/pluginsGLPI/example
 * -------------------------------------------------------------------------
 */



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

   /**
     * This function is called from GLPI to allow the plugin to insert one or more item
     *  inside the left menu of a Itemtype.
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0)
   {
      return __('Config', 'glpiwithbookstack');
   }

   static function configUpdate($input) {
      $input['configuration'] = 1 - $input['configuration'];
      return $input;
   }

   // function showFormExample() { ... } // Remove stub, not used

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      if ($item->getType() == 'Config') {
         $config = new self();
         $config->showFormExample();
      }
   }

}
