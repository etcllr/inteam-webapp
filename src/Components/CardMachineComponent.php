<?php

namespace App\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('card-machine')]
class CardMachineComponent
{
    public int $id;
    public string $name;
    public string $brand;
    public string $category;
    public string $code;
    public string $image;
}

?>