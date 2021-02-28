<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\FlowService\Guide;

use League\Tactician\CommandBus;
use phpDocumentor\Descriptor\DocumentationSetDescriptor;
use phpDocumentor\Descriptor\GuideSetDescriptor;
use phpDocumentor\FlowService\FlowService;
use phpDocumentor\FileSystem\FlySystemFactory;
use phpDocumentor\Guides\ParseDirectoryCommand;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

final class Parser implements FlowService
{
    /** @var CommandBus */
    private $commandBus;

    /** @var LoggerInterface */
    private $logger;

    /** @var FlySystemFactory */
    private $flySystemFactory;

    public function __construct(CommandBus $commandBus, LoggerInterface $logger, FlySystemFactory $flySystemFactory)
    {
        $this->commandBus = $commandBus;
        $this->logger = $logger;
        $this->flySystemFactory = $flySystemFactory;
    }

    public function operate(DocumentationSetDescriptor $documentationSet): void
    {
        if (!$documentationSet instanceof GuideSetDescriptor) {
            throw new \InvalidArgumentException('Invalid documentation set');
        }

        $this->log('Parsing guides', LogLevel::NOTICE);

        $dsn = $documentationSet->getSource()->dsn();
        $origin = $this->flySystemFactory->create($dsn);
        $sourcePath = (string) ($documentationSet->getSource()->paths()[0] ?? '');

        $this->commandBus->handle(
            new ParseDirectoryCommand($documentationSet, $origin, $sourcePath)
        );
    }

    /**
     * Dispatches a logging request.
     *
     * @param string $priority The logging priority as declared in the LogLevel PSR-3 class.
     * @param string[] $parameters
     */
    private function log(string $message, string $priority = LogLevel::INFO, array $parameters = []) : void
    {
        $this->logger->log($priority, $message, $parameters);
    }
}
