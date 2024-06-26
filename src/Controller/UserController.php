<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\EntityFetcher;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user")
 */
class UserController extends BaseController
{
    protected $entityClass = User::class;

    public function __construct(EntityFetcher $entityFetcher)
    {
        parent::__construct($entityFetcher);
    }
}
