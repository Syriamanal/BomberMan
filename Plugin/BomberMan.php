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
