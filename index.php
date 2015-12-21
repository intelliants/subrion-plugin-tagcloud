<?php
/******************************************************************************
 *
 * Subrion - open source content management system
 * Copyright (C) 2015 Intelliants, LLC <http://www.intelliants.com>
 *
 * This file is part of Subrion.
 *
 * Subrion is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Subrion is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Subrion. If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @link http://www.subrion.org/
 *
 ******************************************************************************/

if ($iaView->getRequestType() == iaView::REQUEST_HTML)
{
	$tag = '';
	$item = '';

	if (!isset($iaCore->requestPath[0]))
	{
		return iaView::errorPage(iaView::ERROR_NOT_FOUND);
	}

	$item = iaSanitize::paranoid($iaCore->requestPath[0]);
	iaBreadcrumb::replaceEnd(iaLanguage::get('tags') . ': ' . iaLanguage::get($item), IA_URL.'tags/'.$item.'/');

	if (isset($iaCore->requestPath[1]))
	{
		$tag = iaSanitize::sql(urldecode($iaCore->requestPath[1]));

		if ($items = $iaDb->all(iaDb::ALL_COLUMNS_SELECTION, "`{$item}_tags` LIKE '%{$tag}%'", 0, null, $item))
		{
			iaBreadcrumb::toEnd($tag, IA_SELF);

			$iaItem = $iaCore->factory('item');
			$package = $iaItem->getPackageByItem($item);
			$pagename = $package . '_index';

			$fields = $iaCore->factory('field')->filter($items, $item, array('page' => 'view_member'));

			$iaView->assign('fields', $fields);
			$iaView->assign('items', $items);
			$iaView->assign('type', $item);

			$iaView->assign('title', iaLanguage::getf('page_title_tags', array('tag' => $tag, 'item' => ucfirst($item))));
		}
		else
		{
			return iaView::errorPage(iaView::ERROR_NOT_FOUND, iaLanguage::get('no_items_with_tags'));
		}

		$iaView->display('list');
	}
	else
	{
		$tags = $iaCore->factoryPlugin(IA_CURRENT_PLUGIN)->getTags($item);
		$iaView->assign('tags', $tags);

		$iaView->display('index');
	}
}