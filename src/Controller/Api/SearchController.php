<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\SearchService;
use Elastica\Aggregation\Terms;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\MatchQuery;
use Elastica\Query\MultiMatch;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    /**
     * @Route("/search", name="search_series")
     * @param Request       $request
     * @param SearchService $searchService
     *
     * @return JsonResponse
     */
    public function getBackgroundLayersAction(
        Request $request,
        SearchService $searchService
    ): JsonResponse {
        $query = new Query();
        $customSearch = new BoolQuery();


        if ($search = $request->get('query')) {
            // add exact match query
            $match = new MultiMatch();

            $match->setQuery($search);
            $match->setFields(['name']);
            $match->setAnalyzer('standard');

            // add miss spell query
            $fuzzy = new Query\Fuzzy();
            $fuzzy->setField('name', $search);

            $customSearch->addMust(
                (new BoolQuery())
                    ->addShould($match)
                    ->addShould($fuzzy)
            );

            $query->setHighlight(
                [
                    'number_of_fragments' => 3,
                    'fragment_size'       => 255,
                    'fields'              => [
                        'name' => (object) [],
                    ],
                ]
            );
        } else {
            $customSearch->addMust(new Query\MatchAll());
        }

        if ($jsonFilters = $request->get('filters')) {
            $filters = json_decode($jsonFilters, true);
            foreach ($filters as $field => $value) {
                if ($value === null) {
                    continue;
                }

                if (is_array($value)) {
                    foreach ($value as $v) {
                        $filterMatch = new MatchQuery();
                        $filterMatch->setFieldQuery($field, $v);
                        $customSearch->addMust($filterMatch);
                    }
                } else {
                    $filterMatch = new MatchQuery();
                    $filterMatch->setFieldQuery($field, $value);
                    $customSearch->addMust($filterMatch);
                }
            }
        }

        $query->setQuery($customSearch);

        $query->setSize(96);
        $query->setSort(
            [
                'import_date' => [
                    'order' => 'desc',
                ],
            ]
        );

        $query->addAggregation(
            (new Terms('locales'))->setField('locale')
        );

        $query->addAggregation(
            (new Terms('types'))->setField('type')
        );

        $query->addAggregation(
            (new Terms('categories'))->setField('categories')
        );

        $result = $searchService->getIndex()->search($query);

        $assets = [];
        foreach ($result as $asset) {
            $assetData = $asset->getSource();

            $highlights = $asset->getHighlights();

            if (isset($highlights['name'])) {
                $assetData['name'] = $highlights['name'][0];
            }

            $assets[] = $assetData;
        }

        $filters = [
            'locales'    => $result->getAggregation('locales')['buckets'],
            'types'      => $result->getAggregation('types')['buckets'],
            'categories' => $result->getAggregation('categories')['buckets'],
        ];

        return new JsonResponse(
            [
                'assets'  => $assets,
                'filters' => $filters,
            ]
        );
    }
}
