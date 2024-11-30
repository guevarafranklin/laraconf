<?php

namespace App\Filament\Resources;

use App\Enums\TalkLength;
use App\Enums\TalkStatus;
use App\Filament\Resources\TalkResource\Pages;
use App\Filament\Resources\TalkResource\RelationManagers;
use App\Models\Talk;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Str;

class TalkResource extends Resource
{
    protected static ?string $model = Talk::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('length')
                    ->required()
                    ->enum(TalkLength::class)
                    ->options(TalkLength::class),
                Forms\Components\Select::make('status')
                    ->required()
                    ->enum(TalkStatus::class)
                    ->options(TalkStatus::class),
                Forms\Components\Select::make('speaker_id')
                    ->relationship('speaker', 'name'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->persistFiltersInSession()
            ->filtersTriggerAction(function ($action) {
                return $action->button()->label('Filter');
            })
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->sortable()
                    ->searchable()
                    ->description(function (Talk $record) {
                        return Str::of($record->description)->limit(50);
                    }),
                Tables\Columns\TextColumn::make('length')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('speaker.avatar')
                    ->label('Speaker Avatar')
                    ->circular()
                    ->defaultImageUrl(function ($record) {
                        return 'https://ui-avatars.com/api/?background=00008B&color=fff&name=' . urlencode($record->speaker->name);
                    }),
                Tables\Columns\TextColumn::make('speaker.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('new_talk')
                    ->icon(function ($record) {
                        return $record->new_talk ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle';
                    }),
                Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(function ($state) {
                    return $state->getColor();
                }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('new_talk')
                    ->options([
                        'Yes' => true,
                        'No' => false,
                    ]),
                Tables\Filters\SelectFilter::make('speaker')
                    ->relationship('speaker', 'name')
                    ->label('Speaker'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->slideOver(),
                Tables\Actions\ActionGroup::make( [
                    Tables\Actions\Action::make(name: 'approve')
                        ->visible(fn (Talk $talk) => $talk->status === TalkStatus::SUBMITTED)
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn (Talk $talk) => $talk->update(['status' => TalkStatus::APPROVED]))
                        ->after(function () {
                            Notification::make()->success()->title('Talk Approved')
                                ->duration(1000)
                                ->body('The talk has been approved.')
                                ->send();
                        }),
                    Tables\Actions\Action::make('reject')
                        ->visible(fn (Talk $talk) => $talk->status === TalkStatus::SUBMITTED)
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn (Talk $talk) => $talk->update(['status' => TalkStatus::REJECTED]))
                        ->requiresConfirmation()
                        ->after(function () {
                            Notification::make()->danger()->title('Talk Rejected')
                                ->duration(1000)
                                ->body('The talk has been rejected.')
                                ->send();
                        }),
                ]),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                //
            ]);
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
            'index' => Pages\ListTalks::route('/'),
            'create' => Pages\CreateTalk::route('/create'),
            //'edit' => Pages\EditTalk::route('/{record}/edit'),
        ];
    }
}
