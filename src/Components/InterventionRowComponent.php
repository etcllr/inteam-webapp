<?php

namespace App\Components;

use DateTime;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('intervention-row')]
class InterventionRowComponent
{
    public int $id;
    public DateTime $date;
    public string $title;
    public string $description;
}

?>