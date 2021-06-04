<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class SetListeningLanguageBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_LISTENING_LANGUAGE_BRICK;
    $this->caption = 'Set listening language to _';
    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}
