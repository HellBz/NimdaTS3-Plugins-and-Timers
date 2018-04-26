<?php
/**
 * Created by Notepad++
 * User: HellBz
 */

namespace Plugin;

use App\Plugin;
use TeamSpeak3\Ts3Exception;

class ChannelJoinAssignGroup extends Plugin implements PluginContract
{
    private $channel;
	
    public function isTriggered()
    {

		if(!array_key_exists($this->info['ctid'], $this->CONFIG['channels'])) {
            return;
        }

		$cl = $this->teamSpeak3Bot->node->clientGetById($this->info['clid']);
		
		$cl_sgs = explode(",", $client['client_servergroups']);

		
		$cha = $this->CONFIG['channels'][$this->info['ctid']];
		
		
		$i = 0;
		while($i < count($cha))
		{
		
			if ( !in_array($cha[$i]['group'] ,$cl_sgs) ) {
				try {
					$this->teamSpeak3Bot->node->execute("servergroupaddclient", array("sgid" => $cha[$i]['group'], "cldbid" => $cl["client_database_id"] ));
					//if($rule[2]) $cl->message($rule[2]);
					//$this->sendOutput($i.'. -> '.json_encode($cha[$i]));
					$this->sendOutput($cha[$i]['msg']);
				} catch(Ts3Exception $e) {
					return;
				}
			}
			$i++;
		}
		//if($rule[3]) $cl->kick(4);

    }
}