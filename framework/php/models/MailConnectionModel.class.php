<?php
/**
 * Mail connection class
 * @subpackage classes
 */
abstract class MailConnectionModel extends SingletonModel
{
	/**
	 * The mail host name
	 * @var string
	 */
	protected $host;
	
	/**
	 * The mail port
	 * @var string
	 */
	protected $port;

	/**
	 * The mail username
	 * @var string
	 */
	protected $username;

	/**
	 * The mail password
	 * @var string
	 */
	protected $password;

	/**
	 * mail connection links
	 * @var resource
	 */
	private $mailLink;

	/**
	 * The instantiated connection list
	 * @var object array
	 */
	private static $mailConnectionList = array();
	
	/**
	 * Get the host for the mail connection
	 */
	abstract public function getMailConnectionHost();

	/**
	 * Get the database name for the mail connection
	 */
	abstract public function getMailConnectionPort();

	/**
	 * Get the username for the mail connection
	 */
	abstract public function getMailConnectionUserName();

	/**
	 * Get the password for the mail connection
	 */
	abstract public function getMailConnectionPassword();
	
	/**
	 * Class constructor. Starts a transaction if transactional mode activated.
	 */
	public function __construct()
	{
		// adds current connection to instantiated connection list
		array_push(MailConnectionModel::$mailConnectionList, $this);
	}
}
?>