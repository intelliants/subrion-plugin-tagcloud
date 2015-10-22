<?php
$plugin = 'tagcloud';

$IA_TABLE_NAME_FIELDS = 'fields';
$IA_TABLE_NAME_LANGUAGE = 'language';
$IA_TABLE_NAME_FIELDS_PAGES = 'fields_pages';

$FIELD_NAME_PATTERN = '%s_tags';
$FIELD_LENGTH = 512;

$LANGUAGE_STRING_NAME_PATTERN = 'field_'.$FIELD_NAME_PATTERN;
$LANGUAGE_STRING_DEFAULT_VALUE = 'Tags';

$iaItem = $iaCore->factory('core', 'item');

foreach($data as $item)
{
	$itemname = $item['item'];
	$field = sprintf($FIELD_NAME_PATTERN, $itemname);

	switch($item['action'])
	{
		case '+':
			if(!$iaDb->exists('`extras` = :plugin AND `item` = :item AND `name` = :field_name', array('plugin' => $plugin, 'item' => $itemname, 'field_name' => $field), $IA_TABLE_NAME_FIELDS))
			{
				$sql = sprintf("ALTER TABLE `%s%s` ADD `%s` TEXT(%d) NOT NULL default ''", $iaCore->iaDb->prefix, $itemname, $field, $FIELD_LENGTH);
				$iaDb->query($sql);

				$id = $iaDb->insert(array(
					'extras' => $plugin,
					'item' => $itemname,
					'name' => $field,
					'type' => 'text',
					'length' => $FIELD_LENGTH,
					'status' => 'active'
				), false, $IA_TABLE_NAME_FIELDS);

				$packageName = $iaItem->getPackageByItem($itemname);

				$sql = "INSERT INTO `".$iaCore->iaDb->prefix.$IA_TABLE_NAME_FIELDS_PAGES."` ";
				$sql .= "(`page_name`, `extras`, `field_id`) VALUES ";
				$sql .= "((SELECT `name` FROM `{$iaCore->iaDb->prefix}pages` WHERE `extras` = '$packageName' AND `action` = 'add'), '$plugin', '$id'), ";
				$sql .= "((SELECT `name` FROM `{$iaCore->iaDb->prefix}pages` WHERE `extras` = '$packageName' AND `action` = 'edit'), '$plugin', '$id')";

				$iaDb->query($sql);

				$iaDb->insert(array(
					'category' => 'common',
					'code' => 'en',
					'extras' => $plugin,
					'key' => sprintf($LANGUAGE_STRING_NAME_PATTERN, $itemname),
					'value' => $LANGUAGE_STRING_DEFAULT_VALUE
				), false, $IA_TABLE_NAME_LANGUAGE);
			}

			break;

		case '-':
			$id = $iaDb->one('id', "`extras` = '".$plugin."' AND `name` = '$field'", $IA_TABLE_NAME_FIELDS);

			if($id)
			{
				$sql = sprintf("ALTER TABLE `%s%s` DROP `%s`", $iaCore->iaDb->prefix, $itemname, $field);
				$iaDb->query($sql);

				$iaDb->delete('`field_id` = :id', $IA_TABLE_NAME_FIELDS_PAGES, array('id' => $id));
				$iaDb->delete('`id` = :id', $IA_TABLE_NAME_FIELDS, array('id' => $id));
				$iaDb->delete('`extras` = :plugin AND `key` = :key', $IA_TABLE_NAME_LANGUAGE, array('plugin' => $plugin, 'key' => sprintf($LANGUAGE_STRING_NAME_PATTERN, $itemname)));
			}
	}
}