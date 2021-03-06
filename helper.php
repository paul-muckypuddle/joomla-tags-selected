<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_popular
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_tags_popular
 *
 * @package     Joomla.Site
 * @subpackage  mod_tags_popular
 * @since       3.1
 */
abstract class ModTagsselectedHelper
{
	public static function getContentList($params)
	{
		$db         = JFactory::getDbo();
		$app        = JFactory::getApplication();
		$user       = JFactory::getUser();
		$groups     = implode(',', $user->getAuthorisedViewLevels());
		//$matchtype  = $params->get('matchtype', 'all');
		$maximum    = $params->get('maximum', 5);
		$tagsHelper = new JHelperTags;
		$option     = $app->input->get('option');
		$view       = $app->input->get('view');
		$prefix     = $option . '.' . $view;
		$id         = (array) $app->input->getObject('id');
		
		// Get module parameters
		$selectedTags = $params->get('selected_tags');
		$contentTypes = $params->get('content_types');
		$includeChildren = $params->get('include_children') == 1 ? true : false;
		$matchLogic = !!$params->get('match_logic');
		$orderByOption = $params->get('order_by_option');
		$orderDir = $params->get('order_dir');
		$stateFilter = $params->get('state');

		// Strip off any slug data.
		foreach ($id as $id)
		{
			if (substr_count($id, ':') > 0)
			{
				$idexplode = explode(':', $id);
				$id        = $idexplode[0];
			}
		}

		if (!$selectedTags || is_null($selectedTags))
		{
			return $results = false;
		}

		// Convert $tagsToMatch to string to work around a bug in \JHelperTags::getTagItemsQuery()
		// When passing in an array the $matchLogic setting does not work. Passing in a string fixes this
		$tagsToMatch = join(',',$selectedTags);
		
		$query=$tagsHelper->getTagItemsQuery($tagsToMatch, $contentTypes, $includeChildren, $orderByOption, $orderDir, $matchLogic, $languageFilter = 'all', $stateFilter);
		$db->setQuery($query, 0, $maximum);

		$results = $db->loadObjectList();

		foreach ($results as $result)
		{
			$explodedAlias = explode('.', $result->type_alias);
			$result->link = 'index.php?option=' . $explodedAlias[0] . '&view=' . $explodedAlias[1] . '&id=' . $result->content_item_id . '-' . $result->core_alias;
		}

		return $results;	
	}
	
	public static function getTypeTitlesByIds($ids){
		
		$ids = "(".implode(",", $ids).")";
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('type_title');
		$query->from($db->quoteName('#__content_types'));
		$query->where($db->quoteName('type_id')." IN ".$ids);
		$db->setQuery($query);
		$result = $db->loadColumn();
		return $result;
	}
}