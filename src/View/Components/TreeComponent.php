<?php

declare(strict_types=1);

namespace Leeto\MoonShineTree\View\Components;

use MoonShine\ActionButtons\ActionButtons;
use MoonShine\Buttons\DeleteButton;
use MoonShine\Buttons\DetailButton;
use MoonShine\Buttons\EditButton;
use MoonShine\Components\MoonshineComponent;
use MoonShine\Resources\ModelResource;
use MoonShine\Traits\HasResource;

/**
 * @method static static make(ModelResource $resource)
 */
final class TreeComponent extends MoonshineComponent
{
    use HasResource;

    protected string $view = 'moonshine-tree::components.tree.index';

    public function __construct(ModelResource $resource)
    {
        $this->setResource($resource);
    }

    protected function items(): array
    {
        $performed = [];
        $resource = $this->getResource();
        $items = $resource->items();

        foreach ($items as $item) {
            $parent = is_null($resource->treeKey()) || is_null($item->{$resource->treeKey()})
                ? 0
                : $item->{$resource->treeKey()};

            $performed[$parent][$item->getKey()] = $item;
        }

        return $performed;
    }

    protected function viewData(): array
    {
        return [
            'items' => $this->items(),
            'resource' => $this->getResource(),
            'route' => $this->getResource()->route('sortable'),
            'buttons' => function($item) {
                $resource = $this->getResource()->setItem($item);

                return ActionButtons::make([
                    ...$resource->getIndexButtons(),
                    DetailButton::for($resource)
                        ->icon('heroicons.outline.bars-4')->canSee(
                            fn($item) => !is_null($item->parent_id) && $this->getResource() instanceof \App\MoonShine\Resources\CatalogRuDeviceResource
                        ),
                    EditButton::for($resource, 'tree')
                        ->customAttributes(['class' => 'btn-outline-primary']),
                    DeleteButton::for($resource, 'tree')
                        ->customAttributes(['class' => 'btn-outline-error'])
                        ->onClick(fn ($item) => "setTimeout(function() {
                            location.reload();
                        }, 2000);", 'prevent'),
                ])->fillItem($item);
            }
        ];
    }
}
