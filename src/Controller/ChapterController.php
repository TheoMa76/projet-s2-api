<?php

namespace App\Controller;

use App\Entity\Chapter;
use App\Service\EntityFetcher;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/chapter")
 */
class ChapterController extends BaseController
{
    protected $entityClass = Chapter::class;

    public function __construct(EntityFetcher $entityFetcher)
    {
        parent::__construct($entityFetcher);
    }
}
