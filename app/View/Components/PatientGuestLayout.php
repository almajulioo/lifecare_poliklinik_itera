<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class PatientGuestLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     * Used for Patient Login (with background image)
     */
    public function render(): View
    {
        return view('layouts.guest-patient');
    }
}
