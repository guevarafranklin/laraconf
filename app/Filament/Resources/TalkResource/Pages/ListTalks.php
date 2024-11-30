<?php

namespace App\Filament\Resources\TalkResource\Pages;

use App\Enums\TalkStatus;
use App\Filament\Resources\TalkResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTalks extends ListRecords
{
    protected static string $resource = TalkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Talks'),
            'approved' => Tab::make('Approved Talks')
            ->modifyQueryUsing(function ($query) {
                return $query->where('status', TalkStatus::APPROVED);
            }),
            'submitted' => Tab::make('Submitted Talks')
            ->modifyQueryUsing( fn (Builder $query) => $query->where('status', TalkStatus::SUBMITTED)),
            'rejected' => Tab::make('Rejected Talks')
            ->modifyQueryUsing( fn (Builder $query) => $query->where('status', TalkStatus::REJECTED)),
        ];
    }


}
