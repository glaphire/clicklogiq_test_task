<?php

namespace App\Controller;

use App\Entity\NearEarthObject;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\Routing\Annotation\Route;

class NearEarthObjectController extends AbstractFOSRestController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/neo/hazardous", name="neo_hazardous", methods={"GET"})
     */
    public function hazardousAction()
    {
        $nearEarthObjectRepository = $this->entityManager->getRepository(NearEarthObject::class);
        $hazardous = $nearEarthObjectRepository->findBy(['is_hazardous' => true]);
        $view = $this->view($hazardous, 200)->setFormat('json');

        return $this->handleView($view);
    }
}
