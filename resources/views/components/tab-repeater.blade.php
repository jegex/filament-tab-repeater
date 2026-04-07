@php
    use Filament\Support\Enums\Alignment;

    $fieldWrapperView = $getFieldWrapperView();
    $items = $getItems();
    $addAction = $getAction($getAddActionName());
    $cloneAction = $getAction($getCloneActionName());
    $deleteAction = $getAction($getDeleteActionName());
    $moveDownAction = $getAction($getMoveDownActionName());
    $moveUpAction = $getAction($getMoveUpActionName());
    $extraItemActions = $getExtraItemActions();

    $hasItemNumbers = $hasItemNumbers();
    $isAddable = $isAddable();
    $isCloneable = $isCloneable();
    $isDeletable = $isDeletable();
    $isReorderableWithButtons = $isReorderableWithButtons();

    $key = $getKey();
    $statePath = $getStatePath();

    // Tabs
    $isContained = $isContained();
    $isVertical = $isVertical();
    $isScrollable = $isScrollable();
    $getActiveTab = $getActiveTab();
@endphp

<x-dynamic-component :component="$fieldWrapperView" :field="$field">
    <div
        {{
            $attributes
                ->merge($getExtraAttributes(), escape: false)
                ->class([
                    'fi-tab-repeater',
                    'fi-sc-tabs',
                    'fi-contained' => $isContained,
                    'fi-vertical' => $isVertical,
                ])
        }}
        x-data="{
            activeTab: '',
            isScrollable: @js($isScrollable)
        }"
        x-init="
            const keys = Object.keys(@js($items));
            activeTab = keys[keys.length - 1] ?? '';
        "
    >

        <x-filament::tabs
            :contained="$isContained"
            :vertical="$isVertical"
            x-bind:style="!isScrollable && {'flex-wrap': 'wrap'}"
            x-sortable
        >
            @foreach ($items as $itemKey => $item)
                @php
                    $itemLabel = $getItemLabel($itemKey);
                    $visibleExtraItemActions = array_filter(
                        $extraItemActions,
                        fn ($action): bool => $action(['item' => $itemKey])->isVisible(),
                    );
                    $cloneAction = $cloneAction(['item' => $itemKey]);
                    $cloneActionIsVisible = $isCloneable && $cloneAction->isVisible();
                    $deleteAction = $deleteAction(['item' => $itemKey]);
                    $deleteActionIsVisible = $isDeletable && $deleteAction->isVisible();
                    $moveUpAction = $moveUpAction(['item' => $itemKey])->disabled($loop->first);
                    $moveUpActionIsVisible = $isReorderableWithButtons && $moveUpAction->isVisible();
                    $moveDownAction = $moveDownAction(['item' => $itemKey])->disabled($loop->last);
                    $moveDownActionIsVisible = $isReorderableWithButtons && $moveDownAction->isVisible();
                    $index = $loop->index;
                @endphp
                <x-filament::tabs.item
                    tag="div"
                    :active="$itemKey === $getActiveTab"
                    x-bind:active="activeTab === '{{ $itemKey }}'"
                    x-on:click="activeTab = '{{ $itemKey }}'"
                    alpineActive="activeTab === '{{ $itemKey }}'"
                    wire:ignore.self
                    wire:key="{{ $item->getLivewireKey() }}.item"
                    x-sortable-item="{{ $itemKey }}"
                    x-sortable-handle
                >
                    @if (filled($itemLabel))
                        <span>
                            {{ $itemLabel }}
                            @if ($hasItemNumbers)
                                {{ $loop->iteration }}
                            @endif
                        </span>
                    @else
                        <span>
                            {{ $loop->iteration }}
                        </span>
                    @endif
                    @if ($moveUpActionIsVisible || $moveDownActionIsVisible || $cloneActionIsVisible || $deleteActionIsVisible || $visibleExtraItemActions)
                        <span
                            class="flex items-center gap-1 ms-2"
                            x-on:click.stop
                        >
                            @foreach ($visibleExtraItemActions as $extraItemAction)
                                {{ $extraItemAction(['item' => $itemKey]) }}
                            @endforeach

                            @if ($moveUpActionIsVisible || $moveDownActionIsVisible)
                                {{ $moveUpAction }}
                                {{ $moveDownAction }}
                            @endif

                            @if ($cloneActionIsVisible)
                                {{ $cloneAction }}
                            @endif

                            @if ($deleteActionIsVisible)
                                {{ $deleteAction }}
                            @endif
                        </span>
                    @endif
                </x-filament::tabs.item>
            @endforeach
            @if($isAddable)
                <x-filament::tabs.item tag="div">
                    {{ $addAction }}
                </x-filament::tabs.item>
            @endif
        </x-filament::tabs>

        @forelse ($items as $itemKey => $item)
            <div
                wire:ignore.self
                wire:key="{{ $item->getLivewireKey() }}.item"
                x-bind:class="{'fi-active': activeTab === @js($itemKey)}"
                class="fi-sc-tabs-tab"
                role="tabpanel"
                x-show="activeTab === @js($itemKey)"
                x-cloak
            >
                {{ $item }}
            </div>
        @empty
            <div class="fi-sc-tabs-tab fi-active">
                <x-filament::empty-state icon="heroicon-o-square-2-stack">
                    <x-slot name="heading">
                        empty {{ $getName() }}
                    </x-slot>
                </x-filament::empty-state>
            </div>
        @endforelse
    </div>
</x-dynamic-component>
