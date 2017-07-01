<?php
/**
 * GWS_Commands have to register via GWS_Commands::register($code, GWS_Command, $binary=true)
 * @author gizmore
 */
abstract class GWS_Command
{
	protected $message;
	
	public function setMessage(GWS_Message $message) { $this->message = $message; return $this; }
	
	/**
	 * @return GWF_User
	 */
	public function user() { return $this->message->user(); }
	public function message() { return $this->message; }
	
	################
	### Abstract ###
	################
	public abstract function execute(GWS_Message $msg);

	############
	### Util ###
	############
	public function userToBinary(GWF_User $user)
	{
		$fields = $user->gdoColumnsExcept('user_password', 'user_register_ip');
		return $this->gdoToBinary($user, array_keys($fields));
	}

	public function gdoToBinary(GDO $gdo, array $fields=null)
	{
		$fields = $fields ? $gdo->getGDOColumns($fields) : $gdo->gdoColumnsCache();
		$payload = '';
		foreach ($fields as $field)
		{
			if ($field instanceof GDO_Int)
			{
				$payload .= GWS_Message::wrN($field->bytes, $gdo->getVar($field->name));
			}
// 			elseif ( ($field instanceof GDO_Password) ||
// 					 ($field instanceof GDO_IP) )
// 			{
// 				# skip
// 			}
			elseif ($field instanceof GDO_String)
			{
				$payload .= GWS_Message::wrS($gdo->getVar($field->name));
			}
			elseif ($field instanceof GDO_Decimal)
			{
				$payload .= GWS_Message::wrF($gdo->getVar($field->name));
			}
			elseif ($field instanceof GDO_Enum)
			{
				$value = array_search($gdo->getVar($field->name), $field->enumValues);
				$payload .= GWS_Message::wr8($value === false ? 0 : $value + 1);
			}
			elseif ($field instanceof GDO_Time)
			{
				$payload .= GWS_Message::wr32($gdo->getVar($field->name));
			}
			elseif ($field instanceof GDO_Date)
			{
				$value = GWF_Time::getTimestamp($gdo->getVar($field->name));
				$payload .= GWS_Message::wr32($value);
			}
		}
		return $payload;
	}

}
