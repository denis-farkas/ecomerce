<?php


namespace App\EventListener;

use App\Entity\Product;
use App\Entity\ProductAudit;
use App\Enum\ActionType;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Bundle\SecurityBundle\Security;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Product::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Product::class)]
#[AsEntityListener(event: Events::preRemove, method: 'preRemove', entity: Product::class)]
class ProductAuditListener
{
    private static array $processed = [];

    public function __construct(
        private Security $security
    ) {}

    public function postPersist(Product $product, PostPersistEventArgs $event): void
    {
        $key = 'insert_' . spl_object_hash($product);
        if (isset(self::$processed[$key])) {
            return;
        }
        self::$processed[$key] = true;

        $audit = new ProductAudit();
        $audit->setProductId($product->getId());
        $audit->setActionType(ActionType::INSERT);
        $audit->setNewName($product->getName());
        $audit->setNewPrice((string) $product->getPrice());
        $audit->setModifiedAt(new \DateTime());
        $audit->setModifiedBy($this->getCurrentUser());

        // Utiliser une nouvelle connexion pour éviter la boucle
        $entityManager = $event->getObjectManager();
        $entityManager->persist($audit);
        
        // Flush uniquement l'audit, pas tout
        $entityManager->flush($audit);
        
        // Nettoyer après traitement
        unset(self::$processed[$key]);
    }

    public function preUpdate(Product $product, PreUpdateEventArgs $event): void
    {
        $key = 'update_' . spl_object_hash($product);
        if (isset(self::$processed[$key])) {
            return;
        }
        self::$processed[$key] = true;

        $changeSet = $event->getEntityChangeSet();
        
        if (empty($changeSet)) {
            unset(self::$processed[$key]);
            return;
        }

        $audit = new ProductAudit();
        $audit->setProductId($product->getId());
        $audit->setActionType(ActionType::UPDATE);
        
        if (isset($changeSet['name'])) {
            $audit->setOldName($changeSet['name'][0]);
            $audit->setNewName($changeSet['name'][1]);
        }
        
        if (isset($changeSet['price'])) {
            $audit->setOldPrice((string) $changeSet['price'][0]);
            $audit->setNewPrice((string) $changeSet['price'][1]);
        }
        
        $audit->setModifiedAt(new \DateTime());
        $audit->setModifiedBy($this->getCurrentUser());

        $entityManager = $event->getObjectManager();
        $entityManager->persist($audit);
        $entityManager->flush($audit);
        
        unset(self::$processed[$key]);
    }

    public function preRemove(Product $product, PreRemoveEventArgs $event): void
    {
        $key = 'delete_' . spl_object_hash($product);
        if (isset(self::$processed[$key])) {
            return;
        }
        self::$processed[$key] = true;

        $audit = new ProductAudit();
        $audit->setProductId($product->getId());
        $audit->setActionType(ActionType::DELETE);
        $audit->setOldName($product->getName());
        $audit->setOldPrice((string) $product->getPrice());
        $audit->setModifiedAt(new \DateTime());
        $audit->setModifiedBy($this->getCurrentUser());

        $entityManager = $event->getObjectManager();
        $entityManager->persist($audit);
        $entityManager->flush($audit);
        
        unset(self::$processed[$key]);
    }

    private function getCurrentUser(): string
    {
        $user = $this->security->getUser();
        return $user ? $user->getUserIdentifier() : 'anonymous';
    }
}