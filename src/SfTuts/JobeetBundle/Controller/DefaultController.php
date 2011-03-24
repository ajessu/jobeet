<?php

namespace SfTuts\JobeetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('SfTutsJobeetBundle:Default:index.html.twig');
    }
}
