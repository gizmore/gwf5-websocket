<?php
/**
 * Ping and ws system hooks.
 * 
 * 1. hook cache invalidation
 * 2. hook module vars changed
 * 
 * @author gizmore
 *
 */
final class GWS_Ping extends GWS_Command
{
    public function execute(GWS_Message $msg)
    {
        $msg->replyBinary(0x0105); # Reply pong
    }
    
    public function hookCacheInvalidate($table, $id)
    {
        $table = GDO::tableFor($table);
        if ($table->cache)
        {
            $table->cache->uncacheID($id);
        }
    }
    
    public function hookModuleVarsChanged($moduleId)
    {
        GWF_ModuleLoader::instance()->initModuleVars();
    }
}

GWS_Commands::register(0x0105, new GWS_Ping());
