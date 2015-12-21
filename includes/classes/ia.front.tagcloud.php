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

class iaTagcloud extends abstractCore
{
	var $tablename = 'tags';

	function _parse_tags_string ($tags)
	{
		$result = explode(',', $tags);
		if (empty($result))
		{
			return array();
		}
		foreach ($result as $key => $item)
		{
			$result[$key] = iaSanitize::sql(trim($item));
			if (!$result[$key])
			{
				unset($result[$key]);
			}
		}
		return array_unique($result);
	}

	function setTags ($item, $previous, $current)
	{
		$new = $this->_parse_tags_string($current);
		$previous = '';
		$old = $this->_parse_tags_string($previous);
		$iaDb = &$this->iaCore->iaDb;
		$iaDb->setTable($this->tablename);

		if ($new)
		{
			foreach ($new as $tag)
			{
				if (in_array($tag, $old)) continue;

				$condition = "`tag` = '$tag' AND `item` = '$item'";

				$row = $iaDb->row('`id`, `count`', $condition);

				if ($row)
				{
					$count = (int) ++$row['count'];
					$iaDb->update(array('count' => $count), "`id` = '{$row['id']}'");
				}
				else
				{
					$iaDb->insert(array('tag' => $tag, 'item' => $item, 'count' => 1));
				}
			}

		}

		$array = array_diff($old, $new);

		if ($array)
		{
			foreach ($array as $tag)
			{
				$condition = "`tag` = '$tag' AND `item` = '$item'";
				$row = $iaDb->row('`id`, `count`', $condition);

				if ($row)
				{
					$count = (int) --$row['count'];
					if ($count <= 0)
					{
						$iaDb->delete('`id` = :id', null, array('id' => $row['id']));
					}
					else
					{
						$iaDb->update(array('count' => $count), "`id` = '{$row['id']}'");
					}
				}
			}
		}

		$iaDb->resetTable();
	}

	function getTags ($item = false)
	{
		$tags = array();
		if ($item === false)
		{
			$items = explode(',',$this->iaCore->get('tagcloud_items_enabled'));
		}
		else
		{
			$items = array($item);
		}

		if ($items && !empty($items))
		{
			$tags = $this->iaCore->iaDb->all('`count`, `tag`, `item`', "`item` IN ('".implode("','", $items)."') ORDER BY RAND()", 0, $this->iaCore->get('tags_num_limit', 10), 'tags');
			$tags = $this->setSizes($tags);
			$tags = $this->setUrls($tags);
		}

		return $tags;
	}

	function setUrls ($array)
	{
		$result = $array;
		if (empty($result)) return $result;

		foreach ($result as $key => $item)
		{
			$result[$key]['url'] = sprintf('%stags/%s/%s/', IA_URL, $item['item'], urlencode($item['tag']));
		}

		return $result;
	}

	function setSizes ($array, $min_px = 18, $max_px = 46, $steps = 100)
	{
		$result = $array;
		if (empty($result))
		{
			return $result;
		}

		$sizes = array();
		foreach ($result as $key => $item)
		{
			$sizes[$key] = (int) $item['count'];
		}

		$range = $max_px - $min_px;
		$count_min = min($sizes);
		$count_max = max($sizes);

		$step = ($count_max - $count_min) / $steps;
		$step = $step ? $step : 1;
		$px_by_step = $range / $steps;
		$min_px -= $px_by_step;

		foreach ($result as $key => $item)
		{
			$result[$key]['size'] = round(($item['count'] - $count_min) / $step * $px_by_step + $min_px);
		}
		return $result;
	}
}
