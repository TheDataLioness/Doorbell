<?php


namespace DataLion\DoorBell\Utils;


use DataLion\DoorBell\Main;
use pocketmine\level\Level;
use pocketmine\Server;

class Setup
{
    public static function setupTable(){
        Main::getDb()->query("CREATE TABLE IF NOT EXISTS doorbells(id INTEGER PRIMARY KEY AUTOINCREMENT , location TEXT NOT NULL)");
    }

    public static function loadBells(){
        $res = Main::getDb()->query("SELECT * FROM doorbells");
        while ($row = $res->fetchArray()){

            $result  = json_decode($row["location"], true);
            $x = $result["x"];
            $y = $result["y"];
            $z = $result["z"];
            $level = null;
            if (Server::getInstance()->isLevelGenerated($result["level"])) {
                if (Server::getInstance()->isLevelLoaded($result["level"])) {
                    $level = Server::getInstance()->getLevelByName($result["level"]);
                } else {
                    if (Server::getInstance()->loadLevel($result["plot_world"])) {
                        $level = Server::getInstance()->getLevelByName($result["level"]);
                    }
                }
            }
            if(is_null($level)){
                Main::getInstance()->getLogger()->error("Could not load level: ".$result["level"]);
                continue;
            }
            new Doorbell($x, $y, $z, $level);

        }
    }
}