<?php

namespace App\Controller\Admin;

use App\Enum\RoleEnum;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AdminPageController
{
    public final const CHART_DAYS = 15;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ChartBuilderInterface $chartBuilder
    ) {
    }

    #[Route(path: '/admin', name: 'admin_dashboard', methods: ['GET'])]
    public function index(): Response
    {
        $users = $this
            ->userRepository
            ->countWithRole(RoleEnum::USER);

        return $this->render(
            'admin/dashboard.html.twig',
            [
                'user_count' => $users,
                'user_chart' => $this->getUserCountChart(),
            ]
        );
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            // the name visible to end users
            ->setTitle('Skeleton')
            ->setFaviconPath('favicon.ico')
            ->setTitle('<img src="images/logo.svg" height="40">Skeleton')

            // set this option if you prefer the page content to span the entire
            // browser width, instead of the default design which sets a max width
            ->renderContentMaximized()

            // by default, users can select between a "light" and "dark" mode for the
            // backend interface. Call this method if you prefer to disable the "dark"
            // mode for any reason (e.g. if your interface customizations are not ready for it)
            ->disableDarkMode(false)
            ->setLocales([
                'en' => '🇬🇧 English',
                'fr' => '🇫🇷 Français',
                'es' => '🇪🇸 Español',
            ]);
    }

    private function getUserCountChart(): Chart
    {
        $chartData = $this
            ->userRepository
            ->totalPerDay(RoleEnum::USER, self::CHART_DAYS);

        return $this
            ->chartBuilder
            ->createChart(Chart::TYPE_BAR)
            ->setData([
                'labels' => array_keys($chartData),
                'datasets' => [
                    [
                        'label' => 'Users per day',
                        'backgroundColor' => '#636767',
                        'borderColor' => '#495057',
                        'data' => array_values($chartData),
                    ],
                ],
            ]);
    }
}
