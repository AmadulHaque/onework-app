<?php

namespace App\View\Components;

use Illuminate\View\Component;

class CustomCard extends Component
{
    public $title;

    public $value;

    public $description;

    public $color;

    public function __construct($title, $value, $description = null, $color = 'gray')
    {
        $this->title = $title;
        $this->value = $value;
        $this->description = $description;
        $this->color = $color;
    }

    public function render()
    {
        return view('components.custom-card');
    }
}
