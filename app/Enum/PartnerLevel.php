<?php

namespace App\Enum;

enum PartnerLevel: string
{
    case DIAMOND = 'diamond';

    case PLATINUM = 'platinum';

    case SILVER = 'silver';

    public function display()
    {
        return ucfirst($this->value);
    }
}
