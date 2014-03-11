<?php

namespace FeatureBranch\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;

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
                $name = 'delete-' . $branch;
                $label = 'Delete ' . $branch;
                $class = 'btn btn-danger';
            }
            else {
                $name = 'deploy-' . $branch;
                $label = 'Deploy ' . $branch;
                $class = 'btn btn-default';

                if ($branch != 'master') {
                  $options = array();
                  foreach ($hosts_state as $host => $deployed) {
                    if ($deployed) {
                      $options[$host] = $host;
                    }
                  }
                  $form->add('pulldb-' . $branch, 'choice', array(
                    'choices' => $options,
                    'empty_value' => 'Deploy ' . $branch . '. Select copy database from',
                    'label' => ' ',
                    'required' => FALSE,
                    'attr' => array('class' => 'form-control'),
                    'label_attr' => array('class' => 'control-label'),
                  ));
                }
            }
            $form->add($name, 'submit', array('label' => $label, 'attr' => array('class' => $class)));
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
                    $origin_branch = $form->get('pulldb-' . $branch)->getData();
                    $ci->createHost($branch, $origin_branch);
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
        return $this->render('FeatureBranchMainBundle:Default:form_landing_page.html.twig', array(
            'ci_url' => $this->container->getParameter('feature_branch.ci_url'),
            'homepage' => $this->generateUrl('feature_branch_main_homepage'),
        ));
    }

  public function loginAction(Request $request)
  {
    if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
      $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
    } else {
      $error = $request->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
    }

    return array(
      'last_username' => $request->getSession()->get(SecurityContext::LAST_USERNAME),
      'error' => $error,
    );
  }

  public function securityCheckAction(Request $request)
  {
  }
}
