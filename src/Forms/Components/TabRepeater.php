<?php

namespace Jegex\FilamentTabRepeater\Forms\Components;

use BadMethodCallException;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;
use Filament\Support\Concerns\CanBeContained;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\ViewErrorBag;
use InvalidArgumentException;
use LogicException;

/**
 * A Repeater that renders one tab per item.
 *
 * State, hydration, dehydration, validation and relationships stay owned by
 * {@see Repeater}. This component only replaces the item presentation.
 */
class TabRepeater extends Repeater
{
    use CanBeContained;

    protected string|Closure $orientation = 'horizontal';

    protected bool|Closure $isScrollable = true;

    protected bool $isHydratingState = false;

    protected string|int|null $pendingActivatedItemKey = null;

    protected bool $hasPendingDeletion = false;

    protected string|int|null $pendingDeletionFallbackKey = null;

    protected string|int|null $pendingDeletionKey = null;

    public function orientation(string|Closure $orientation = 'horizontal'): static
    {
        $this->orientation = $orientation;

        return $this;
    }

    public function vertical(bool|Closure $condition = true): static
    {
        $this->orientation = $condition instanceof Closure
            ? static fn (TabRepeater $component): string => $component->evaluate($condition) ? 'vertical' : 'horizontal'
            : ($condition ? 'vertical' : 'horizontal');

        return $this;
    }

    public function horizontal(bool|Closure $condition = true): static
    {
        $this->orientation = $condition instanceof Closure
            ? static fn (TabRepeater $component): string => $component->evaluate($condition) ? 'horizontal' : 'vertical'
            : ($condition ? 'horizontal' : 'vertical');

        return $this;
    }

    public function getOrientation(): string
    {
        $orientation = $this->evaluate($this->orientation);

        if (! in_array($orientation, ['horizontal', 'vertical'], true)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid TabRepeater orientation [%s]. Supported orientations are [horizontal] and [vertical].',
                is_scalar($orientation) ? (string) $orientation : get_debug_type($orientation),
            ));
        }

        return $orientation;
    }

    public function isVertical(): bool
    {
        return $this->getOrientation() === 'vertical';
    }

    public function scrollable(bool|Closure $condition = true): static
    {
        $this->isScrollable = $condition;

        return $this;
    }

    public function isScrollable(): bool
    {
        return (bool) $this->evaluate($this->isScrollable);
    }

    public function getActiveTab(): string|int|null
    {
        $items = $this->getItems();

        return $this->resolveActiveKey($items, $this->getItemErrorCounts($items));
    }

    public function simple(Field|Closure|null $field = null): static
    {
        if ($field === null) {
            return $this;
        }

        throw new BadMethodCallException('TabRepeater does not support the simple repeater layout. Use a regular Repeater instead.');
    }

    /**
     * @param  array<mixed> | Closure | null  $columns
     */
    public function table(array|Closure|null $columns = null): static
    {
        if ($columns === null) {
            return $this;
        }

        throw new BadMethodCallException('TabRepeater does not support the table repeater layout. Use a regular Repeater instead.');
    }

    public function collapsed(bool|Closure $condition = true, bool $shouldMakeComponentCollapsible = true): static
    {
        if ($this->evaluate($condition)) {
            throw new BadMethodCallException('TabRepeater does not support collapsing items. Every item is a tab instead.');
        }

        return $this;
    }

    public function collapsible(bool|Closure|null $condition = true): static
    {
        if ($this->evaluate($condition)) {
            throw new BadMethodCallException('TabRepeater does not support collapsible items. Every item is a tab instead.');
        }

        return $this;
    }

    public function persistCollapsed(bool|Closure $condition = true): static
    {
        if ($this->evaluate($condition)) {
            throw new BadMethodCallException('TabRepeater does not support persisting collapsed items.');
        }

        return $this;
    }

    public function addBetweenAction(?Closure $callback = null): static
    {
        if ($callback !== null) {
            throw new BadMethodCallException('TabRepeater does not support an add-between action.');
        }

        return $this;
    }

    public function itemHeaders(bool|Closure $condition = true): static
    {
        return $this;
    }

    public function labelBetweenItems(string|Closure|null $label): static
    {
        return $this;
    }

    public function grid(array|int|Closure|null $columns = 2): static
    {
        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateUpdated(static function (TabRepeater $component): void {
            $component->dispatchTabChangeEvents();
        });
    }

    public function hydrateState(?array &$hydratedDefaultState, bool $shouldCallHydrationHooks = true, bool $shouldApplyStateCasts = true, array &$appliedStateCastPaths = []): void
    {
        $this->isHydratingState = true;

        $this->pendingActivatedItemKey = null;
        $this->hasPendingDeletion = false;
        $this->pendingDeletionFallbackKey = null;
        $this->pendingDeletionKey = null;

        try {
            parent::hydrateState($hydratedDefaultState, $shouldCallHydrationHooks, $shouldApplyStateCasts, $appliedStateCastPaths);
        } finally {
            $this->isHydratingState = false;
        }
    }

    public function hydrateStatePartially(array $statePaths, bool $shouldCallHydrationHooks = true): void
    {
        $this->isHydratingState = true;

        try {
            parent::hydrateStatePartially($statePaths, $shouldCallHydrationHooks);
        } finally {
            $this->isHydratingState = false;
        }
    }

    public function rawState(mixed $state): static
    {
        if ($this->isHydratingState) {
            return parent::rawState($state);
        }

        $previousKeys = $this->getItemKeys($this->getRawState());

        parent::rawState($state);

        $currentKeys = $this->getItemKeys($this->getRawState());

        if ($previousKeys === $currentKeys) {
            return $this;
        }

        $addedKeys = array_values(array_diff($currentKeys, $previousKeys));
        $removedKeys = array_values(array_diff($previousKeys, $currentKeys));

        if (count($addedKeys) === 1 && count($removedKeys) === 0) {
            $this->pendingActivatedItemKey = $addedKeys[0];

            return $this;
        }

        if (count($removedKeys) === 1 && count($addedKeys) === 0) {
            $removedIndex = array_search($removedKeys[0], $previousKeys, true);

            $fallbackKey = is_int($removedIndex)
                ? ($previousKeys[$removedIndex - 1] ?? $previousKeys[$removedIndex + 1] ?? null)
                : null;

            $this->hasPendingDeletion = true;
            $this->pendingDeletionKey = $removedKeys[0];
            $this->pendingDeletionFallbackKey = ($fallbackKey !== null && in_array($fallbackKey, $currentKeys, true))
                ? $fallbackKey
                : null;
        }

        return $this;
    }

    /**
     * @return array<int, string | int>
     */
    protected function getItemKeys(mixed $state): array
    {
        if ($state instanceof Arrayable) {
            $state = $state->toArray();
        }

        return is_array($state) ? array_keys($state) : [];
    }

    protected function dispatchTabChangeEvents(): void
    {
        if ($this->pendingActivatedItemKey !== null) {
            $itemKey = $this->pendingActivatedItemKey;
            $this->pendingActivatedItemKey = null;

            $this->getLivewire()->dispatch(
                'tab-repeater-activated',
                componentKey: $this->getKey(),
                itemKey: $itemKey,
            );
        }

        if (! $this->hasPendingDeletion) {
            return;
        }

        $activeKey = $this->pendingDeletionFallbackKey;
        $deletedKey = $this->pendingDeletionKey;

        $this->hasPendingDeletion = false;
        $this->pendingDeletionFallbackKey = null;
        $this->pendingDeletionKey = null;

        $this->getLivewire()->dispatch(
            'tab-repeater-deleted',
            componentKey: $this->getKey(),
            activeKey: $activeKey,
            deletedKey: $deletedKey,
        );
    }

    public function toEmbeddedHtml(): string
    {
        $html = view('filament-tab-repeater::filament.forms.components.tab-repeater', $this->getTabRepeaterViewData())->render();

        return $this->wrapEmbeddedHtml($html, labelTag: 'div');
    }

    /**
     * @return array<string, mixed>
     */
    protected function getTabRepeaterViewData(): array
    {
        $items = $this->getItems();
        $errorCounts = $this->getItemErrorCounts($items);
        $itemCount = count($items);

        $activeKey = $this->resolveActiveKey($items, $errorCounts);

        $addAction = $this->getAction($this->getAddActionName());

        if ($addAction === null) {
            throw new LogicException('TabRepeater requires a registered add action.');
        }

        $addAction = clone $addAction;
        $addActionIsVisible = $this->isAddable() && $addAction->isVisible();

        $addIconAction = (clone $addAction)
            ->iconButton()
            ->icon(Heroicon::Plus);

        $emptyAddAction = (clone $addAction)
            ->icon(Heroicon::Plus);

        $itemActions = [];

        if (! $this->isDisabled()) {
            $index = 0;

            foreach ($items as $itemKey => $item) {
                $itemActions[$itemKey] = $this->getItemActions($itemKey, $index, $itemCount);

                $index++;
            }
        }

        return [
            'component' => $this,
            'items' => $items,
            'itemKeys' => array_keys($items),
            'itemCount' => $itemCount,
            'itemActions' => $itemActions,
            'errorCounts' => $errorCounts,
            'activeKey' => $activeKey,
            'componentKey' => $this->getKey(),
            'isVertical' => $this->isVertical(),
            'isContained' => $this->isContained(),
            'isScrollable' => $this->isScrollable(),
            'isDisabled' => $this->isDisabled(),
            'isDragEnabled' => (! $this->isDisabled()) && $this->isReorderableWithDragAndDrop(),
            'reorderAnimationDuration' => $this->getReorderAnimationDuration(),
            'isAddable' => $addActionIsVisible,
            'addIconAction' => $addIconAction,
            'emptyAddAction' => $emptyAddAction,
        ];
    }

    /**
     * @param  array<string | int, Schema>  $items
     * @param  array<string | int, int>  $errorCounts
     */
    protected function resolveActiveKey(array $items, array $errorCounts): string|int|null
    {
        foreach ($items as $itemKey => $item) {
            if (($errorCounts[$itemKey] ?? 0) > 0) {
                return $itemKey;
            }
        }

        return array_key_first($items);
    }

    /**
     * @param  array<string | int, Schema>  $items
     * @return array<string | int, int>
     */
    protected function getItemErrorCounts(array $items): array
    {
        $errors = view()->shared('errors');

        if (! $errors instanceof ViewErrorBag) {
            return [];
        }

        $messages = $errors->getBag('default')->getMessages();
        $counts = [];

        foreach ($messages as $messageKey => $keyMessages) {
            foreach ($items as $itemKey => $item) {
                $itemStatePath = $item->getStatePath();

                if (blank($itemStatePath) || (! str_starts_with($messageKey, "{$itemStatePath}."))) {
                    continue;
                }

                $counts[$itemKey] = ($counts[$itemKey] ?? 0) + count($keyMessages);

                break;
            }
        }

        return $counts;
    }

    /**
     * @return array{actions: array<Action>, extras: array<Action>}
     */
    protected function getItemActions(string|int $itemKey, int $index, int $itemCount): array
    {
        $actions = [];
        $isHorizontal = ! $this->isVertical();

        if ($this->isCloneable() && ($cloneAction = $this->getAction($this->getCloneActionName()))) {
            $cloneAction = $cloneAction(['item' => $itemKey]);

            if ($cloneAction->isVisible()) {
                $actions[] = $cloneAction;
            }
        }

        if ($this->isReorderableWithButtons() && ($index > 0) && ($moveUpAction = $this->getAction($this->getMoveUpActionName()))) {
            $moveUpAction = $moveUpAction(['item' => $itemKey])
                ->label($isHorizontal ? __('Move left') : __('filament-forms::components.repeater.actions.move_up.label'))
                ->icon($isHorizontal ? Heroicon::ArrowLeft : Heroicon::ArrowUp);

            if ($moveUpAction->isVisible()) {
                $actions[] = $moveUpAction;
            }
        }

        if ($this->isReorderableWithButtons() && ($index < ($itemCount - 1)) && ($moveDownAction = $this->getAction($this->getMoveDownActionName()))) {
            $moveDownAction = $moveDownAction(['item' => $itemKey])
                ->label($isHorizontal ? __('Move right') : __('filament-forms::components.repeater.actions.move_down.label'))
                ->icon($isHorizontal ? Heroicon::ArrowRight : Heroicon::ArrowDown);

            if ($moveDownAction->isVisible()) {
                $actions[] = $moveDownAction;
            }
        }

        if (
            $this->isDeletable()
            && ($itemCount > ($this->getMinItems() ?? 0))
            && ($deleteAction = $this->getAction($this->getDeleteActionName()))
        ) {
            $deleteAction = $deleteAction(['item' => $itemKey]);

            if ($deleteAction->isVisible()) {
                $actions[] = $deleteAction;
            }
        }

        $extras = [];

        foreach ($this->getExtraItemActions() as $extraAction) {
            $extraAction = $extraAction(['item' => $itemKey])->grouped();

            if ($extraAction->isVisible()) {
                $extras[] = $extraAction;
            }
        }

        return [
            'actions' => $actions,
            'extras' => $extras,
        ];
    }
}
