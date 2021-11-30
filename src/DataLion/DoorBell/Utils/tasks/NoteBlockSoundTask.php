<?php


namespace DataLion\DoorBell\Utils\tasks;


use pocketmine\world\Position;
use pocketmine\scheduler\Task;
use pocketmine\world\sound\NoteInstrument;
use pocketmine\world\sound\NoteSound;

class NoteBlockSoundTask extends Task
{

    private Position $position;

    public function __construct(Position $position)
    {
        $this->position = $position;
    }

    public function onRun(): void
    {
        $this->position->getWorld()->addSound($this->position, new NoteSound(NoteInstrument::PIANO(), 6));
    }
}