<?php
namespace Craft;

/**
 * Class GuestEntriesPlugin
 *
 * @package Craft
 */
class GuestEntriesPlugin extends BasePlugin
{
	/**
	 * @return mixed
	 */
	public function getName()
	{
		return 'Guest Entries';
	}

	/**
	 * @return string
	 */
	public function getVersion()
	{
		return '1.3.2';
	}

	/**
	 * @return string
	 */
	public function getDeveloper()
	{
		return 'Pixel & Tonic';
	}

	/**
	 * @return string
	 */
	public function getDeveloperUrl()
	{
		return 'http://pixelandtonic.com';
	}

	/**
	 * @return bool
	 */
	public function hasSettings()
	{
		return true;
	}

	/**
	 * @return mixed
	 */
	public function getSettingsHtml()
	{
		$editableSections = array();
		$allSections = craft()->sections->getAllSections();

		foreach ($allSections as $section)
		{
			// No sense in doing this for singles.
			if ($section->type !== 'single')
			{
				$editableSections[$section->handle] = array('section' => $section);
			}
		}

		// Let's construct the potential default users for each section.
		foreach ($editableSections as $handle => $value)
		{
			// If we're running on Client Edition, add both accounts.
			if (craft()->getEdition() == Craft::Client)
			{
				$defaultAuthorOptionCriteria = craft()->elements->getCriteria(ElementType::User);
				$authorOptions = $defaultAuthorOptionCriteria->find();
			}
			else if (craft()->getEdition() == Craft::Pro)
			{
				$defaultAuthorOptionCriteria = craft()->elements->getCriteria(ElementType::User);
				$defaultAuthorOptionCriteria->can = 'createEntries:'.$value['section']->id;
				$authorOptions = $defaultAuthorOptionCriteria->find();
			}
			else
			{
				// 2.x on Personal Edition.
				$authorOptions = array(craft()->userSession->getUser());
			}

			foreach ($authorOptions as $key => $authorOption)
			{
				$authorLabel = $authorOption->username;
				$authorFullName = $authorOption->getFullName();

				if ($authorFullName)
				{
					$authorLabel .= ' ('.$authorFullName.')';
				}

				$authorOptions[$key] = array('label' => $authorLabel, 'value' => $authorOption->id);
			}

			array_unshift($authorOptions, array('label' => 'Don’t Allow', 'value' => 'none'));

			$editableSections[$handle] = array_merge($editableSections[$handle], array('authorOptions' => $authorOptions));
		}

		return craft()->templates->render('guestentries/_settings', array(
			'settings' => $this->getSettings(),
			'editableSections' => $editableSections,
		));
	}

	/**
	 * @return array
	 */
	protected function defineSettings()
	{
		return array(
			'allowGuestSubmissions'     => AttributeType::Bool,
			'defaultAuthors'            => AttributeType::Mixed,
			'enabledByDefault'          => AttributeType::Mixed,
			'validateEntry'             => AttributeType::Mixed,
		);
	}
}
