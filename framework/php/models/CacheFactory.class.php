<?php
/**
 * Cache factory for elements class
 */
abstract class CacheFactory
{
	const LIST_INDEX_SEPARATOR = '|';

	/**
	 * Array whith cached element
	 * Only element ids are cached, refering to $cachedElementListArray array
	 * @var array
	 */
	private static $cachedElementArray = array();

	/**
	 * Array whith cached element
	 * Only element ids are cached, refering to $cachedElementListArray array
	 * @var array
	 */
	private static $cachedElementListArray = array();

	/**
	 * Adds an element to cache
	 * @param Element $element The element to add
	 */
	public static function addElement($element)
	{
		$elementClass = $element->getElementClass();
		if (self::isCachedElement($elementClass, $element->id))
			return;

		if (!ArrayTool::array_key_exists($elementClass, self::$cachedElementArray))
			self::$cachedElementArray[$elementClass] = array();

		// Adds element to cache
		self::$cachedElementArray[$elementClass][$element->id] = $element;
	}

	/**
	 * Updates an element in cache
	 * @param Element $element The element to update
	 */
	public static function updateElement($element)
	{
		$elementClass = $element->getElementClass();
		$elementId = $element->id;

		// Updates element attributes if already cached
		if (self::isCachedElement($elementClass, $elementId))
			self::$cachedElementArray[$elementClass][$elementId] = $element;
	}

	/**
	 * Gets an element by Id from cache
	 * @param string $elementClass The element type searched
	 * @param int $elementId The element Id searched
	 * @return Element The found element or an exception if the element hasn't been cached
	 */
	public static function getElement($elementClass, $elementId, $logTrace=true)
	{
		// Element is not cached yet
		if (!self::isCachedElement($elementClass, $elementId))
			throw new CacheFactoryException('No element found in cache for ' . $elementClass . ' with id #' . $elementId);

		// Element cached
		if($logTrace)
			LogTool::getInstance()->logDebug('Getting Element from cache');
		return self::$cachedElementArray[$elementClass][$elementId];
	}

	/**
	 * Checks if an element is cached
	 * @param string $elementClass The element type searched
	 * @param int $elementId The element Id searched
	 * @return boolean Returns true if the element is cached, else returns false
	 */
	public static function isCachedElement($elementClass, $elementId)
	{
		return(isset(self::$cachedElementArray[$elementClass][$elementId]));
	}

	/**
	 * Returns the list cache index depending on access conditions
	 * @param string $conditions The conditions string to apply
	 * @param string $orderBy The order string to apply
	 * @return string The list cache index
	 */
	public static function getListIndex($conditions, $orderBy)
	{
		return $conditions . self::LIST_INDEX_SEPARATOR . $orderBy;
	}

	/**
	 * Adds element list to cache
	 * @param string $elementClass The element type to add
	 * @param array $elementList The element list to add
	 * @param string $conditions The conditions string to apply
	 * @param string $orderBy The order string to apply
	 */
	public static function addElementList($elementClass, $elementList, $conditions=null, $orderBy=null)
	{
		if (!ArrayTool::array_key_exists($elementClass, self::$cachedElementArray))
			self::$cachedElementListArray[$elementClass] = array();

		$elementIdList = array();
		foreach($elementList as $element)
			$elementIdList[] = $element->id;
		self::$cachedElementListArray[$elementClass][self::getListIndex($conditions, $orderBy)] = $elementIdList;

		foreach ($elementList as $element)
		{
			// Caches current element
			self::addElement($element);
		}
	}

	/**
	 * Checks if an elementList is cached
	 * @param string $elementType The element type searched
	 * @param string $conditions The conditions string to apply
	 * @param string $orderBy The order string to apply
	 * @param string $join The join string to apply
	 * @return boolean Returns true if the elementList is cached, else returns false
	 */
	public static function isCachedElementList($elementClass, $conditions=null, $orderBy=null, $join=null)
	{
		return isset(self::$cachedElementListArray[$elementClass][self::getListIndex($conditions, $orderBy)]);
	}

	/**
	 * Gets all entities by type from cache
	 * @param string $elementClass The element type searched
	 * @param string $conditions The conditions string to apply
	 * @param string $orderBy The order string to apply
	 * @return array The found entities or an exception if the entities hasn't been cached
	 */
	public static function & getElementList($elementClass, $conditions=null, $orderBy=null)
	{
		// Entities not cached yet
		if (!self::isCachedElementList($elementClass, $conditions, $orderBy))
			throw new CacheFactoryException('No element list found in cache for ' . $elementClass . ' (conditions:"'.$conditions.'", order:"'.$orderBy.'")');

		// Element cached
		LogTool::getInstance()->logDebug('Getting List from cache');
		$cachedElementIdList = self::$cachedElementListArray[$elementClass][self::getListIndex($conditions, $orderBy)];
		$cachedElementList = array();
		foreach($cachedElementIdList as $cachedElementId)
			$cachedElementList[$cachedElementId] = self::getElement($elementClass, $cachedElementId, false);

		return $cachedElementList;
	}

	/**
	 * Gets current cached element counts by element types
	 * @return string The cached element counts by type
	 */
	public static function show()
	{
		echo '<h2>Cached elements:</h2>';
		print_r(self::$cachedElementArray);
		echo '<h2>Cached element lists:</h2>';
		print_r(self::$cachedElementListArray);
	}
	
	/**
	 * Resets cache
	 */
	public static function clear()
	{
		self::$cachedElementArray = array();
		self::$cachedElementListArray = array();
	}
}
?>
