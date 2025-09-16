<?php

declare(strict_types = 1);

namespace App\Command;

use App\Entity\Episode;
use App\Service\YoutubeService;
use App\Repository\EpisodeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VideoDurationUpdateCommand extends Command
{
    private YoutubeService $youtubeService;
    private EpisodeRepository $episodeRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        YoutubeService $youtubeService,
        EpisodeRepository $episodeRepository,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->entityManager     = $entityManager;
        $this->youtubeService    = $youtubeService;
        $this->episodeRepository = $episodeRepository;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:video:duration')
            ->setDescription('Update episode durations');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->section('Loading Episode to update');
        $episodes = $this->episodeRepository->findAllWithoutDuration();

        $io->section('Loading Episode duration');
        $io->progressStart(count($episodes) / 50);
        $durations = $this->getEpisodeDurations($episodes, $io);

        $io->section('Updating Episode duration');
        $io->progressStart(count($episodes));
        foreach ($episodes as $episode) {
            if ($duration = $durations[$episode->getCode()] ?? null) {
                $episode->setDuration($duration);
            }

            $io->progressAdvance();
        }
        $io->progressFinish();

        $this->entityManager->flush();

        $io->success(sprintf('%d entities updated.', count($episodes)));
        return Command::SUCCESS;
    }

    /**
     * @throws Exception
     *
     * @param SymfonyStyle    $io
     * @param array|Episode[] $episodes
     *
     * @return array<string, int>
     */
    private function getEpisodeDurations(array $episodes, SymfonyStyle $io): array
    {
        $result = [];

        do {
            $subdivision = array_slice($episodes, 0, 50);
            $episodes    = array_slice($episodes, 50);
            $durations   = $this->youtubeService->getVideoDuration($subdivision);

            foreach ($durations as $code => $duration) {
                $result[$code] = $duration;
            }

            $io->progressAdvance();
        } while (count($episodes));

        $io->progressFinish();

        return $result;
    }
}
