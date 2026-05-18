<?php

namespace App\Filament\Resources\Students\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uid')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('grade')
                    ->required()
                    ->numeric(),
            ]);
    }
}
