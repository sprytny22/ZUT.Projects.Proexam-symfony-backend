<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\AbstractFOSRestController;

class UserController extends AbstractFOSRestController
{
    public function index(): Response
    {
        $array = [
            'lol' => 'lol2'
        ];

        return$this->handleView($this->view($array));
    }
}
