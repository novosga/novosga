<?php

namespace AppBundle\Controller;

use Mangati\BaseBundle\Controller\LoginController as BaseLoginController;

/**
 * LoginController.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class LoginController extends BaseLoginController
{
    
    protected function getLoginTemplate()
    {
        return 'login.html.twig';
    }


}
