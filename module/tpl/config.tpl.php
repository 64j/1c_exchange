<form action="<?php echo $mod_page; ?>" name="module" method="post">
	<input name="action" type="hidden" value="" />
	<input name="item_id" type="hidden" value="" />
	<input name="item_val" type="hidden" value="" />
	<div style="float:right;"><?php echo $conf_shk_version; ?></div>
	<h2><?php echo $langTxt['configTitle']; ?></h2>
	<div class="dynamic-tab-pane-control" id="tabs">
		<div class="tab-row">
			<h2 class="tab selected"><span>Настройки импорта</span></h2>
		</div>
		
		<!-- \\\tab content 1\\\ -->
		<div class="tab-page">
			<table cellpadding="2" width="100%">
				<col width="50%" />
				<col width="50%" />
				<tr>
					<td><label>
							<input type="checkbox" name="status" value="1"<?php if(!empty($conf_status) && $conf_status): ?> checked="checked"<?php endif; ?>>
							Разрешить выгрузку товаров из 1С</label>
						<br>
						<input type="text" name="user" style="width: 140px;" value="<?php echo $conf_user; ?>">
						Пользователь <br>
						<input type="text" name="pass" style="width: 140px;" value="<?php echo $conf_pass; ?>">
						Пароль <br>
						<textarea name="allow_ip" cols="40" rows="5" style="height:90px; width:140px;vertical-align: top;"><?php echo $conf_allow_ip; ?></textarea>
						Разрешённые IP (каждый с новой строки)<br>
						<br></td>
				</tr>
				<tr>
					<td><div class="split"></div></td>
				</tr>
				<tr>
					<td><input type="text" name="price_id" style="width: 40px;" value="<?php echo $conf_price_id; ?>">
						ID TV-параметра цены <br>
						<input type="text" name="currency_id" style="width: 40px;" value="<?php echo $conf_currency_id; ?>">
						ID TV-параметра валюты <br>
						<input type="text" name="catalog_id" style="width: 40px;" value="<?php echo $conf_catalog_id; ?>">
						ID каталога товаров <br>
						
						<!--<input type="text" name="brands_catalog_id" style="width: 40px;" value="<?php echo $conf_brands_catalog_id; ?>">
						ID раздела Производители <br>
						<input type="text" name="brand_tpl_id" style="width: 40px;" value="<?php echo $conf_brand_tpl_id; ?>">
						ID шаблона для производителя <br>
						<input type="text" name="brand_tv_id" style="width: 40px;" value="<?php echo $conf_brand_tv_id; ?>">
						ID TV-параметра производителя <br>-->
						
						<input type="text" name="category_podcat_tpl_id" style="width: 40px;" value="<?php echo $conf_category_podcat_tpl_id; ?>">
						ID шаблона категории c подкатегориями товаров <br>
						<input type="text" name="category_tpl_id" style="width: 40px;" value="<?php echo $conf_category_tpl_id; ?>">
						ID шаблона категории товаров <br>
						<input type="text" name="product_tpl_id" style="width: 40px;" value="<?php echo $conf_product_tpl_id; ?>">
						ID шаблона товара <br>
						<input type="text" name="trash_catalog_id" style="width: 40px;" value="<?php echo $conf_trash_catalog_id; ?>">
						ID папки корзины <br>
						
						<!--<input type="text" name="prodution_catalog_id" style="width: 40px;" value="<?php echo $conf_prodution_catalog_id; ?>">
						ID раздела товаров снятых с производства<br>
						<input type="text" name="product_prodution_tpl_id" style="width: 40px;" value="<?php echo $conf_product_prodution_tpl_id; ?>">
						ID шаблона товара снятого с производства<br>--></td>
				</tr>
				<tr>
					<td><div class="split"></div></td>
				</tr>
				<tr>
					<td><label>
							<input type="checkbox" name="add_categories" value="1"<?php if(!empty($conf_add_categories) && $conf_add_categories): ?> checked="checked"<?php endif; ?>>
							Добавлять новые категории</label>
						<br>
						<label>
							<input type="checkbox" name="update_categories" value="1"<?php if(!empty($conf_update_categories) && $conf_update_categories): ?> checked="checked"<?php endif; ?>>
							Обновлять категории</label>
						<br>
						<label>
							<input type="checkbox" name="delete_categories" value="1"<?php if(!empty($conf_delete_categories) && $conf_delete_categories): ?> checked="checked"<?php endif; ?>>
							Удалять категории</label>
						<br>
						<label>
							<input type="checkbox" name="moved_deleted_category_to_trash" value="1"<?php if(!empty($conf_moved_deleted_category_to_trash) && $conf_moved_deleted_category_to_trash): ?> checked="checked"<?php endif; ?>>
							При удалении переносить категории в корзину</label>
						<br></td>
				</tr>
				<tr>
					<td><div class="split"></div></td>
				</tr>
				<tr>
					<td><label>
							<input type="checkbox" name="add_products" value="1"<?php if(!empty($conf_add_products) && $conf_add_products): ?> checked="checked"<?php endif; ?>>
							Добавлять новые товары</label>
						<br>
						<label>
							<input type="checkbox" name="update_products" value="1"<?php if(!empty($conf_update_products) && $conf_update_products): ?> checked="checked"<?php endif; ?>>
							Обновлять товары</label>
						<br>
						<label>
							<input type="checkbox" name="update_description_product" value="1"<?php if(!empty($conf_update_description_product) && $conf_update_description_product): ?> checked="checked"<?php endif; ?>>
							Обновлять описание товара</label>
						<br>
						<label>
							<input type="checkbox" name="delete_products" value="1"<?php if(!empty($conf_delete_products) && $conf_delete_products): ?> checked="checked"<?php endif; ?>>
							Удалять товары</label>
						<br>
						<label>
							<input type="checkbox" name="moved_deleted_product_to_trash" value="1"<?php if(!empty($conf_moved_deleted_product_to_trash) && $conf_moved_deleted_product_to_trash): ?> checked="checked"<?php endif; ?>>
							При удалении переносить товары в корзину</label>
						<br></td>
				</tr>
				<tr>
					<td><div class="split"></div></td>
				</tr>
				<tr>
					<td><label>
							<input type="checkbox" name="update_prices" value="1"<?php if(!empty($conf_update_prices) && $conf_update_prices): ?> checked="checked"<?php endif; ?>>
							Обновлять цены</label>
						<br>
						
						<!--<label>
							<input type="checkbox" name="add_options" value="1"<?php if(!empty($conf_add_options) && $conf_add_options): ?> checked="checked"<?php endif; ?>>
							Добавлять характеристики</label>
						<br>
						<label>
							<input type="checkbox" name="clear_tvs" value="1"<?php if(!empty($conf_clear_tvs) && $conf_clear_tvs): ?> checked="checked"<?php endif; ?>>
							Удалять неиспользуемые TV параметры для шаблонов товаров снятых с производства</label>
						<br>--></td>
				</tr>
				<tr>
					<td><div class="split"></div></td>
				</tr>
				<tr>
					<td><input type="text" name="uid" style="width: 140px;" value="<?php echo $conf_uid; ?>">
						Поле в таблице "site_content" по которому будут синхронизироваться товары, в 1С это Код товара<br>
						<input type="text" name="price_type" style="width: 140px;" value="<?php echo $conf_price_type; ?>">
						Основная цена товара (Розничная) <br>
						<input type="text" name="price_currency" style="width: 140px;" value="<?php echo $conf_price_currency; ?>">
						Название цены из которой берётся валюта товара (Закупочная)</td>
				</tr>
			</table>
		</div>
		<!-- ///tab content 1/// --> 
		
	</div>
	<br />
	<br />
	<ul class="actionButtons" style="float:right; text-align:right;">
		<li><a href="#" onClick="if(confirm('Удалить модуль ?')){postForm('uninstall',null,null)};return false;"><img src="media/style/<?php echo $theme; ?>/images/icons/delete.png" alt="">&nbsp; Удалить модуль</a></li>
	</ul>
	<ul class="actionButtons" style="float:left">
		<li><a href="#" onClick="postForm('save_config',null,null);return false;"><img src="media/style/<?php echo $theme; ?>/images/icons/save.png" alt="">&nbsp; Сохранить настройки</a></li>
	</ul>
	<br />
	<br />
</form>
