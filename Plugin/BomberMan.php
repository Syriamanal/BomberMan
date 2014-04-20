<?php

/*
__PocketMine Plugin__
name=BomberMan
description=Make a path using TNT and kill the others!
version=0.5.0
author=Comedyman937
class=BomberMan
apiversion=11,12
*/

/*
===============
   Changelog
===============

0.5.0
- Initial Release

===============
 End Changelog
===============
*/

class BomberMan implements Plugin
{
    private $api;

   //---------Begin Game Data---------\\
    private $playersOnline = 0;
    private $BomberManActive = false;
   //----------End Game Data----------\\

    public function __construct(ServerAPI $api, $server = false)
    {
        $this->api = $api;
    }

    public function init()
    {

        if(!(file_exists($this->api->plugin->configPath($this)."config.yml"))){
            $this->config = new Config($this->api->plugin->configPath($this)."config.yml", CONFIG_YAML, array(
                "MinutesBeforeGame" => 10,
                "MinutesPerGame" => 30,
                "MaxPlayers" => 8,
                "BomberWallBlockID" => 18,
                "ExplosionSize" => 2,
                "BomberManLevelName" => 'BomberMan',
            ));
        }

        $this->CONFIG = $this->api->plugin->readYAML($this->api->plugin->configPath($this) . "config.yml");

        $this->api->addHandler("player.spawn", array($this, "EventHandler"));
        $this->api->addHandler("player.connect", array($this, "EventHandler"));
        $this->api->addHandler("entity.health.change", array($this, "EventHandler"));
        $this->api->addHandler("player.pickup", array($this, "EventHandler"));
        $this->api->addHandler("player.block.break", array($this, "EventHandler"));
        $this->api->addHandler("player.block.place", array($this, "EventHandler"));
        $this->api->addHandler("tnt.block.explode", array($this, "EventHandler"));

            console("[INFO] BomberMan Loaded!");

            $this->api->schedule($this->CONFIG["MinutesBeforeGame"]*20*60, array($this, "GameStarter"), true);
    }

    public function PlayerJoinBomberMan($data, $event){
        switch($event){
            case "player.block.touch":

                if(!($this->api->tile->get(new Position($data["target"]->x, $data["target"]->y, $data["target"]->z, $data["target"]->level)) instanceof Tile)){
                    return;
                }

                $t = $this->api->tile->get(new Position($data["target"]->x, $data["target"]->y, $data["target"]->z, $data["target"]->level));

                if(!($t instanceof Tile)){
                    return;
                }

                    if($t->class != TILE_SIGN){
                        return;
                    }

                        if($BomberManActive == false){
                            $getGameStats = "Waiting...";
                                }elseif($BomberManActive == true){
                                    $getGameStats = "In Progress";
                                        }else{
                                            $getGameStats = "-=ERROR=-";
                                        }

                        if($playersOnline < $this->CONFIG["MaxPlayers"]){
                            $Queued = $playersOnline;
                                }elseif($playersOnline = $this->CONFIG["MaxPlayers"]){
                                    $Queued = "GAME FULL";
                                        }else{
                                            $Queued = "-=ERROR=-";
                                        }

                        if(strtolower($t->data["Text1"]) == "[BomberMan]"){
                            $t->data["Text2"] == $this->CONFIG["BomberManLevel"];
                            $t->data["Text3"] == $Queued . "/" . $this->CONFIG["MaxPlayers"];
                            $t->data["Text4"] == $getGameStats;
                        }
                        $pos = $this->api->level->getLevel($this->CONFIG["BomberManLevel"]);

                            if($playersOnline == $this->CONFG["MaxPlayers"]){
                                return "[BomberMan] Unable To Join!\nThe game is currently full!";
                                return false;
                                }else{
                                    //tp player to the game
                                }

                            if($BomberManActive == true){
                                return "[BomberMan] Unable To Join Game!\nThe game is currently in session!";
                                return false;
                                    }else{
                                        //tp player to game
                                    }
               break;
          }
     }


    public function updateBomberManSignText($tile, $target = false){
        if(!($tile instanceof Tile)){
            return;
        }
            if($tile->class != TILE_SIGN){
                return;
            }
                    if($BomberManActive == false){
                        $getGameStats = "Waiting...";
                            }elseif($BomberManActive == true){
                                $getGameStats = "In Progress";
                                    }else{
                                        $getGameStats = "-=ERROR=-";
                                    }

                        if($playersOnline < $this->CONFIG["MaxPlayers"]){
                            $Queued = $playersOnline;
                                }elseif($playersOnline = $this->CONFIG["MaxPlayers"]){
                                    $Queued = "GAME FULL";
                                        }else{
                                            $Queued = "-=ERROR=-";
                                        }

                    $nbt = new NBT();
                    $nbt->write(chr(NBT::TAG_COMPOUND)."\x00\x00");
                
                    $nbt->write(chr(NBT::TAG_STRING));
                    $nbt->writeTAG_String("Text1");
                    $nbt->writeTAG_String("[BomberMan]);
                
                    $nbt->write(chr(NBT::TAG_STRING));
                    $nbt->writeTAG_String("Text2");
                    $nbt->writeTAG_String($this->CONFIG["BomberManLevel"]);
                        
                    $nbt->write(chr(NBT::TAG_STRING));
                    $nbt->writeTAG_String("Text3");
                    $nbt->writeTAG_String($Queued . "/" . $this->CONFIG["MaxPlayers"]);
                
                    $nbt->write(chr(NBT::TAG_STRING));
                    $nbt->writeTAG_String("Text4");
                    $nbt->writeTAG_String($getGameStats);

                    $nbt->write(chr(NBT::TAG_STRING));
                    $nbt->writeTAG_String("id");
                    $nbt->writeTAG_String($tile->class);
                    $nbt->write(chr(NBT::TAG_INT));
                    $nbt->writeTAG_String("x");
                    $nbt->writeTAG_Int((int) $tile->x);
        
                    $nbt->write(chr(NBT::TAG_INT));
                    $nbt->writeTAG_String("y");
                    $nbt->writeTAG_Int((int) $tile->y);
                                
                    $nbt->write(chr(NBT::TAG_INT));
                    $nbt->writeTAG_String("z");
                    $nbt->writeTAG_Int((int) $tile->z);
                                
                    $nbt->write(chr(NBT::TAG_END)); 

            $pk = new EntityDataPacket();
            $pk->x = $tile->x;
            $pk->y = $tile->y;
            $pk->z = $tile->z;
            $pk->namedtag = $nbt->binary;

                if($target instanceof Player){
                    $target->dataPacket($pk);
                        }else{
                            $players = $this->api->player->getAll($tile->level);
                                foreach($players as $pIndex => $player){
                                    if($player->spawned == false){
                                        unset($players[$pIndex]);
                                    }
                        }
                                            $this->api->player->broadcastPacket($players, $pk);
        }
    }

    public function EventHandler($data, $event)
    {
        switch($event)
        {
            case "player.spawn":
                if($this->BomberManActive == false){
                    $this->playersOnline = $this->playersOnline + 1;
                        }else{
                            $data->close("There is currently a game in session!");
                        }
                                break;

            case "player.connect":
                if($playersOnline == $this->CONFIG["MaxPlayers"]){
                    return false;
                }
                        break;

            case "entity.health.change":
                return false;
                    break;

            case "player.pickup":
                return false;
                    break;

            case "player.block.break":
                if($this->api->ban->isOP($data) == true){
                    return;
                        }else{
                            return false;
                        }
                                break;

            case "player.block.place":
                if($this->api->ban->isOP($data['player']) == true){
                    return;
                        }elseif(!($this->api->block->getItem(46))){
                            return "[BomberMan] Only TNT Blocks Are Permitted on a BomberMan Server!";
                            return false;
                                }elseif($BomberManActive == true){
                                    $BomberManExplosion = new Explosion(new Position($data["block"]->x, $data["block"]->y, $data["block"]->z, $data["block"]->level), $this->CONFIG["ExplosionSize"]);
                                    $BomberManExplosion->explode();

                                    $this->api->console->run("give " . $data . " 46 1");
                                        }else{
                                            return "[BomberMan] You must wait for the game to start!";
                                            return false;
                                        }
                                            break;

            case "tnt.block.explode":
                if($data["block"] == $this->CONFIG["BomberWallBlock"]){
                    console ("[BomberMan] " . $data["player"] . " Has destroyed a wall!");
                        }else{
                            return false;
                        }
                                break;
        }
    }

    public function GameStarter()
    {
        if($this->BomberManActive == false){
            $this->api->chat->broadcast("[BomberMan] Starting Game...");
            $this->BomberManActive = true;
        }
                foreach($this->api->player->getAll() as $p){
                    $this->api->console->run("give " . $p . " 46 6");
                }
                       $this->api->chat->broadcast("[BomberMan] Gathering Level Name...");
                       $BomberManLevelName = $this->CONFIG["BomberManLevelName"];
                       $this->api->chat->broadcast("[BomberMan] Loading Level " . $BomberManLevelName . "...");
                       $this->api->level->loadLevel($this->CONFIG["BomberManLevelName"]);

                       $this->api->chat->broadcast("[BomberMan] Teleporting Players...");
                       foreach($this->api->player->getAll() as $p){
                           $this->api->console->run("tp " . $p . " w:" . $this->CONFIG["BomberManLevelName"]);
                       }

                       $this->api->chat->broadcast("[BomberMan] Starting Clock...");
                       $this->api->schedule($this->CONFIG["MinutesPerGame"]*20*60, array($this, "GameEnder"), true);
                       $this->api->chat->broadcast("[BomberMan] This Game Will Last " . $this->CONFIG["MinutesPerGame"] . " Minutes!");

                       $this->api->chat->broadcast("[BomberMan] The Game Has Started!");

    }

    public function GameEnder()
    {
        if($this->BomberManActive == true){
            $this->BomberManActive == false;

            $this->api->chat->broadcast("[BomberMan] The game is over!  Stopping server for cleanup...");

            foreach($this->api->player->getAll() as $p){
                    $this->api->console->run("kill " . $p);
                }

            $this->api->console->run("stop");
        }
    }

    public function __destruct()
    {
        console("[INFO] BomberMan Unloaded!");
    }
}

?>
