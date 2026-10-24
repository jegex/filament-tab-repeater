@php
    $idPrefix = 'tab-repeater-' . preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) $componentKey);
@endphp

@once
    <style>
        .fi-tab-repeater {
            --fi-tab-repeater-add-border-color: var(--gray-200);
            --fi-tab-repeater-label-width: 12rem;
            --fi-tab-repeater-rail-width: 15rem;
            --fi-tab-repeater-rail-max-height: 24rem;
            row-gap: 0;
        }

        .fi-tab-repeater-tabs {
            flex: 1 1 auto;
            min-width: 0;
        }

        .fi-tab-repeater-not-scrollable .fi-tabs {
            flex-wrap: wrap;
            overflow-x: visible;
        }

        .fi-tab-repeater-not-scrollable .fi-tab-repeater-rail .fi-tabs {
            max-height: none;
            overflow-y: visible;
        }

        /*
         * Native non-contained tabs use mx-auto on the nav. The nav needs a
         * flex parent for those auto margins to center it at its content width.
         */
        .fi-tab-repeater:not(.fi-contained) .fi-tab-repeater-tabs {
            display: flex;
        }

        .fi-tabs-item.fi-tab-repeater-tab {
            padding: 0;
            gap: 0;
        }

        .fi-tab-repeater-tab-activator {
            display: flex;
            flex: 1 1 auto;
            align-items: center;
            gap: 0.5rem;
            min-width: 0;
            padding: 0.5rem 0.75rem;
            border: 0;
            border-radius: inherit;
            background-color: transparent;
            color: inherit;
            font: inherit;
            text-align: start;
            cursor: pointer;
            outline: none;
        }

        .fi-tab-repeater-tab-activator:focus-visible {
            box-shadow: inset 0 0 0 2px var(--primary-600);
        }

        .fi-tab-repeater-tab-label-wrapper {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 0.5rem;
        }

        .fi-tab-repeater-tab-label {
            display: inline-block;
            min-width: 0;
        }

        .fi-tab-repeater-tab-label-truncated {
            max-width: var(--fi-tab-repeater-label-width);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .fi-tab-repeater.fi-vertical .fi-tab-repeater-tab-label-truncated {
            max-width: 10rem;
        }

        .fi-tab-repeater-tab-label-wrapper .fi-badge {
            flex-shrink: 0;
        }

        .fi-tab-repeater-drag-handle {
            display: inline-flex;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            cursor: grab;
            color: var(--gray-400);
            touch-action: none;
            user-select: none;
        }

        .fi-tab-repeater-drag-handle:hover {
            color: var(--gray-600);
        }

        .fi-tab-repeater-drag-handle:active {
            cursor: grabbing;
        }

        .fi-tab-repeater-drag-handle .fi-tab-repeater-drag-handle-icon {
            display: block;
            width: 0.625rem;
            height: 1rem;
        }

        .dark .fi-tab-repeater-drag-handle {
            color: var(--gray-500);
        }

        .dark .fi-tab-repeater-drag-handle:hover {
            color: var(--gray-300);
        }

        .fi-tab-repeater-tab-actions {
            display: flex;
            flex-shrink: 0;
            align-items: center;
            gap: 0.125rem;
            padding-inline-end: 0.375rem;
        }

        .fi-tab-repeater-tab-actions .fi-icon-btn {
            margin: 0;
        }

        .fi-tab-repeater-add {
            display: flex;
            flex-shrink: 0;
            align-items: center;
            margin-inline-start: 0.25rem;
            padding-inline-start: 0.25rem;
            border-inline-start: 1px solid var(--fi-tab-repeater-add-border-color);
        }

        .fi-tab-repeater-add .fi-icon-btn {
            margin: 0;
        }

        .fi-tabs.fi-vertical .fi-tab-repeater-add {
            margin-inline-start: 0;
            margin-block-start: 0.25rem;
            padding-inline-start: 0;
            padding-block-start: 0.25rem;
            border-inline-start: 0;
            border-block-start: 1px solid var(--fi-tab-repeater-add-border-color);
        }

        .fi-tab-repeater.fi-vertical {
            align-items: flex-start;
        }

        .fi-tab-repeater-rail {
            display: flex;
            flex: 0 0 var(--fi-tab-repeater-rail-width);
            flex-direction: column;
            gap: 0.5rem;
            width: var(--fi-tab-repeater-rail-width);
            max-width: 100%;
        }

        .fi-tab-repeater-rail .fi-tabs {
            max-height: var(--fi-tab-repeater-rail-max-height);
        }

        /*
         * Filament's contained panel rule is a descendant selector, so a
         * non-contained repeater nested inside a contained schema Tabs would
         * otherwise inherit its p-6 / mt-0. Restore the native non-contained
         * spacing for the repeater's own panel only.
         */
        .fi-tab-repeater:not(.fi-contained) > .fi-sc-tabs-tab.fi-active {
            margin-block-start: 1.5rem;
            padding: 0;
        }

        .fi-tab-repeater.fi-vertical:not(.fi-contained) > .fi-sc-tabs-tab.fi-active {
            margin-block-start: 0;
            margin-inline-start: 1.5rem;
            padding: 0;
        }

        .fi-tab-repeater-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
            padding: 2.5rem 1.5rem;
            border: 1px dashed var(--gray-300);
            border-radius: var(--radius-xl, 0.75rem);
            background-color: color-mix(in oklab, var(--gray-50) 50%, transparent);
            text-align: center;
        }

        .fi-tab-repeater-empty-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 9999px;
            background-color: white;
            box-shadow: 0 0 0 1px var(--gray-200);
            color: var(--gray-400);
        }

        .fi-tab-repeater-empty-icon .fi-icon {
            width: 1.25rem;
            height: 1.25rem;
        }

        .fi-tab-repeater-empty-title {
            font-size: 0.875rem;
            font-weight: 500;
            line-height: 1.25rem;
            color: var(--gray-950);
        }

        .dark .fi-tab-repeater {
            --fi-tab-repeater-add-border-color: color-mix(in oklab, white 10%, transparent);
        }

        .dark .fi-tab-repeater-empty {
            border-color: color-mix(in oklab, white 10%, transparent);
            background-color: color-mix(in oklab, white 5%, transparent);
        }

        .dark .fi-tab-repeater-empty-icon {
            background-color: var(--gray-900);
            box-shadow: 0 0 0 1px color-mix(in oklab, white 10%, transparent);
        }

        .dark .fi-tab-repeater-empty-title {
            color: white;
        }

        @media (max-width: 40rem) {
            .fi-tab-repeater.fi-vertical {
                flex-direction: column;
            }

            .fi-tab-repeater.fi-vertical .fi-tab-repeater-rail {
                flex-basis: auto;
                width: 100%;
            }

            .fi-tab-repeater.fi-vertical .fi-tabs.fi-contained {
                border-inline-end-width: 0;
                border-block-end-width: 1px;
            }

            .fi-tab-repeater.fi-vertical:not(.fi-contained) > .fi-sc-tabs-tab.fi-active {
                margin-inline-start: 0;
                margin-block-start: 1.5rem;
            }

            .fi-tab-repeater.fi-vertical.fi-contained > .fi-sc-tabs-tab.fi-active {
                margin-inline-start: 0;
            }
        }
    </style>
@endonce

<div
    @class([
        'fi-sc-tabs',
        'fi-tab-repeater',
        'fi-contained' => $isContained && ($itemCount > 0),
        'fi-vertical' => $isVertical,
        'fi-tab-repeater-not-scrollable' => ! $isScrollable,
    ])
    wire:ignore.self
    data-tab-repeater="{{ $componentKey }}"
    data-active-key="{{ $activeKey }}"
    x-data="{
        active: @js($activeKey === null ? null : (string) $activeKey),
        componentKey: @js((string) $componentKey),
        activate(itemKey) {
            this.active = String(itemKey);

            this.$nextTick(() => {
                const tab = Array.from(this.$el.querySelectorAll('[data-tab-repeater-tab]'))
                    .find((element) => element.dataset.tabRepeaterTab === this.active);

                tab?.scrollIntoView({ block: 'nearest', inline: 'nearest' });
            });
        },
    }"
    x-on:tab-repeater-activated.window="if (String($event.detail.componentKey) === componentKey) activate($event.detail.itemKey)"
    x-on:tab-repeater-deleted.window="
        if (String($event.detail.componentKey) !== componentKey) return;

        if (String($event.detail.deletedKey) !== active) return;

        active = $event.detail.activeKey === null ? null : String($event.detail.activeKey);
    "
>
    @if ($itemCount > 0)
        @if ($isVertical)
            <div class="fi-tab-repeater-rail">
                @include('filament-tab-repeater::filament.forms.components.partials.tabs')
            </div>
        @else
            @include('filament-tab-repeater::filament.forms.components.partials.tabs')
        @endif
    @endif

    @if ($itemCount === 0)
        <div class="fi-tab-repeater-empty">
            <span class="fi-tab-repeater-empty-icon">
                <x-filament::icon icon="heroicon-o-rectangle-stack" />
            </span>

            <p class="fi-tab-repeater-empty-title">
                {{ __('No items yet') }}
            </p>

            @if ($isAddable)
                {!! $emptyAddAction->toHtml() !!}
            @endif
        </div>
    @endif

    @foreach ($items as $itemKey => $item)
        @php
            $index = $loop->index;
            $keyString = (string) $itemKey;
        @endphp

        <div
            wire:key="{{ $item->getLivewireKey() }}.tab-repeater-panel"
            @class([
                'fi-sc-tabs-tab',
                'fi-tab-repeater-panel',
            ])
            role="tabpanel"
            id="{{ $idPrefix }}-panel-{{ $index }}"
            aria-labelledby="{{ $idPrefix }}-tab-{{ $index }}"
            tabindex="0"
            x-bind:class="{ 'fi-active': active === @js($keyString) }"
            x-on:expand="activate(@js($keyString))"
        >
            {!! $item->toHtml() !!}
        </div>
    @endforeach
</div>
