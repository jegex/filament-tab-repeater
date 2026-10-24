<?php

namespace Jegex\FilamentTabRepeater\Tests\Fixtures;

use Filament\Forms\Components\TextInput;
use Filament\Forms\FormsComponent;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Jegex\FilamentTabRepeater\Forms\Components\TabRepeater;
use Jegex\FilamentTabRepeater\Tests\Fixtures\Models\Product;

class TabRepeaterRelationshipTestComponent extends FormsComponent
{
    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    public ?int $recordId = null;

    public function mount(int $recordId): void
    {
        $this->recordId = $recordId;

        $this->form->fill([]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->model(Product::query()->findOrFail($this->recordId))
            ->components([
                TabRepeater::make('variants')
                    ->relationship()
                    ->orderColumn('sort')
                    ->itemLabel(static fn (array $state): ?string => $state['name'] ?? null)
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $this->form->getState();
    }

    public function render(): View
    {
        return view('filament-tab-repeater-tests::fixture');
    }
}
