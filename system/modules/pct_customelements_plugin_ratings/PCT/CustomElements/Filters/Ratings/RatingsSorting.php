<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2013 Leo Feyer
 * 
 * @copyright	Tim Gatzky 2018, Premium Contao Webworks, Premium Contao Themes
 * @author		Tim Gatzky <info@tim-gatzky.de>
 * @package		tl_pct_customelement_ratings
 * @link		http://contao.org
 * @license     LGPL
 */
 

/**
 * Namespace
 */
namespace PCT\CustomElements\Filters;


/**
 * Imports
 */
use PCT\CustomElements\Models\RatingsModel;


/**
 * Class file
 * RatingsSorting
 */
class RatingsSorting extends \PCT\CustomElements\Filters\Sorting
{
	/**
	 * Prepare the sql query array for this filter and return it as array
	 * @return array
	 * 
	 * called from getQueryOption() in \PCT\CustomElements\Filter
	 */	
	public function getQueryOptionCallback()
	{
		$arrValues = array_filter($this->getValue()) ?: array();
		
		if(empty($arrValues))
		{
			return array();
		}
		
		$arrIds = array();
		$arrOptions = array();
		foreach($arrValues as $k)
		{
			$sorting = 'asc';
			$field = $k;
			
			$blnPreg = preg_match("/(.*)\[(.*)\]/", $k, $arrMatch);
			if($blnPreg)
			{
				$field = $arrMatch[1];
				$sorting = $arrMatch[2];
			}
				
			// mysql has no natural order for numeric values, so we multiply 
			if($this->get('type') == 'sorting_numeric')
			{
				$field .= "+0"; 
			}
			
			$options['order'] = RatingsModel::getTable().'.'.$field.' '.strtoupper($sorting);
			
			// find matching rating records ordered
			$objRatings = RatingsModel::findPublishedBySourceAndAttributeAndCustom($this->getTable(),$this->get('attr_id'),'',$options);
			
			// collect ids in order
			if($objRatings !== null)
			{
				foreach($objRatings as $objRating)
				{
					$arrIds[] = $objRating->pid;
				}
			}
			
			unset($options);
			unset($sorting);
			unset($field);
		}
		
		if(!empty($arrIds))
		{
			$arrOptions['order'] = array('FIELD(id,'.implode(',', $arrIds).')');
		}
		
		return $arrOptions;
	}
	
	/**
	 * Prepare the options for the widget
	 * @return array
	 */
	protected function getSelectOptions()
	{
		$arrReturn = array();
		$arrFields = deserialize( $this->get('defaultMulti') );
		if( empty($arrFields) )
		{
			$arrFields = array('rating','tstamp','helpful','not_helpful');
		}
		
		foreach($arrFields as $f)
		{
			$arrReturn[$f.'[asc]'] 	= $GLOBALS['TL_LANG']['MSC']['ratings_filter'][$f]['asc'];
			$arrReturn[$f.'[desc]'] = $GLOBALS['TL_LANG']['MSC']['ratings_filter'][$f]['desc'];
		}
		
		return $arrReturn;
	}

}