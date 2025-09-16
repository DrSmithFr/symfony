<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Category;
use App\Entity\Episode;
use App\Entity\Season;
use App\Entity\Series;
use App\Entity\SeriesType;
use App\Repository\CategoryRepository;
use App\Repository\EpisodeRepository;
use App\Repository\SeasonRepository;
use App\Repository\SeriesRepository;
use App\Repository\SeriesTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

class SeriesImportCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private SeriesTypeRepository $seriesTypeRepository;
    private SeriesRepository $seriesRepository;
    private SeasonRepository $seasonRepository;
    private EpisodeRepository $episodeRepository;
    private CategoryRepository $categoryRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        SeriesTypeRepository $seriesTypeRepository,
        CategoryRepository $categoryRepository,
        SeriesRepository $seriesRepository,
        SeasonRepository $seasonRepository,
        EpisodeRepository $episodeRepository
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->seriesTypeRepository = $seriesTypeRepository;
        $this->seriesRepository = $seriesRepository;
        $this->seasonRepository = $seasonRepository;
        $this->episodeRepository = $episodeRepository;
        $this->categoryRepository = $categoryRepository;
    }

    protected function configure()
    {
        $this
            ->setName('app:series:import')
            ->setDescription('Rebuild the Index and populate it.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $series = $this->getYmlSeriesData('series.yml');
        $movies = $this->getYmlSeriesData('movies.yml');
        $types = $this->getYmlSeriesData('types.yml');
        $categories = $this->getYmlSeriesData('categories.yml');

        $seriesTypeMap = $this->getSeriesTypes($types);
        $categoriesMap = $this->getCategories($categories);

        $io->section('Importing series');
        $io->progressStart(count($series));

        foreach ($series as $key => $data) {
            try {
                $s = $this->getSeries($key, $data, $seriesTypeMap, $categoriesMap);
                $this->entityManager->persist($s);
                $this->entityManager->flush();
            } catch (Exception $e) {
                throw $e;
            }

            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success(sprintf('%d series imported', count($series)));

        $io->section('Importing movies');
        $io->progressStart(count($movies));

        foreach ($movies as $key => $data) {
            try {
                $m = $this->getMovie($key, $data, $seriesTypeMap, $categoriesMap);
                $this->entityManager->persist($m);
                $this->entityManager->flush();
            } catch (Exception $e) {
                throw $e;
            }

            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success(sprintf('%d movies imported', count($movies)));

        return Command::SUCCESS;
    }

    private function getYmlSeriesData(string $filename)
    {
        $fileString = file_get_contents(__DIR__ . '/../Resources/' . $filename);

        return Yaml::parse($fileString);
    }

    private function getSeriesTypes(array $types): array
    {
        $seriesTypesMap = [];

        foreach ($types as $key => $data) {
            $type = $this->seriesTypeRepository->findOneBy(['importCode' => $key]);

            if (null === $type) {
                $type = (new SeriesType())->setImportCode($key);
            }

            $type->setName($data['name']);
            $this->entityManager->persist($type);

            $seriesTypesMap[$key] = $type;
        }

        $this->entityManager->flush();

        return $seriesTypesMap;
    }

    private function getCategories(array $categories): array
    {
        $categoriesMap = [];

        foreach ($categories as $key => $data) {
            /** @var Category $category */
            $category = $this->categoryRepository->findOneBy(['slug' => $key]);

            if (null === $category) {
                $category = (new Category())->setSlug($key);
            }

            $category->setName($data['name']);
            $this->entityManager->persist($category);

            $categoriesMap[$key] = $category;
        }

        $this->entityManager->flush();

        return $categoriesMap;
    }

    /**
     * @param string $importCode
     * @param array  $data
     * @param array  $seriesTypesMap
     * @param array  $categoriesMap
     * @return Series
     * @throws NonUniqueResultException
     */
    private function getSeries(
        string $importCode,
        array $data,
        array $seriesTypesMap,
        array $categoriesMap
    ): Series {
        $series = $this->seriesRepository->findOneByImportCode($importCode);

        if (null === $series) {
            $series = (new Series())->setImportCode($importCode);
        }

        $typeId = $data['type'];

        $series
            ->setName($data['name'])
            ->setLocale($data['locale'])
            ->setImage($data['image'])
            ->setDescription($data['description'])
            ->setType($seriesTypesMap[$typeId])
            ->setTags($data['tags'] ?? []);

        foreach ($data['categories'] ?? [] as $category) {
            $series->addCategory($categoriesMap[$category]);
        }

        foreach ($data['seasons'] as $index => $seasonData) {
            $season = $this->getSeason($importCode, $index, $seasonData);
            $series->addSeason($season);
        }

        $this->entityManager->persist($series);

        return $series;
    }

    /**
     * @param string $importCode
     * @param array  $data
     * @param array  $seriesTypesMap
     * @param array  $categoriesMap
     * @return Series
     * @throws NonUniqueResultException
     */
    private function getMovie(
        string $importCode,
        array $data,
        array $seriesTypesMap,
        array $categoriesMap
    ): Series {
        $series = $this->seriesRepository->findOneByImportCode($importCode);

        if (null === $series) {
            $series = (new Series())->setImportCode($importCode);
        }

        $typeId = $data['type'];

        $series
            ->setName($data['name'])
            ->setLocale($data['locale'])
            ->setImage($data['image'])
            ->setDescription($data['description'])
            ->setType($seriesTypesMap[$typeId])
            ->setTags($data['tags'] ?? []);

        foreach ($data['categories'] ?? [] as $category) {
            $series->addCategory($categoriesMap[$category]);
        }

        $seasonData = [
            'name'     => '',
            'episodes' => [
                [
                    'name' => $data['name'],
                    'code' => $data['code'],
                ],
            ],
        ];

        $season = $this->getSeason($importCode, 0, $seasonData);
        $series->addSeason($season);

        $this->entityManager->persist($series);

        return $series;
    }

    private function getSeason(string $importCode, int $index, array $data): Season
    {
        $importCode = sprintf('%s_%s', $importCode, $index);
        $season = $this->seasonRepository->findOneBy(['importCode' => $importCode]);

        if (null === $season) {
            $season = (new Season())->setImportCode($importCode);
        }

        $season
            ->setRank($index)
            ->setName($data['name']);

        foreach ($data['episodes'] as $epIndex => $episodeData) {
            $episode = $this->getEpisode($importCode, $epIndex, $episodeData);
            $season->addEpisode($episode);
        }

        $this->entityManager->persist($season);

        return $season;
    }

    private function getEpisode(string $importCode, int $index, array $data): Episode
    {
        $importCode = sprintf('%s_%s', $importCode, $index);
        $episode = $this->episodeRepository->findOneBy(['importCode' => $importCode]);

        if (null === $episode) {
            $episode = (new Episode())->setImportCode($importCode);
        }

        $episode
            ->setRank($index)
            ->setName($data['name'])
            ->setCode($data['code']);

        $this->entityManager->persist($episode);

        return $episode;
    }
}
