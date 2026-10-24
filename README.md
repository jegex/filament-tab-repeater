# Filament Tab Repeater

A Filament form field that behaves like `Repeater` but renders every item as a
native Filament tab instead of a collapsible block. Horizontal tabs are the
default; a vertical rail is available for long item lists. The tab bar and the
active panel share one native card by default, matching the look of Filament's
schema `Tabs`.

`TabRepeater` extends `Filament\Forms\Components\Repeater`, so item state,
relationship handling, validation, hydration and dehydration stay owned by
Filament. Only the item presentation, navigation and per-item actions are
replaced.

## Requirements

- PHP 8.3+
- Filament 5.9+
- Laravel 13+

## Installation

```bash
composer require jegex/filament-tab-repeater
```

The service provider is registered through package discovery
(`extra.laravel.providers`). It only registers the `filament-tab-repeater` view
namespace; there is no config, migration, asset build or panel plugin to install.

If you registered `FilamentTabRepeaterPlugin` manually in a panel, it is still
available for backwards compatibility, but the field no longer needs it.

## Usage

```php
use Jegex\FilamentTabRepeater\Forms\Components\TabRepeater;

TabRepeater::make('variants')
    ->relationship()
    ->orderColumn('sort')
    ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
    ->itemNumbers()
    ->schema([
        TextInput::make('name')
            ->required(),
        TextInput::make('slug'),
        // ...
    ]);
```

Each item receives one tab. The tab label falls back to `Item {number}` when
`itemLabel()` returns nothing. Error badges and the initial active tab are
derived from the validation errors of each item.

### Reordering

Reordering follows native `Repeater` defaults: drag and drop is enabled, and the
per-item Move actions are opt-in.

```php
// Drag a tab by its grip handle. Enabled by default.
TabRepeater::make('variants')
    ->schema([...]);

// Show Move left/right (or up/down in vertical layouts) next to each label.
TabRepeater::make('variants')
    ->reorderableWithButtons()
    ->schema([...]);

// Disable all reordering, including the grip handles.
TabRepeater::make('variants')
    ->reorderable(false)
    ->schema([...]);
```

Dragging uses Filament's bundled `x-sortable` directive, so there is no extra
JavaScript dependency or asset build. The new order is submitted through
Filament's native `reorder` action, which means the active tab, panel state and
validation errors follow their items. Combine reordering with `orderColumn()`
when the items belong to a relationship so the new order is persisted.

### Orientation

```php
TabRepeater::make('variants')
    ->vertical()
    ->schema([...]);

TabRepeater::make('variants')
    ->orientation('horizontal') // or a Closure returning "horizontal" / "vertical"
    ->schema([...]);
```

`vertical()` and `horizontal()` also accept a boolean or a Closure. Any other
orientation value throws an `InvalidArgumentException`.

### Containment

`TabRepeater` is contained by default, like Filament's schema `Tabs`: the tab
bar and the active panel are wrapped in one rounded card, and the bar is
separated from the panel by a single bottom border.

```php
// Default: one outer card around the tab bar and the panel.
TabRepeater::make('variants')
    ->schema([...]);

// No outer card: the tab bar gets its own pill card and the panel follows
// after it, exactly like non-contained schema tabs.
TabRepeater::make('variants')
    ->contained(false)
    ->schema([...]);
```

`contained()` accepts a boolean or a Closure, so it can react to the form state
or the current user. The empty state is always rendered without the outer card,
because there are no tabs to contain.

### Scrolling

Tabs scroll horizontally by default, and a vertical rail scrolls vertically once
it reaches its maximum height. Disable that when you prefer the tabs to wrap:

```php
TabRepeater::make('variants')
    ->scrollable(false)
    ->schema([...]);
```

`scrollable()` accepts a boolean or a Closure and defaults to `true`. With
`false`, horizontal tabs wrap onto multiple rows and a vertical rail grows
instead of scrolling.

## Supported API

These methods keep their native `Repeater` behaviour:

| Method | Notes |
| --- | --- |
| `relationship()` | Relationship saving and item keys are unchanged. |
| `orderColumn()` | Re-enables reordering when used after `relationship()`. |
| `itemLabel()` | Displayed in the tab; may return a string or `Htmlable`. |
| `itemNumbers()` | Prefixes a real label with `{n}. `. |
| `truncateItemLabel()` | Truncates the tab label with CSS from the component view. |
| `orientation()` | `horizontal` (default) or `vertical`. |
| `vertical()` | Shortcut for `orientation('vertical')`; accepts a boolean or Closure. |
| `horizontal()` | Shortcut for `orientation('horizontal')`; accepts a boolean or Closure. |
| `contained()` | One outer card around the bar and panel; default `true`, like schema `Tabs`. |
| `scrollable()` | Scroll overflowing tabs; default `true`. `false` makes them wrap. |
| `getActiveTab()` | Public accessor for the currently active item key. |
| `reorderable()` | Enables reordering; `false` also removes the drag handles. |
| `reorderableWithDragAndDrop()` | Enabled by default; shows a grip handle on every tab. |
| `reorderableWithButtons()` | Opt-in Move left/right (up/down when vertical) item actions. |
| `reorderAnimationDuration()` | Milliseconds for the sortable animation; Filament's default is 300. |
| `minItems()` | Also hides Delete once the minimum is reached. |
| `maxItems()` | Respected by the native Add and Clone actions. |
| `addable()` | Hides the Add button at the end of the tab bar when disabled. |
| `cloneable()` | Shows a Clone button inside each tab when enabled. |
| `deletable()` | Hides the Delete button inside each tab when disabled. |
| `disabled()` | Hides the tab actions and Add button, and disables the native actions. |
| `hidden()` | Standard field visibility. |
| `columnSpan()` | Standard grid span. |
| `schema()` | Item schema, including nested fields and validation. |

## Unsupported API

The following methods throw `BadMethodCallException` when asked to enable the
feature. Passing the disabled value (`false`, or `null` for the callbacks) is
accepted as a no-op so that shared Repeater configuration keeps working.

| Method | Reason |
| --- | --- |
| `simple($field)` | The simple layout has no tab bar. |
| `table($columns)` | The table layout has no tab bar. |
| `collapsed()` | Items are tabs, never collapsed blocks. |
| `collapsible()` | Items are tabs, never collapsible blocks. |
| `persistCollapsed()` | Collapse state does not exist. |
| `addBetweenAction($callback)` | Items are not stacked, so there is no "between" position. |

These methods are accepted for compatibility but ignored:
`itemHeaders()`, `grid()`, `labelBetweenItems()`.

## Behaviour notes

- One tab and one panel per item. The tab bar is horizontal by default; with
  `vertical()` it becomes a scrollable rail beside the panel.
- Contained mode reuses Filament's native `.fi-sc-tabs` / `.fi-tabs` classes, so
  the card, bottom border, panel padding and vertical border all follow the
  panel's own theme. `contained(false)` falls back to the native non-contained
  tab bar and panel spacing.
- All panels stay mounted in the DOM. Switching tabs only toggles visibility, so
  every item keeps its Livewire state and validation errors.
- Each tab shows a danger badge with the number of validation errors inside
  that item. Errors belonging to the repeater itself are not counted.
- The active tab defaults to the first item with errors, otherwise the first
  item. Clicking a tab activates it; Filament's `expand` browser event and the
  `tab-repeater-activated` / `tab-repeater-deleted` events also move the active
  tab when items are added or cloned. Deleting the active item activates its
  left neighbour (or the right one when there is no left), while deleting any
  other tab leaves the active tab unchanged.
- Each tab shows its action buttons immediately after the label, in this order:
  Clone, Move left/up, Move right/down, Delete. Move actions only appear with
  `reorderableWithButtons()`; Delete is hidden at `minItems()`. Extra item
  actions stay in an ellipsis menu at the end of the tab.
- The Add button sits at the end of the tab bar, after the last tab. It is not a
  tab itself and never takes part in drag and drop. With no items, the empty
  state still shows its own Add button.
- Drag and drop is enabled by default and uses a grip handle per tab, so the tab
  label remains clickable. Dragging calls Filament's native `reorder` action and
  keeps the currently active item active. Disable it with
  `reorderableWithDragAndDrop(false)` (or all reordering with `reorderable(false)`);
  a disabled field never exposes handles.
- There is no keyboard arrow navigation between tabs. Nesting a `TabRepeater`
  inside another one is not supported.
- The field renders its own styles inside an `@once` block using semantic
  `fi-tab-repeater-*` classes. There is no CSS build step.

## Views

Views are registered under the `filament-tab-repeater` namespace. To override
them, publish or copy them into the host application:

```
resources/views/vendor/filament-tab-repeater/filament/forms/components/tab-repeater.blade.php
resources/views/vendor/filament-tab-repeater/filament/forms/components/partials/tabs.blade.php
```

The component contract for those views is intentionally small: they receive the
`TabRepeater` instance as `$component`, the item schemas as `$items`, the
per-item actions and error counts, the computed `$activeKey`, the `$isVertical`,
`$isContained` and `$isScrollable` layout flags, and the `$isDragEnabled` /
`$reorderAnimationDuration` reordering flags.

## Testing

The package ships with an Orchestra Testbench + Pest suite:

```bash
composer install
vendor/bin/pest
```

Lint with Pint:

```bash
vendor/bin/pint --test
```

## Upgrading from 1.x

The public class name is unchanged:

```php
use Jegex\FilamentTabRepeater\Forms\Components\TabRepeater;
```

- `scrollable()` still exists and now reliably wraps tabs when set to `false`.
- `getActiveTab()` is available again as a public method.
- The view namespace changed from `tab-repeater` to `filament-tab-repeater`.
  If you published overrides, move them to
  `resources/views/vendor/filament-tab-repeater/`.
- The old CSS asset and the panel plugin are no longer required. The plugin
  class remains for compatibility but is not auto-registered.
- Styling is rendered inline, so there is no `vendor:publish` step for assets.

## License

MIT. See [LICENSE](LICENSE).
