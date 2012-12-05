<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage AttributeText
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */
if (!defined('TL_ROOT'))
{
	die('You cannot access this file directly!');
}

/**
 * This is the MetaModelAttribute class for handling translated text fields.
 *
 * @package	   MetaModels
 * @subpackage AttributeText
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModelAttributeTranslatedAlias
extends MetaModelAttributeTranslatedReference
{
	protected function getValueTable()
	{
		return 'tl_metamodel_translatedtext';
	}

	public function getAttributeSettingNames()
	{
		return array_merge(parent::getAttributeSettingNames(), array('talias_fields', 'isunique', 'force_talias'));
	}
	
	public function getFieldDefinition()
	{
		$arrFieldDef = parent::getFieldDefinition();

		$arrFieldDef['inputType'] = 'text';

		// we do not need to set mandatory, as we will automatically update our value when isunique is given.
		if ($this->get('isunique'))
		{
			$arrFieldDef['eval']['mandatory'] = false;
		}
		return $arrFieldDef;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function modelSaved($objItem)
	{
		// alias already defined and no update forced, get out!
		if ($objItem->get($this->getColName()) && (!$this->get('force_talias')))
		{
			return;
		}

		$arrAlias = '';
		foreach (deserialize($this->get('talias_fields')) as $strAttribute)
		{
			$arrValues = $objItem->parseAttribute($strAttribute['field_attribute'], 'text', null);
			$arrAlias[] = $arrValues['text'];
		}

		// implode with '-'
		$strAlias  = standardize(implode('-', $arrAlias));

		// we need to fetch the attribute values for all attribs in the alias_fields and update the database and the model accordingly.
		if ($this->get('isunique') && $this->searchFor($strAlias))
		{
			
			
			$intCount = 1;
			// ensure uniqueness.
			while (count($this->searchFor($strAlias . '-' . (++$intCount))) > 0){}
			$strAlias = $strAlias . '-' . $intCount;
		}
		
		

		$this->setDataFor(array($objItem->get('id') => $strAlias));
		$objItem->set($this->getColName(), $strAlias);
	}
}

?>