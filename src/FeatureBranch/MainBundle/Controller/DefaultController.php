<?php

namespace FeatureBranch\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('FeatureBranchMainBundle:Default:index.html.twig', array('name' => $name));
    }
}
