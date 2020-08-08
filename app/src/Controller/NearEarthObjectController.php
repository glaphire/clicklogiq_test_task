<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class NearEarthObjectController extends AbstractController
{
    /**
     * @Route("/near/earth/object", name="near_earth_object")
     */
    public function index()
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/NearEarthObjectController.php',
        ]);
    }
}
