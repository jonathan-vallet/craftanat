<?php
/**
 * Database factory class
 */
abstract class DatabaseFactory
{
	const COLUMN_SEPARATOR = '_';

	const JOIN_TABLE_SEPARATOR = ', ';
	const JOIN_TABLE_FIELD_SEPARATOR = '_SEPARATOR';
	const JOIN_SYNTAX_SEPARATOR = ' ';
	const JOIN_CONDITIONS_SEPARATOR = ' ON ';

	/**
	 * Gets the name of the table from an element class string
	 * @param string $elementClass The element class name
	 * @return string The table name
	 */
	public static function getElementTableName($elementClass)
	{
		// Table format is "table_name", class name format is "ClassName"
		return StringTool::lower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $elementClass));
	}

	/**
	 * Gets the name of the element class from a table name string
	 * @param string $elementTable The element class name
	 * @return string The class name
	 */
	public static function getElementClassName($elementTable)
	{
		// Table format is "table_name", class name format is "ClassName"

		// old (php <5.5)
		//return preg_replace('/(?:^|_)(.?)/e', "strtoupper('$1')", $elementTable);

		return preg_replace_callback(
			'/(?:^|_)(.?)/',
			function ($m) {
				return strtoupper($m[1]);
			},
			$elementTable
		);
	}
	
	/**
	 * Gets the name of the parent column id
	 * @param string $parentClass The parent class
	 * @return string The parent id column name
	 */
	public static function & getParentIdColumnName($parentClass)
	{
		$parentId = DatabaseFactory::getElementTableName($parentClass) . '_id';
		return $parentId;
	}

	/**
	 * Returns The SQL query join part (shared between getElementList and getElementCount)
	 * @param string $mainTableName The main table name
	 * @param string $join The join string to apply (ex : 'World IJ Region, Region LJ City, World IJ City')
	 * @return array array($request, $joinTableNameList, $joinTableAttachements, $specificJoinConditions, $joinOrder)
	 */
	public static function getJoin($mainTableName, $joinCondition)
	{
		$joinSelectRequest = ''; // SELECT part of the request
		$joinFromRequest = ''; // FROM part of the request
		// Parses join string (ex : 'World IJ Region, Region LJ User')
		$joinTableNameList = array(); // Element types array (ex : [World, Region, City])
		$joinOperators = array(); // Join operators array (ex : [IJ, LJ, RJ])
		$joinLeftTypes = array(); // Join left types array (ex : [World, Region, World]])
		$joinRightTypes = array(); // Join right types array (ex : [Region, City, City]])
		$joinConditions = array();
		$joinTableAttachements = array(); // Join table attachements array (ex : [Region => World, City => [Region, World]])

		// Sets a join per table/condition match, separeted by comma (ie: 0 => Character IJ User, 1 => Character IJ Type)
		$joinList = StringTool::split(DatabaseFactory::JOIN_TABLE_SEPARATOR, $joinCondition);
		$joinOrder = array(); // join order array (ex: ['Region'=>0, 'User'=>-1, 'Zone'=>1])

		$joinOrder[$mainTableName] = 0; // join order init

		foreach($joinList as &$join)
		{
			// Parse join conditions and joined tables
			$joinSplit = StringTool::split(DatabaseFactory::JOIN_CONDITIONS_SEPARATOR, $join);
			$joinTables = $joinSplit[0];
			$joinCondition = isset($joinSplit[1]) ? $joinSplit[1] : NULL;

			// Separates left and right parts from spaces (ie: Character IJ Type => array(Character, IJ, Type))
			list($leftSyntax, $joinOperator, $rightSyntax) = StringTool::split(DatabaseFactory::JOIN_SYNTAX_SEPARATOR, $joinTables);

			// Scans left/right part for aliases
			$leftType = $leftSyntax;
			$rightType = $rightSyntax;

			// Gets table names from types
			$leftTableName = DatabaseFactory::getElementTableName($leftType);
			$rightTableName = DatabaseFactory::getElementTableName($rightType);
			if (isset($joinOrder[$leftTableName]))
				$joinOrder[$rightTableName] = $joinOrder[$leftTableName] + 1;
			elseif (isset($joinOrder[$rightTableName]))
				$joinOrder[$leftTableName] = $joinOrder[$rightTableName] - 1;
			else
				LogTool::getInstance()->logWarning('Join syntax is not in the right order');

			if ($leftTableName != $mainTableName &&	!in_array($leftTableName, $joinTableNameList))
			$joinTableNameList[] = $leftTableName;
			if ($rightTableName != $mainTableName && !in_array($rightTableName, $joinTableNameList))
			$joinTableNameList[] = $rightTableName;

			$joinOperators[] = $joinOperator;
			$joinLeftTypes[] = $leftType;
			$joinRightTypes[] = $rightType;
			$joinConditions[] = $joinCondition;
			$joinTableAttachements[$rightTableName][] = $leftTableName;
		}

		// Builds request field list part
		$usedTableNameList = array($mainTableName);
		$joinSelectRequest .= $mainTableName.'.*';

		foreach ($joinTableNameList as &$joinTableName)
		$joinSelectRequest .= ', NULL AS ' . $joinTableName . DatabaseFactory::JOIN_TABLE_FIELD_SEPARATOR . ', ' . $joinTableName . '.*';

		// Builds request "FROM" part
		$joinFromRequest .= ' FROM ' . $mainTableName;

		// Builds request join list part
		$specificJoinConditions = FALSE;
		$joinOperatorsCount = count($joinOperators);
		for ($fieldIndex = 0; $fieldIndex < $joinOperatorsCount; $fieldIndex++)
		{
			$joinOperator = $joinOperators[$fieldIndex];
			$joinLeftType = $joinLeftTypes[$fieldIndex];
			$joinRightType = $joinRightTypes[$fieldIndex];
			$joinLeftTableName = DatabaseFactory::getElementTableName($joinLeftType);
			$joinRightTableName = DatabaseFactory::getElementTableName($joinRightType);
			$joinLeftField = $joinLeftTableName . '_id';
			$joinRightField = DatabaseFactory::getParentIdColumnName($joinLeftType);

			$joinCondition = $joinConditions[$fieldIndex];
			if ($joinCondition === NULL)
			{
				$joinCondition = $joinRightTableName . '.' . $joinRightField.
							'='. $joinLeftTableName . '.' . $joinLeftField;
			}
			else
			$specificJoinConditions = TRUE;

			switch ($joinOperator)
			{
				// Inner join
				case 'IJ':
					$joinOperatorSql = ' INNER JOIN ';
					break;
					// Left join
				case 'LJ':
					$joinOperatorSql = ' LEFT JOIN ';
					break;
				default:
					// Unknown join
					throw new DatabaseException('Invalid join operator specified : "'.$joinOperator.'"');
			}

			// Right type already used (joining on left type)
			if (in_array($joinRightTableName, $usedTableNameList))
				$joinFromRequest .= $joinOperatorSql.$joinLeftTableName;
			else
				$joinFromRequest .= $joinOperatorSql.$joinRightTableName;
			$joinFromRequest .= ' ON '.$joinCondition;

			// Marks join types as used
			$usedTableNameList[] = array($joinLeftTableName);
			$usedTableNameList[] = array($joinRightTableName);
		}

		return array($joinSelectRequest, $joinFromRequest, $joinTableNameList, $joinTableAttachements, $specificJoinConditions, $joinOrder);
	}

	/**
	 * Gets all elements by type from database
	 * @param string $elementClass The element type searched
	 * @param string $conditions The conditions string to apply (ex : Region.x < 10 AND World.size = 500)
	 * @param string $orderBy The order string to apply (ex : 'Region.x, City.name DESC')
	 * @param string $join the joined tables
	 * @return array The element list
	 */
	public static function getElementList($elementClass, $conditions=NULL, $orderBy=NULL, $join=NULL)
	{
		$tableName = DatabaseFactory::getElementTableName($elementClass);

		if($join !== NULL)
		{
			list($select, $from, $joinTableNameList, $joinTableAttachements) = DatabaseFactory::getJoin($tableName, $join);
			$request = 'SELECT ' . $select . $from;
		}
		else
		$request = 'SELECT * FROM ' . $tableName;

		if ($conditions !== NULL)
			$request .= ' WHERE '. $conditions;
		if ($orderBy !== NULL)
			$request .= ' ORDER BY '.$orderBy;

		$databaseConnection = $elementClass::getDatabaseConnection();

		// For join requests, needs to fetch results by index (to handle several fields with same name)
		if ($join === NULL)
		{
			// Execute request on database
			$resultType = MysqlDatabaseConnectionModel::ARRAY_TYPE_ASSOC;
			$resultList = $databaseConnection->selectRequest($request, $resultType, false, false);
		}
		else
		{
			// Gets field list with resut to get them for joined tables
			$resultType = MysqlDatabaseConnectionModel::ARRAY_TYPE_NUM;
			list($resultList, $fieldNameList) = $databaseConnection->selectRequest($request, $resultType, TRUE, FALSE);

			// Builds main element field list
			$fieldIndex = 0;
			$fieldNumber = count($fieldNameList);
			$elementFieldNameList = array();
			while ($fieldIndex < $fieldNumber)
			{
				$fieldName = $fieldNameList[$fieldIndex++];
				if (StringTool::endsWith($fieldName, DatabaseFactory::JOIN_TABLE_FIELD_SEPARATOR))
				break;
				$elementFieldNameList[$tableName][] = $fieldName;
			}
			// Builds joined elements field list
			foreach ($joinTableNameList as &$joinTableName)
			{
				while ($fieldIndex < $fieldNumber)
				{
					$fieldName = $fieldNameList[$fieldIndex++];
					if (StringTool::endsWith($fieldName, DatabaseFactory::JOIN_TABLE_FIELD_SEPARATOR))
					break;
					$elementFieldNameList[$joinTableName][] = $fieldName;
				}
			}
		}

		// Constructs typed elements from results
		$elementList = array();
		$retrievedElementList = array();
		while ($result = MysqlDatabaseConnectionModel::fetch_array($resultList, $resultType))
		{
			$rowElementList = array();

			if ($join === NULL)
			{
				// Sets element
				$element = Element::getElementFromArray($elementClass, $result);
				$element->setId();
				$elementList[$element->id] = $element;
			}
			else
			{
				// Additionnal tables requested
				$fieldIndex = 0;
				$fieldValues = array_values($result);

				$rowElementList = array();
				// Sets elements attributes for each table on the row
				foreach ($elementFieldNameList as $elementTableName => $fieldNameList)
				{
					$elementAttributeList = array();
					foreach($fieldNameList as $fieldName)
						$elementAttributeList[$fieldName] = $fieldValues[$fieldIndex++];
					// Go next to avoid separator field
					++$fieldIndex;

					// Sets element
					$element = Element::getElementFromArray(DatabaseFactory::getElementClassName($elementTableName), $elementAttributeList);
					$element->setId();

					// Ignores empty LJ elements from "_has_" tables
					if($element->id == '-')
						continue;
					
					// Adds it to element list if it is the main table
					if($tableName == $elementTableName && !ArrayTool::array_key_exists($element->id, $elementList))
						$elementList[$element->id] = $element;
					$rowElementList[$elementTableName] = $element;
				}
				
				// Attaches elements with each other
				foreach ($rowElementList as $rowElementTable => $rowElement)
				{
					CacheFactory::addElement($rowElement);
					// Checks if element has to be attached to another one
					if (isset($joinTableAttachements[$rowElementTable]))
					{
						foreach ($joinTableAttachements[$rowElementTable] as &$joinTableAttachement)
						{
							if (isset($rowElementList[$joinTableAttachement]))
							{
								if ($rowElementList[$joinTableAttachement]->getElementClass() != $rowElement->getElementClass() || $rowElementList[$joinTableAttachement]->id != $rowElement->id)
									$retrievedElementList[$joinTableAttachement][$rowElementList[$joinTableAttachement]->id][$rowElementTable][$rowElement->id] = $rowElement;
							}
						}
					}
				}
			}
		}

		MysqlDatabaseConnectionModel::free_result($resultList);
		return array($elementList, $retrievedElementList);
	}

	/**
	 * Adds an element to database
	 * @param $element The element to add
	 */
	public static function addElement($element)
	{
		$elementClass = $element->getElementClass();
		$tableName = DatabaseFactory::getElementTableName($elementClass);

		// Gets database connection instance
		$databaseConnection = $elementClass::getDatabaseConnection();

		// Builds request string
		$elementPropertyList = $element->getPropertyList();

		$fieldNameString = '';
		$fieldValueString = '';

		foreach ($elementPropertyList as $propertyName => &$propertyValue)
		{
			// Ignores 'id' field for '_has_' tables
			if(StringTool::contains($tableName, ElementFactory::TABLE_JOIN_SEPARATOR) && $propertyName == 'id')
			continue;
			// restore $table name prefix
			$fieldNameString .= DatabaseFactory::getFieldNameFromTableName($propertyName, $tableName) . ', ';

			$fieldValueString .= DatabaseFactory::getFormattedPropertyValue($elementClass, $propertyName, $propertyValue) . ', ';
		}

		// Removes ", " at the end of the property name and value string
		$fieldNameString = StringTool::truncateLastChars($fieldNameString, 2);
		$fieldValueString = StringTool::truncateLastChars($fieldValueString, 2);

		$request = 'INSERT INTO ' . $tableName . ' (' . $fieldNameString . ') VALUES (' . $fieldValueString . ')';

		// Request execution on database
		$elementId = $databaseConnection->insertRequest($request);
		if($tableName == 'map')
		$element->id = $element->x . '-' . $element->y;
		else if(StringTool::contains($tableName, ElementFactory::TABLE_JOIN_SEPARATOR))
		{
			// Creates an attribute 'id' from both primary keys
			$tableList = StringTool::split(ElementFactory::TABLE_JOIN_SEPARATOR, $tableName);
			// Sets condition from both tables
			$table1FieldName = $tableList[0] . '_id';
			$table2FieldName = $tableList[1] . '_id';
			$element->id = $element->$table1FieldName . '-' . $element->$table2FieldName;
		}
		else
		$element->id = $elementId;

		return $databaseConnection->getAffectedRowCount();
	}

	/**
	 * Updates an element in database
	 * @param Element $element The element to update
	 * @return int The affected rows number
	 */
	public static function updateElement($element)
	{
		$elementClass = $element->getElementClass();
		$tableName = DatabaseFactory::getElementTableName($elementClass);

		// '_has_' tables have 2 primary keys
		if(!StringTool::contains($tableName, ElementFactory::TABLE_JOIN_SEPARATOR))
		$conditions = $tableName . '_id = \'' . $element->id . '\'';
		else
		{
			$tableList = StringTool::split(ElementFactory::TABLE_JOIN_SEPARATOR, $tableName);
			// Sets condition from both tables
			$tableFieldName = $tableList[0] . '_id';
			$conditions = $tableFieldName . ' = \'' . $element->$tableFieldName . '\'';
			$tableFieldName = $tableList[1] . '_id';
			$conditions .= ' AND ' . $tableFieldName . ' = \'' . $element->$tableFieldName . '\'';
		}

		$updatedPropertyList = $element->getUpdatedPropertyList();

		// Updates the element in database
		$affectedRowNumber = DatabaseFactory::updateElementList($elementClass, $updatedPropertyList, $conditions);

		return $affectedRowNumber;
	}

	/**
	 * Updates an element list in database
	 * @param string $elementClass The element type to update
	 * @param array $updatedPropertyList An array discribing attributes to update (format is array(name => value) or array(name => array(value, operator)))
	 * @param string $conditions The update request conditions
	 * @return int The affected rows number
	 */
	public static function updateElementList($elementClass, $updatedPropertyList, $conditions=NULL)
	{
		$tableName = DatabaseFactory::getElementTableName($elementClass);

		// Gets database connection instance
		$databaseConnection = $elementClass::getDatabaseConnection();

		// Builds request string
		$request = 'UPDATE ' . $tableName . ' SET ';
		foreach ($updatedPropertyList as $propertyName => &$propertyValue)
		{
			// Don't update id
			if ($propertyName == 'id')
			continue;

			// restore $table name prefix for non ids fields
			if(!StringTool::endsWith($propertyName, '_id'))
			$propertyName = DatabaseFactory::getFieldNameFromTableName($propertyName, $tableName);

			$request .= $propertyName . ' = ' . DatabaseFactory::getFormattedPropertyValue($elementClass, $propertyName, $propertyValue) . ', ';
		}

		// Removes ", " at the end of the property name and value string
		$request = StringTool::truncateLastChars($request, 2);
		if ($conditions !== NULL)
		$request .= ' WHERE (' . $conditions . ')';

		// Request execution on database
		$affectedRowNumber = $databaseConnection->updateRequest($request);

		return $affectedRowNumber;
	}

	/**
	 * Deletes an element in database
	 * @param Element $element The element to delete
	 */
	public static function deleteElement($element)
	{
		// Builds request string
		$elementClass = $element->getElementClass();
		$tableName = DatabaseFactory::getElementTableName($elementClass);

		$request = 'DELETE FROM ' . $tableName . ' WHERE ' . $tableName . '_id = \'' . $element->id . '\'';

		// Gets database connection instance
		$databaseConnection = $elementClass::getDatabaseConnection();
		$affectedRowNumber = $databaseConnection->deleteRequest($request);

		// Affected row number is incorrect
		if ($affectedRowNumber != 1)
		throw new DatabaseException('Invalid affected row number while deleting ' . $elementType . ' element with id #' . $elementId);
	}

	/**
	 * Deletes an element list in database
	 * @param string $elementClass The element class to delete
	 * @param string $conditions The delete request conditions
	 * @return int The affected rows number
	 */
	public static function deleteElementList($elementClass, $conditions=null)
	{
		$tableName = DatabaseFactory::getElementTableName($elementClass);

		// Gets database connection instance
		$databaseConnection = $elementClass::getDatabaseConnection();

		// Builds request string
		$request = 'DELETE FROM '.$tableName;

		if ($conditions !== NULL)
		$request .= ' WHERE ('.$conditions.')';

		// Executes request in database
		$affectedRowNumber = $databaseConnection->deleteRequest($request);

		return $affectedRowNumber;
	}

	/**
	 * Gets property value in correct format for request
	 * @param string $propertyName The name of the property to detect field type
	 * @param string $propertyValue The property value
	 * @return string the value in correct format for the request
	 */
	private static function getFormattedPropertyValue($elementClass, $propertyName, $propertyValue)
	{
		// NULL value
		if ($propertyValue === null)
		$formattedValue = 'NULL';
		// Integer
		else
		{
			$formattedValue = '\'';
			if (StringTool::isInt($propertyValue))
			{
				// Datetime / date property type
				if (StringTool::endsWith($propertyName, 'date'))
				$formattedValue .= DateTool::timestampToString($propertyValue);
				else
				$formattedValue .= StringTool::toInt($propertyValue, FALSE);
			}
			// Float
			else if (StringTool::isFloat($propertyValue, FALSE))
				$formattedValue .= StringTool::toFloat($propertyValue, FALSE);
			// Default (string)
			else
				$formattedValue .= $elementClass::getDatabaseConnection()->realEscapeString($propertyValue);

			$formattedValue .= '\'';
		}
		return $formattedValue;
	}

	public static function getFieldNameFromTableName($propertyName, $tableName)
	{
		if(StringTool::contains($tableName, ElementFactory::TABLE_JOIN_SEPARATOR) || StringTool::endsWith($propertyName, '_id'))
		return $propertyName;

		return $tableName . '_' . $propertyName;
	}

	/**
	 * Starts a transaction
	 */
	public static function startTransaction()
	{
		LogTool::getInstance()->logNotice('Starting transaction...');
		DatabaseConnectionModel::setTransaction();
	}

	/**
	 * Commits current transactions
	 */
	public static function commit()
	{
		LogTool::getInstance()->logNotice('Committing database modifications...');
		DatabaseConnectionModel::commitTransaction();
	}

	/**
	 * Cancels current transactions
	 */
	public static function rollback()
	{
		LogTool::getInstance()->logNotice('Rolling back database modifications...');
		DatabaseConnectionModel::rollbackTransaction();
	}

	/**
	 * Returns TRUE if a transaction is in progress, FALSE else
	 * @return boolean TRUE if a transaction is in progress, FALSE else
	 */
	public static function isTransactionInProgress()
	{
		return DatabaseConnectionModel::isTransactionEnabled();
	}
}
?>