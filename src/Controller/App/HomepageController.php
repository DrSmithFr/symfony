<?php

namespace App\Controller\App;

use App\Repository\SeriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function homeAction(SeriesRepository $seriesRepository): Response
    {
        $series = $seriesRepository->findAll();

        return $this->render('app/homepage.html.twig', [
            // send only 10 to template
            'movies' => array_slice($series, 0, 10),
        ]);
    }
}
