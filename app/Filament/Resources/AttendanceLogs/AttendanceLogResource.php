<?php

namespace App\Filament\Resources\AttendanceLogs;

use App\Filament\Resources\AttendanceLogs\Pages\CreateAttendanceLog;
use App\Filament\Resources\AttendanceLogs\Pages\EditAttendanceLog;
use App\Filament\Resources\AttendanceLogs\Pages\ListAttendanceLogs;
use App\Filament\Resources\AttendanceLogs\Schemas\AttendanceLogForm;
use App\Filament\Resources\AttendanceLogs\Tables\AttendanceLogsTable;
use App\Models\AttendanceLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class AttendanceLogResource extends Resource
{
    protected static ?string $model = AttendanceLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'AttendanceLog';

    public static function form(Schema $schema): Schema
    {
        return AttendanceLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            TextColumn::make('created_at')
                ->label('Waktu')
                ->dateTime('H:i:s')
                ->sortable(),
            TextColumn::make('student.name')
                ->label('Nama Siswa')
                ->searchable(),
            TextColumn::make('student.grade')
                ->label('Kelas'),
            TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'DIIZINKAN' => 'success',
                    'DITOLAK' => 'danger',
                })
        ])
        ->defaultSort('created_at', 'desc') // Data terbaru di atas
        ->poll('3s'); // AUTO REFRESH TIAP 3 DETIK!
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAttendanceLogs::route('/'),
            'create' => CreateAttendanceLog::route('/create'),
            'edit' => EditAttendanceLog::route('/{record}/edit'),
        ];
    }
}
