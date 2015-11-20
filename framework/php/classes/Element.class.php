<?php

/**
 * Generic element class
 */
abstract class Element
{
	/**
	 * Element property list array
	 * @var array
	 */
	protected $propertyList = array();
	
	/**
	 * Element original property list array, the property values when object was loaded
	 * @var array
	 */
	protected $originalPropertyList = array();
	
	/**
	 * Retrieves the element type
	 * @return string The element type
	 */
	public function getElementClass()
	{
		return get_class($this);
	}
	
	/**
	 * Gets the database connection used by current element
	 * @return DatabaseConnection instance
	 */
	public static function getDatabaseConnection()
	{
		throw new DatabaseConnectionException('Method has to be implemented, have to return the database connection of the element');	
	}
	
	/**
	 * Sets element id if table has no "id" attribute
	 */
	public function setId()
	{
		if(isset($this->id))
			return;

		$tableName = DatabaseFactory::getElementTableName($this->getElementClass());
		
		// Creates an attribute "id" for "_has_" tables
		if(StringTool::contains($tableName, ElementFactory::TABLE_JOIN_SEPARATOR))
		{
			// Creates an attribute 'id' from both primary keys
			$tableList = StringTool::split(ElementFactory::TABLE_JOIN_SEPARATOR, $tableName);
			// Sets condition from both tables
			$table1FieldName = $tableList[0] . '_id';
			$table2FieldName = $tableList[1] . '_id';
			$this->id = $this->$table1FieldName . '-' . $this->$table2FieldName;
		}
	}
	
	/**
	 * Constructs an element from an array
	 * @param string $elementClass The element type
	 * @param array $array The element propertyList array
	 * @return Element The typed element or an exception if the specified type is invalid
	 */
	public static function getElementFromArray($elementClass, &$array)
	{
		// TODO: Check if element is in cache

		// Instantiates element from array
		$element = new $elementClass();
		$element->setPropertyList($array);
		return $element;
	}
	
	/**
	 * Gets element propertyList array
	 * @return array $propertyList The element propertyList array
	 */
	public function getPropertyList()
	{
		return $this->propertyList;
	}
	
	/**
	 * Sets element propertyList array
	 * @param array $propertyList The element propertyList array
	 */
	public function setPropertyList($propertyList)
	{
		foreach ($propertyList as $propertyName => &$propertyValue)
			$this->setProperty($propertyName, $propertyValue);

		// properties are set from database, resets original properties
		$this->originalPropertyList = array();
	}

	/**
	 * Checks if a property of the element is set
	 * @param string $propertyName the name of the property to check
	 * @return boolean If the property is set or not
	 */
	public function issetProperty($propertyName)
	{
		return ArrayTool::array_key_exists($propertyName, $this->propertyList);
	}
	
	/**
	 * Property writing accessor
	 * @param string $propertyName The property name
	 * @param string $value The property value
	 */
	public function setProperty($propertyName, $value)
	{
		// Non-null value
		if ($value !== NULL)
		{
			// Numeric value
			if (StringTool::isInt($value))
				$value = StringTool::toInt($value, false);
			else if (StringTool::isFloat($value, FALSE))
				$value = StringTool::toFloat($value, false);
			// Datetime / date property type 
			else if (StringTool::endsWith($propertyName, 'date'))
			{
				// Date has a 10 length (YYYY-mm-dd)
				if (StringTool::strlen($value) == 10)
					$value = DateTool::stringToTimestamp($value, DateTool::FORMAT_MYSQL_DATE);
				else
					$value = DateTool::stringToTimestamp($value);
			}
			// Day property type 
		}
		
		// Removes table name at the beginning of field name, not for id fields nor xxx_has_xxx tables
		$tableName = DatabaseFactory::getElementTableName($this->getElementClass());
		if(!StringTool::contains($tableName, ElementFactory::TABLE_JOIN_SEPARATOR))
		{
			$tablePrefix = $tableName . '_';
			$tableIdField = $tablePrefix . 'id';

			if(StringTool::startsWith($propertyName, $tablePrefix) && (!StringTool::endsWith($propertyName, '_id') || $propertyName == $tableIdField))
				$propertyName = StringTool::truncateFirstChars($propertyName, StringTool::strlen($tablePrefix));
		}

		// Updates original property list
		if (!ArrayTool::array_key_exists($propertyName, $this->propertyList))
		{
			// It's the first time this property gets a value, it will be updated (set a null value to original property list)
			$this->originalPropertyList[$propertyName] = NULL;			
		}
		else if (ArrayTool::array_key_exists($propertyName, $this->originalPropertyList))
		{
			// Attribute value had already changed (originalPropertyList already has a value for this property)
			// If value has been reset to original value, removes the update of the property
			if ($value == $this->originalPropertyList[$propertyName])
				unset($this->originalPropertyList[$propertyName]);
		}
		else if ($value !== $this->propertyList[$propertyName])
		{
			// If value has changed, updates original value 
			$this->originalPropertyList[$propertyName] = $this->propertyList[$propertyName];
		}
		
		// Sets property new value
		$this->propertyList[$propertyName] = $value;
	}

	/**
	 * Returns updated property list
	 * @return array The element updated property list
	 */
	public function & getUpdatedPropertyList()
	{
		$updatedPropertyList = array();
		
		// Checks if updated properties have different value than before
		foreach ($this->originalPropertyList as $propertyName => &$propertyValue)
		{
			if($propertyName === 'id')
				continue;
			if ($propertyValue !== $this->propertyList[$propertyName])
				$updatedPropertyList[$propertyName] = $this->propertyList[$propertyName];
		}

		return $updatedPropertyList;
	}

	/**
	 * Checks if a property has changed since it's last update/add
	 * @param string $propertyName The property name
	 * @return boolean If the property has been updated or not
	 */
	public function hasChangedProperty($propertyName)
	{
		return ArrayTool::array_key_exists($propertyName, $this->originalPropertyList);
	}
	
	/**
	 * Returns parent element
	 * @param string $elementClass The parent element class
	 * @param string $conditions The conditions string to apply
	 * @param string $orderBy The order string to apply
	 * @return int The parent element
	 */
	private function getParentElement($parentClass, $conditions=null, $orderBy=null)
	{
		return ElementFactory::getParentElement($this, $parentClass, $conditions, $orderBy);
	}

	/**
	 * Returns child element list from current element
	 * @param string $elementClass The child element type
	 * @param string $conditions The conditions string to apply
	 * @param string $orderBy The order string to apply
	 * @return array The element list linked to current element
	 */
	private function & getElementList($elementClass, $conditions=NULL, $orderBy=NULL, $join=NULL)
	{
		return ElementFactory::getElementListFromParent($elementClass, $this, $conditions, $orderBy, $join);
	}

	/**
	 * Adds current element, insert it in database
	 */
	public function add()
	{
		// Adds element by factory
		ElementFactory::addElement($this);

		// resets original attributes, has element is now added, there is no more property to update
		$this->originalPropertyList = array();
	}
	
	/**
	 * Updates current element
	 */
	public function update()
	{
		$updatedPropertyList = $this->getUpdatedPropertyList();

		// Checks if element has to be updated
		if (count($updatedPropertyList) > 0)
		{
			// Updates element
			ElementFactory::updateElement($this);

			// resets original attributes, has element is now updted, there is no more property to update
			$this->originalPropertyList = array();
		}
		else
			LogTool::getInstance()->logDebug('Trying to update ' . $this->getElementClass() . ' but no change detected');
	}

	/**
	 * Deletes current element
	 */
	public function delete()
	{
		// Deletes element by factory
		ElementFactory::deleteElement($this);
	}
	
	/**
	 * Property reading interceptor
	 * @param string $propertyName The property name
	 * @return string The property value or an exception if the property doesn't exist
	 */
	public function __get($propertyName)
	{
		// Property is set
		try
		{
			return $this->propertyList[$propertyName];
		}
		catch (Exception $e)
		{
			throw new ElementPropertyDoesNotExistException('"' . $propertyName . '" property is not defined for ' . $this->getElementClass() . ' with id #' . $this->id);
		}
	}

	/**
	 * Property writing interceptor
	 * @param string $propertyName The property name
	 * @param string $value The property value
	 */
	public function __set($propertyName, $value)
	{
		$this->setProperty($propertyName, $value);
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
		$matches = null;

		// getParentXxx method match
		if (mb_ereg('^getParent([a-zA-Z0-9_]+)$', $methodName, $matches))
		{
			$parentClass = $matches[1];

			// Sets additionnal parameters
			$conditions = (count($params) >= 1 ? $params[0] : null);
			$orderBy = (count($params) >= 2 ? $params[1] : null);
			
			return $this->getParentElement($parentClass, $conditions, $orderBy);
		}
		else if (mb_ereg('^get([a-zA-Z0-9_]+)List$', $methodName, $matches))
		{
			$elementClass = $matches[1];
			$conditions = (count($params) >= 1 ? $params[0] : null);
			$orderBy = (count($params) >= 2 ? $params[1] : null);
			$join = (count($params) >= 3 ? $params[2] : null);

			return $this->getElementList($elementClass, $conditions, $orderBy, $join);
		}
		throw new ElementException('Invalid method called: "' . $methodName . '"');
	}
	
	/**
	 * Gets an array representing current element
	 * @param string $format The format to use to convert element to array
	 * @return array The array
	 */
	protected function & toArray($format, $params=array())
	{
		return array();
	}
	
	/**
	 * Gets an array representing an element list
	 * @param array $elementList The element list
	 * @param string $format The format to use to convert element to array
	 * @return array The array
	 */
	public static function &elementListToArray($elementList, $format, $params=array())
	{
		$elementListArray = array();

		// Adds each element arrays
		foreach ($elementList as $index => $element)
		{
			$array = $element->toArray($format, $params);
			if (count($array) > 0)
				$elementListArray[$index] = $array;
		}

		return $elementListArray;
	}	
}
?>