<?php
final class GWS_Login extends GWS_Command
{
	public function execute(GWS_Message $msg)
	{
		die('X');
	}
}

GWS_Commands::register(0x0103, new GWS_Login());
