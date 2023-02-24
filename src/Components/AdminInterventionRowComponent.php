<?php

namespace App\Components;

use DateTime;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('admin-intervention-row')]
class AdminInterventionRowComponent
{
    public int $id;
    public DateTime $date;
    public string $title;
    public string $description;
}

?>