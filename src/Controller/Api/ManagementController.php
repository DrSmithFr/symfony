<?php

declare(strict_types = 1);

namespace App\Controller\Api;

use App\Form\SeriesType;
use App\Repository\SeriesRepository;
use App\Service\YoutubeService;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/management", name="management_")
 */
class ManagementController extends AbstractController
{
    /**
     * @Route("/series", name="series_managed", methods={"GET"})
     * @param SeriesRepository $repository
     *
     * @return JsonResponse
     */
    public function managedSeriesAction(
        SeriesRepository $repository
    ): JsonResponse {
        $managed = [];

        if ($this->isGranted('ROLE_ADMIN')) {
            $managed = $repository->getManaged();
        }

        return new JsonResponse($managed);
    }

    /**
     * @Route("/series", name="series_update", methods={"PATCH", "PUT"})
     * @param Request                $request
     * @param SeriesRepository       $repository
     * @param EntityManagerInterface $entityManager
     *
     * @return JsonResponse
     */
    public function seriesUpdateAction(
        Request $request,
        SeriesRepository $repository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $series = $repository->find($request->get('id'));

        if (!$series) {
            throw new RuntimeException('series not found');
        }

        $form = $this
            ->createForm(SeriesType::class, $series)
            ->submit($request->request->all());

        if (!$form->isSubmitted()) {
            return new JsonResponse(['message' => 'No form submitted'], Response::HTTP_NOT_ACCEPTABLE);
        }

        if (!$form->isValid()) {
            throw new RuntimeException('something append');
        }

        $entityManager->flush();

        return $this->json(['message' => 'series updated']);
    }

    /**
     * @Route("/episodes-form-playlist", name="series_from_playlis", methods={"POST"})
     * @param Request        $request
     * @param YoutubeService $youtubeService
     *
     * @return JsonResponse
     */
    public function loadSeasonPlaylist(
        Request $request,
        YoutubeService $youtubeService
    ): JsonResponse {
        $playlistCode = $request->get('playlistId');
        $data         = $youtubeService->getEpisodesFormPlaylist($playlistCode);

        return $this->json($data);
    }

    /**
     * @Route("/series-form-playlist", name="season-from-playlist", methods={"POST"})
     * @param Request        $request
     * @param YoutubeService $youtubeService
     *
     * @return JsonResponse
     */
    public function loadSeriesPlaylist(
        Request $request,
        YoutubeService $youtubeService
    ): JsonResponse {
        $playlistCode = $request->get('playlistId');
        $data         = $youtubeService->getSeriesFromPlaylist($playlistCode);

        return $this->json($data);
    }
}
