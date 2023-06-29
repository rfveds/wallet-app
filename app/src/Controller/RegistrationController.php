<?php
/**
 * Registration controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\RegistrationType;
use App\Security\LoginFormAuthenticator;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class RegistrationController.
 */
class RegistrationController extends AbstractController
{
    /**
     * User service.
     */
    private UserService $userService;

    /**
     * Translator.
     */
    private TranslatorInterface $translator;

    /**
     * RegistrationController constructor.
     *
     * @param UserService $userService User service
     */
    public function __construct(UserService $userService, TranslatorInterface $translator)
    {
        $this->userService = $userService;
        $this->translator = $translator;
    }// end __construct()

    /**
     * Register action.
     *
     * @param Request                    $request           HTTP request
     * @param UserAuthenticatorInterface $userAuthenticator Authenticator
     * @param LoginFormAuthenticator     $authenticator     Login form authenticator
     *
     * @return Response HTTP response
     */
    #[Route(
        '/register',
        name: 'app_register',
        methods: [
            'GET',
            'POST',
        ],
    )]
    public function register(Request $request, UserAuthenticatorInterface $userAuthenticator, LoginFormAuthenticator $authenticator): Response
    {
        $user = new User();
        $form = $this->createForm(
            RegistrationType::class,
            $user,
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->save($user, $form->get('password')->getData());

            $this->addFlash(
                'success',
                $this->translator->trans('message.registered_successfully'));

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request,
            );
        }

        return $this->render(
            'registration/register.html.twig',
            ['form' => $form->createView()]
        );
    }// end register()
}// end class
