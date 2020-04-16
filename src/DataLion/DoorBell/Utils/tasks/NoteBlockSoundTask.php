<?php


namespace DataLion\DoorBell\Utils\tasks;


use DataLion\DoorBell\Sounds\NoteBlockSound;

use pocketmine\level\Position;
use pocketmine\scheduler\Task;

class NoteBlockSoundTask extends Task
{
    private $position;

    public function __construct(Position $position)
    {
        $this->position = $position;
    }

    public function onRun(int $currentTick)
    {
        $this->position->getLevel()->addSound(new NoteBlockSound($this->position, 6));
    }
}