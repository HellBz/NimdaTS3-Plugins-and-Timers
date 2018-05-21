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

class TwitchStatus extends Timer implements TimerContract
{
    public function isTriggered()
    {
        //Triggered 60 Secounds		
		
		foreach($this->CONFIG['statuschannelid'] as $cid => $ch ){
			
			$channelinfo = $this->teamSpeak3Bot->node->channelGetById($cid);

			$jdc = json_decode($this->file_get_contents_curl('https://api.twitch.tv/kraken/streams/'.$ch['twitch_user'].'?client_id='.$this->CONFIG['client_id']));
			if($jdc->stream == null){
				$jdc2 = json_decode($this->file_get_contents_curl('https://api.twitch.tv/kraken/users/'.$ch['twitch_user'].'?client_id='.$this->CONFIG['client_id']));
				
				$replace = array(
							'{user}' 	=> $ch['twitch_user'],
							'{logo}' 	=> $jdc2->logo
							);
				$ch_name = $this->str_replace_assoc( $replace , $ch['ch_name_text_off'] );	
				$ch_name = $this->cut( $ch_name );
				$ch_desc = $this->str_replace_assoc( $replace , $ch['ch_desc_text_off'] );
				
				if( $ch['ch_name'] == "true" && $channelinfo->channel_name != $ch_name ){
					try{ $channelinfo->channel_name = $ch_name;  } catch(Ts3Exception $e) { echo $e->getMessage(); }
				}
				
				if( $ch['ch_desc'] == "true" && $channelinfo->channel_description != $ch_desc ){
					try{ $channelinfo->channel_description= $ch_desc;  } catch(Ts3Exception $e) { echo $e->getMessage(); }
				}

			}else{
				
				$replace = array(
							'{user}' 	=> $ch['twitch_user'],
							'{logo}' 	=> $jdc->stream->channel->logo,
							'{url}' 	=> $jdc->stream->channel->url,
							'{game}' 	=> $jdc->stream->game,
							'{title}' 	=> $jdc->stream->channel->status,
							'{viewer}' 	=> $jdc->stream->viewers
							);
							
				$ch_name = $this->str_replace_assoc( $replace , $ch['ch_name_text_on'] );	
				$ch_name = $this->cut( $ch_name );
				$ch_desc = $this->str_replace_assoc( $replace , $ch['ch_desc_text_on'] );
				
				$namechanged = false;
				if( $ch['ch_name'] == "true" && $channelinfo->channel_name != $ch_name ){
					try{ $channelinfo->channel_name = $ch_name;  } catch(Ts3Exception $e) { echo $e->getMessage(); }
					$namechanged = true;
				}
				
				$descchanged = false;
				if( $ch['ch_desc'] == "true" && $channelinfo->channel_description != $ch_desc ){
					try{ $channelinfo->channel_description = $ch_desc;  } catch(Ts3Exception $e) { echo $e->getMessage(); }
					$descchanged = true;
				}
				
				if ( ( $namechanged & $descchanged ) && $ch['server_ad'] == "true" ){
					
					//Server Advert
					$ad_text = $this->str_replace_assoc( $replace , $ch['server_ad_text'] );
					$this->teamSpeak3Bot->node->message($ad_text);
				}				
			}
			unset($channelinfo);
		}		
    }
	
	public function file_get_contents_curl($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);       
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	
	public function str_replace_assoc(array $replace, $subject) {
	   return str_replace(array_keys($replace), array_values($replace), $subject);   
	} 
	
	public function cut($str)
	{
		if ( strlen($str) > 48 )
			return substr($str,0,45)."...";
		else
			return $str;
	}

}
