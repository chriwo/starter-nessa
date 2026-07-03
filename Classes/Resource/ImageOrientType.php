<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Resource;

enum ImageOrientType: int
{
    case ABOVE_CENTER = 0;

    case ABOVE_RIGHT = 1;

    case ABOVE_LEFT = 2;

    case BELOW_CENTER = 8;

    case BELOW_RIGHT = 9;

    case BELOW_LEFT = 10;

    case IN_TEXT_RIGHT = 17;

    case IN_TEXT_LEFT = 18;

    case BESIDE_TEXT_RIGHT = 25;

    case BESIDE_TEXT_LEFT = 26;
}
