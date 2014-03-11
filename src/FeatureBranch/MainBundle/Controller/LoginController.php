<?php

namespace FeatureBranch\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;

class LoginController extends Controller
{
    public function loginAction(Request $request)
    {
      $request = $this->getRequest();
      $session = $request->getSession();

      // get the login error if there is one
      if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
        $error = $request->attributes->get(
          SecurityContext::AUTHENTICATION_ERROR
        );
      } else {
        $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
        $session->remove(SecurityContext::AUTHENTICATION_ERROR);
      }

      return $this->render(
        'FeatureBranchMainBundle:Login:login.html.twig',
        array(
          // last username entered by the user
          'last_username' => $session->get(SecurityContext::LAST_USERNAME),
          'error'         => $error,
        )
      );
    }
}
