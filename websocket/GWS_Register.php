<?php
final class GWS_Register extends GWS_Command
{
	public function execute(GWS_Message $msg)
	{
		$form = method('Register', 'Form');
		GWS_Form::bind($form, $msg);
	}
}

GWS_Commands::register(0x0102, new GWS_Register());
