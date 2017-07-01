<?php
final class GWS_Global
{
// 	public static $LOGGING = false;
	/**
	 * @var GWF_User[]
	 */
	public static $USERS = array();
	public static $CONNECTIONS = array();
	
	##################
	### User cache ###
	##################
	public static function addUser(GWF_User $user, $conn)
	{
		self::$USERS[$user->getID()] = $user;
		self::$CONNECTIONS[$user->getID()] = $conn;
	}
	
	public static function removeUser(GWF_User $user, $reason='NO_REASON')
	{
		$key = $user->getID();
		if (isset(self::$USERS[$key]))
		{
			unset(self::$USERS[$key]);
			GWS_Global::disconnect($user, $reason);
		}
	}
	
// 	public static function getUser($name)
// 	{
// 		return isset(self::$USERS[$name]) ? self::$USERS[$name] : false;
// 	}
	
	/**
	 * @param int $id
	 * @return GWF_User
	 */
	public static function getUserByID($id)
	{
		return @self::$USERS[$id];
	}
	
	public static function getOrLoadUserById($id)
	{
		if ($user = self::getUserByID($id))
		{
			return $user;
		}
		return self::loadUserById($id);
	}
	
	public static function loadUserById($id)
	{
		if ($user = GWF_User::getByID($id))
		{
			self::$USERS[$id] = $user;
		}
		return $user;
	}
	
	
// 	public static function getOrLoadUser($name, $allowGuests)
// 	{
// 		if (false !== ($user = self::getUser($name)))
// 		{
// 			return $user;
// 		}
// 		return self::loadUser($name, $allowGuests);
// 	}
	
	#################
	### Messaging ###
	#################
// 	/**
// 	 * @deprecated
// 	 * @param GWF_User $user
// 	 * @param string $command
// 	 * @param string $payload
// 	 * @return boolean
// 	 */
// 	public static function sendCommand(GWF_User $user, $command, $payload)
// 	{
// 		return self::send($user, "$command:$payload");
// 	}

// 	/**
// 	 * @deprecated
// 	 * @param GWF_User $user
// 	 * @param string $command
// 	 * @param array $payload
// 	 * @return boolean
// 	 */
// 	public static function sendJSONCommand(GWF_User $user, $command, $payload)
// 	{
// 		return self::sendCommand($user, $command, json_encode($payload));
// 	}
	
	public static function broadcast($payload)
	{
		GWF_Log::logWebsocket(sprintf("!BROADCAST! << %s", $payload));
		foreach (self::$USERS as $user)
		{
			self::send($user, $payload);
		}
		return true;
	}

	public static function broadcastBinary($payload)
	{
		GWF_Log::logWebsocket(sprintf("!BROADCAST!"));
		GWS_Message::hexdump($payload);
		foreach (self::$USERS as $user)
		{
			self::sendBinary($user, $payload);
		}
		return true;
	}
	
	public static function send(GWF_User $user, $payload)
	{
		if ($conn = self::$CONNECTIONS[$user->getID()])
		{
			GWF_Log::logWebsocket(sprintf("%s << %s", $user->displayName(), $payload));
			$conn->send($payload);
			return true;
		}
		else
		{
			GWF_Log::logError(sprintf('User %s not connected.', $user->displayName()));
			return false;
		}
	}
	
	public static function sendBinary(GWF_User $user, $payload)
	{
		if ($conn = self::$CONNECTIONS[$user->getID()])
		{
			GWF_Log::logWebsocket(sprintf("%s << BIN", $user->displayName()));
			GWS_Message::hexdump($payload);
			$conn->sendBinary($payload);
			return true;
		}
		else
		{
			GWF_Log::logWebsocket(sprintf('User %s not connected.', $user->displayName()));
			return false;
		}
	}

	###############
	### Private ###
	###############
// 	private static function loadUser($name, $allowGuests)
// 	{
// 		$letter = $name[0];
// 		if (($letter >= '0') && ($letter <= '9'))
// 		{
// 			if ($allowGuests) {
// 				return self::getUserBySessID($name);
// 			} else {
// 				return false;
// 			}
// 		}
// 		else
// 		{
// 			return GWF_User::getByName($name);
// 		}
// 	}
	
// 	private static function getUserBySessID($number)
// 	{
// 		$number = (string)$number;
// 		if (!isset(self::$USERS[$number]))
// 		{
// 			$user = GWF_Guest::getGuest($number);
// 			$user->setVar('user_password', sha1(GWF_SECRET_SALT.$number.GWF_SECRET_SALT));
// 			self::$USERS[$number] = $user;
// 		}
// 		return self::$USERS[$number];
// 	}
	
	##################
	### Connection ###
	##################
	public static function disconnect(GWF_User $user, $reason="NO_REASON")
	{
		if ($conn = @self::$CONNECTIONS[$user->getID()])
		{
			$conn->send($user, "CLOSE:".$reason);
			unset(self::$CONNECTIONS[$user->getID()]);
		}
	}
	
// 	public static function isConnected($user)
// 	{
// 		return !!isset(self::$CONNECTIONS[$user->getID()]);
// 	}
	

// 	public static function setConnectionInterface($user, $conn)
// 	{
// 		if (self::isConnected($user))
// 		{
// 			self::disconnect($user);
// 		}
// 		self::$CONNECTIONS[$user->getID()] = $conn;
// 	}
	
// 	public static function getConnectionInterface($user)
// 	{
// 		return isset(self::$CONNECTIONS[$user->getID()]) ? self::$CONNECTIONS[$user->getID()] : false;
// 	}

}
