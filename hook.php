<?php
if (!defined('GLPI_ROOT')) { define('GLPI_ROOT', realpath(__DIR__ . '/../..')); }

/**
 * -------------------------------------------------------------------------
 * GLPIwithBookstack plugin for GLPI
 * Copyright (C) 2024 by the GLPIwithBookstack Development Team.
 * -------------------------------------------------------------------------
 *
 * MIT License
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * --------------------------------------------------------------------------
 */

/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_glpiwithbookstack_install()
{
    global $DB;
    // Run SQL install script to create configs table
    $sqlfile = __DIR__ . '/sql/install.sql';
    if (file_exists($sqlfile)) {
        $query = file_get_contents($sqlfile);
        foreach (explode(';', $query) as $statement) {
            $statement = trim($statement);
            if ($statement) {
                $DB->request($statement);
            }
        }
    }

    // Set default config values
    // Insert default config values directly
    $defaults = [
        'bookstack_url' => '',
        'bookstack_token_id' => '',
        'bookstack_token_secret' => '',
        'search_in_tags_only' => '0',
        'search_type_pages_only' => '1',
        'search_category_name_only' => '0',
        'search_category_completename_but_only_visible' => '1',
        'curl_timeout' => '1',
        'curl_ssl_verifypeer' => '1',
        'display_max_search_results' => '10',
        'display_text_tab_name' => 'Knowledge base',
        'display_text_title' => 'Title',
        'display_text_content_preview' => 'Content preview',
        'display_text_search_on_bookstack' => 'Search [search_term] on Bookstack',
        'display_text_max_results_reached' => '[result_count] of [max_results] results are displayed. Click here to view all: [url]'
    ];
    global $DB;
    foreach ($defaults as $name => $value) {
        $name_esc = addslashes($name);
        $value_esc = addslashes($value);
        $sql = "INSERT IGNORE INTO glpi_plugin_glpiwithbookstack_configs (name, value) VALUES ('{$name_esc}', '{$value_esc}')";
        $DB->request($sql);
    }
    return true;
}

/**
 * Plugin uninstall process
 *
 * @return boolean
 */
function plugin_glpiwithbookstack_uninstall()
{
    // Remove all config values directly
    global $DB;
    $DB->request("DELETE FROM glpi_plugin_glpiwithbookstack_configs");

    //ProfileRight::deleteProfileRights(['glpiwithbookstack:read']);
    return true;
}
/*
 * TODO: no integrated search yet, so need to create a page for it
 * Add new menu for Bookstack integrated search on top level
*/
function plugin_myplugin_redefine_menus($menu) {
    if (empty($menu)) {
        return $menu;
    }
    /*
     * Create custom menu for the new Bookstack knowledge base.
     * It will be placed on the top menu so it can be reached directly.
     * A new search form will be display to show the API search
    */
    if (array_key_exists('knowledgebase', $menu) === false && $_SESSION['glpiactiveprofile']['interface'] == 'central') {
        $menu['knowledgebase'] = [
        //'default'   => '/plugins/myplugin/front/model.php',
        'default'   => '/front/knowbaseitem.php',
        'title'     => __('Knowledge base', 'knowledgebase'),
        'icon'      => 'ti ti-lifebuoy',
        'content'   => [true]
    ];
    }
    return $menu;
}
