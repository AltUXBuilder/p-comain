<?php

namespace App\View\Components\Crm;

use Illuminate\View\Component;

class NavItem extends Component
{
    public function __construct(
        public string $href,
        public bool $active = false,
        public string $icon = 'home'
    ) {}

    public function render()
    {
        return view('components.crm.nav-item');
    }
}
