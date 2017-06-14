<?php
include "GWS_Form.php";
/**
 * GWS_Commands have to register via GWS_Commands::register($code, GWS_Command, $binary=true)
 * @author gizmore
 */
abstract class GWS_Command
{
	protected $message;
	
	public function setMessage(GWS_Message $message) { $this->message = $message; return $this; }
	
	public function user() { return $this->message->user(); }
	public function message() { return $this->message; }
	
	################
	### Abstract ###
	################
	public abstract function execute(GWS_Message $msg);
}
