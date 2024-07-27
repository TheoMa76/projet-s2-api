<?php

namespace App\Controller;

use App\Entity\Tuto;
use App\Service\EntityFetcher;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

//TODO RAJOUTER UNE IMAGE DE PRESENTATION ET UNE DESCRIPTION

#[Route("/tuto")]
class TutoController extends BaseController
{
    protected $entityClass = Tuto::class;

    public function __construct(EntityFetcher $entityFetcher)
    {
        parent::__construct($entityFetcher, $this->entityClass);
    }
}
