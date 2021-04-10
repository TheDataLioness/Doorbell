<?php

declare(strict_types=1);

namespace DataLion\DoorBell;

use DataLion\DoorBell\Utils\Doorbell;
use DataLion\DoorBell\Utils\Setup;
use DataLion\DoorBell\Utils\tasks\DoorbellCreateTask;
use pocketmine\block\BlockLegacyIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat as C;

class Main extends PluginBase implements Listener {

    private static $db;
    private static $instance;

    /** @var Task[] */
    public static $doorbellPlaceSession;

    /**
     * @return \SQLite3
     */
    public static function getDb(): \SQLite3
    {
        return self::$db;
    }

    /**
     * @return Main
     */
    public static function getInstance(): Main
    {
        return self::$instance;
    }

    public function onEnable(): void
    {
        self::$instance = $this;
        self::$db = new \SQLite3($this->getDataFolder()."doorbell.db");

        Setup::setupTable();
        Setup::loadBells();


        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		switch($command->getName()){
			case "createdoorbell":
                if($sender instanceof Player){

                    $sender->sendMessage(C::GREEN."[DoorBell] Click on a button within 10 seconds to create a doorbell.");
                    $task = new DoorbellCreateTask($sender->getName());
                    $this->getScheduler()->scheduleDelayedTask($task, 200);
                    self::$doorbellPlaceSession[$sender->getName()] = $task;
                    return true;
                }
                $sender->sendMessage(C::RED."[DoorBell] You can only use this command in-game.");

				return true;
			default:
				return false;
		}
	}


	public function onCreateClick(PlayerInteractEvent $e){
        $blockid = $e->getBlock()->getId();

        //CHECK IF CLICKED BLOCK IS A BUTTON
        if($blockid !== BlockLegacyIds::STONE_BUTTON && $blockid !== BlockLegacyIds::WOODEN_BUTTON) return;

        //CHECK IF SESSION EXISTS
        if(!isset(self::$doorbellPlaceSession[$e->getPlayer()->getName()])) return;

        //CANCEL TASK
        $e->cancel();

        //CHECK IF DOORBELL ALREADY EXISTS THERE
        $doorbell = Doorbell::getByPosition($e->getBlock()->getPos());

        if(!is_null($doorbell)){
            $e->getPlayer()->sendMessage(C::RED."[Doorbell] There is already a doorbell here.");
            return;
        }

        //UNSET SESSION
        self::$doorbellPlaceSession[$e->getPlayer()->getName()]->getHandler()->cancel();
        unset(self::$doorbellPlaceSession[$e->getPlayer()->getName()]);

        //CREATE NEW INSTANCE OF DOORBELL
        Doorbell::createDoorbell($e->getBlock()->getPos());
        $e->getPlayer()->sendMessage(C::GREEN."[Doorbell] Doorbell created");
    }

    public function onBlockBreak(BlockBreakEvent $e){
        if($e->isCancelled()) return;

        $doorbell = Doorbell::getByPosition($e->getBlock()->getPos());
        if(is_null($doorbell)) return;
        $doorbell->delete();
        $e->getPlayer()->sendMessage(C::GREEN."[Doorbell] Doorbell deleted");
    }

    public function onDoorbellClick(PlayerInteractEvent $e){
        if($e->isCancelled()) return;
        $doorbell = Doorbell::getByPosition($e->getBlock()->getPos());
        if(!is_null($doorbell)) $doorbell->activate();
    }
}
