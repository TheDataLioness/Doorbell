<?php


namespace DataLion\DoorBell\Utils\tasks;


use DataLion\DoorBell\Main;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class DoorbellCreateTask extends Task
{
	private bool $cancel = false;

    public function __construct(private string $playername)
    {}


    /**
     * @param bool $cancel
     */
    public function setCanceled(bool $cancel = true): void
    {
        $this->cancel = $cancel;
    }

    /**
     * @return bool
     */
    public function isCanceled(): bool
    {
        return $this->cancel;
    }


    public function onRun(): void
    {
        if($this->isCanceled()) return;
        if(!isset(Main::$doorbellPlaceSession[$this->playername])) return;
        unset(Main::$doorbellPlaceSession[$this->playername]);


        $player = Server::getInstance()->getPlayerExact($this->playername);


        if(!is_null($player)) $player->sendMessage(TextFormat::GREEN."[Doorbell] Creation time ended.");
    }
}