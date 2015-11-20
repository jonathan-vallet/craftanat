<?php
/**
 * Translation tool class
 */
class TranslationTool extends SingletonModel
{
	const DEFAULT_LOCALE = 'fr';
	static $languageList = array('fr','en','de');

	protected $selectedLocale = NULL;

	/**
	 * Unique instance (singleton) retrieval method
	 * @param string $forcedLocale The locale that has to be used instead of the configured one
	 * @return Localization The Localization unique instance
	 */
	public static function getInstance($locale=TranslationTool::DEFAULT_LOCALE)
	{
		return self::getClassInstance(get_class(), $locale);
	}
	
	/**
	* Return the locale of the client browser
	* @param string $language The language of the browser
	* @return string The locale of the client
	*/
	public static function getLocaleFromLanguage($language){
		$locale = StringTool::substr($language,0,2);
		if(in_array($locale, TranslationTool::$languageList)){
			return $locale;
		}else{
			return TranslationTool::DEFAULT_LOCALE;
		}
	}
	
	/**
	 * Class constructor (initializes gettext)
	 * @param string $forcedLocale The locale that has to be used instead of the configured one
	 */
	public function __construct($locale=TranslationTool::DEFAULT_LOCALE)
	{
		if ($locale == NULL && !TranslationTool::isTranslationAllowed())
				throw new TranslationException('Unable to set locale in an other context than web');
		
		// TODO: check locale from broswer, session, registration params...
		$this->setLocale(TranslationTool::getLocaleFromLanguage($locale));
	}

	/**
	 * Gets selected locale
	 * @return string The locale string
	 */
	public function getLocale()
	{
		return $this->selectedLocale;
	}

	/**
	 * Sets selected locale
	 * @param string $locale The locale string
	 */
	private function setLocale($locale)
	{
		$this->selectedLocale = $locale;
	}

	/**
	 * Sets translation in database
	 *
	 * @param string $codeName the code naeme to add/update/delete
	 * @param array $translationList array like array('en' => 'enValue', 'fr' => 'deValue')
	 */
	public static function setTranslations($codeName, $translationList)
	{
		if(empty($translationList))
			return;

		foreach($translationList as $language => $translation)
		{
			// If translation text is null, removes the entry
			if($translation === NULL)
			{
				ElementFactory::deleteElementList('Translation', 'translation_text=\'' . $codeName . '\' AND translation_language=\'' . $language . '\'');
				continue;
			}

			try
			{
				$translationElement = ElementFactory::getElement('Translation', NULL, 'translation_text=\'' . $codeName . '\' AND translation_language=\'' . $language . '\'');
				$translationElement->value = $translation;
				$translationElement->update();
			}
			catch(ElementNoResultException $e)
			{
				$translationElement = new Translation();
				$translationElement->language = $language;
				$translationElement->text = $codeName;
				$translationElement->value = $translation;
				$translationElement->add();
			}
		}
	}
	
	/**
	 * Removes translations in database
	 *
	 * @param string $codeName the code naeme to add/update/delete
	 */
	public static function removeTranslations($codeName)
	{
		ElementFactory::deleteElementList('Translation', 'translation_text=\'' . $codeName . '\'');
	}

	/**
	 * Translates the string in correct language
	 * @param string $string The string to translate
	 * @param array $paramList The param list to be replaced in the string
	 * @param int $quantity The quantity to pluralize the string
	 * @param string $category The translation's category
	 * @return string The translated string
	 */
	public function translate($string, $paramList = NULL, $quantity = NULL, $category = NULL){
		// Checks if string has to be pluralized
		return $string;
		if($quantity !== NULL && $quantity !== 1)
				$string .= '_PLURAL';
		// Set silent mode to avoid every translation log
		LogTool::getInstance()->setSilentMode();
		// Gets string from database
		try
		{
			if($category !== NULL)
				$category = ' AND translation_category = \''.$category.'\'';
			else
				$category = '';
			$translation = ElementFactory::getElement('Translation', NULL, 'translation_language = \'' . $this->getLocale() . '\' AND translation_text = \''.$string.'\''.$category);
			LogTool::getInstance()->unsetSilentMode();
			if ($paramList === NULL)
				return $translation->value;

			// String has params "%1" to be replaced
			if (!is_array($paramList))
				$paramList = array($paramList);

			// Sets pattern to be replaced
			$patternList = array();
			for ($paramNumber = 1; $paramNumber <= count($paramList); ++$paramNumber)
				$patternList[] = '/%' . $paramNumber . '(?![0-9])/';
				
			return preg_replace($patternList, $paramList, $translation->value);
		}
		catch (ElementNoResultException $e)
		{
			// String is not localized

			try {
				$warningTracking = new WarningTracking();

				$warningTracking->addTracking("Missing ".$this->getLocale()." translation on ".$string); // TODO : Get template.
				DatabaseFactory::commit();				
			}
			catch (Exception $e)
			{
			}
			
			LogTool::getInstance()->unsetSilentMode();

			return 'TO_BE_LOCALIZED(' . $string . ')';
		}

	}
	
	/**
	* Sets the locale by the selected flag
	* @param string $locale The new locale string
	*/
	public function setLocaleByFlag($locale){
		$this->setLocale($locale);
		$_SESSION['locale'] = $locale;
	}

	/**
	 * Checks if context allowed translation
	 */
	public static function isTranslationAllowed()
	{
		// No gettext in other context than web
		return SystemTool::isWebProcess();
	}
	
}
?>
