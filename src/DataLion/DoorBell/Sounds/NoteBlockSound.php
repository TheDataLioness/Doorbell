<?php


namespace DataLion\DoorBell\Sounds;


use pocketmine\level\sound\Sound;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

class NoteBlockSound extends Sound
{
    public const INSTRUMENT_PIANO = 0;
    public const INSTRUMENT_BASS_DRUM = 1;
    public const INSTRUMENT_CLICK = 2;
    public const INSTRUMENT_TABOUR = 3;
    public const INSTRUMENT_BASS = 4;

    protected $instrument = self::INSTRUMENT_PIANO;
    protected $note = 0;

    /**
     * NoteBlockSound constructor.
     *
     * @param Vector3 $pos
     * @param int $instrument
     * @param int $note
     */
    public function __construct(Vector3 $pos, int $note = 0, int $instrument = self::INSTRUMENT_PIANO){
        parent::__construct($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());

        $this->instrument = $instrument;
        $this->note = $note;
    }

    public function encode(){
        $pk = new BlockEventPacket();
        $pk->x = $this->x;
        $pk->y = $this->y;
        $pk->z = $this->z;
        $pk->eventType = $this->instrument;
        $pk->eventData = $this->note;

        $pk2 = new LevelSoundEventPacket();
        $pk2->sound = LevelSoundEventPacket::SOUND_NOTE;
        $pk2->position = $this;
        $pk2->extraData = $this->instrument | $this->note;

        return [$pk, $pk2];
    }
}