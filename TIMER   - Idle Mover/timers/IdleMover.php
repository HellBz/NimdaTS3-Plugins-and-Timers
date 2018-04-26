<?php
/**
 * Created by Notepad++
 * User: HellBz
 */

namespace Timer;

use App\Timer;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Timer\Models\IdleMove;
use TeamSpeak3\Ts3Exception;

class IdleMover extends Timer implements AdvancedTimerContract
{
    public function isTriggered()
    {	
	
	$maxidle  = $this->CONFIG['maxidle'];
			
	$this->teamSpeak3Bot->node->clientListReset();
	
	$movetimes = $this->CONFIG['idle_movetimes'];
	ksort($movetimes);	
	while (list($time, $opt) = each($movetimes)) {
		$small_idle = $time;
		break; // break loop after first iteration
	}
	krsort($movetimes);	

	foreach ($this->teamSpeak3Bot->node->clientList(array("client_type" => "0")) as $client) {
		
		$clientidle = ($client['client_idle_time']/1000);
		$clienservergroups = explode(",", $client['client_servergroups']);
		//$clientInfo = $this->teamSpeak3Bot->node->clientInfoDb($this->teamSpeak3Bot->node->clientFindDb($client['client_nickname']));

		//Nomove User
		if( in_array((string)$client['client_unique_identifier'], $this->CONFIG['nomove_user']) ||
			count(array_intersect($clienservergroups, $this->CONFIG['nomove_group'])) > 0 ||
            in_array($client['cid'], $this->CONFIG['nomove_channel']) 
			){
				unset($clienservergroups);				
			}else{
				//echo '---'.$client['client_nickname'];	
					
				if (  ( ($this->CONFIG['deactiv_away_move'] == "true" && $client['client_away'] ) ||    /* Move Away */
						($this->CONFIG['deactiv_in_move']   == "true"   && $client['client_input_muted'] ) ||  /* Mic muted */
						($this->CONFIG['deactiv_out_move']  == "true"  && $client['client_output_muted'] )    /* Move Away */  
					   ) && $clientidle >= $this->CONFIG['deactiv_move_delay'] && $clientidle <= $small_idle
				){
					if ( $client['cid'] != $this->CONFIG['deactiv_move_channel']  ){
						try {  $this->teamSpeak3Bot->node->clientMove( $client['clid'], $this->CONFIG['deactiv_move_channel'] );  } catch(Ts3Exception $e) {  }
						try {  $this->teamSpeak3Bot->node->clientGetByDbid ( $client['client_database_id'] )->message($this->CONFIG['deactiv_move_msg']);  } catch(Ts3Exception $e) {  }
         
						if( $this->CONFIG['moveback']  == "true" ){
							
							$term = (string)$client['client_unique_identifier'];
							$user = IdleMove::select('id','unique','channel')
								->where('unique', 'LIKE', "%{$term}%")
								->pluck('channel','id')
								->toArray();

							if($user) {
								//Found Active User and Move them Instantly Back
								while (list($id, $channel) = each($user)) {
									$deluser = IdleMove::findOrFail($id);
									$deluser->delete();
								}
							}
								
							$quote = IdleMove::create([
									'unique' => (string)$client['client_unique_identifier'],
									'afk' =>  true,
									'moved' =>  true,
									'msg' =>  true,
									'channel' => (string)$client['cid'],
								]);
								
							}
						
					}
					
				}elseif( $this->CONFIG['idlemove'] == "true" && $clientidle >= $small_idle ){
						
						while (list($time, $opt) = each($movetimes)) {
							if( $clientidle >= $time ){
								break;
							}
						}
						
						if ( $clientidle >= $time && $client['cid'] != $opt['channel']  ){
							try {  $this->teamSpeak3Bot->node->clientMove( $client['clid'], $opt['channel'] );  } catch(Ts3Exception $e) {  }
							try {  $this->teamSpeak3Bot->node->clientGetByDbid ( $client['client_database_id'] )->message($opt['msg']);  } catch(Ts3Exception $e) {  }
							
							if( $this->CONFIG['moveback'] == "true" ){
								
							$term = (string)$client['client_unique_identifier'];
							$user = IdleMove::select('id','unique','channel')
								->where('unique', 'LIKE', "%{$term}%")
								->pluck('channel','id')
								->toArray();

							if($user) {
								//Found Active User and Move them Instantly Back
								while (list($id, $channel) = each($user)) {
									$deluser = IdleMove::findOrFail($id);
									$deluser->delete();
								}
							}
								
							$quote = IdleMove::create([
									'unique' => (string)$client['client_unique_identifier'],
									'afk' =>  true,
									'moved' =>  true,
									'msg' =>  true,
									'channel' => (string)$client['cid'],
								]);
								
							}
						}	
						
				}else{
					
					if( $clientidle < $small_idle && $this->CONFIG['moveback'] == "true" ){
						
						$term = (string)$client['client_unique_identifier'];
						$user = IdleMove::select('id','unique','channel')
							->where('unique', 'LIKE', "%{$term}%")
							->pluck('channel','id')
							->toArray();

						if($user) {
							//Get Channel List								
							$this->teamSpeak3Bot->node->channelListReset();
							$validch = array();
							foreach ($this->teamSpeak3Bot->node->channelList() as $chan) {
								array_push($validch, $chan['cid']);
							}
							//Found Active User and Move them Instantly Back
							$cnt = count($user);
							while (list($id, $channel) = each($user)) {
								
								if( $cnt != count($user) ){
									$deluser = IdleMove::findOrFail($id);
									$deluser->delete();
								}else{
										
									if(in_array($channel,$validch) ) {
										
										try {  $ch_info = $this->teamSpeak3Bot->node->channelGetById( $channel ); } catch(Ts3Exception $e) {  }
										$movemsg = $this->CONFIG['moveback_msg'];
										$moveback = true;
										
										if( $ch_info['channel_flag_password'] && $this->CONFIG['moveback_channel_password'] == "false" ){
											$movemsg = $this->CONFIG['moveback_channel_password_msg'];
											$moveback = false;
										}elseif( ($ch_info['total_clients']  >= $ch_info['channel_maxclients'] &&  $ch_info['channel_maxclients'] != '-1' ) && $this->CONFIG['moveback_channel_full']  == "false" ){
											$movemsg = $this->CONFIG['moveback_channel_full_msg'];
											$moveback = false;
										}elseif(  in_array($channel, $this->CONFIG['moveback_channel_block']) ){
											$movemsg = $this->CONFIG['moveback_channel_block_msg'];
											$moveback = false;
										}
										
									}else{
										$movemsg = $this->CONFIG['moveback_channel_exists'];
										$moveback = false;
									}	
										$format = [ "%CH_NAME%"  => $ch_info["channel_name"],];
										$movemsg = strtr( $movemsg, $format );
									
										if ( $moveback ){
											
											try {  $this->teamSpeak3Bot->node->clientGetByDbid ( $client['client_database_id'] )->message($movemsg);  } catch(Ts3Exception $e) {  }
										
											try {  $this->teamSpeak3Bot->node->clientMove( $client['clid'], $channel );  } catch(Ts3Exception $e) {  }
											
										}else{
											
											try {  $this->teamSpeak3Bot->node->clientGetByDbid ( $client['client_database_id'] )->message($movemsg);  } catch(Ts3Exception $e) {  }
										
										}

								$deluser = IdleMove::findOrFail($id);
								$deluser->delete();
								}
								$cnt--;
								
							}

						}else{
							
							//No User Found in Database
							//Be Cool, do Nothing
							
						}

					}
						
				}

			}
		
		//Unset current Client data for next Client */
		unset($client);	
		}
	/* Unset current Client List */
	unset($e);	
    }
	
	public function install()
    {
        Manager::schema()->create($this->CONFIG['table'], function(Blueprint $table) {
            $table->increments('id');
            $table->text('unique');
            $table->text('afk');
			$table->text('moved');
			$table->text('msg');
			$table->text('channel');
            $table->timestamps();
			});
    }
}