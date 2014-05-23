<?php
define('MODX_API_MODE', true);
include_once("../../index.php");
$modx->db->connect();
if (empty($modx->config)) {
	$modx->getSettings();
}
// Загружаем конфигурацию
$config = array();
if (mysql_num_rows(mysql_query("SHOW TABLES FROM " . $modx->db->config['dbase'] . " LIKE '" . $modx->db->config['table_prefix'] . "exchange1c_manager_config'")) > 0) {
	$config_query = $modx->db->select("*", $modx->getFullTableName('exchange1c_manager_config'));
	while ($conf = mysql_fetch_array($config_query)) {
		if ($conf[1] == 'price_additional_type') {
			$conf[2] = unserialize($conf[2]);
		}
		$config[str_replace("conf_", "", $conf[1])] = $conf[2];
	}
}

$user = $config['user'];
$pass = $config['pass'];

if (!isset($_SERVER['PHP_AUTH_USER']) or $_SERVER['PHP_AUTH_USER']!==$user or $_SERVER['PHP_AUTH_PW']!==$pass) {
	
	header('WWW-Authenticate: Basic realm="Auth"');
	header('HTTP/1.0 401 Unauthorized');
	exit("Sorry, Access Denied");
	
} else {

	// добавляем в конфиг что не хватило
	$config['currentdate'] = time() + $modx->config['server_offset_time']; // текущая дата
	$config['USD']         = $modx->getConfig('USD') ? $modx->getConfig('USD') : 1; // курс доллара
	$config['EUR']         = $modx->getConfig('EUR') ? $modx->getConfig('EUR') : 1; // курс евро
	$config['руб']      = $modx->getConfig('RUB') ? $modx->getConfig('RUB') : 1; // курс рубля
	
	// разбираем POST запросы
	if (isset($_REQUEST['mode']) && $_REQUEST['type'] == 'catalog') {
		require_once(MODX_BASE_PATH . MGR_DIR . "/1c_exchange/classes/class.exchange_1c.catalog_import.php");
		$ModxExchange1c = new ModxExchange1c($modx, $config);
		switch ($_REQUEST['mode']) {
			case 'checkauth':
			$ModxExchange1c->modeCheckauth();
			break;
			case 'init':
			$ModxExchange1c->modeInit();
			break;
			case 'file':
			$ModxExchange1c->modeFile();
			break;
			case 'import':
			$ModxExchange1c->modeImport();
			break;
			default:
			echo "success\n";
		}
	} else {
		echo "failure\n";
		exit;
	}
}
?>
