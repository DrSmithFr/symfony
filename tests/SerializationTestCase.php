<?php

declare(strict_types=1);

namespace App\Tests;

use App\Controller\Traits\SerializerAware;
use JMS\Serializer\SerializerInterface;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

abstract class SerializationTestCase extends WebTestCase
{
    use SerializerAware;

    public function setUp(): void
    {
        $container = self::getContainer();

        try {
            /** @var SerializerInterface $serializer */
            $serializer = $container->get(SerializerInterface::class);
        } catch (ServiceNotFoundException $e) {
            throw new LogicException('Serializer not found.');
        }

        $this->setSerializer($serializer);
    }
}
