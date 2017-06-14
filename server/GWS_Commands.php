<?php
include 'GWS_Command.php';

/**
 * Command handler base class.
 * Override this and set in websocket module config
 * @author gizmore
 */
class GWS_Commands
{
	const MID_LENGTH = 7; # Sync Message ID
	const DEFAULT_MID = '0000000'; # Sync Message ID
	
	################
	### Commands ###
	################
	/**
	 * 
	 * @var GWS_Command[]
	 */
	public static $COMMANDS = array();
	public static function register(int $code, GWS_Command $command, $binary=true)
	{
		if (isset(self::$COMMANDS[$code]))
		{
			throw new GWF_Exception('err_gws_dup_code', [$code, get_class($command)]);
		}
		self::$COMMANDS[$code] = $command;
	}

	############
	### Exec ###
	############
	public function executeMessage(GWS_Message $message)
	{
		return $this->command($message)->execute($message);
	}
	
	/**
	 * Get command for a message
	 * @param GWS_Message $message
	 * @return GWS_Command
	 */
	public function command(GWS_Message $message)
	{
		$cmd = $message->cmd();
		if (!isset(self::$COMMANDS[$cmd]))
		{
			throw new GWF_Exception('err_gws_unknown_cmd', [$cmd]);
		}
		return self::$COMMANDS[$cmd]->setMessage($message);
	}

	################
	### Override ###
	################
	public function init() {}
	public function timer() {}
	public function connect(GWF_User $user) {}
	public function disconnect(GWF_User $user) {}
}
