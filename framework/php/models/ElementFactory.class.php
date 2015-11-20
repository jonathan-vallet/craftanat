<?php

/**
 * Element factory class
 * This class extends SingletonModel to use magic methods (__call) without having to create several class instances
 */
class ElementFactory extends SingletonModel
{
	const TABLE_FIELD_SEPARATOR = '_';
	const TABLE_JOIN_SEPARATOR = '_has_';
	
	/**
	 * Unique instance (singleton) retrieval method
	 * @return ElementFactory The ElementFactory unique instance
	 */
	public static function getInstance($param=NULL)
	{
		return self::getClassInstance(get_class(), $param);
	}

	/**
	 * Gets a single element either by id or by conditions/order combination
	 * @param string $elementClass The element class searched
	 * @param int $elementId The element id searched, NULL if a specific condition is applied
	 * @param string $conditions The conditions string to apply
	 * @param string $orderBy The order string to apply
	 * @return Element The found element or an exception if any error occures
	 */
	public static function getElement($elementClass, $elementId, $conditions=NULL, $orderBy=NULL, $join=NULL)
	{
		$logInstance = LogTool::getInstance();
		$logInstance->logDebug('Gets ' . $elementClass . ' with id #' . $elementId . ' element');

		// Gets element by id
		if ($elementId !== NULL)
		{
			// check that element id is an integer
			if (!StringTool::isInt($elementId))
				throw new Exception('ElementFactory::getElement: element id have to be an integer ('.$elementId.')');

			$elementTable = DatabaseFactory::getElementTableName($elementClass);

			// Gets element from cache
			if ($join === NULL)
			{
				try
				{
					return CacheFactory::getElement($elementClass, $elementId);
				}
				catch (ElementFactoryException $e) {}
			}
			// Builds element id condition (table.table_id)
			$elementIdConditions = $elementTable . '.' . $elementTable . '_id = \''.$elementId.'\'';

			// Merges id search and specific conditions 
			$conditions = ($conditions !== NULL) ? $elementIdConditions.' AND ('.$conditions.')' : $elementIdConditions;
		}

		// Gets element by conditions/order combination from database
		$elementList = ElementFactory::getElementList($elementClass, $conditions, $orderBy, $join);

		// Wrong element list size
		$elementCount = count($elementList);
		if ($elementCount != 1)
		{
			if ($elementId !== NULL)
				throw new ElementDoesNotExistException('Cannot find #'.$elementId.' '.$elementTable.' ('.$elementCount.' results)');
			else if ($elementCount == 0)
				throw new ElementNoResultException('Cannot get a single Element : nothing found ("' . $conditions . '", "' . $orderBy . '")');
			else
				throw new ElementManyResultsException('Cannot get a single Element : ' . $elementCount . ' element(s) retrieved ("' . $conditions . '", "' . $orderBy . '")');
		}

		// Extracts the element from list
		return reset($elementList);
	}
	
	/**
	 * Gets all elements by class
	 * @param string $elementClass The element type searched
	 * @param string $conditions The conditions string to apply (ex : Region.x < 10 AND World.size = 500)
	 * @param string $orderBy The order string to apply (ex : 'Region.x, City.name DESC')
	 * @return array The found element list array
	 */
	public static function & getElementList($elementClass, $conditions=NULL, $orderBy=NULL, $join=NULL)
	{
		$logInstance = LogTool::getInstance();
		$logInstance->logDebug('Gets ' . $elementClass . ' list (conditions="' . $conditions . '", order="' . $orderBy . '")');
		// Tries to get element list from cache
		if($join === NULL)
		{
			try
			{
				return CacheFactory::getElementList($elementClass, $conditions, $orderBy);
			}
			catch (ElementFactoryException $e) {}
		}
		// Gets element list from database
		list($elementList, $retrievedElementList) = DatabaseFactory::getElementList($elementClass, $conditions, $orderBy, $join);

		// Adds result to cache 
		CacheFactory::addElementList($elementClass, $elementList, $conditions, $orderBy);

		if($join !== NULL)
		{
			foreach($retrievedElementList as $mainElementTableName => $mainElementElementList)
			{
				foreach($mainElementElementList as $mainElementId => $attachedElementList)
					foreach($attachedElementList as $childElementTableName => $childElementList)
					{
						// Builds parent id conditions to cache list
						$parentIdFieldName = DatabaseFactory::getParentIdColumnName(DatabaseFactory::getElementClassName($mainElementTableName));
						$childRequestConditions = $childElementTableName . '.' . $parentIdFieldName . '=' . $mainElementId;
						CacheFactory::addElementList(DatabaseFactory::getElementClassName($childElementTableName), $childElementList, $childRequestConditions, $orderBy);
					}
			}
		}
		return $elementList;
	}

	/**
	 * Gets element list link to an element
	 * @param string $elementClass The element class searched
	 * @param string $parentElement The parent element to get list of
	 * @param string $conditions The conditions string to apply
	 * @param string $orderBy The order string to apply)
	 * @return array The element list array
	 */
	public static function & getElementListFromParent($elementClass, $parentElement, $conditions=NULL, $orderBy=NULL, $join=NULL)
	{
		$parentClass = $parentElement->getElementClass();
		$parentId = $parentElement->id;

		// Builds parent id conditions
		$parentIdFieldName = DatabaseFactory::getParentIdColumnName($parentClass);

		$tableName = DatabaseFactory::getElementTableName($elementClass);
		$parentIdCondition = $tableName . '.' . $parentIdFieldName . '=' . $parentId;

		if ($conditions !== NULL && StringTool::strlen($conditions) > 0)
			$conditions = $parentIdCondition . ' AND (' . $conditions . ')';
		else
			$conditions = $parentIdCondition;

		$elementList = ElementFactory::getElementList($elementClass, $conditions, $orderBy, $join);

		return $elementList;
	}

	/**
	 * Gets parent element from an element
	 * @param Element $element The child element
	 * @param string $parentClass The parent element class
	 * @param string $conditions The conditions string to apply
	 * @param string $orderBy The order string to apply
	 * @return Element The parent element
	 */
	public static function getParentElement($element, $parentClass, $conditions=NULL, $orderBy=NULL)
	{
		$logInstance = LogTool::getInstance();
		$logInstance->logDebug('Gets ' . $element->getElementClass() . ' parent ' . $parentClass . ' element...');

		// Gets parent element by type and id
		$parentIdFieldName = DatabaseFactory::getParentIdColumnName($parentClass);

		// Split parent class name for search like people_mother, to get mother_people_id field from People table 
		$parentClassNameList = StringTool::split(ElementFactory::TABLE_FIELD_SEPARATOR, $parentClass);
		$parentClass = end($parentClassNameList);

		$parentId = $element->$parentIdFieldName;

		// Parent element id is null
		if ($parentId === NULL)
			throw new ElementException('Cannot get ' . $parentClass . ' parent for ' . $element->getElementClass() . ' with id #' . $element->id . ': ' . $parentIdFieldName . ' is NULL');

		return ElementFactory::getElement($parentClass, $parentId, $conditions, $orderBy);
	}

	/**
	 * Adds an element, insert it in database
	 * @param Element $element The element to add
	 */
	public static function addElement($element)
	{
		$logInstance = LogTool::getInstance();
		$logInstance->logDebug('Adds ' . $element->getElementClass() . ' element...');

		// Adds element to database
		DatabaseFactory::addElement($element);
	}
	

	/**
	 * Updates an element
	 * @param Element $element The element to update
	 * @return int The affected rows number
	 */
	public static function updateElement($element)
	{
		$logInstance = LogTool::getInstance();
		$logInstance->unsetSilentMode();

		//TODO: manage logs for tables with both id (_has_ tables)
		$logInstance->logDebug('Updates ' . $element->getElementClass() . ' element with id #' . $element->id);
		
		// Updates element in database
		$affectedRowNumber = DatabaseFactory::updateElement($element);

		// Updates element in cache
		///CacheFactory::updateElement($element);
	
		return $affectedRowNumber;
	}

	/**
	 * Deletes an element
	 * @param Element $element The element to delete
	 */
	public static function deleteElement($element)
	{
		$logInstance = LogTool::getInstance();
		$logInstance->logDebug('Deletes ' . $element->getElementClass() . ' element with id #' . $element->id);

		// Updates element in database
		DatabaseFactory::deleteElement($element);
	}
	

	/**
	 * Deletes an element list in database
	 * @param string $elementClass The element class to delete
	 * @param string $conditions The delete request conditions
	 * @return int The affected rows number
	 */
	public static function deleteElementList($elementClass, $conditions)
	{
		$logInstance = LogTool::getInstance();
		$logInstance->logDebug('Deletes ' . $elementClass . ' element list');
		
		// Deletes element in database
		return DatabaseFactory::deleteElementList($elementClass, $conditions);
	}
	
	/**
	 * Undefined methods interceptor
	 * Adds method :
	 * - getParentXxx($conditions, $orderBy)
	 * @param string $methodName The called method name
	 * @param string $params The called method parameters
	 */
	public function __call($methodName, $params)
	{
		$regs = NULL;

		// getXXXList method type
		if (mb_ereg('^get([a-zA-Z0-9_]+)List$', $methodName, $regs))
		{
			$elementClass = $regs[1];
			$conditions = (count($params) >= 1 ? $params[0] : NULL);
			$orderBy = (count($params) >= 2 ? $params[1] : NULL);
			return ElementFactory::getElementList($elementClass, $conditions, $orderBy);
		}
		
		throw new ElementException('Invalid method called: "' . $methodName . '"');	}
}
?>