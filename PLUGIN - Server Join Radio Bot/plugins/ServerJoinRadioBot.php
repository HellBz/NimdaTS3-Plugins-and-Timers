<?php
/**
 * Created by Notepad++
 * User: HellBz
 */

namespace Plugin;

use App\Plugin;
use Carbon\Carbon;
use TeamSpeak3\Ts3Exception;

class ServerJoinRadioBot extends Plugin implements PluginContract
{
    public function isTriggered()
    {
        try {
			
			$client = $this->teamSpeak3Bot->node->clientGetById($this->info['clid']);
            $clientInfo = $this->teamSpeak3Bot->node->clientInfoDb($this->teamSpeak3Bot->node->clientFindDb($client['client_nickname']));
			
        } catch(Ts3Exception $e) {
            return;
        }
		
		if(!array_key_exists((string)$clientInfo['client_unique_identifier'], $this->CONFIG['botuids'])) {
            return;
        }

		try
		{
			if( array_key_exists( (string)$clientInfo['client_unique_identifier'],$this->CONFIG['botuids']) ){

				$this->sendOutput( $this->CONFIG['botuids'][(string)$clientInfo['client_unique_identifier']] );
				return;
			}
		}
		catch (Exception $e)
		{
			return;
		}
	}
}
