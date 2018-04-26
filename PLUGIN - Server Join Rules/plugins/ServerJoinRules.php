<?php

namespace Plugin;

use App\Plugin;
use Carbon\Carbon;
use TeamSpeak3\Ts3Exception;

class ServerJoinRules extends Plugin implements PluginContract
{
    public function isTriggered()
    {
        try {
            $client = $this->teamSpeak3Bot->node->clientGetById($this->info['clid']);
        } catch(Ts3Exception $e) {
            return;
        }
		
		$cl_sgs = explode(",", $client['client_servergroups']);
		try
		{
			if( !in_array('589',$cl_sgs) /*&& ( count($cl_sgs) <= 1  )*/ ){
				//$this->teamSpeak3Bot->node->clientMove($this->info['clid'], 13336 );
				$this->teamSpeak3Bot->node->execute("clientpoke", array("clid" => $client["clid"], "msg" => "PLS Accept OUR ServerRules." ));
				$this->teamSpeak3Bot->node->execute("clientpoke", array("clid" => $client["clid"], "msg" => "Read Our Rules, find the Secret Password.." ));
				$this->teamSpeak3Bot->node->execute("clientpoke", array("clid" => $client["clid"], "msg" => "Go to the Channel '╠  A C C E P T    R U L E S' .."));
				$this->teamSpeak3Bot->node->execute("clientpoke", array("clid" => $client["clid"], "msg" => "NOW, feel free to Speak on Our Teamspeak.."));
				$this->teamSpeak3Bot->node->execute("clientpoke", array("clid" => $client["clid"], "msg" => "PLS all time, HAVE FUN ;)"));
			}
		}
		catch (Exception $e)
		{
			return;
		}
	}
}
