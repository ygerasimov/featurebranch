<?php

namespace FeatureBranch\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('FeatureBranchMainBundle:Default:index.html.twig');
    }
    public function gitupdateAction()
    {
        $git = $this->container->get('feature_branch_gitclass');
        $git->checkState();
        
        return new Response('Git repo rebuilt.');
    }
}
