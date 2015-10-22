<?php
//##copyright##

set_time_limit(0);

$plugin = 'tagcloud';
$table_name_items_pages = 'items_pages';
$field_name_pattern = '%s_tags';
$field_length = 512;

$language_string_name_pattern = 'field_' . $field_name_pattern;
$language_string_default_value = 'Tags';

$pages = array(
	'autos' => array('submit_auto', 'auto_edit'),
	'articles' => array('submit_article', 'edit_article'),
	//'estates' => array('estate_submit', 'estate_edit'),
	'listings' => array('add_listing', 'edit_listing'),
	'ypages' => array('ypage_add', 'ypage_edit')
);

$iaItem = $iaCore->factory('item');
$iaField = $iaCore->factory('field');

foreach ($data as $item)
{
	$itemName = $item['item'];
	$package = $iaItem->getPackageByItem($itemName);
	$field = sprintf($field_name_pattern, $itemName);

	switch($item['action'])
	{
		case '+':
			if (!$iaDb->exists('`extras` = :plugin AND `item` = :item AND `name` = :field_name', array('plugin' => $plugin, 'item' => $itemName, 'field_name' => $field), iaField::getTable()))
			{
				$sql = sprintf("ALTER TABLE `%s%s` ADD `%s` TEXT(%d) NOT NULL default ''", $iaCore->iaDb->prefix, $itemName, $field, $field_length);
				$iaDb->query($sql);
				$id = $iaDb->insert(array(
					'extras' => $plugin,
					'item' => $itemName,
					'name' => $field,
					'type' => iaField::TEXT,
					'length' => $field_length,
					'status' => iaCore::STATUS_ACTIVE
				), null, iaField::getTable());

				foreach($pages[$itemName] as $page)
				{
					$iaDb->insert(array(
						'page_name' => $page,
						'extras' => $plugin,
						'field_id' => $id
					), null, iaField::getTablePages());
				}

				$iaDb->insert(array(
					'page_name' => $plugin,
					'item' => $itemName
				), null, $table_name_items_pages);

				iaLanguage::addPhrase(
					sprintf($language_string_name_pattern, $itemName),
					$language_string_default_value,
					'en',
					$plugin,
					iaLanguage::CATEGORY_COMMON,
					false
				);
			}

			break;

		case '-':
			if ($id = $iaDb->one_bind('id', '`extras` = :plugin AND `name` = :field', array('plugin' => $plugin, 'field' => $field), iaField::getTable()))
			{
				$sql = sprintf("ALTER TABLE `%s%s` DROP `%s`", $iaCore->iaDb->prefix, $itemName, $field);
				$iaDb->query($sql);

				$iaDb->delete('`field_id` = :id', iaField::getTablePages(), array('id' => $id));
				$iaDb->delete('`page_name` = :page_name AND `item` = :itemname', $table_name_items_pages, array('page_name' => $plugin, 'itemname' => $itemName));
				$iaDb->delete('`id` = :id', iaField::getTable(), array('id' => $id));
				$iaDb->delete('`extras` = :plugin AND `key` = :key', iaLanguage::getTable(), array('plugin' => $plugin, 'key' => sprintf($language_string_name_pattern, $itemName)));
			}
	}
}