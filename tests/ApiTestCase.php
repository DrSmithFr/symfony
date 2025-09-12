<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\User;
use Exception;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use LogicException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

abstract class ApiTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    protected function apiPatch(
        string $url,
        mixed $object,
        array $group = ['Default']
    ): Crawler {
        return $this->call('PATCH', $url, $object, $group);
    }

    protected function apiPut(
        string $url,
        mixed $object,
        array $group = ['Default']
    ): Crawler {
        return $this->call('PUT', $url, $object, $group);
    }

    protected function apiPost(
        string $url,
        mixed $object,
        array $group = ['Default']
    ): Crawler {
        return $this->call('POST', $url, $object, $group);
    }

    protected function apiGet(string $url): Crawler
    {
        $this
            ->client
            ->enableProfiler();

        return $this
            ->client
            ->request(
                'GET',
                $url,
            );
    }

    /**
     * @param string $method
     * @param string $url
     * @param mixed  $object
     * @param array  $group
     *
     * @return Crawler
     * @throws Exception
     */
    protected function call(
        string $method,
        string $url,
        mixed $object,
        array $group = ['Default']
    ): Crawler {
        $container = self::getContainer();

        try {
            $serializer = $container->get(SerializerInterface::class);
        } catch (ServiceNotFoundException $e) {
            throw new LogicException('Serializer not found.');
        }

        $context = (SerializationContext::create())
            ->setSerializeNull(true)
            ->setGroups($group);

        $json = $serializer->serialize($object, 'json', $context);

        $this->client->enableProfiler();

        return $this->client->request(
            $method,
            $url,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $json
        );
    }

    protected function getResponse(): Response
    {
        return $this->client->getResponse();
    }

    protected function getApiResponse(): array
    {
        return json_decode($this->getResponse()->getContent(), true);
    }

    protected function loginApiUser(User $user)
    {
        $container = self::getContainer();

        $token = $container
            ->get(JWTTokenManagerInterface::class)
            ->create($user);

        $this
            ->client
            ->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $token));
    }

    protected function disconnectUser(): void
    {
        $this
            ->client
            ->setServerParameters([]);
    }
}
