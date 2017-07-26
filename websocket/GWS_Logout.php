<?php
final class GWS_Logout extends GWS_Command
{
    public function execute(GWS_Message $msg)
    {
        method('Login', 'Logout')->execute();
        GWF_User::$CURRENT = $user = GWF_User::ghost();
        $msg->replyBinary($msg->cmd(), $this->userToBinary($user));
    }
}

GWS_Commands::register(0x0104, new GWS_Logout());
