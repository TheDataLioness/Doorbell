<?php


namespace DataLion\DoorBell\Utils;


use DataLion\DoorBell\Main;
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


            $worldManager = Server::getInstance()->getWorldManager();
            if ($worldManager->isWorldGenerated($result["level"])) {
                if ($worldManager->isWorldLoaded($result["level"])) {
                    $level = $worldManager->getWorldByName($result["level"]);
                } else {
                    if ($worldManager->loadWorld($result["level"])) {
                        $level = $worldManager->getWorldByName($result["level"]);
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