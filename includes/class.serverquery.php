<?php
/*--------------------------------------------------------+
| SourcePunish WebApp                                     |
| Copyright (C) 2015 https://sourcepunish.net             |
+---------------------------------------------------------+
| This program is free software and is released under     |
| the terms of the GNU Affero General Public License      |
| version 3 as published by the Free Software Foundation. |
| You can redistribute it and/or modify it under the      |
| terms of this license, which is included with this      |
| software as agpl-3.0.txt or viewable at                 |
| http://www.gnu.org/licenses/agpl-3.0.html               |
+--------------------------------------------------------*/
if(!defined('IN_SP')) die('Access Denied!');

/* TODO
    - Test compressed data some how?
    - Test different types of mod
*/

class ServerQuery {
    private $Timeout = 3;
    private $Connection = null;
    private $DataRaw = '';
    private $DataPosition = 0;
    private $DataLength = 0;

    /* Ensure connection closes */
    public function __destruct() {
        PrintDebug('Called ServerQuery->__destruct', 2);
        $this->Disconnect();
    }
    /* Open a connection to a server */
    public function Connect($Address, $Port = 27015, $Timeout = null) {
        PrintDebug('Called ServerQuery->Connect with Address: "'.$Address.'" / Port: "'.$Port.'" / Timeout: "'.$Timeout.'"', 2);
        if($this->Connection !== null)
            $this->Disconnect();
        else
            $this->Reset(true);
        if($Timeout !== null)
            $this->Timeout = (int)$Timeout;
        else
            $Timeout = $this->Timeout;
        $this->Connection = @fsockopen('udp://'.$Address, $Port, $ErrorNum, $ErrorString, $this->Timeout);
        if($ErrorNum || $this->Connection === false) {
            $this->Disconnect();
            return false;
        }
        stream_set_timeout($this->Connection, $Timeout);
        stream_set_blocking($this->Connection, true);
        return true;
    }
    /* Close the connection to the server */
    public function Disconnect() {
        PrintDebug('Called ServerQuery->Disconnect', 2);
        if($this->Connection !== null) {
            @fclose($this->Connection);
            $this->Reset(true);
        }
    }
    /* Fetch basic information from the server */
    public function GetServerInfo() {
        PrintDebug('Called ServerQuery->GetServerInfo', 2);
        if($this->Connection === null)
            return false;
        $TryRequest = $this->DataRequest("\x54Source Engine Query\0");
        if($TryRequest === false || empty($this->DataRaw)) {
            $this->Reset();
            return false;
        }
        unset($TryRequest);
        $Type = $this->GetRaw();
        $ServerInfo = array();
        if($Type == "\x49") { // Source Servers
            $ServerInfo['protocol'] = $this->GetByte(); 
            $ServerInfo['hostname'] = $this->GetString();  
            $ServerInfo['map'] = $this->GetString();
            $ServerInfo['mod']['short'] = $this->GetString();
            $ServerInfo['mod']['name'] = $this->GetString();
            $ServerInfo['mod']['id'] = $this->GetShort();
            $ServerInfo['numplayers'] = $this->GetByte();
            $ServerInfo['maxplayers'] = $this->GetByte();
            $ServerInfo['bots'] = $this->GetByte();
            $ServerInfo['servertype'] = $this->GetRaw();
            $ServerInfo['os'] = $this->GetRaw();
            $ServerInfo['password'] = $this->GetByte();
            $ServerInfo['vac'] = $this->GetByte();
            if($ServerInfo['mod']['id'] === 2400) { // The Ship
                $ServerInfo['ship']['gamemode'] = $this->GetByte();
                $ServerInfo['ship']['witnesscount'] = $this->GetByte();
                $ServerInfo['ship']['wintesstime'] = $this->GetByte();
            }
            $ServerInfo['version'] = $this->GetString();
            $ExtraData = $this->GetByte();
            if($ExtraData & 0x80)
                $ServerInfo['port'] = $this->GetShort();
            if($ExtraData & 0x10) {
                $this->GetLong(); // SteamID (Long Long)
                $this->GetLong();
            }
            if($ExtraData & 0x40) {
                $ServerInfo['sourcetv']['port'] = $this->GetShort();
                $ServerInfo['sourcetv']['name'] = $this->GetString();
            }
            if($ExtraData & 0x20)
                $ServerInfo['keywords'] = $this->GetString();
            if($ExtraData & 0x01)
                $this->GetLong(); // GameID - Same as mod ID above
        } else if ($Type == "\x6D") { // GoldSource Servers
            // Should we bother supporting this?
        } else {
            // Error?
            return false;
        }
        $this->Reset();
        return $ServerInfo;
    }
    /* Get a player list from the server */
    public function GetPlayers() {
        PrintDebug('Called ServerQuery->GetPlayers', 2);
        if($this->Connection === null)
            return false;
        $Challenge = $this->GetChallenge("\x55");
        if($Challenge === false || empty($Challenge))
            return false;
        $TryRequest = $this->DataRequest("\x55".$Challenge);
        unset($Challenge);
        if($TryRequest === false || empty($this->DataRaw)) {
            $this->Reset();
            return false;
        }
        unset($TryRequest);
        $Type = $this->GetRaw();
        $Players = array();
        if($Type == "\x44") {
            $PlayerCount = $this->GetByte();
            for($i = 0; $i < $PlayerCount; $i++) {
                if(($this->DataLength - $this->DataPosition) < 1)
                    break;
                $Player = array(
                    'id'=>$this->GetByte(),
                    'name'=>$this->GetString(),
                    'score'=>$this->GetLong(),
                    'time'=>intval($this->GetFloat())
                );
                if(empty($Player['name']) && $Player['time'] == 0) // Connecting player
                    continue;
                else
                    $Players[$i] = $Player;
            }
        }
        $this->Reset();
        return $Players;
    }
    
    public function GetRules() {
        PrintDebug('Called ServerQuery->GetRules', 2);
        if($this->Connection === null)
            return false;
        $Challenge = $this->GetChallenge("\x56");
        if($Challenge === false)
            return false;
        $TryRequest = $this->DataRequest("\x56".$Challenge);
        if($TryRequest === false || empty($this->DataRaw)) {
            $this->Reset();
            return false;
        }
        $Type = $this->GetRaw();
        $Rules = array();
        if($Type == "\x45") {
            $Count = $this->GetShort();
            for($i = 0; $i < $Count; $i++) {
            	$Rule = $this->GetString();
                $Value = $this->GetString();
                if(!empty($Rule))
                    $Rules[$Rule] = $Value;
            }
        }
        $this->Reset();
        return $Rules;
    }
    
    private function GetChallenge($Code) {
        $this->DataRequest($Code."\xFF\xFF\xFF\xFF");
        $Response = $this->GetRaw();
        if($Response == "\x41") {
            return $this->GetRaw(4);
        }
        return false;
    }
    private function Reset($Connection = false) {
        PrintDebug('Called ServerQuery->Reset', 2);
        if($Connection) {
            $this->Timeout = 3;
            $this->Connection = null;
        }
        $this->DataRaw = '';
        $this->DataPosition = 0;
        $this->DataLength = 0;
    }
    private function Set($Data) {
        $this->Reset();
        $this->DataRaw = $Data;
        $this->DataLength = strlen($Data);
    }
    private function DataRequest($Request) {
        fwrite($this->Connection, "\xFF\xFF\xFF\xFF".$Request);
        if($this->ReadData())
            return true;
        return false;
    }
    private function ReadData() {
        $Data = fread($this->Connection, 1400);
        if(empty($Data))
            return false;
        $this->Set($Data);
        unset($Data);
        $Type = $this->GetLong();
        if($Type == -1) {
            /* Single packet, we don't need to do anything else */
            return true;
        } else if($Type == -2) {
            /* Split packet */
            $Packets = array();
            $Compressed = false;
            $ReadMore = true;
            while($ReadMore) {
                $RequestID = $this->GetLong();
                if(($RequestID & 0x80000000) === 1)
                    $Compressed = true;
                $PacketCount = $this->GetByte();
                $PacketNumber = $this->GetByte();
                $this->GetShort(); // Packet size
                if($PacketNumber == 0 && $Compressed) {
                    $this->GetLong();
                    $PacketChecksum = $this->GetLong();
                }
                $Packets[$PacketNumber] = $this->GetRaw(0);
                /* Is this the last packet? */
                if(count($Packets) >= $PacketCount)
                    break;
                /* Get next packet */
                $Data = fread($this->Connection, 1400);
                if(strlen($Data) < 4)
                    break;
                $this->Set($Data);
                if($this->GetLong() !== -2)
                    break;
            }
            $Buffer = implode($Packets);
            unset($Packets);
            if($Compressed) { // TEST!
                if(!function_exists('bzdecompress'))
                    return false;
                $Buffer = bzdecompress($Buffer);
                if(crc32($Buffer) !== $PacketChecksum)
                    return false;
            }
            $this->Set(substr($Buffer, 4));
            return true;
        } else {
            // Error ?
            return false;
        }
    }
    private function GetRaw($Length = 1) {
        if($Length < 0 || $Length > ($this->DataLength - $this->DataPosition))
            return '';
        if($Length == 0) {
            $Length = $this->DataLength - $this->DataPosition;
        }
        $Data = substr($this->DataRaw, $this->DataPosition, $Length);
        $this->DataPosition += $Length;
        return $Data;
    }
    private function GetByte() {
        return ord($this->GetRaw());
    }
    private function GetShort() {
        $Data = $this->GetRaw(2);
        if(empty($Data))
            return '';
        $Data = @unpack('v', $Data);
        return $Data[1];
    }
    private function GetLong() {
        $Data = $this->GetRaw(4);
        if(empty($Data))
            return '';
        $Data = @unpack('l', $Data);
        return $Data[1];
    }
    private function GetUnsignedLong() {
        $Data = $this->GetRaw(4);
        if(empty($Data))
            return '';
        $Data = @unpack('V', $Data);
        return $Data[1];
    }
    private function GetFloat() {
        $Data = $this->GetRaw(4);
        if(empty($Data))
            return '';
        $Data = @unpack('f', $Data);
        return $Data[1];
    }
    private function GetString() {
        if($this->DataPosition > $this->DataLength)
            return '';
        $Position = strpos($this->DataRaw, "\0", $this->DataPosition);
        if($Position === false)
            return '';
        if(($Position - $this->DataPosition) <= 0) // Empty string
            $String = '';
        else
            $String = $this->GetRaw($Position - $this->DataPosition);
        $this->DataPosition++;
        return $String;
    }
}
?>