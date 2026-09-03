<?php

namespace App\Livewire\Components;

use Illuminate\View\View;
use Livewire\Component;
use Lunar\Models\Collection;

class Navigation extends Component
{
    /**
     * Return the collections in a tree.
     */
    public function getCollectionsProperty()
    {
        return Collection::with(['defaultUrl'])->get()->toTree();
    }

    public function render(): View
    {
        return view('livewire.components.navigation');
    }
}
