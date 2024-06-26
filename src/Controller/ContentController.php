<?php

namespace App\Controller;

use App\Entity\Content;
use App\Service\EntityFetcher;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/content')]
class ContentController extends BaseController
{
    protected $entityClass = Content::class;

    public function __construct(EntityFetcher $entityFetcher)
    {
        parent::__construct($entityFetcher);
    }
}
