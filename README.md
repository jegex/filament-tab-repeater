# Filament Tab Repeater

Custom form repeater with tabs UI for FilamentPHP. This package extends the native `Filament\Forms\Components\Repeater` to display items as tabs instead of a list.

## Features

- **Tab UI** - Each repeater item is displayed as a clickable tab
- **Horizontal & Vertical** - Support for horizontal or vertical tab orientation
- **Scrollable** - Tabs can scroll when there are too many
- **Contained** - Support for contained layout style
- **Native Repeater Features** - All native repeater features still work (clone, delete, reorder, validation, relationships, etc.)
- **Auto Active Tab** - Latest tab automatically becomes active after adding an item
- **Consistent UI** - Uses native `<x-filament::tabs>` component for UI consistency

## Installation

This package is already installed via composer path repository. If not, run:

```bash
composer require jegex/filament-tab-repeater
```

## Usage

### Basic Usage

```php
use Jegex\FilamentTabRepeater\Forms\Components\TabRepeater;

TabRepeater::make('sections')
    ->schema([
        TextInput::make('title')->required(),
        Textarea::make('content'),
    ])
    ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'New Section'),
```

### Vertical Tabs

```php
TabRepeater::make('sections')
    ->schema([...])
    ->vertical()
    ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'New Section'),
```

### Non-Scrollable Tabs

```php
TabRepeater::make('sections')
    ->schema([...])
    ->scrollable(false)
    ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'New Section'),
```

### Full Example

```php
use Jegex\FilamentTabRepeater\Forms\Components\TabRepeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;

TabRepeater::make('sections')
    ->schema([
        TextInput::make('title')
            ->live(onBlur: true)
            ->required(),
        Select::make('type')
            ->options([
                'text' => 'Text',
                'image' => 'Image',
                'video' => 'Video',
            ])
            ->live(),
        Textarea::make('content'),
    ])
    ->vertical(false)
    ->scrollable(false)
    ->cloneable()
    ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'New Section')
    ->columnSpanFull(),
```

## API Reference

### Methods

| Method | Description | Default |
|--------|-------------|---------|
| `vertical(bool\|Closure $condition = true)` | Set tab orientation to vertical | `false` |
| `isVertical()` | Check if tabs are vertical | - |
| `scrollable(bool\|Closure $condition = true)` | Set whether tabs are scrollable | `true` |
| `isScrollable()` | Check if tabs are scrollable | - |
| `contained(bool\|Closure $condition = true)` | Set contained layout (from `CanBeContained` trait) | - |
| `isContained()` | Check if contained | - |

### Inherited from Native Repeater

All methods from `Filament\Forms\Components\Repeater` are available:

| Method | Description |
|--------|-------------|
| `schema(array $schema)` | Child form fields |
| `itemLabel(string\|Closure\|null $label)` | Label for each tab |
| `cloneable()` | Enable clone feature |
| `reorderableWithButtons()` | Enable reorder with up/down buttons |
| `reorderableWithDragAndDrop()` | Enable reorder with drag & drop |
| `collapsible()` | Enable collapsible items |
| `minItems(int $count)` | Minimum items |
| `maxItems(int $count)` | Maximum items |
| `defaultItems(int $count)` | Default items |
| `addable()` | Enable add feature |
| `deletable()` | Enable delete feature |
| `relationship(string $relationship)` | Eloquent relationship |

## How It Works

This package uses the native `<x-filament::tabs>` component for rendering tabs. Each repeater item is displayed as a tab, and the item content is displayed as a tab panel.

The active tab is managed by Alpine.js with `x-data` and `x-init`. Every time Livewire re-renders (after add/delete), `x-init` runs again and sets `activeTab` to the last item key.

## Package Structure

```
src/
├── Forms/Components/
│   └── TabRepeater.php          # Main component class
├── FilamentTabRepeaterPlugin.php
└── FilamentTabRepeaterServiceProvider.php

resources/
├── views/components/
│   └── tab-repeater.blade.php   # Blade view
└── css/
    └── tab-repeater.css         # Custom styles
```

## Requirements

- PHP 8.3+
- Laravel 13+
- Filament v5+

## License

MIT
