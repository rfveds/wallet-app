<?php
/**
 * Homepage controller.
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class OperationController.
 */
#[Route('/')]
class HomepageController extends AbstractController
{
    /**
     * Index action.
     *
     */
    #[Route(
        name: 'homepage',
        methods: 'GET'
    )]
    public function index(): Response
    {
        return $this->render(
            'homepage/index.html.twig'
        );
    }


}
