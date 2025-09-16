<?php

declare(strict_types = 1);

namespace App\Service;

use Exception;
use DateInterval;
use RuntimeException;
use App\Entity\Episode;
use Madcoda\Youtube\Youtube;

class YoutubeService
{
    private Youtube $youtube;

    /**
     * YoutubeService constructor.
     *
     * @throws Exception
     *
     * @param string $apiKey
     */
    public function __construct(string $apiKey)
    {
        $this->youtube = new Youtube(['key' => $apiKey]);
    }

    /**
     * @throws Exception
     *
     * @param string      $code
     *
     * @return array
     */
    public function getSeriesFromPlaylist(string $code): array
    {
        $infos   = $this->getEpisodesFormPlaylist($code);

        return [
            'name'        => $infos[0]['name'],
            'locale'      => 'fr',
            'type'        => 'series',
            'image'       => $infos[0]['thumb'],
            'description' => $infos[0]['description'],
            'categories'  => [],
            'seasons'     => [
                [
                    'name'     => 'Saison 1',
                    'episodes' => $infos,
                ],
            ],
        ];
    }


    public function getEpisodesFormPlaylist(string $code, string $page = null): array
    {
        $params = [
            'playlistId' => $code,
            'part'       => 'snippet',
            'maxResults' => 50,
        ];

        if ($page) {
            $params['pageToken'] = $page;
        }

        $infos = $this->youtube->getPlaylistItemsByPlaylistIdAdvanced($params, true);

        $firstEp = null;
        $data    = [];

        foreach ($infos['results'] as $info) {
            if ($firstEp === null) {
                $firstEp = $info;
            }

            $thumb = $firstEp->snippet->thumbnails->standard->url;

            if (isset($firstEp->snippet->thumbnails->maxres)) {
                $thumb = $firstEp->snippet->thumbnails->maxres->url;
            }
            $data[] = [
                'name'        => ucfirst(strtolower($info->snippet->title)),
                'code'        => $info->snippet->resourceId->videoId,
                'thumb'       => $thumb,
                'description' => $firstEp->snippet->description
            ];
        }

        if ($token = $infos['info']['nextPageToken']) {
            $data = array_merge($data, $this->getEpisodesFormPlaylist($code, $token));
        }

        return $data;
    }

    /**
     * @throws Exception
     *
     * @param array|Episode[] $episodes
     *
     * @return array<string, int>
     */
    public function getVideoDuration(array $episodes): array
    {
        if (count($episodes) === 0) {
            throw new RuntimeException('cannot process without id');
        }

        if (count($episodes) > 50) {
            throw new RuntimeException('cannot process more than 50 episodes');
        }

        $API_URL = $this->youtube->getApi('videos.list');

        $codes = array_map(
            static function (Episode $ep) {
                return $ep->getCode();
            },
            $episodes
        );

        $params = [
            'id'   => implode(',', $codes),
            'part' => 'contentDetails',
        ];

        $apiData = $this->youtube->api_get($API_URL, $params);
        $datas   = $this->youtube->decodeList($apiData);

        $result = [];

        foreach ($datas as $data) {
            $duration = $data->contentDetails->duration;
            $hours    = (new DateInterval($duration))->h;
            $minutes  = (new DateInterval($duration))->i;
            $seconds  = (new DateInterval($duration))->s;

            $result[$data->id] = $hours * 3600 + $minutes * 60 + $seconds;
        }

        return $result;
    }
}
