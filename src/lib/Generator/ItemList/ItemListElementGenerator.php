<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\PersonalizationClient\Generator\ItemList;

use EzSystems\EzPlatformRest\Output\Generator;
use Ibexa\Contracts\PersonalizationClient\Value\ItemListInterface;

final class ItemListElementGenerator implements ItemListElementGeneratorInterface
{
    public function generateElement(Generator $generator, ItemListInterface $itemList): Generator
    {
        $generator->startObjectElement('contentList');
        $generator->startList('content');

        /** @var \Ibexa\Contracts\PersonalizationClient\Value\ItemInterface $item */
        foreach ($itemList as $item) {
            $itemType = $item->getType();

            $generator->startObjectElement('content');
            $generator->valueElement('contentId', $item->getId());
            $generator->valueElement('contentTypeId', $itemType->getId());
            $generator->valueElement('contentTypeIdentifier', $itemType->getIdentifier());
            $generator->valueElement('itemTypeName', $itemType->getName());
            $generator->valueElement('language', $item->getLanguage());

            foreach ($item->getAttributes() as $name => $value) {
                $generator->valueElement($name, $value);
            }

            $generator->endObjectElement('content');
        }

        $generator->endList('content');
        $generator->endObjectElement('contentList');

        return $generator;
    }
}
