<?php

namespace Iut\TrainingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('IutTrainingBundle::index.html.twig', array('name' => $name));
    }
}
