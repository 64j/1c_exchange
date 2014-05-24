<?php
class ModxToolCategory extends ModxExchange1c {
	private $CATEGORIES_ID = array();
	function __construct(&$modx, $config) {
		parent::__construct($modx, $config);
	}
	/**
	* Работа с категориями
	*/
	function getCategory($xml, $parent = 0) {
		$i = 0;
		foreach ($xml as $category) {
			if (isset($category->Ид) && isset($category->Наименование)) {
				$id    = (string) $category->Ид;
				$title = $this->escape(trim($category->Наименование));
				if ($category->Группы) {
					$template = $this->config['category_podcat_tpl_id'];
				} else {
					$template = $this->config['category_tpl_id'];
				}
				$category_id = (int) $this->getValue('SELECT id FROM ' . $this->config['tbl_catalog'] . ' WHERE '.$this->config['xml_id'].'="'. $this->escape($id) .'"');
				// Ставим родитель для раздела Оборудование снятое с производства
				if ($this->config['prodution_catalog_id'] && !$category_id && $title == $this->getValue('SELECT pagetitle FROM ' . $this->config['tbl_catalog'] . ' WHERE id="' . $this->config['prodution_catalog_id'] . '" AND isfolder = 1')) {
					$parent = 0;
					$category_id = $this->config['prodution_catalog_id'];
				}
				//
				if (!$category_id) {
					$category_id = (int) $this->getValue('SELECT id FROM ' . $this->config['tbl_catalog'] . ' WHERE pagetitle = "' . $title . '" AND parent = "' . $parent . '" AND isfolder = 1');
				}
				if ($category_id) {
					$data = array(
						'pagetitle' => $title,
						'menuindex' => $i++,
						'published' => 1,
						'deleted' => 0,
						'editedon' => $this->config['currentdate'],
						$this->config['xml_id'] => $id
					);
					if ($this->config['update_categories']) {
						$this->updateCategory($category_id, $data);
					}
				} else {
					$data = array(
						'type' => 'document',
						'parent' => $parent,
						'pagetitle' => $title,
						'longtitle' => $title,
						'alias' => $this->translit($title),
						'isfolder' => 1,
						'menuindex' => $i++,
						'hidemenu' => 0,
						'template' => $template,
						'published' => 1,
						'deleted' => 0,
						'createdon' => $this->config['currentdate'],
						'editedon' => $this->config['currentdate'],
						$this->config['xml_id'] => $id
					);
					if ($this->config['add_categories']) {
						$category_id = $this->addCategory($data);
					}
				}
				parent::$CATEGORIES[$id] = $category_id;
				if($this->config['prodution_catalog_id'] && $parent == $this->config['prodution_catalog_id']) {
					parent::$CATEGORIES_PRODUTION[$id] = $category_id;
				}
			}
			if ($category->Группы) {
				$this->getCategory($category->Группы->Группа, $category_id);
			}
		}
		unset($xml);
	}
	/**
	* Обновляем категорию
	*/
	function updateCategory($category_id, $data) {
		$this->update($data, $this->config['tbl_catalog'], "id = '" . $category_id . "'");
		$this->SaveLog(array('internalKey'=>1, 'username'=>'admin', 'action'=>27, 'itemId'=>$category_id, 'itemName'=>$data['pagetitle'], 'msg'=> 'Edit Category'));
	}
	/**
	* Добавляем категорию
	*/
	function addCategory($data) {
		$category_id = $this->insert($data, $this->config['tbl_catalog']);
		$this->SaveLog(array('internalKey'=>1, 'username'=>'admin', 'action'=>4, 'itemId'=>$category_id, 'itemName'=>$data['pagetitle'], 'msg'=> 'Add Category'));
		return $category_id;
	}
	/**
	* Загружаем ID категорий
	*/
	function getIdsCategories($parent) {
		$result = $this->query('SELECT id FROM ' . $this->config['tbl_catalog'] . ' WHERE parent = "' . $parent . '" AND isfolder = "1"');
		while ($row = $this->modx->db->getRow($result)) {
			$this->CATEGORIES_ID[] = $row['id'];
			if ($row['id']) {
				$this->getIdsCategories($row['id']);
			}
		}
	}
	/**
	* Чистим категории
	*/
	function cleanCategories() {
		if ($this->config['delete_categories']) {
			if ($this->config['moved_deleted_category_to_trash']) {
				$parent_trash = ", parent = '" . $this->config['trash_catalog_id'] . "'";
			}
			$this->getIdsCategories($this->config['catalog_id']);
			$deleted_ids = array_diff($this->CATEGORIES_ID, parent::$CATEGORIES);
			if ($deleted_ids) {
				$this->query("UPDATE " . $this->config['tbl_catalog'] . " SET deleted = '1' " . $parent_trash . " WHERE id IN(" . implode(',', $deleted_ids) . ") AND isfolder = '1'");
				foreach($deleted_ids as $k) {
					$this->SaveLog(array('internalKey'=>1, 'username'=>'admin', 'action'=>6, 'itemId'=>$k, 'itemName'=>$data['pagetitle'], 'msg'=> 'Deleted Category'));
				}
			}
			unset($this->CATEGORIES_ID);
		}
	}
}
?>
