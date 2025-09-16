<?php

declare(strict_types=1);

namespace App\Service;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastica\Client;
use Elastica\Index;
use Elastica\Reindex;
use Elastica\Request;
use Elastica\Response;

class SearchService
{
    private Client $client;

    private string $indexName;

    public function __construct(Client $client, string $elasticSearchIndexName)
    {
        $this->client = $client;
        $this->indexName = $elasticSearchIndexName;
    }

    public function createTempIndex(): Index
    {
        $index = $this->getTempIndex();

        if ($index->exists()) {
            $index->delete();
        }

        $index->create($this->makeIndexConfiguration());

        return $index;
    }

    public function getTempIndex(): Index
    {
        return $this->client->getIndex(
            sprintf('%s_temp', $this->indexName)
        );
    }

    public function createIndex(): Index
    {
        $index = $this->getIndex();

        if ($index->exists()) {
            $index->delete();
        }

        $index->create($this->makeIndexConfiguration());

        return $index;
    }

    public function getIndex(): Index
    {
        return $this->client->getIndex($this->indexName);
    }

    /**
     * @throws ClientResponseException
     * @throws ServerResponseException
     * @throws MissingParameterException
     */
    public function switchTempIndexWithProduction(): Response
    {
        $indexTemp = $this
            ->client
            ->getIndex(sprintf('%s_temp', $this->indexName));

        $indexDest = $this
            ->client
            ->getIndex($this->indexName);

        $reindex = new Reindex($indexTemp, $indexDest);
        $reindex->setParam('refresh', true);
        $reindex->setParam('requests_per_second', -1);

        return $reindex->run();
    }

    private function makeIndexConfiguration(): array
    {
        return [
            'settings' => $this->makeIndexSettings(),
            'mappings' => $this->makeAssetsMapping(),
        ];
    }

    private function makeIndexSettings(): array
    {
        return [
            'number_of_shards' => 1,
            'analysis'         => [
                'filter'   => [
                    'autocomplete_filter' => [
                        'type'     => 'edge_ngram',
                        'min_gram' => 1,
                        'max_gram' => 10,
                    ],
                ],
                'analyzer' => [
                    'autocomplete' => [
                        'type'      => 'custom',
                        'tokenizer' => 'standard',
                        'filter'    => [
                            'lowercase',
                            'autocomplete_filter',
                        ],
                    ],
                ],
            ],
        ];
    }

    private function makeAssetsMapping(): array
    {
        $properties = [];

        $properties['id'] = [
            'type' => 'keyword',
        ];

        $properties['import_date'] = [
            'type' => 'date',
        ];

        $properties['type'] = [
            'type' => 'keyword',
        ];

        $properties['categories'] = [
            'type' => 'keyword',
        ];

        $properties['locale'] = [
            'type' => 'keyword',
        ];

        $properties['name'] = [
            'type'     => 'text',
            'analyzer' => 'autocomplete',
        ];

        $properties['description'] = [
            'type'     => 'text',
            'analyzer' => 'autocomplete',
        ];

        return [
            'properties' => $properties,
        ];
    }
}
