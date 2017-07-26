<?php
final class GWS_Register extends GWS_CommandForm
{
	public function getMethod()
	{
		return method('Register', 'Form');
	}
	
	public function replySuccess(GWS_Message $msg, GWF_Form $form, GWF_Response $response)
	{
		$msg->replyBinary($msg->cmd());
	}
}

GWS_Commands::register(0x0102, new GWS_Register());
