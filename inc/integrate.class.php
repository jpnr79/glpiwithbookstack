<?php

if (!defined('GLPI_ROOT')) { define('GLPI_ROOT', realpath(__DIR__ . '/../..')); }
require_once __DIR__ . '/config.class.php';


require_once __DIR__ . '/config.class.php';

class PluginGlpiwithbookstackIntegrate extends CommonGLPI
{
    /**
     * This function is called from GLPI to allow the plugin to insert one or more item
     *  inside the left menu of a Itemtype.
    */
	function getTabNameForItem(CommonGLPI $item, $withtemplate=0)
	{
		return __('Bookstack', 'glpiwithbookstack');
	}
    /**
	 * @param string $category String with the category and its subcategories, depending on the config they could be cut
	 *
	 * @return void
	*/
	function getBookstackSearchResults($categoryid)
	{
		// load plugin configuration
		$my_config = PluginGlpiwithbookstackConfig::getConfigurationValues('plugin:Glpiwithbookstack');
		$bookstack_url = $my_config['bookstack_url'] ?? '';
		$bookstack_token = [
			$my_config['bookstack_token_id'] ?? '',
			$my_config['bookstack_token_secret'] ?? ''
		];
		if ($bookstack_url === '' || $bookstack_token[0] === '' || $bookstack_token[1] === '') {
			return null;
		}
		global $DB;
		$search = '';
		if (!empty($my_config['search_category_name_only'])) {
			$result = $DB->request('SELECT name FROM glpi_itilcategories WHERE id = '.$categoryid);
			$row = is_array($result) && count($result) ? $result[0] : null;
			if ($row && isset($row['name'])) {
				$search = str_replace(' ', '+', $row['name']);
			}
		} else if (!empty($my_config['search_category_completename_but_only_visible'])) {
			$query = 'WITH RECURSIVE getParent AS (';
			$query .= ' SELECT 1 AS row_num, id AS child_id, name AS child_name, itilcategories_id AS child_itilcategories_id, is_helpdeskvisible as child_is_helpdeskvisible FROM glpi_itilcategories WHERE id = ';
			$query .= $categoryid;
			$query .= ' UNION ALL';
			$query .= ' SELECT row_num+1, id, name, itilcategories_id, is_helpdeskvisible FROM getParent, glpi_itilcategories WHERE id = child_itilcategories_id AND child_itilcategories_id <> 0)';
			$query .= ' SELECT GROUP_CONCAT(child_name ORDER BY row_num DESC SEPARATOR "+") AS part_name FROM getParent WHERE child_is_helpdeskvisible = 1;';
			$result = $DB->request($query);
			$row = is_array($result) && count($result) ? $result[0] : null;
			if ($row && isset($row['part_name']) && $row['part_name'] !== '') {
				$search = str_replace(' ', '+', str_replace(' > ', ' ', $row['part_name']));
			} else {
				return null;
			}
		} else {
			$result = $DB->request('SELECT completename FROM glpi_itilcategories WHERE id = '.$categoryid);
			$row = is_array($result) && count($result) ? $result[0] : null;
			if ($row && isset($row['completename'])) {
				$search = str_replace(' ', '+', str_replace(' > ', ' ', $row['completename']));
			}
		}
		if (!empty($my_config['search_in_tags_only'])) {
			$search = '['.$search.']';
		}
		if (!empty($my_config['search_type_pages_only'])) {
			$url_api   = $bookstack_url.'/api/search?count='.($my_config['display_max_search_results'] ?? 10).'&query='.$search.'+{type:page}';
			$url_front = $bookstack_url.'/search?term='.$search.'+{type:page}';
		} else {
			$url_api   = $bookstack_url.'/api/search?count='.($my_config['display_max_search_results'] ?? 10).'&query='.$search;
			$url_front = $bookstack_url.'/search?term='.$search;
		}
		$search_term = str_replace('[search_term]', '"'.$search.'"', $my_config['display_text_search_on_bookstack'] ?? 'Search Bookstack');
		$search_term = str_replace('+', ' ', $search_term);
		$url_display = '<a target="_blanc" href="'.$url_front.'">'.$search_term.'</a>';
		$ch = curl_init($url_api);
		curl_setopt($ch, CURLOPT_HTTPGET, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $my_config['curl_timeout'] ?? 5);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $my_config['curl_ssl_verifypeer'] ?? 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Token '.$bookstack_token[0].':'.$bookstack_token[1]]);
		$response_json = curl_exec($ch);
		curl_close($ch);
		if (!isset($response_json) || $response_json === false) {
			return null;
		}
		$response = json_decode($response_json, true);
		if (isset($response['error'])) {
			return null;
		}
		if (empty($response['total'])) {
			return null;
		}
		// ... (table rendering code omitted for brevity)
		return null;
	}
    /**
     * This function is called from GLPI to render the form when the user click
     *  on the menu item generated from getTabNameForItem()
    */
	public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0): bool
	{
		// call function for search result and display
		$config = new self();
		$table_with_results = $config->getBookstackSearchResults(($item->fields['itilcategories_id'] ?? ''));
		echo $table_with_results['table'];
		return true;
	}
	/**
		* Display contents at the begining of item forms.
		*
		* @param array $params Array with "item" and "options" keys
		*
		* @return void
		*/
	static public function postTicketForm($params) {
		$item = $params['item'];
		$options = $params['options'];

		// Check if option-id is not set that means new ticket and check if search for title is activated
		if (!isset($options['id']) && $item instanceof Ticket && true) // && $options['itilcategories_id'] !== 0
		{
			// call function for search result and display
			$config = new self();
			$table_with_results = $config->getBookstackSearchResults($options['itilcategories_id']);
			echo $table_with_results['table'];
			return true;
		}
		// Check if option-id is not set and categoy is set, that means new ticket and category selected
		else if (!isset($options['id']) && $item instanceof Ticket && $options['itilcategories_id'] !== 0)
		{
			// call function for search result and display
			$config = new self();
			$table_with_results = $config->getBookstackSearchResults($options['itilcategories_id']);
			echo $table_with_results['table'];
			return true;
		}
	}
}
