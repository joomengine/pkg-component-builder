<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    30th April, 2015
 * @author     Llewellyn van der Merwe <https://dev.vdm.io>
 * @git        Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

/**
 * Script File of Component builder Package
 */
class pkg_component_builderInstallerScript
{
	/**
	 * Called after any type of action
	 *
	 * @return  boolean  True on success
	 */
	public function postflight($type, $parent)
	{
		// enable the JCB plugins
		$this->enableJCBPlugins();

		// only run these if we have an update
		if ('update' == $type)
		{
			// update the update server location
			$this->updateServerLocation();
		}
	}

	/**
	 * Update server location
	 *
	 * @return  void
	 */
	protected function updateServerLocation()
	{
		$location = "https://git.vdm.dev/joomla/Component-Builder/raw/branch/3.10/componentbuilder_update_server.xml";
		$elements = ['pkg_component_builder', 'com_componentbuilder'];

		// Get the Package Update Site Details
		foreach ($elements as $element)
		{
			if (($sites = $this->getUpdateSites($element)) !== null)
			{
				foreach ($sites as $site)
				{
					if ($site->location !== $location)
					{
						// Update the update site location
						$site->location = $location;
						Factory::getDbo()->updateObject('#__update_sites', $site, 'update_site_id');
					}
				}
			}
		}
	}

	/**
	 * Get Update Sites
	 *
	 * @return  array|null
	 */
	protected function getUpdateSites(string $element): ?array
	{
		// Get The Database object
		$db = Factory::getDbo();

		// Get the Package Update Site Details
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('s.location', 's.update_site_id')));
		$query->from($db->quoteName('#__update_sites', 's'));
		$query->join('LEFT', $db->quoteName('#__update_sites_extensions', 'u') . ' ON ' . $db->quoteName('s.update_site_id') . ' = ' . $db->quoteName('u.update_site_id'));
		$query->join('LEFT', $db->quoteName('#__extensions', 'e') . ' ON ' . $db->quoteName('u.extension_id') . ' = ' . $db->quoteName('e.extension_id'));
		$query->where($db->quoteName('e.element') . ' = ' . $db->quote($element));
		$db->setQuery($query);
		$db->execute();

		if ($db->getNumRows())
		{
			return $db->loadObjectList();
		}
		return null;
	}

	/**
	 * Enable all JCB Plugins
	 *
	 * @return  void
	 */
	protected function enableJCBPlugins()
	{
		// Get The Database object
		$db = Factory::getDbo();
		// enable all JCB plugins Always!
		$plugins = [
			'componentbuilderadminheaderstabs',
			'componentbuildercomponentdashboardheaderstabs',
			'componentbuildercomponentheaderstabs',
			'componentbuildercustomadminheaderstabs',
			'componentbuilderlanguagetabs',
			'componentbuildersiteheaderstabs',
			'componentbuilderdynamicgetheaderstabs',
			'componentbuilderprivacytabs',
			'componentbuilderfieldorderingtabs',
			'componentbuilderactionlogcompiler',
			'componentbuilderexportcompiler',
			'componentbuilderfieldorderingcompiler',
			'componentbuilderheaderscompiler',
			'componentbuilderlanguagepackaging',
			'componentbuilderpowersautoloadercompiler',
			'componentbuilderprivacycompiler'
		];

		// Create a new query object.
		$query = $db->getQuery(true);
		// we must update the enabled field
		$fields = [
			$db->quoteName('enabled') . ' = 1'
		];
		// Conditions for which records should be updated.
		$conditions = [
			$db->quoteName('element') . ' IN (' . implode(',', array_map([$db, 'quote'], $plugins)) . ')'
		];
		// load the update query
		$query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);
		// Reset the query using our newly populated query object.
		$db->setQuery($query);

		$db->execute();
	}
}
