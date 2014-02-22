<?php

namespace FeatureBranch\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        $git = $this->container->get('feature_branch_gitclass');
        $branches = $git->parseState();

        if (empty($branches)) {
            $git->checkState();
        }

        $branches = $git->parseState();
        $ci = $this->container->get('feature_branch_ci');
        $hosts_state = $ci->getHostsConfig();

        $form = $this->createFormBuilder();

        foreach ($branches as $branch => $commit) {
            if (isset($hosts_state[$branch]) && $hosts_state[$branch]) {
                $name = 'delete-' .$branch;
                $label = 'Delete ' . $branch;
            }
            else {
                $name = 'deploy-' .$branch;
                $label = 'Deploy ' . $branch;
            }
            $form->add($name, 'submit', array('label' => $label));
        }
        
        $form = $form->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $submitted_button_name = $form->getClickedButton()->getName();

            // Both words 'delete' and 'deploy' have same length of 6 chars.
            $operation = substr($submitted_button_name, 0, 6);
            $branch = substr($submitted_button_name, 7);

            switch ($operation) {
                case 'deploy':
                    $ci->createHost($branch);
                    $hosts_state[$branch] = TRUE;
                    break;
                case 'delete':
                    $ci->deleteBranch($branch);
                    $hosts_state[$branch] = FALSE;
                    break;
            }
            
            $ci->saveHostsConfig($hosts_state);

            return $this->redirect($this->generateUrl('feature_branch_main_hosts_landing_page'));
        }

        return $this->render('FeatureBranchMainBundle:Default:index.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function gitUpdateAction()
    {
        $git = $this->container->get('feature_branch_gitclass');
        $git->checkState();
        
        return new Response('Git repo rebuilt.');
    }

    public function hostsLandingPageAction()
    {
        return new Response('Operation successful. <a href="' . $this->generateUrl('feature_branch_main_homepage') . '">Go back to the form</a>');
    }
}
