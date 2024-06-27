<?php

namespace App\Controller;

use App\Entity\Tuto;
use App\Service\EntityFetcher;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/tuto")]
class TutoController extends BaseController
{
    protected $entityClass = Tuto::class;

    public function __construct(EntityFetcher $entityFetcher)
    {
        parent::__construct($entityFetcher, $this->entityClass);
    }
}
