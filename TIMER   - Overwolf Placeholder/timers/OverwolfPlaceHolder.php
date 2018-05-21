<?php
/**
 * Created by PhpStorm.
 * User: Jake
 * Date: 14/02/2017
 * Time: 15:24
 */

namespace Timer;

use App\Timer;
use TeamSpeak3\Ts3Exception;

class OverwolfPlaceHolder extends Timer implements TimerContract
{
    public function isTriggered()
    {
        //Triggered 5 Secounds		
		foreach ($this->teamSpeak3Bot->node->clientList(array("client_type" => "0")) as $client) {
			
			
		$cl_sgs = explode(",", $client['client_servergroups']);
		
		$badges = (string)$client['client_badges'] ;
		
		preg_match("/(overwolf=([0-1]))/", $badges, $overwolf);

		if( ( count($cl_sgs) >= 2  ) && ( !in_array('529',$cl_sgs) || !in_array('360',$cl_sgs) ) ){

			if ( $overwolf['2'] == 1  || (string)$client['client_icon_id'] != 0 ) {
				if ( in_array('527',$cl_sgs) ) $this->teamSpeak3Bot->node->execute("servergroupdelclient", array("sgid" => 527, "cldbid" => $client["client_database_id"] ));
				//echo 'Got Icon Or Overwolf';		
			}else{
				if ( !in_array('527',$cl_sgs) ) $this->teamSpeak3Bot->node->execute("servergroupaddclient", array("sgid" => 527, "cldbid" => $client["client_database_id"] ));
				//echo 'No Overwolf or ICON';		
			}
		}
			
			
			
		}
		
		
    }
}