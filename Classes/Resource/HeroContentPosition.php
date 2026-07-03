<?php

declare(strict_types=1);

namespace StarterTeam\StarterNessa\Resource;

enum HeroContentPosition: string
{
    case BOTTOM_LEFT = 'bottom-left';

    case CENTER_LEFT = 'center-left';

    case CENTER = 'center';

    case NONE = 'none';
}
