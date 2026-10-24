<?php

use Filament\Actions\Testing\TestAction;
use Filament\Forms\Components\TextInput;
use Illuminate\View\ViewException;
use InvalidArgumentException;
use Jegex\FilamentTabRepeater\Forms\Components\TabRepeater;
use Jegex\FilamentTabRepeater\Tests\Fixtures\TabRepeaterTestComponent;
use Livewire\Livewire;

it('renders a tab and a panel per item', function () {
    $component = Livewire::test(TabRepeaterTestComponent::class, [
        'config' => ['defaultItems' => 2],
        'data' => [
            'items' => [
                ['name' => 'Default'],
                ['name' => 'Premium'],
            ],
        ],
    ]);

    $keys = array_keys($component->get('data')['items']);

    expect($keys)->toHaveCount(2);

    $component
        ->assertSee('Default')
        ->assertSee('Premium')
        ->assertSeeHtml('data-tab-repeater-tab="'.$keys[0].'"')
        ->assertSeeHtml('data-tab-repeater-tab="'.$keys[1].'"')
        ->assertSeeHtml('data-active-key="'.$keys[0].'"')
        ->assertSeeHtml('role="tabpanel"')
        ->assertSeeHtml('x-sortable="x-sortable"')
        ->assertSeeHtml('x-sortable-item="'.$keys[0].'"')
        ->assertSeeHtml('x-sortable-handle')
        ->assertSeeHtml('data-sortable-animation-duration="300"')
        ->assertSeeHtml('x-on:end.stop')
        ->assertSeeHtml('mountAction(&#039;reorder&#039;, { items: $event.target.sortable.toArray() }, { schemaComponent: &#039;form.items&#039; })')
        ->assertSeeHtml("x-on:click=\"activate('".$keys[0]."')\"")
        ->assertDontSeeHtml('@js(')
        ->assertDontSeeHtml('fi-sc-tabs fi-tab-repeater fi-contained fi-vertical')
        ->assertDontSeeHtml('fi-tab-repeater-panel-card');
});

it('renders the vertical orientation', function () {
    Livewire::test(TabRepeaterTestComponent::class, [
        'config' => ['defaultItems' => 1, 'orientation' => 'vertical'],
    ])
        ->assertSeeHtml('fi-sc-tabs fi-tab-repeater fi-contained fi-vertical')
        ->assertSeeHtml('fi-tabs fi-contained fi-vertical');
});

it('renders native contained tabs by default', function () {
    $component = Livewire::test(TabRepeaterTestComponent::class, [
        'config' => ['defaultItems' => 2],
    ]);

    expect($component->html())
        ->toContain('class="fi-sc-tabs fi-tab-repeater fi-contained"')
        ->toContain('fi-tabs fi-contained')
        ->not->toContain('fi-tab-repeater-panel-card');

    expect($component->instance()->getSchema('form')->getComponent('items')->isContained())->toBeTrue();
});

it('renders non-contained tabs without an outer card', function () {
    $component = Livewire::test(TabRepeaterTestComponent::class, [
        'config' => ['defaultItems' => 2, 'contained' => false],
    ]);

    expect($component->html())
        ->toContain('class="fi-sc-tabs fi-tab-repeater"')
        ->toContain('class="fi-tabs" aria-label="Items"');

    expect($component->instance()->getSchema('form')->getComponent('items')->isContained())->toBeFalse();
});

it('defaults to contained and exposes the contained API', function () {
    expect(TabRepeater::make('items')->isContained())->toBeTrue()
        ->and(TabRepeater::make('items')->contained(false)->isContained())->toBeFalse()
        ->and(TabRepeater::make('items')->contained(static fn (): bool => false)->isContained())->toBeFalse();

    $component = TabRepeater::make('items');

    expect($component->contained())->toBe($component)
        ->and($component->isContained())->toBeTrue();
});

it('can switch orientation with the vertical and horizontal helpers', function () {
    $component = TabRepeater::make('items');

    expect($component->getOrientation())->toBe('horizontal');

    $component->vertical();

    expect($component->getOrientation())->toBe('vertical');

    $component->vertical(false);

    expect($component->getOrientation())->toBe('horizontal');

    $component->horizontal(false);

    expect($component->getOrientation())->toBe('vertical');
});

it('supports the legacy scrollable toggle', function () {
    $component = TabRepeater::make('items');

    expect($component->isScrollable())->toBeTrue()
        ->and($component->scrollable(false))->toBe($component)
        ->and($component->isScrollable())->toBeFalse();

    Livewire::test(TabRepeaterTestComponent::class, [
        'config' => ['defaultItems' => 2, 'scrollable' => false],
    ])
        ->assertSeeHtml('fi-tab-repeater-not-scrollable')
        ->assertSeeHtml('class="fi-tabs fi-contained"');
});

it('rejects an unsupported orientation', function () {
    $component = TabRepeater::make('items')->orientation('diagonal');

    expect(fn () => $component->getOrientation())->toThrow(InvalidArgumentException::class);

    Livewire::test(TabRepeaterTestComponent::class, [
        'config' => ['orientation' => 'diagonal'],
    ]);
})->throws(ViewException::class);

it('inherits the native reorderable configuration', function () {
    $schema = Livewire::test(TabRepeaterTestComponent::class)->instance()->getSchema('form');

    $component = TabRepeater::make('items')->container($schema);

    expect($component->isReorderable())->toBeTrue()
        ->and($component->isReorderableWithDragAndDrop())->toBeTrue()
        ->and($component->isReorderableWithButtons())->toBeFalse();

    expect($component->reorderableWithDragAndDrop(false))->toBe($component)
        ->and($component->isReorderableWithDragAndDrop())->toBeFalse()
        ->and($component->isReorderableWithButtons())->toBeFalse();

    expect($component->reorderableWithDragAndDrop()->reorderableWithButtons())->toBe($component)
        ->and($component->isReorderableWithDragAndDrop())->toBeTrue()
        ->and($component->isReorderableWithButtons())->toBeTrue();

    $component->reorderable(false);

    expect($component->isReorderableWithDragAndDrop())->toBeFalse()
        ->and($component->isReorderableWithButtons())->toBeFalse();
});

it('guards unsupported repeater layouts', function () {
    $component = TabRepeater::make('items');

    expect($component->simple(null))->toBe($component)
        ->and($component->table(null))->toBe($component)
        ->and($component->collapsed(false))->toBe($component)
        ->and($component->collapsible(false))->toBe($component)
        ->and($component->persistCollapsed(false))->toBe($component)
        ->and($component->itemHeaders())->toBe($component)
        ->and($component->grid())->toBe($component)
        ->and($component->labelBetweenItems('Between'))->toBe($component);

    expect(fn () => $component->simple(TextInput::make('value')))->toThrow(BadMethodCallException::class)
        ->and(fn () => $component->table([]))->toThrow(BadMethodCallException::class)
        ->and(fn () => $component->collapsed())->toThrow(BadMethodCallException::class)
        ->and(fn () => $component->collapsible())->toThrow(BadMethodCallException::class)
        ->and(fn () => $component->persistCollapsed())->toThrow(BadMethodCallException::class)
        ->and(fn () => $component->addBetweenAction(static fn (): null => null))->toThrow(BadMethodCallException::class);
});

it('activates a newly added item', function () {
    $component = Livewire::test(TabRepeaterTestComponent::class, [
        'config' => ['defaultItems' => 1],
    ]);

    $previousKeys = array_keys($component->get('data')['items']);

    $component->callAction(TestAction::make('add')->schemaComponent('items'));

    $currentKeys = array_keys($component->get('data')['items']);
    $newKey = array_values(array_diff($currentKeys, $previousKeys))[0];

    $component->assertDispatched(
        'tab-repeater-activated',
        componentKey: 'form.items',
        itemKey: $newKey,
    );

    // The action only repaints the repeater through a partial, so the new tab
    // lives in the partial HTML rather than the main component HTML.
    expect(collect($component->effects['partials'] ?? [])->implode(''))
        ->toContain('data-tab-repeater-tab="'.$newKey.'"');
});

it('activates the cloned item', function () {
    $component = Livewire::test(TabRepeaterTestComponent::class, [
        'config' => ['defaultItems' => 1, 'cloneable' => true],
    ]);

    $existingKey = array_key_first($component->get('data')['items']);

    $component->callAction(
        TestAction::make('clone')
            ->schemaComponent('items')
            ->arguments(['item' => $existingKey]),
    );

    $currentKeys = array_keys($component->get('data')['items']);
    $newKey = array_values(array_diff($currentKeys, [$existingKey]))[0];

    $component->assertDispatched(
        'tab-repeater-activated',
        componentKey: 'form.items',
        itemKey: $newKey,
    );
});

it('activates the left neighbour after deleting an item', function () {
    $component = Livewire::test(TabRepeaterTestComponent::class, [
        'config' => ['defaultItems' => 3],
    ]);

    $keys = array_keys($component->get('data')['items']);

    $component->callAction(
        TestAction::make('delete')
            ->schemaComponent('items')
            ->arguments(['item' => $keys[1]]),
    );

    $component->assertDispatched(
        'tab-repeater-deleted',
        componentKey: 'form.items',
        activeKey: $keys[0],
        deletedKey: $keys[1],
    );
});

it('falls back to the right neighbour when the first item is deleted', function () {
    $component = Livewire::test(TabRepeaterTestComponent::class, [
        'config' => ['defaultItems' => 3],
    ]);

    $keys = array_keys($component->get('data')['items']);

    $component->callAction(
        TestAction::make('delete')
            ->schemaComponent('items')
            ->arguments(['item' => $keys[0]]),
    );

    $component->assertDispatched(
        'tab-repeater-deleted',
        componentKey: 'form.items',
        activeKey: $keys[1],
        deletedKey: $keys[0],
    );
});

it('clears the active item when the last remaining item is deleted', function () {
    $component = Livewire::test(TabRepeaterTestComponent::class, [
        'config' => ['defaultItems' => 1],
    ]);

    $key = array_key_first($component->get('data')['items']);

    $component->callAction(
        TestAction::make('delete')
            ->schemaComponent('items')
            ->arguments(['item' => $key]),
    );

    $component->assertDispatched(
        'tab-repeater-deleted',
        componentKey: 'form.items',
        activeKey: null,
        deletedKey: $key,
    );
});

it('renders move actions when reorder buttons are enabled', function () {
    Livewire::test(TabRepeaterTestComponent::class, [
        'config' => ['defaultItems' => 3, 'reorderableWithButtons' => true],
    ])
        ->assertSee('Move right')
        ->assertSee('Move left')
        ->assertDontSee('Move up')
        ->assertDontSee('Move down');
});

it('omits move actions when only drag and drop is enabled', function () {
    Livewire::test(TabRepeaterTestComponent::class, [
        'config' => ['defaultItems' => 3],
    ])
        ->assertDontSee('Move right')
        ->assertDontSee('Move left')
        ->assertSeeHtml('x-sortable-handle');
});

it('omits move actions when the repeater is not reorderable', function () {
    Livewire::test(TabRepeaterTestComponent::class, [
        'config' => ['defaultItems' => 3, 'reorderable' => false, 'reorderableWithButtons' => true],
    ])
        ->assertDontSee('Move right')
        ->assertDontSee('Move left')
        ->assertDontSeeHtml('x-sortable="x-sortable"')
        ->assertDontSeeHtml('x-sortable-handle');
});

it('omits drag and drop markup when drag and drop is disabled', function () {
    Livewire::test(TabRepeaterTestComponent::class, [
        'config' => ['defaultItems' => 2, 'reorderableWithDragAndDrop' => false],
    ])
        ->assertDontSeeHtml('x-sortable="x-sortable"')
        ->assertDontSeeHtml('x-sortable-item=')
        ->assertDontSeeHtml('x-sortable-handle')
        ->assertDontSeeHtml('data-sortable-animation-duration')
        ->assertDontSeeHtml('x-on:end.stop');
});

it('omits drag and drop markup when the component is disabled', function () {
    Livewire::test(TabRepeaterTestComponent::class, [
        'config' => ['defaultItems' => 2, 'disabled' => true],
    ])
        ->assertDontSeeHtml('x-sortable="x-sortable"')
        ->assertDontSeeHtml('x-sortable-handle')
        ->assertDontSeeHtml('x-on:end.stop')
        ->assertDontSeeHtml('class="fi-tab-repeater-tab-actions"')
        ->assertDontSeeHtml('class="fi-tab-repeater-add"');
});

it('renders item actions inside each tab and the add action after the last tab', function () {
    $component = Livewire::test(TabRepeaterTestComponent::class, [
        'config' => ['defaultItems' => 2, 'cloneable' => true],
        'data' => [
            'items' => [
                ['name' => 'Default'],
                ['name' => 'Premium'],
            ],
        ],
    ]);

    $html = $component->html();
    $keys = array_keys($component->get('data')['items']);

    expect(substr_count($html, 'class="fi-tab-repeater-tab-actions"'))->toBe(2)
        ->and(substr_count($html, 'class="fi-tab-repeater-tab-activator"'))->toBe(2)
        ->and(substr_count($html, 'class="fi-tab-repeater-add"'))->toBe(1)
        ->and(substr_count($html, 'aria-label="Clone"'))->toBe(2)
        ->and(substr_count($html, 'aria-label="Delete"'))->toBe(2)
        ->and($html)->not->toContain('class="fi-tab-repeater-actions"')
        ->and(strpos($html, 'class="fi-tab-repeater-add"'))
        ->toBeGreaterThan(strrpos($html, 'data-tab-repeater-tab="'.$keys[1].'"'));
});

it('reorders items with the native reorder action', function () {
    $component = Livewire::test(TabRepeaterTestComponent::class, [
        'config' => ['defaultItems' => 3],
    ]);

    $keys = array_keys($component->get('data')['items']);
    $reorderedKeys = array_reverse($keys);

    $component->callAction(
        TestAction::make('reorder')
            ->schemaComponent('items')
            ->arguments(['items' => $reorderedKeys]),
    );

    expect(array_keys($component->get('data')['items']))->toBe($reorderedKeys);

    $component
        ->assertNotDispatched('tab-repeater-activated')
        ->assertNotDispatched('tab-repeater-deleted');
});

it('renders extra item actions after the built in ones', function () {
    Livewire::test(TabRepeaterTestComponent::class, [
        'config' => ['defaultItems' => 1, 'extraItemActions' => true],
    ])->assertSee('Extra action');
});

it('hides the add action when max items is reached', function () {
    Livewire::test(TabRepeaterTestComponent::class, [
        'config' => ['defaultItems' => 2, 'maxItems' => 2],
    ])->assertDontSee('Add to items');

    Livewire::test(TabRepeaterTestComponent::class, [
        'config' => ['defaultItems' => 1, 'maxItems' => 2],
    ])->assertSee('Add to items');
});

it('hides the delete action when min items is reached', function () {
    Livewire::test(TabRepeaterTestComponent::class, [
        'config' => ['defaultItems' => 2, 'minItems' => 2],
    ])->assertDontSee('Delete');

    Livewire::test(TabRepeaterTestComponent::class, [
        'config' => ['defaultItems' => 3, 'minItems' => 2],
    ])->assertSee('Delete');
});

it('numbers item labels when item numbers are enabled', function () {
    Livewire::test(TabRepeaterTestComponent::class, [
        'config' => ['defaultItems' => 2, 'itemNumbers' => true],
        'data' => [
            'items' => [
                ['name' => 'Default'],
                ['name' => 'Premium'],
            ],
        ],
    ])
        ->assertSee('1. Default')
        ->assertSee('2. Premium');
});

it('renders the empty state with an add button', function () {
    Livewire::test(TabRepeaterTestComponent::class, [
        'config' => ['defaultItems' => 0],
    ])
        ->assertSee('No items yet')
        ->assertSee('Add to items')
        ->assertDontSeeHtml('data-tab-repeater-tab=');
});

it('switches to the first invalid tab and shows an error badge', function () {
    $component = Livewire::test(TabRepeaterTestComponent::class, [
        'config' => ['defaultItems' => 2],
        'data' => [
            'items' => [
                ['name' => 'Default'],
                ['name' => ''],
            ],
        ],
    ]);

    $keys = array_keys($component->get('data')['items']);

    $component
        ->call('save')
        ->assertHasErrors([
            "data.items.{$keys[1]}.name" => 'required',
        ])
        ->assertSeeHtml('data-active-key="'.$keys[1].'"')
        ->assertSeeHtml('fi-badge');
});
