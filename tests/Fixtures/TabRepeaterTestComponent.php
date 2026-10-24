<?php

namespace Jegex\FilamentTabRepeater\Tests\Fixtures;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\FormsComponent;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Jegex\FilamentTabRepeater\Forms\Components\TabRepeater;

class TabRepeaterTestComponent extends FormsComponent
{
    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    /**
     * @var array<string, mixed>
     */
    public array $config = [];

    public function mount(array $config = [], array $data = []): void
    {
        $this->config = $config;

        $this->form->fill($data === [] ? null : $data);
    }

    public function form(Schema $schema): Schema
    {
        $component = TabRepeater::make('items')
            ->defaultItems($this->config['defaultItems'] ?? 0)
            ->itemLabel(static fn (array $state): ?string => $state['name'] ?? null)
            ->schema([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug'),
            ]);

        $component->orientation($this->config['orientation'] ?? 'horizontal');

        if (array_key_exists('contained', $this->config)) {
            $component->contained((bool) $this->config['contained']);
        }

        if (array_key_exists('scrollable', $this->config)) {
            $component->scrollable((bool) $this->config['scrollable']);
        }

        if ($this->config['itemNumbers'] ?? false) {
            $component->itemNumbers();
        }

        if ($this->config['truncate'] ?? false) {
            $component->truncateItemLabel();
        }

        if (array_key_exists('reorderable', $this->config)) {
            $component->reorderable((bool) $this->config['reorderable']);
        }

        if (array_key_exists('reorderableWithDragAndDrop', $this->config)) {
            $component->reorderableWithDragAndDrop((bool) $this->config['reorderableWithDragAndDrop']);
        }

        if (array_key_exists('reorderableWithButtons', $this->config)) {
            $component->reorderableWithButtons((bool) $this->config['reorderableWithButtons']);
        }

        if (array_key_exists('minItems', $this->config)) {
            $component->minItems($this->config['minItems']);
        }

        if (array_key_exists('maxItems', $this->config)) {
            $component->maxItems($this->config['maxItems']);
        }

        if (array_key_exists('addable', $this->config)) {
            $component->addable($this->config['addable']);
        }

        if (array_key_exists('cloneable', $this->config)) {
            $component->cloneable($this->config['cloneable']);
        }

        if (array_key_exists('deletable', $this->config)) {
            $component->deletable($this->config['deletable']);
        }

        if ($this->config['disabled'] ?? false) {
            $component->disabled();
        }

        if ($this->config['extraItemActions'] ?? false) {
            $component->extraItemActions([
                Action::make('extra')
                    ->label('Extra action')
                    ->action(static function (): void {}),
            ]);
        }

        return $schema
            ->components([$component])
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
