<?php

use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Jegex\FilamentTabRepeater\Tests\Fixtures\Models\Product;
use Jegex\FilamentTabRepeater\Tests\Fixtures\Models\ProductVariant;
use Jegex\FilamentTabRepeater\Tests\Fixtures\TabRepeaterRelationshipTestComponent;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('hydrates relationship items in the configured order', function () {
    $product = Product::query()->create([
        'name' => 'Test product',
    ]);

    $second = ProductVariant::query()->create([
        'product_id' => $product->id,
        'name' => 'Second variant',
        'sort' => 2,
    ]);

    $first = ProductVariant::query()->create([
        'product_id' => $product->id,
        'name' => 'First variant',
        'sort' => 1,
    ]);

    $component = Livewire::test(TabRepeaterRelationshipTestComponent::class, [
        'recordId' => $product->id,
    ]);

    $items = $component->get('data')['variants'];

    expect(array_keys($items))->toBe([
        "record-{$first->id}",
        "record-{$second->id}",
    ]);

    $component
        ->assertSee('First variant')
        ->assertSee('Second variant');
});

it('saves new relationship items in their sort order', function () {
    $product = Product::query()->create([
        'name' => 'Test product',
    ]);

    $component = Livewire::test(TabRepeaterRelationshipTestComponent::class, [
        'recordId' => $product->id,
    ]);

    $component->callAction(TestAction::make('add')->schemaComponent('variants'));

    $newKey = array_key_first($component->get('data')['variants']);

    $component
        ->set("data.variants.{$newKey}.name", 'Fresh variant')
        ->call('save')
        ->assertHasNoErrors();

    expect($product->variants()->pluck('name')->all())->toBe(['Fresh variant']);
});
