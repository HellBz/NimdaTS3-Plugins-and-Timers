<?php

namespace Plugin;

use App\Plugin;
use Carbon\Carbon;
use TeamSpeak3\Ts3Exception;

class ServerJoinGroup extends Plugin implements PluginContract
{
    public function isTriggered()
    {
        try {
            $client = $this->teamSpeak3Bot->node->clientGetById($this->info['clid']);
        } catch(Ts3Exception $e) {
            return;
        }
		
		$cl_sgs = explode(",", $client['client_servergroups']);

		if(  !in_array('589',$cl_sgs)  ){

			$this->teamSpeak3Bot->node->execute("servergroupaddclient", array("sgid" => 589, "cldbid" => $client["client_database_id"] ));

		}
    }
}
