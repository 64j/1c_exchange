<?php
class EX1Cmanager {
	function __construct(&$modx) {
		$this->modx                       = $modx;
		$this->dbname                     = $modx->db->config['dbase'];
		$this->mod_tbl_config             = $modx->db->config['table_prefix'] . "exchange1c_manager_config"; // таблица конфигурации модуля
		$this->mod_tbl_catalog            = $modx->getFullTableName('site_content'); // таблица с документами/ресурсами
		$this->mod_tbl_catalog_tv         = $modx->getFullTableName('site_tmplvar_contentvalues'); // таблица с ТВ параметрами
		$this->mod_tbl_attr_group         = $modx->getFullTableName('exchange1c_product_attributes_group'); // таблица с категориями свойств
		$this->mod_tbl_attr               = $modx->getFullTableName('exchange1c_product_attributes'); // таблица свойств
		$this->mod_tbl_opt                = $modx->getFullTableName('exchange1c_product_options'); // таблица с характеристиками
		$this->mod_tbl_opt_val            = $modx->getFullTableName('exchange1c_product_options_values'); // таблица со значениями характеристик
		$this->mod_dir_temp               = MODX_BASE_PATH . MGR_DIR . '/1c_exchange/temp/'; // Временная папка для обмена выгрузкой/загрузкой
		$this->mod_price_additional       = ''; // true | false - Включить дополнительные цены товара
		$this->mod_price_additional_type  = serialize(array('Мелкий опт' => array('position' => 1),'Крупный опт' => array('position' => 2))); // Дополнительные цены товара
		$this->xml_id                     = 'xml_id'; // название колонки в таблице $this->mod_tbl_catalog с уникальным идентификатором 1С
	}
	
	
	function modInstall() {
		$sql = array();
		// добавим колонку в таблицу $this->mod_tbl_catalog для уникального идентификатора 1С
		$sql[] = "ALTER TABLE $this->mod_tbl_catalog ADD COLUMN $this->xml_id varchar (255)";
		
		// создаём таблицы для свойств номенклатуры
		$sql[] = "CREATE TABLE IF NOT EXISTS $this->mod_tbl_attr_group (`id` int(11) NOT NULL auto_increment,`name` varchar(255) NOT NULL, `attr_values` TEXT, `visible` INT(1), `attr_index` INT(11), `attr_group_id` varchar(255) NOT NULL, PRIMARY KEY (`id`));";
		$sql[] = "CREATE TABLE IF NOT EXISTS $this->mod_tbl_attr (`id` int(20) NOT NULL auto_increment,`product_id` varchar(255) NOT NULL, `attribute_id` varchar(255) NOT NULL, `text` TEXT, PRIMARY KEY (`id`));";
		
		// создаём таблицы для характеристик номенклатуры
		$sql[] = "CREATE TABLE IF NOT EXISTS $this->mod_tbl_opt (`id` int(20) NOT NULL auto_increment,`option_id` varchar(255) NOT NULL, `product_option_id` varchar(255) NOT NULL, `name` varchar(255) NOT NULL, `type` varchar(255) NOT NULL, `required` INT(1), `position` INT(11) NOT NULL, PRIMARY KEY (`id`));";
		$sql[] = "CREATE TABLE IF NOT EXISTS $this->mod_tbl_opt_val (`id` int(20) NOT NULL auto_increment,`product_option_value_id` varchar(255) NOT NULL,`option_value_id` varchar(255) NOT NULL, `quantity` varchar(255) NOT NULL, `price` varchar(255) NOT NULL, `value` varchar(255) NOT NULL, `position` INT(11) NOT NULL, PRIMARY KEY (`id`));";
		
		// создаём таблицу с конфигурацией модуля
		$sql[] = "CREATE TABLE IF NOT EXISTS `$this->mod_tbl_config` (`id` INT(11) NOT NULL AUTO_INCREMENT, `setting` VARCHAR(255), `value` TEXT, PRIMARY KEY (`id`));";
		
		// заполняем конфигурацию модуля
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_version', '$this->mod_cur_version');"; // версия модуля
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_user', 'user');"; // пользователь
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_pass', 'pass');"; // пароль
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_status', '1');"; // статус, включить/выключить модуль загрузки
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_allow_ip', '');"; // разрешённые IP
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_price_id', '4');"; // id тв-параметра цены
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_currency_id', '3');"; // id тв-параметра валюты
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_catalog_id', '3');"; // id каталога товаров
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_brands_catalog_id', '');"; // id раздела производители
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_brand_tpl_id', '');"; // id шаблона производителя
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_brand_tv_id', '');"; // id тв-параметра производителя
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_category_podcat_tpl_id', '7');"; // id шаблона подкатегории товаров
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_category_tpl_id', '7');"; // id шаблона, непосредственно самой категории с товарами
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_product_tpl_id', '8');"; // id шаблона товара
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_trash_catalog_id', '179');"; // id раздела корзины, в которую переносятся товары при удалении
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_prodution_catalog_id', '');"; // id раздела товаров снятых с производства
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_product_prodution_tpl_id', '');"; // id шаблона для товаров снятых с производства
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_add_products', '1');"; // добавлять товары
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_update_products', '1');"; // обновлять товары
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_update_description_product', '');"; // обновлять описания товаров
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_delete_products', '');"; // удалять товары
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_moved_deleted_product_to_trash', '');"; // переносить удалённые товары в корзину
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_add_categories', '1');"; // добавлять категории товаров
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_update_categories', '1');"; // обновлять категории товаров
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_delete_categories', '1');"; // удалять категории товаров
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_moved_deleted_category_to_trash', '');"; // переносить удалённые категории товаров в корзину
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_update_prices', '1');"; // обновлять цены
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_add_options', '');"; // добавлять характеристики
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_clear_tvs', '');"; // удалять неиспользуемые тв-параметры товаро снятых с производства
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_xml_id', '$this->xml_id');"; // название колонки в таблице $this->mod_tbl_catalog с уникальным идентификатором 1С 
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_uid', 'description');"; // поле в таблице $this->mod_tbl_catalog по которому будут синхронизироваться товары, в 1С это Код товара
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_price_type', 'Розничная');"; // название розничной цены 
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_price_currency', 'Закупочная');"; // название закупочной цены, используется только для получения валюты товара
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_price_additional', '$this->mod_price_additional');"; // включить дополнительные цены товара
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_price_additional_type', '$this->mod_price_additional_type');"; // сериализованный массив с дополнительными ценами товаров
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_dir_temp', '$this->mod_dir_temp');"; // папка синхронизации с временными файлами 1С
		// добавляем в таблицу конфигурации полные названия таблиц
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_tbl_catalog', '$this->mod_tbl_catalog');"; // таблица с документами/ресурсами
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_tbl_catalog_tv', '$this->mod_tbl_catalog_tv');"; // таблица с ТВ параметрами
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_tbl_attr_group', '$this->mod_tbl_attr_group');"; // таблица с категориями свойств
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_tbl_attr', '$this->mod_tbl_attr');"; // таблица свойст
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_tbl_opt', '$this->mod_tbl_opt');"; // таблица с характеристиками
		$sql[] = "INSERT INTO `$this->mod_tbl_config` VALUES (NULL, 'conf_tbl_opt_val', '$this->mod_tbl_opt_val');"; // таблица со значениями характеристик
		
		foreach ($sql as $line) {
			$this->modx->db->query($line);
		}
		
	}
	
	/**
   * Получаем полную конфигурацию модуля
   *     
   * @return array
   */
	function getModConfig() {
		$output = array();
		if (mysql_num_rows(mysql_query("SHOW TABLES FROM $this->dbname LIKE '$this->mod_tbl_config'")) > 0) {
			$config_query = $this->modx->db->select("*", $this->mod_tbl_config);
			while ($config = mysql_fetch_array($config_query)) {
				$output[$config[1]] = $config[2];
			}
		}
		return $output;
	}
	
	/**
   * Сохраняет конфигурацию модуля
   *     
   * @param array $data
   */
	function saveConfig($data) {
		$config = array(
			'conf_user' => array("value" => $this->modx->db->escape($data['user'])),
			'conf_pass' => array("value" => $this->modx->db->escape($data['pass'])),
			'conf_status' => array("value" => $data['status']),
			'conf_allow_ip' => array("value" => $this->modx->db->escape($data['allow_ip'])),
			'conf_price_id' => array("value" => $this->modx->db->escape($data['price_id'])),
			'conf_currency_id' => array("value" => $this->modx->db->escape($data['currency_id'])),
			'conf_catalog_id' => array("value" => $this->modx->db->escape($data['catalog_id'])),
			'conf_brands_catalog_id' => array("value" => $this->modx->db->escape($data['brands_catalog_id'])),
			'conf_brand_tpl_id' => array("value" => $this->modx->db->escape($data['brand_tpl_id'])),
			'conf_brand_tv_id' => array("value" => $this->modx->db->escape($data['brand_tv_id'])),
			'conf_category_podcat_tpl_id' => array("value" => $this->modx->db->escape($data['category_podcat_tpl_id'])),
			'conf_category_tpl_id' => array("value" => $this->modx->db->escape($data['category_tpl_id'])),
			'conf_product_tpl_id' => array("value" => $this->modx->db->escape($data['product_tpl_id'])),
			'conf_trash_catalog_id' => array("value" => $this->modx->db->escape($data['trash_catalog_id'])),
			'conf_prodution_catalog_id' => array("value" => $this->modx->db->escape($data['prodution_catalog_id'])),
			'conf_product_prodution_tpl_id' => array("value" => $this->modx->db->escape($data['product_prodution_tpl_id'])),
			'conf_add_products' => array("value" => $data['add_products']),
			'conf_update_products' => array("value" => $data['update_products']),
			'conf_update_description_product' => array("value" => $data['update_description_product']),
			'conf_delete_products' => array("value" => $data['delete_products']),
			'conf_moved_deleted_product_to_trash' => array("value" => $data['moved_deleted_product_to_trash']),
			'conf_add_categories' => array("value" => $data['add_categories']),
			'conf_update_categories' => array("value" => $data['update_categories']),
			'conf_delete_categories' => array("value" => $data['delete_categories']),
			'conf_moved_deleted_category_to_trash' => array("value" => $data['moved_deleted_category_to_trash']),
			'conf_update_prices' => array("value" => $data['update_prices']),
			'conf_add_options' => array("value" => $data['add_options']),
			'conf_clear_tvs' => array("value" => $data['clear_tvs']),
			'conf_uid' => $data['uid'] ? array("value" => $this->modx->db->escape($data['uid'])) : array("value" => "link_attributes"),
			'conf_price_type' => $data['price_type'] ? array("value" => $this->modx->db->escape($data['price_type'])) : array("value" => "Розничная"),
			'conf_price_currency' => $data['price_currency'] ? array("value" => $this->modx->db->escape($data['price_currency'])) : array("value" => "Закупочная")
		);
		
		foreach ($config as $key => $value) {
			$query = $this->modx->db->update($value, $this->mod_tbl_config, "setting = '$key'");
		}
	}
	
	/**
   * Удаляет все данные модуля из БД
   */
	function modUninstall() {
		$sql   = array();
		$sql[] = "ALTER TABLE $this->mod_tbl_catalog DROP COLUMN $this->xml_id";
		$sql[] = "DROP TABLE IF EXISTS $this->mod_tbl_config";
		$sql[] = "DROP TABLE IF EXISTS $this->mod_tbl_attr_group";
		$sql[] = "DROP TABLE IF EXISTS $this->mod_tbl_attr";
		$sql[] = "DROP TABLE IF EXISTS $this->mod_tbl_opt";
		$sql[] = "DROP TABLE IF EXISTS $this->mod_tbl_opt_val";
		
		foreach ($sql as $line) {
			$this->modx->db->query($line);
		}
	}
	
}
