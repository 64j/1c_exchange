<?php
class ModxToolAttributes extends ModxExchange1c {
	function __construct(&$modx, $config) {
		parent::__construct($modx, $config);
	}
	/**
   * Создает атрибуты из свойств
   *
   * @param         SimpleXMLElement
   */
	function insertAttribute($xml) {
		foreach ($xml as $attribute) {
			$id     = (string) $attribute->Ид;
			$name   = (string) $attribute->Наименование;
			$values = array();
			if ((string) $attribute->ВариантыЗначений) {
				if ((string) $attribute->ТипЗначений == 'Справочник') {
					foreach ($attribute->ВариантыЗначений->Справочник as $option_value) {
						if ((string) $option_value->Значение != '') {
							$values[(string) $option_value->ИдЗначения] = (string) $option_value->Значение;
						}
					}
				}
			}
			$old_values = $this->getValue('SELECT attr_values FROM ' . $this->config['tbl_attr_group'] . ' WHERE attr_group_id = "' . $id . '"');
			$old_values = unserialize($old_values);
			$new_values = $values;
			if ($old_values) {
				$values = array_merge($old_values, $new_values);
			}
			$values  = $values ? serialize($values) : '';
			$data    = array(
				'name' => $name,
				'attr_values' => $values,
				'visible' => 1,
				'attr_index' => $i++,
				'attr_group_id' => $id
			);
			$attr_id = $this->getValue('SELECT id FROM ' . $this->config['tbl_attr_group'] . ' WHERE attr_group_id = "' . $id . '"');
			if (!$attr_id) {
				$attr_id = $this->addAttribute($data);
			} else {
				$data['id'] = $attr_id;
				$this->updateAtribute($data);
			}
			parent::$PROPERTIES[$id] = array(
				'id' => $attr_id,
				'name' => $name,
				'values' => $values ? unserialize($values) : ''
			);
		}
		unset($xml);
	}
	/**
	* Добавляем свойства
	* @data
	*/
	function addAttribute($data) {
		return $this->insert($data, $this->config['tbl_attr_group']);
	}
	/**
	* Обновляем свойства
	*@data
	*/
	function updateAtribute($data) {
		$this->update($data, $this->config['tbl_attr_group'], 'id = "' . $data['id'] . '"');
	}
}
