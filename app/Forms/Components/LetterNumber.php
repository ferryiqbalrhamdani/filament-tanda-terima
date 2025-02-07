<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Component;

class LetterNumber extends Component
{
    protected string $view = 'forms.components.letter-number';

    public static function make(): static
    {
        return app(static::class);
    }
}
