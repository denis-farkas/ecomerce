<?php


namespace App\Handler;

use App\Entity\CartLog;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

class DatabaseCartLogHandler extends AbstractProcessingHandler
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function write(LogRecord $record): void
    {
        // Ne traiter que les logs du channel 'cart'
        if ($record->channel !== 'cart') {
            return;
        }

        $cartLog = new CartLog();
        $cartLog->setLevel(strtolower($record->level->name));
        $cartLog->setMessage($record->message);
        $cartLog->setContext($record->context);
        $cartLog->setCreatedAt(new \DateTimeImmutable());

        // Extraire des informations spécifiques du contexte
        if (isset($record->context['user_identifier'])) {
            $cartLog->setUserIdentifier($record->context['user_identifier']);
        }

        if (isset($record->context['product_id'])) {
            $cartLog->setProductId($record->context['product_id']);
        }

        // Déterminer l'action basée sur le message
        $message = $record->message;
        if (str_contains($message, 'add')) {
            $cartLog->setAction('add');
        } elseif (str_contains($message, 'remove')) {
            $cartLog->setAction('remove');
        } elseif (str_contains($message, 'increase')) {
            $cartLog->setAction('increase');
        } elseif (str_contains($message, 'decrease')) {
            $cartLog->setAction('decrease');
        } elseif (str_contains($message, 'Displaying cart')) {
            $cartLog->setAction('view');
        }

        $this->entityManager->persist($cartLog);
        $this->entityManager->flush();
    }
}