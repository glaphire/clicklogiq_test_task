<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class NearEarthObjectController extends AbstractController
{
    /**
     * @Route("/neo/hazardous", name="neo_hazardous", methods={"GET"})
     */
    public function hazardous()
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/NearEarthObjectController.php',
        ]);
    }
}
