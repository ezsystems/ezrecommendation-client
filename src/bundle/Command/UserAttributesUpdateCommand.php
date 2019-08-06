<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClientBundle\Command;

use EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface;
use EzSystems\EzRecommendationClient\Event\UserAPIEvent;
use EzSystems\EzRecommendationClient\SPI\UserAPIRequest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;

class UserAttributesUpdateCommand extends Command
{
    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface */
    private $eventDispatcher;

    /** @var \EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface */
    private $client;

    /**
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \EzSystems\EzRecommendationClient\Client\EzRecommendationClientInterface $client
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EzRecommendationClientInterface $client
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->client = $client;

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Update the set of the user attributes');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $event = new UserAPIEvent();
        $this->eventDispatcher->dispatch(UserAPIEvent::UPDATE, $event);

        $request = $event->getUserAPIRequest();

        $output->writeln([
            'Updating user attributes',
            ''
        ]);

        if (!$request) {
            $output->writeln('<fg=red>Request object is empty');
            return;
        } elseif (!$request->source) {
            $output->writeln('<fg=red>Property source is not defined');
            return;
        } elseif (!$request->xmlBody) {
            $output->writeln('<fg=red>Property xmlBody is not defined');
            return;
        }

        $response = $this->client->user()->updateUserAttributes($request);

        if ($response && $response->getStatusCode() === Response::HTTP_OK) {
            $output->writeln('<fg=green>User attributes updated successfully!</>');
        }
    }
}
