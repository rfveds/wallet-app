<?php
/**
 * User controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\UserPasswordType;
use App\Service\OperationServiceInterface;
use App\Service\UserServiceInterface;
use App\Service\WalletServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserController.
 */
#[Route('/user')]
class UserController extends AbstractController
{
    /**
     * User service.
     */
    private UserServiceInterface $userService;

    /**
     * UserController constructor.
     */
    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Index action.
     */
    #[Route(
        '/',
        name: 'user_index',
        methods: 'GET')]
    #[isGranted('ROLE_ADMIN')]
    public function index(Request $request): Response
    {
        $pagination = $this->userService->createPaginationList(
            $request->query->getInt('page', 1)
        );

        return $this->render('user/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Show action.
     *
     * @param User $user User entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'user_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET',
    )]
    public function show(User $user): Response
    {
        return $this->render(
            'user/show.html.twig',
            ['user' => $user,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param User    $user    User entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/delete',
        name: 'user_delete',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|DELETE',
    )]
    public function delete(Request $request, User $user): Response
    {
        $form = $this->createForm(
            FormType::class,
            $user,
            [
                'method' => 'DELETE',
                'action' => $this->generateUrl(
                    'user_delete',
                    ['id' => $user->getId()]
                ),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->userService->delete($user);
            $this->addFlash('success', 'message.deleted_successfully');

            return $this->redirectToRoute('homepage');
        }

        return $this->render(
            'user/delete.html.twig',
            [
                'form' => $form->createView(),
                'user' => $user,
            ]
        );
    }

    /**
     * Edit password action.
     */
    #[Route(
        '/{id}/edit-password',
        name: 'user_edit_password',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|PUT',
    )]
    public function editPassword(Request $request, User $user): Response
    {
        $form = $this->createForm(
            UserPasswordType::class,
            $user,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl(
                    'user_edit_password',
                    ['id' => $user->getId()],
                ),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->userService->upgradePassword($user, $form->get('password')->getData());

            $this->addFlash('success', 'message.updated_successfully');

            return $this->redirectToRoute('homepage');
        }

        return $this->render(
            'user/edit_password.html.twig',
            [
                'form' => $form->createView(),
                'user' => $user,
            ]
        );
    }

}
