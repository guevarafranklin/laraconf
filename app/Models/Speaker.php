<?php

namespace App\Models;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Speaker extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'avatar',
        'email',
        'qualifications',
        'phone',
        'bio',
        'twitter',
        'linkedin',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'qualifications' => 'array',
    ];

    public function conferences(): BelongsToMany
    {
        return $this->belongsToMany(Conference::class);
    }
    public function talks(): HasMany
    {
        return $this->hasMany(Talk::class);
    }

    public static function getForm(): array
    {
        return [
            TextInput::make('name')
                ->required(),
            FileUpload::make('avatar')
                ->maxSize(1024 * 1024 * 2)
                ->avatar()
                ->image(),
            TextInput::make('email')
                ->email()
                ->required(),
            CheckboxList::make('qualifications')
                ->columnSpanFull()
                ->BulkToggleable()
                ->options([
                    'Business Leader' => 'Business Leader',
                    'Developer' => 'Developer',
                    'Designer' => 'Designer',
                    'Project Manager' => 'Project Manager',
                    'Quality Assurance' => 'Quality Assurance',
                    'Cybersecurity Specialist' => 'Cybersecurity Specialist',
                    'Data Scientist' => 'Data Scientist',
                    'DevOps Engineer' => 'DevOps Engineer',
                    'IT Professional' => 'IT Professional',
                ])
                ->columns(3)
                ->required(),
            TextInput::make('phone')
                ->tel()
                ->required(),
            Textarea::make('bio')
                ->required()
                ->columnSpanFull(),
            TextInput::make('twitter')
                ->required(),
            TextInput::make('linkedin')
                ->required(),
        ];
    }
}
