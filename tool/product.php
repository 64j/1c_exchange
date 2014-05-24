<?php
class ModxToolProduct extends ModxExchange1c {
	function __construct(&$modx, $config) {
		parent::__construct($modx, $config);
	}
	/**
	* Добавляем товар
	* @data - массив значений 
	*/
	public function addProduct($data) {
		$product    = array(
			$this->config['uid'] => (string) $data['uid'],
			'parent' => $data['product_category'],
			'pagetitle' => addslashes($data['product_description']['name']),
			'longtitle' => addslashes($data['product_description']['fullname']),
			'alias' => $this->translit($data['product_description']['name']),
			'introtext' => addslashes($data['product_description']['description']),
			'hidemenu' => 1,
			'template' => $data['template'],
			'published' => 1,
			'deleted' => 0,
			'createdon' => $this->config['currentdate'],
			'editedon' => $this->config['currentdate'],
			'type' => 'document',
			$this->config['xml_id'] => $data[$this->config['xml_id']]
		);
		// добавляем товар
		$product_id = $this->insert($product, $this->config['tbl_catalog']);
		// добавялем производителя 
		/*    $this->insert(array(
      'tmplvarid' => $this->config['brand_tv_id'],
      'contentid' => (int) $product_id,
      'value' => $this->escape($data['manufacturer_name'])
    ), $this->config['tbl_catalog_tv']);*/
	  $this->SaveLog(array('internalKey'=>1, 'username'=>'admin', 'action'=>4, 'itemId'=>$product_id, 'itemName'=>$data['product_description']['name'], 'msg'=> 'Add Product'));
	  return $product_id;
  }
	/**
	* Обновляем товар
	* @product_id - id товара, data - массив значений
	*/
	public function editProduct($product_id, $data) {
		$product = array(
			$this->config['uid'] => (string) $data['uid'],
			'parent' => (int) $data['product_category'],
			'pagetitle' => addslashes($data['product_description']['name']),
			'introtext' => addslashes($data['product_description']['description']),
			'template' => $data['template'],
			'published' => 1,
			'deleted' => 0,
			'editedon' => $this->config['currentdate'],
			$this->config['xml_id'] => $data[$this->config['xml_id']] 
		);
		if (!$this->config['update_description_product']) {
			unset($product['introtext']);
		}
		$this->update($product, $this->config['tbl_catalog'], "id = '" . (int) $product_id . "'");
		$this->SaveLog(array('internalKey'=>1, 'username'=>'admin', 'action'=>27, 'itemId'=>$product_id, 'itemName'=>$data['product_description']['name'], 'msg'=> 'Edit Product'));
		/*    $this->update(array(
      'value' => $data['manufacturer_name']
    ), $this->config['tbl_catalog_tv'], "tmplvarid = '" . $this->config['brand_tv_id'] . "' AND contentid = '" . $product_id . "'");
    if (!empty($data['product_attribute'])) {
      foreach ($data['product_attribute'] as $product_attribute) {
        if ($product_attribute['attribute_id']) {
          $this->query("DELETE FROM " . $this->config['tbl_attr'] . " WHERE product_id = '" . (int) $product_id . "' AND attribute_id = '" . (int) $product_attribute['attribute_id'] . "'");
          foreach ($product_attribute['product_attribute_description'] as $product_attribute_description) {
            $this->query("INSERT INTO " . $this->config['tbl_attr'] . " SET product_id = '" . (int) $product_id . "', attribute_id = '" . (int) $product_attribute['attribute_id'] . "', text = '" . $this->escape($product_attribute_description) . "'");
          }
        }
      }
    }*/
  }
}
?>
