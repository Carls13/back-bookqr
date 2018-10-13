<?php

namespace App\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;

class TimestampSubscriber implements EventSubscriber
{

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $this->setTimestampFields($entity);
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $this->setTimestampFields($entity);
    }

    /**
     * Set updated_at and created_at
     * @param $entity
     */
    private function setTimestampFields($entity)
    {
        //set updated_at field if exist
        if (method_exists($entity, 'setUpdatedAt')) {
            $entity->setUpdatedAt(new \DateTime('now'));
        }

        //set created_at field if exist
        if (method_exists($entity, 'setCreatedAt')) {
            if ($entity->getCreatedAt() == null) {
                $entity->setCreatedAt(new \DateTime('now'));
            }
        }

    }

}