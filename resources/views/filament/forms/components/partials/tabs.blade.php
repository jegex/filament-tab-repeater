@php
    // The `x-filament::tabs` slot binds its own `$component` variable, so the
    // repeater instance has to be captured before the slot is entered.
    $tabRepeater = $component;

    $tabsLabel = $tabRepeater->getLabel();

    if ((! is_string($tabsLabel)) || blank($tabsLabel)) {
        $tabsLabel = __('Items');
    }

    $schemaComponent = \Illuminate\Support\Js::from((string) $componentKey);

    $reorderExpression = $isDragEnabled
        ? '$wire.mountAction(\'reorder\', { items: $event.target.sortable.toArray() }, { schemaComponent: ' . $schemaComponent . ' })'
        : null;
@endphp

<div class="fi-tab-repeater-tabs">
    <x-filament::tabs
        :label="$tabsLabel"
        :contained="$isContained"
        :vertical="$isVertical"
        role="tablist"
        :x-sortable="$isDragEnabled ? true : null"
        :data-sortable-animation-duration="$isDragEnabled ? $reorderAnimationDuration : null"
        :x-on:end.stop="$reorderExpression"
    >
        @foreach ($items as $itemKey => $item)
            @php
                $index = $loop->index;
                $keyString = (string) $itemKey;
                $errorCount = $errorCounts[$itemKey] ?? 0;
                $actions = $itemActions[$itemKey] ?? ['actions' => [], 'extras' => []];
                $isInitiallyActive = $keyString === (string) $activeKey;

                $itemLabel = $tabRepeater->getItemLabel($keyString, $index);

                if (blank($itemLabel)) {
                    $itemLabel = __('Item :number', ['number' => $index + 1]);
                } elseif ($tabRepeater->hasItemNumbers()) {
                    $itemLabel = ($index + 1) . '. ' . $itemLabel;
                }

                $labelText = trim(strip_tags($itemLabel instanceof \Illuminate\Contracts\Support\Htmlable ? $itemLabel->toHtml() : (string) $itemLabel));

                // Blade directives are not compiled inside component attributes,
                // so the JS value is computed before the component tag.
                $keyJson = \Illuminate\Support\Js::from($keyString);
            @endphp

            <div
                @class([
                    'fi-tabs-item',
                    'fi-tab-repeater-tab',
                ])
                x-bind:class="{ 'fi-active': active === {{ $keyJson }} }"
                data-tab-repeater-tab="{{ $keyString }}"
                wire:key="{{ $item->getLivewireKey() }}.tab"
                @if ($isDragEnabled)
                    x-sortable-item="{{ $keyString }}"
                @endif
            >
                <button
                    type="button"
                    role="tab"
                    aria-selected="{{ $isInitiallyActive ? 'true' : 'false' }}"
                    x-bind:aria-selected="active === {{ $keyJson }}"
                    x-on:click="activate({{ $keyJson }})"
                    id="{{ $idPrefix }}-tab-{{ $index }}"
                    aria-controls="{{ $idPrefix }}-panel-{{ $index }}"
                    class="fi-tab-repeater-tab-activator"
                >
                    <span class="fi-tab-repeater-tab-label-wrapper">
                        @if ($isDragEnabled)
                            <span
                                class="fi-tab-repeater-drag-handle"
                                x-sortable-handle
                                title="{{ __('filament-forms::components.repeater.actions.reorder.label') }}"
                                aria-hidden="true"
                            >
                                <svg
                                    class="fi-tab-repeater-drag-handle-icon"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 10 16"
                                    fill="currentColor"
                                    aria-hidden="true"
                                >
                                    <circle cx="2.5" cy="3" r="1.25" />
                                    <circle cx="7.5" cy="3" r="1.25" />
                                    <circle cx="2.5" cy="8" r="1.25" />
                                    <circle cx="7.5" cy="8" r="1.25" />
                                    <circle cx="2.5" cy="13" r="1.25" />
                                    <circle cx="7.5" cy="13" r="1.25" />
                                </svg>
                            </span>
                        @endif

                        <span
                            @class([
                                'fi-tabs-item-label',
                                'fi-tab-repeater-tab-label',
                                'fi-tab-repeater-tab-label-truncated' => $tabRepeater->isItemLabelTruncated(),
                            ])
                            title="{{ $labelText }}"
                        >{{ $labelText }}</span>

                        @if ($errorCount > 0)
                            <x-filament::badge color="danger" size="sm">
                                {{ $errorCount }}
                            </x-filament::badge>
                        @endif
                    </span>
                </button>

                @if (filled($actions['actions']) || filled($actions['extras']))
                    <div class="fi-tab-repeater-tab-actions">
                        @foreach ($actions['actions'] as $action)
                            {!! $action->toHtml() !!}
                        @endforeach

                        @if (filled($actions['extras']))
                            <x-filament::dropdown placement="bottom-end" shift>
                                <x-slot name="trigger">
                                    <x-filament::icon-button
                                        icon="heroicon-m-ellipsis-horizontal"
                                        color="gray"
                                        size="sm"
                                        :label="__('Item actions')"
                                    />
                                </x-slot>

                                <x-filament::dropdown.list>
                                    @foreach ($actions['extras'] as $action)
                                        {!! $action->toHtml() !!}
                                    @endforeach
                                </x-filament::dropdown.list>
                            </x-filament::dropdown>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach

        @if ($isAddable)
            <span class="fi-tab-repeater-add">
                {!! $addIconAction->toHtml() !!}
            </span>
        @endif
    </x-filament::tabs>
</div>
