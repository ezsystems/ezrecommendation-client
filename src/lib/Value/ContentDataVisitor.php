<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Value;

use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use EzSystems\EzRecommendationClient\Exception\ResponseClassNotImplementedException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ContentDataVisitor converter for REST output.
 */
class ContentDataVisitor extends ValueObjectVisitor
{
    /** @var array */
    private $responseRenderers = [];

    /**
     * @param array $responseRenderers
     */
    public function setResponseRenderers($responseRenderers)
    {
        $this->responseRenderers = $responseRenderers;
    }

    /**
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param mixed $data
     *
     * @return mixed
     *
     * @throws \EzSystems\EzRecommendationClient\Exception\ResponseClassNotImplementedException
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'responseType' => 'http',
        ]);

        $data->options = $resolver->resolve($data->options);

        $visitor->setHeader('Content-Type', $generator->getMediaType('ContentList'));

        if (empty($data->contents)) {
            $visitor->setStatus(204);

            return;
        }

        if (!isset($this->responseRenderers[$data->options['responseType']])) {
            throw new ResponseClassNotImplementedException(sprintf('Renderer for %s response not implemented.', $data->options['responseType']));
        }

        return $this->responseRenderers[$data->options['responseType']]->render($generator, $data);
    }
}
