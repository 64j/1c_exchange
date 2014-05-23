<?php
defined('IN_MANAGER_MODE') or die();
$theme       = $modx->config['manager_theme'];
$mod_page    = "index.php?a=112&id=" . $_GET['id'];
$cur_version = '0.1';

define("EXCHANGE1C_PATH", "../manager/1c_exchange/");

require_once EXCHANGE1C_PATH . "classes/class.exchange_1c.manager.php";

$exchange1c                     = new EX1Cmanager($modx);
$exchange1c->mod_cur_version    = $cur_version;

//Настройки модуля
$tmp_config = $exchange1c->getModConfig();
extract($tmp_config);

$installed = isset($conf_version) ? 1 : 0;

$action = !empty($_GET['action']) ? $_GET['action'] : (!empty($_POST['action']) ? $_POST['action'] : '');
switch ($action) {
	
	//Установка модуля
	case 'install':
	$exchange1c->modInstall();
	$modx->sendRedirect($mod_page, 0, "REDIRECT_HEADER");
	break;
	
	//Удаление модуля
	case "uninstall":
	if (!$modx->hasPermission('save_document')) {
		global $e;
		$e->setError(3);
		$e->dumpError();
		exit;
	}
	$exchange1c->modUninstall();
	$modx->sendRedirect($mod_page, 0, "REDIRECT_HEADER");
	break;
	
	//Сохранение конфигурации
	case "save_config":
	if (!$modx->hasPermission('save_document')) {
		global $e;
		$e->setError(3);
		$e->dumpError();
		exit;
	}
	$exchange1c->saveConfig($_POST);
	$modx->sendRedirect($mod_page, 0, "REDIRECT_HEADER");
	break;
	
	
	// страница свойств номенклатуры	
	case "load_attributes":
	include "tpl/header.tpl.php";
	include "tpl/page_attributes.tpl.php";
	include "tpl/footer.tpl.php";
	break;
	
	// страница характеристик номенклатуры
	case "load_options":
	include "tpl/header.tpl.php";
	include "tpl/page_options.tpl.php";	
	include "tpl/footer.tpl.php";
	break;
	
	//Страница модуля
	default:
	include "tpl/header.tpl.php";
	
	if ($installed == 0) {
		echo '<br /><ul class="actionButtons"><li><a href="' . $mod_page . '&action=install">Установить модуль</a></li></ul>';
	} else {
		include "tpl/config.tpl.php";
	}
	
	include "tpl/footer.tpl.php";
	break; 
}
?>
