<?php
final class GWS_AsGuest extends GWS_Command
{
	public function execute(GWS_Message $msg)
	{
	}
}

GWS_Commands::register(0x0101, new GWS_AsGuest());
