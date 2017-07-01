<?php
final class GWS_Login extends GWS_Command
{
	public function execute(GWS_Message $msg)
	{
		$username = $msg->readString();
		$password = $msg->readString();
		method('Login', 'Form')->onLogin($username, $password);
	}
}

GWS_Commands::register(0x0103, new GWS_Login());
