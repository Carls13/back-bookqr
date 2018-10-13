<?php

namespace App\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Ramsey\Uuid\Uuid;

class UuidSubscriber implements EventSubscriber
{

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $this->fillUuid($entity);
    }

    /**
     * Auto generate uuid value
     * @param $entity
     */
    public function fillUuid($entity)
    {
        if (method_exists($entity, 'getUuid') AND $entity->getUuid() === null) {
            $entity->setUuid(Uuid::uuid4()->toString());
        }
    }

}