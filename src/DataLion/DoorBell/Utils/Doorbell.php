<?php


namespace DataLion\DoorBell\Utils;


use DataLion\DoorBell\Main;
use DataLion\DoorBell\Utils\tasks\NoteBlockSoundTask;
use pocketmine\world\Position;
use pocketmine\world\sound\NoteInstrument;
use pocketmine\world\sound\NoteSound;
use pocketmine\world\World;

class Doorbell extends Position
{
    /** @var Doorbell[] */
    private static array $bells = [];

    private int $cooldown = 0;

    /**
     * Doorbell constructor.
     * @param int $x
     * @param int $y
     * @param int $z
     * @param World|null $level
     */
    public function __construct($x = 0, $y = 0, $z = 0, World $level = null)
    {

        parent::__construct($x, $y, $z, $level);
        self::$bells[$this->__toString()] = $this;

    }


    public function activate(){

        // COOLDOWN OF 3 SECONDS
        if($this->cooldown > time()) return;
        $this->cooldown = time() + 3;

        // SEND SOUNDS TO POSITION
        $this->getWorld()->addSound($this, new NoteSound(NoteInstrument::PIANO(), 10));
        Main::getInstance()->getScheduler()->scheduleDelayedTask(new NoteBlockSoundTask($this), 5);
    }

    public function delete(){

        // UNSET DOORBELL FROM ARRAY
        unset(self::$bells[$this->__toString()]);

        // BUILD LOCATION FOR DB VALUE
        $location_json = json_encode([
           "x" => $this->getX(),
           "y" => $this->getY(),
           "z" => $this->getZ(),
           "level" => $this->getWorld()->getFolderName(),
        ]);

        // BUILD SQL STATEMENT
        $stmt = Main::getDb()->prepare("DELETE FROM doorbells WHERE location = :location");
        $stmt->bindParam(":location", $location_json);

        // EXECUTE SQL STATEMENT
        $stmt->execute();
        $stmt->close();
    }

    public static function createDoorbell(Position $position): ?Doorbell
    {
        $doorbell = null;
        if(is_null(self::getByPosition($position))){
            $location_json = json_encode([
                "x" => $position->getX(),
                "y" => $position->getY(),
                "z" => $position->getZ(),
                "level" => $position->getWorld()->getFolderName(),
            ]);
            $stmt = Main::getDb()->prepare("INSERT INTO doorbells(location) VALUES (:location)");
            $stmt->bindParam(":location", $location_json);
            $stmt->execute();
            $stmt->close();

            $doorbell = new Doorbell($position->getX(), $position->getY(), $position->getZ(), $position->getWorld());
        }

        return $doorbell;
    }

    public static function getByPosition(Position $position): ?self {
        if(isset(self::$bells[$position->__toString()])) return self::$bells[$position->__toString()];
        return null;
    }
}