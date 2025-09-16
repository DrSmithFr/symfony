<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Series;
use App\Repository\SeriesRepository;
use App\Service\IndexerService;
use App\Service\SearchService;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SeriesIndexationCommand extends Command
{
    private SearchService $elasticSearchService;
    private IndexerService $indexer;
    private SeriesRepository $repository;

    /**
     * SeriesIndexationCommand constructor.
     *
     * @param SearchService    $elasticSearchService
     * @param IndexerService   $indexer
     * @param SeriesRepository $repository
     */
    public function __construct(
        SearchService $elasticSearchService,
        IndexerService $indexer,
        SeriesRepository $repository
    ) {
        $this->elasticSearchService = $elasticSearchService;
        $this->indexer              = $indexer;
        $this->repository           = $repository;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:series:indexation')
            ->setDescription('Rebuild the Index and populate it.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     * @throws ClientResponseException
     * @throws MissingParameterException
     * @throws ServerResponseException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->section('Index creation');

        $index = $this->elasticSearchService->createTempIndex();

        $io->success('Index created!');

        $io->section('Assets indexation');

        $assets = $this->repository->findAll();

        $io->progressStart(count($assets));

        $documents = [];

        /** @var Series $asset */
        foreach ($assets as $asset) {
            if ($document = $this->indexer->buildSeriesDocument($asset)) {
                $documents[] = $document;
            }

            $io->progressAdvance();
        }
        $io->progressFinish();

        $index->addDocuments($documents);
        $index->refresh();

        $io->success('Index populated and ready!');
        $io->section('Switching temporary index to production');

        $this->elasticSearchService->createIndex();
        $response = $this->elasticSearchService->switchTempIndexWithProduction();

        if ($response->getStatus() === 200) {
            $io->success('Index switched!');
        } else {
            $io->error('Index cannot switch');
        }

        return Command::SUCCESS;
    }
}
