<?php
class ModxExchange1c {
	protected static $CATEGORIES = array();
	//protected static $PROPERTIES = array();
	protected $UPDATE_CATEGORIES = array();
	protected static $CATEGORIES_PRODUTION = array();
	function __construct(&$modx, $config = array()) {
		$this->modx   = $modx;
		$this->config = $config;
	}
	/**
   *  modeCheckauth
   * @
   */
	function modeCheckauth() {
		// Проверяем включен или нет модуль
		if (!$this->config['status']) {
			echo "failure\n";
			echo "1c module OFF";
			exit;
		}
		// Разрешен ли IP
		if ($this->config['allow_ip'] != '') {
			$ip        = $_SERVER['REMOTE_ADDR'];
			$allow_ips = explode("\r\n", $this->config['allow_ip']);
			if (!in_array($ip, $allow_ips)) {
				echo "failure\n";
				echo "IP is not allowed";
				exit;
			}
		}
		echo "success\n";
		echo session_name() ."\n";
		echo session_id() ."\n";
	}
	/*
   *  modeInit
   * @
   */
	function modeInit() {
		$tmp_files = glob($this->config['dir_temp'] .'*.*');
		if (is_array($tmp_files)) {
			foreach ($tmp_files as $v) {
				unlink($v);
			}
		}
		$limit = 100000 * 1024;
		echo "zip=no\n";
		echo "file_limit=". $limit ."\n";
	}
	/*
   *  modeFile
   * @
   */
	function modeFile() {
		$filename = basename($_REQUEST['filename']);
		$DATA     = file_get_contents("php://input");
		if ($DATA !== false) {
			if ($fp = fopen($this->config['dir_temp'] . $filename, "wb")) {
				$result = fwrite($fp, $DATA);
				if ($result === strlen($DATA)) {
					echo "success\n";
					chmod($this->config['dir_temp'], 0777);
				} else {
					echo "failure\n";
				}
			} else {
				echo "failure\n";
				echo "Can not open file: " . $this->config['dir_temp'] . $filename . "\n";
			}
		} else {
			echo "failure\n";
			echo "No data file\n";
		}
	}
	/**
   * modeImport
   * @
   */
	function modeImport() {
		$filename = basename($_REQUEST['filename']);
		if (isset($filename) && $this->config['status']) {
			$importFile = $this->config['dir_temp'] . $filename;
		} else {
			echo "failure\n";
			echo "ERROR 10: No file name variable";
			return 0;
		}
		if ($filename == 'import.xml') {
			$this->parseImport($importFile);
			echo "success\n";
		} elseif ($filename == 'offers.xml') {
			$this->parseOffers($importFile);
			echo "success\n";
		} else {
			echo "failure\n";
			echo $filename;
		} 
		$this->clearVars();
		return;
	}
	/**
	* Удаляем все неиспользуемые параметры товаров снятых с производства
	* @
	*/
	function clearVars(){
		// Удалим все TV параметры не спользуемые в шаблоне для товаров снятых с производства
		if($this->config['clear_tvs'] && $this->config['product_prodution_tpl_id'] && $this->config['prodution_catalog_id']) {
			$tv_p_res = $this->query("SELECT tmplvarid FROM " . $this->modx->getFullTableName('site_tmplvar_templates') . " WHERE templateid = " . $this->config['product_tpl_id']);
			while($tv = mysql_fetch_array($tv_p_res)) {
				$tv_product[] = $tv[0];
			}
			$tv_p_p_res = $this->query("SELECT tmplvarid FROM " . $this->modx->getFullTableName('site_tmplvar_templates') . " WHERE templateid = " . $this->config['product_prodution_tpl_id']);
			while($tv = mysql_fetch_array($tv_p_p_res)) {
				$tv_product_prodution[] = $tv[0];
			}
			$this->query("DELETE FROM " . $this->config['tbl_catalog_tv'] . " 
					WHERE contentid IN (" . implode(',',$this->modx->getChildIds($this->config['prodution_catalog_id'])) . ") 
					AND tmplvarid IN (" . implode(",", array_diff($tv_product,$tv_product_prodution)) . ")");
		}
		// Полная очистка кэша
		$this->modx->clearCache(); 
		include_once MODX_BASE_PATH . 'manager/processors/cache_sync.class.processor.php'; 
		$sync = new synccache(); 
		$sync->setCachepath(MODX_BASE_PATH . "assets/cache/"); 
		$sync->setReport(false); 
		$sync->emptyCache();
	}
	/**
   * Парсит товары и категории
   * @
   */
	function parseImport($importFile) {
		$xml        = simplexml_load_file($importFile);
		$data       = array();
		// Группы
		if ($xml->Классификатор->Группы) {
			$this->model('tool/category');
			$this->tool_category->getCategory($xml->Классификатор->Группы->Группа, $this->config['catalog_id']);
			$this->tool_category->cleanCategories();
		}
		// Свойства
		/*    if ($xml->Классификатор->Свойства) {
      $this->model('tool/attributes');
      $this->tool_attributes->insertAttribute($xml->Классификатор->Свойства->Свойство);
    }*/
	  // Товары
	  if ($xml->Каталог->Товары->Товар) {
		  foreach ($xml->Каталог->Товары->Товар as $product) {
			  $uuid          = explode('#', (string) $product->Ид);
			  $data['1c_id'] = $uuid[0];
			  $data['model'] = $product->Артикул ? (string) $product->Артикул : '';
			  $data['sku']   = $data['model'];
			  $data['name']  = $product->Наименование ? (string) trim($product->Наименование) : '';
			  if ($product->Группы) {
				  $data['category_1c_id'] = (string) $product->Группы->Ид;
			  }
			  if ($product->Описание) {
				  $data['description'] = (string) $product->Описание;
			  }
			  if ($product->Статус) {
				  $data['status'] = (string) $product->Статус;
			  }
			  // Свойства продукта
			  /*        if ($product->ЗначенияСвойств) {
          foreach ($product->ЗначенияСвойств->ЗначенияСвойства as $property) {
            if (isset(static::$PROPERTIES[(string) $property->Ид]['name'])) {
              $attribute = static::$PROPERTIES[(string) $property->Ид];
              if (isset($attribute['values'][(string) $property->Значение])) {
                $attribute_value = str_replace("'", "&apos;", (string) $attribute['values'][(string) $property->Значение]);
              } else if ((string) $property->Значение != '') {
                $attribute_value = str_replace("'", "&apos;", (string) $property->Значение);
              } else {
                continue;
              }
              switch ($attribute['name']) {
                case 'Производитель':
                  $manufacturer_name = $attribute_value;
                  $manufacturer_id   = $this->getValue("SELECT id FROM " . $this->config['tbl_catalog'] . " WHERE pagetitle = '" . $manufacturer_name . "'");
                  if (!$manufacturer_id) {
                    $data_manufacturer = array(
                      'type' => 'document',
                      'parent' => $this->config['brands_catalog_id'],
                      'pagetitle' => $manufacturer_name,
                      'longtitle' => $manufacturer_name,
                      'alias' => $this->translit($manufacturer_name),
                      'template' => $this->config['brand_tpl_id'],
                      'hidemenu' => 1,
                      'published' => 1,
                      'deleted' => 0,
                      'createdon' => $this->config['currentdate'],
                      'editedon' => $this->config['currentdate']
                    );
                    $manufacturer_id   = $this->insert($data_manufacturer, $this->config['tbl_catalog']);
                  }
                  $data['manufacturer_name'] = $manufacturer_name;
                  $data['manufacturer_id']   = $manufacturer_id;
                  break;
                default:
                  $attribute_value             = explode(", ", $attribute_value);
                  $data['product_attribute'][] = array(
                    'attribute_id' => $attribute['id'],
                    'product_attribute_description' => $attribute_value
                  );
              }
            }
          }
        }*/
		  // Реквизиты продукта
		  if ($product->ЗначенияРеквизитов) {
			  foreach ($product->ЗначенияРеквизитов->ЗначениеРеквизита as $requisite) {
				  switch ($requisite->Наименование) {
					  case 'ОписаниеВФорматеHTML':
					  $data['description'] = $requisite->Значение ? (string) $requisite->Значение : '';
					  break;
					  case 'Код':
					  $data['uid'] = (string) $requisite->Значение;
					  break;
					  case 'Полное наименование':
					  $data['fullname']  = $requisite->Значение ? (string) trim(str_replace($product->Наименование, '' , $requisite->Значение)) : '';
					  break; 	
				  }
			  }
		  }
		  $this->setProduct($data);
		  unset($data);
	  }
	}
	  if ($this->config['delete_products']) {
		  if ($this->config['moved_deleted_product_to_trash'])
			  $parent_trash = ", parent = '" . $this->config['trash_catalog_id'] . "'";
		  $this->query("UPDATE " . $this->config['tbl_catalog'] . " SET deleted = '1' " . $parent_trash . " WHERE parent IN (" . implode(",", $this->UPDATE_CATEGORIES) . ") AND editedon != " . $this->config['currentdate'] . "");
	  }
	  unset($xml, $this->UPDATE_CATEGORIES);
  }
	/**
   * Функция работы с продуктом
   *
   * @param array
   */
	private function setProduct($product) {
		if (!$product)
			return;
		//Проверяем есть ли такой товар в БД
		$product_id = ''; 
		// по коду
		if (!$product_id && $this->config['uid']) {
			$product_id = $this->getValue('SELECT id FROM ' . $this->config['tbl_catalog'] . ' 
						WHERE ' . $this->config['uid'] . ' = "' . (string) $product['uid'] . '" OR '.$this->config['xml_id'].' = "'.(string) $product['1c_id'].'" AND isfolder = 0');
	}
	  // по имени
	  if (!$product_id) {
		  $title      = $this->escape(trim((string) $product['name']));
		  $title_trim = str_replace(' ', '', $title);
		  $product_id = $this->getValue('SELECT id FROM ' . $this->config['tbl_catalog'] . ' WHERE 
						`pagetitle` = "' . $title . '" OR 
						`alias` = "' . $this->translit($title) . '" OR
						`pagetitle` = "' . $title_trim . '" OR 
						`alias` = "' . $this->translit($title_trim) . '"');
	}
	  $data = $this->initProduct($product);
	  $this->model('tool/product');
	  if ($product_id) {
		  if ($this->config['update_products']) {
			  $this->tool_product->editProduct($product_id, $data);
		  }
		  
	  } else {
		  if ($this->config['add_products']) {
			  $product_id = $this->tool_product->addProduct($data);
		  }
	  }
  }
	/**
   * Обновляет массив с информацией о продукте
   *
   * @param         array         новые данные
   * @param         array         обновляемые данные
   * @return         array
   */
	private function initProduct($product, $data = array()) {
		$result = array(
			$this->config['xml_id'] => $product['1c_id'],
			'model' => (isset($product['model'])) ? $product['model'] : (isset($data['model']) ? $data['model'] : ''),
			'sku' => (isset($product['sku'])) ? $product['sku'] : (isset($data['sku']) ? $data['sku'] : ''),
			'uid' => (isset($product['uid'])) ? $product['uid'] : (isset($data['uid']) ? $data['uid'] : ''),
			'manufacturer_id' => (isset($product['manufacturer_id'])) ? $product['manufacturer_id'] : (isset($data['manufacturer_id']) ? $data['manufacturer_id'] : 0),
			'manufacturer_name' => (isset($product['manufacturer_name'])) ? $product['manufacturer_name'] : (isset($data['manufacturer_name']) ? $data['manufacturer_name'] : ''),
			'createdon' => $this->config['currentdate'],
			'product_attribute' => (isset($product['product_attribute'])) ? $product['product_attribute'] : (isset($data['product_attribute']) ? $data['product_attribute'] : array())
		);
		$result['product_description'] = array(
			'name' => isset($product['name']) ? $product['name'] : (isset($data['product_description']['name']) ? $data['product_description']['name'] : ''),
			'fullname' => isset($product['fullname']) ? $product['fullname'] : (isset($data['product_description']['fullname']) ? $data['product_description']['fullname'] : ''),
			'description' => isset($product['description']) ? "<p>" . htmlentities(strip_tags(preg_replace('|\s+|', ' ', $product['description'])), ENT_QUOTES, "UTF-8") . "</p>" : (isset($data['product_description']['description']) ? $data['product_description']['description'] : '')
		);
		if (isset($product['category_1c_id']) && isset(static::$CATEGORIES[$product['category_1c_id']])) {
			$result['product_category'] = (int) static::$CATEGORIES[$product['category_1c_id']];
		} else {
			$result['product_category'] = $data['product_category'] ? $data['product_category'] : 0;
		}
		if ($product['category_1c_id'] && static::$CATEGORIES_PRODUTION[$product['category_1c_id']] && $this->config['product_prodution_tpl_id']) {
			$result['template'] = $this->config['product_prodution_tpl_id'];
		} else {
			$result['template'] = $this->config['product_tpl_id'];
		}
		$this->UPDATE_CATEGORIES[$result['product_category']] = $result['product_category'];
		return $result;
	}
	/**
   * Парсит цены и количество
   *
   * @param    string    наименование типа цены
   */
	public function parseOffers($importFile) {
		$xml         = simplexml_load_file($importFile);
		$data        = array();
		$price_types = array();
		if ($xml->ПакетПредложений->ТипыЦен->ТипЦены) {
			foreach ($xml->ПакетПредложений->ТипыЦен->ТипЦены as $type) {
				$price_types[(string) $type->Ид] = (string) $type->Наименование;
			}
		}
		if ($xml->ПакетПредложений->Предложения->Предложение) {
			foreach ($xml->ПакетПредложений->Предложения->Предложение as $offer) {
				//UUID без номера после #
				$uuid               = explode("#", $offer->Ид);
				$data['1c_id']      = $uuid[0];
				$data['product_id'] = $this->getValue('SELECT id FROM ' . $this->config['tbl_catalog'] . ' WHERE '.$this->config['xml_id'].'="'.$this->escape($data['1c_id']).'" AND isfolder = 0');
				//Цена за единицу
				if ($offer->Цены) {
					// найдём валюту закупочной цены
					if (!empty($this->config['price_currency']) && $offer->Цены->Цена->ИдТипаЦены) {
						foreach ($offer->Цены->Цена as $price) {
							if ($price_types[(string) $price->ИдТипаЦены] == $this->config['price_currency']) {
								$data['currency'] = (string) $price->Валюта;
							}
						}
					}
					// Первая цена по умолчанию - $config_price_type_main
					if (!$this->config['price_type']) {
						$data['price'] = (float) $offer->Цены->Цена->ЦенаЗаЕдиницу;
					} else {
						if ($offer->Цены->Цена->ИдТипаЦены) {
							foreach ($offer->Цены->Цена as $price) {
								if ($price_types[(string) $price->ИдТипаЦены] == $this->config['price_type']) {
									$data['price'] = (float) sprintf("%0.2f", ($price->ЦенаЗаЕдиницу / $this->config[$data['currency']]));
								}
							}
						}
					}
					// Вторая цена и тд - $discount_price_type
					if (!empty($this->config['price_additional_type']) && $offer->Цены->Цена->ИдТипаЦены && $this->config['price_additional']) {
						foreach ($offer->Цены->Цена as $price) {
							$key = $price_types[(string) $price->ИдТипаЦены];
							if (isset($this->config['price_additional_type'][$key])) {
								$value                      = array(
									'id' => (string) $price->ИдТипаЦены,
									'type' => $key,
									'position' => $this->config['price_additional_type'][$key]['position'],
									'price' => (float) sprintf("%0.0f", ($price->ЦенаЗаЕдиницу / $this->config[$data['currency']]))
								);
								$data['price_additional'][] = $value;
								unset($value);
							}
						}
					}
					//Количество
					$data['quantity'] = isset($offer->Количество) ? (int) $offer->Количество : 0;
					//Характеристики
			/*if ($offer->ХарактеристикиТовара && $this->config['add_options']) {
            $product_option_value_data = array();
            $product_option_data       = array();
            $count                     = count($offer->ХарактеристикиТовара->ХарактеристикаТовара);
            foreach ($offer->ХарактеристикиТовара->ХарактеристикаТовара as $i => $opt) {
              $name_1c  = $this->escape((string) $opt->Наименование);
              $value_1c = $this->escape((string) $opt->Значение);
              if (!empty($name_1c) && !empty($value_1c)) {
                $product_option_value_data[] = array(
                  'product_option_value_id' => $uuid[0],
                  'option_value_id' => $uuid[1],
                  'quantity' => isset($data['quantity']) ? (int) $data['quantity'] : 0,
                  'price' => isset($data['price']) ? (int) $data['price'] : 0,
                  'value' => $value_1c
                );
                $product_option_data         = array(
                  'option_id' => $uuid[1],
                  'product_option_id' => $uuid[0],
                  'name' => (string) $name_1c,
                  'type' => 'select',
                  'required' => 1,
                  'product_option_value' => $product_option_value_data
                );
              }
            }
            $this->setOption($product_option_data);
          }*/
		}
		  if ($this->config['update_prices']) {
			  $this->setPrice($data);
		  }
		  unset($data);
	  }
	}
  }
	/**
	* Установка характеристик
	*
	*/
	/*  private function setOption($data) {
    $option_id            = $this->getValue("SELECT id FROM " . $this->config['tbl_opt'] . " WHERE option_id = '" . $data['option_id'] . "'");
    $product_option_value = $data['product_option_value'];
    unset($data['product_option_value']);
    if ($option_id) {
      $this->update($data, $this->config['tbl_opt'], "id = " . $option_id);
      $this->setOptionValue($option_id, $product_option_value);
    } else {
      $option_id = $this->insert($data, $this->config['tbl_opt']);
      $this->setOptionValue($option_id, $product_option_value);
    }
  }*/
	/**
	* Установка значения характеристики
	*
	*/
	/*  private function setOptionValue($option_id, $values) {
    foreach ($values as $val) {
      $value_id = $this->getValue("SELECT id FROM " . $this->config['tbl_opt_val'] . " WHERE value = '" . $val['value'] . "' AND option_value_id = '" . $val['option_value_id'] . "'");
      if ($value_id) {
        $this->update($val, $this->config['tbl_opt_val'], "id = " . $value_id);
      } else {
        $this->insert($val, $this->config['tbl_opt_val']);
      }
    }
  }*/
	/**
	* Установка цены
	*
	*/
	private function setPrice($data) {
		if ($data['1c_id']) {
			$price_id    = $this->getValue('SELECT id FROM ' . $this->config['tbl_catalog_tv'] . ' WHERE tmplvarid = "' . $this->config['price_id'] . '" AND contentid = "' . $data['product_id'] . '"');
			$currency_id = $this->getValue('SELECT id FROM ' . $this->config['tbl_catalog_tv'] . ' WHERE tmplvarid = "' . $this->config['currency_id'] . '" AND contentid = "' . $data['product_id'] . '"');
			if ($price_id) {
				$this->update(array(
					'value' => $data['price']
				), $this->config['tbl_catalog_tv'], "id = " . $price_id);
			} else {
				$this->insert(array(
					'tmplvarid' => $this->config['price_id'],
					'contentid' => $data['product_id'],
					'value' => $data['price']
				), $this->config['tbl_catalog_tv']);
			}
			if ($currency_id && $data['currency'] != 'руб') {
				$this->update(array(
					'value' => $data['currency']
				), $this->config['tbl_catalog_tv'], "id = " . $currency_id);
			} elseif (!$currency_id && $data['currency'] != 'руб') {
				$this->insert(array(
					'tmplvarid' => $this->config['currency_id'],
					'contentid' => $data['product_id'],
					'value' => $data['currency']
				), $this->config['tbl_catalog_tv']);
			} elseif ($currency_id && $data['currency'] == 'руб') {
				$this->query("DELETE FROM " . $this->config['tbl_catalog_tv'] . " WHERE id = " . $currency_id);
			}
		}
	}
	protected function query($SQL) {
		return $this->modx->db->query($SQL);
	}
	protected function update($array, $table, $where = '') {
		return $this->modx->db->update($array, $table, $where);
	}
	protected function insert($array, $table) {
		$this->modx->db->insert($array, $table);
		return $this->modx->db->getInsertId();
	}
	protected function getValue($SQL) {
		return $this->modx->db->getValue($this->modx->db->query($SQL));
	}
	protected function escape($escape) {
		return $this->modx->db->escape($escape);
	}
	/**
	* Подгружаем классы по шаблону
	* @ $this->model('tool/product');
	* @ $this->tool_product-> .....
	*/
	function model($model) {
		$file  = MODX_BASE_PATH . MGR_DIR . '/1c_exchange/' . $model . '.php';
		$class = 'Modx' . preg_replace('/[^a-zA-Z0-9]/', '', $model);
		if (file_exists($file)) {
			include_once($file);
			$obj        = str_replace('/', '_', $model);
			$this->$obj = new $class($this->modx, $this->config);
		} else {
			trigger_error('Error: Could not load model ' . $file . '!');
			exit();
		}
	}
	function SaveLog($data) {
		$tbl_manager_log = $this->modx->getFullTableName('manager_log');
		$fields['timestamp']   = time();
		$fields['internalKey'] = $this->modx->db->escape($data['internalKey']);
		$fields['username']    = $this->modx->db->escape($data['username']);
		$fields['action']      = $data['action'];
		$fields['itemid']      = $data['itemId'];
		$fields['itemname']    = $this->modx->db->escape($data['itemName']);
		$fields['message']     = $this->modx->db->escape($data['msg']);
		$this->insert($fields,$tbl_manager_log);
	}
	/**
	* транслит из русского
	*
	*/
	function translit($text) {
		$ru   = explode('-', "А-а-Б-б-В-в-Ґ-ґ-Г-г-Д-д-Е-е-Ё-ё-Є-є-Ж-ж-З-з-И-и-І-і-Ї-ї-Й-й-К-к-Л-л-М-м-Н-н-О-о-П-п-Р-р-С-с-Т-т-У-у-Ф-ф-Х-х-Ц-ц-Ч-ч-Ш-ш-Щ-щ-Ъ-ъ-Ы-ы-Ь-ь-Э-э-Ю-ю-Я-я");
		$en   = explode('-', "A-a-B-b-V-v-G-g-G-g-D-d-E-e-YO-yo-E-e-ZH-zh-Z-z-I-i-I-i-I-i-Y-y-K-k-L-l-M-m-N-n-O-o-P-p-R-r-S-s-T-t-U-u-F-f-H-h-TS-ts-CH-ch-SH-sh-SCH-sch---Y-y---E-e-YU-yu-YA-ya");
		$text = str_replace($ru, $en, $text);
		$text = preg_replace("/[\s]+/ui", '-', $text);
		$text = strtolower(preg_replace("/[^0-9a-zа-я\-]+/ui", '', $text));
		$id   = $this->getValue("SELECT id FROM " . $this->config['tbl_catalog'] . " WHERE alias='" . $text . "' LIMIT 1");
		if ($id) {
			$text = $text . "-" . $id;
		}
		return $text;
	}
}
?>
