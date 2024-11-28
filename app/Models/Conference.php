<?php

namespace App\Models;

use App\Enums\Region;
use App\Models\Builder as AppBuilder;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Builder as FormBuilder;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\{Eloquent\Factories\HasFactory,
    Eloquent\Model,
    Eloquent\Relations\BelongsTo,
    Eloquent\Relations\BelongsToMany,
    Eloquent\Builder as EloquentBuilder,
    Eloquent\SoftDeletingScope};
use Filament\Forms\Components\Wizard;
use App\Models\ResetStars;

class Conference extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
        'region',
        'venue_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'region' => Region::class,
        'venue_id' => 'integer',
    ];

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function speakers(): BelongsToMany
    {
        return $this->belongsToMany(Speaker::class);
    }

    public function talks(): BelongsToMany
    {
        return $this->belongsToMany(Talk::class);
    }

    public static function getForm(): array
    {
        return [
            Section::make('Conference Information')
            ->columnSpanFull()
            ->schema([
                    TextInput::make('name')
                        ->label('Conference Name')
                        ->required(),
                    TextInput::make('description')
                        ->required(),
                    DateTimePicker::make('start_date')
                        ->required(),
                    DateTimePicker::make('end_date')
                        ->required(),
                    Select::make('status')
                        ->options([
                            'draft' => 'Draft',
                            'published' => 'Published',
                            'archived' => 'Archived',
                        ])
                        ->required(),
                ]),
            Section::make('Conference Details')
                ->columnSpanFull()
                ->schema([
                    Select::make('region')
                        ->live()
                        ->enum(Region::class)
                        ->options(Region::class),
                    Select::make('venue_id')
                        ->searchable()
                        ->preload()
                        ->editOptionForm(schema: Venue::getForm())
                        ->createOptionForm(schema: Venue::getForm())
                        ->relationship('venue', titleAttribute: 'name', modifyQueryUsing: function (EloquentBuilder $query, $get) {
                            return $query->where('region', $get('region'));
                        }),
                    CheckboxList::make('speakers')
                        ->relationship('speakers', titleAttribute: 'name')
                        ->columns(3)
                        ->BulkToggleable()
                        ->options(fn () => \App\Models\Speaker::pluck('name', 'id')->toArray()),
                ]),
            Section::make('Dev Tools')
            ->columnSpanFull()
            ->schema([
                Actions::make(actions: [
                    Action::make('Star')
                        ->icon('heroicon-m-star')
                        ->label('Fill with Factory Data')
                        ->visible( function (string $operation) {
                            if ($operation !== 'create') {
                                return false;
                            }

                            if(! app()->environment('local')) {
                                return false;
                            }

                            return true;
                        })
                        ->requiresConfirmation()
                        ->action(function ($livewire) {

                            $data = Conference::factory()->make()->toArray();
                            $livewire->form->fill($data);
                        }),
            ])
            ]),
            ];

    }
}
