<?php

namespace Plugin;

use App\Plugin;
use Carbon\Carbon;
use TeamSpeak3\Ts3Exception;

class ServerJoinPlaceholder extends Plugin implements PluginContract
{
    public function isTriggered()
    {
        try {
            $client = $this->teamSpeak3Bot->node->clientGetById($this->info['clid']);
        } catch(Ts3Exception $e) {
            return;
        }
		
		$cl_sgs = explode(",", $client['client_servergroups']);

		if( ( count($cl_sgs) >= 2  ) && ( !in_array('529',$cl_sgs) || !in_array('360',$cl_sgs) ) ){

			if ( substr_count($data['client_badges'], "overwolf=1") == 1 || (string)$client['client_icon_id'] != 0 ) {
				if ( in_array('527',$cl_sgs) ) $this->teamSpeak3Bot->node->execute("servergroupdelclient", array("sgid" => 527, "cldbid" => $client["client_database_id"] ));
				//echo 'Got Icon Or Overwolf';		
			}else{
				if ( !in_array('527',$cl_sgs) ) $this->teamSpeak3Bot->node->execute("servergroupaddclient", array("sgid" => 527, "cldbid" => $client["client_database_id"] ));
				//echo 'No Overwolf or ICON';		
			}
		}
    }
}
