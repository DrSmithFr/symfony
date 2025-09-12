<?php

namespace App\EventSubscriber;

use DateTime;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    private string $userId;

    public function __construct(
        TokenStorageInterface $tokenStorage,
    ) {
        $token = $tokenStorage->getToken();

        if (null === $token) {
            throw new RuntimeException('The token storage contains no authentication token. Have you logged in?');
        }

        $this->userId = $token->getUserIdentifier();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => ['beforeEntityPersistedEvent'],
            BeforeEntityUpdatedEvent::class => ['beforeEntityUpdatedEvent'],
        ];
    }

    public function beforeEntityPersistedEvent(BeforeEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        $this->trySet($entity, 'setCreatedAt', new DateTime());
        $this->trySet($entity, 'setCreatedBy', $this->userId . '-easyadmin');

        $this->trySet($entity, 'setUpdatedAt', new DateTime());
        $this->trySet($entity, 'setUpdatedBy', $this->userId . '-easyadmin');
    }

    public function beforeEntityUpdatedEvent(BeforeEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        $this->trySet($entity, 'setUpdatedAt', new DateTime());
        $this->trySet($entity, 'setUpdatedBy', $this->userId . '-easyadmin');
    }

    private function trySet($entity, $setter, $value): void
    {
        if (!method_exists($entity, $setter)) {
            return;
        }

        $entity->$setter($value);
    }
}
