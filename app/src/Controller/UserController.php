<?php
/**
 * User controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\UserDataType;
use App\Form\Type\UserPasswordType;
use App\Form\Type\UserRoleType;
use App\Service\UserServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

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
     * Translator.
     */
    private TranslatorInterface $translator;

    /**
     * UserController constructor.
     *
     * @param UserServiceInterface $userService User service
     * @param TranslatorInterface  $translator  Translator
     */
    public function __construct(UserServiceInterface $userService, TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->userService = $userService;
    }// end __construct()

    /**
     * Index action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route(
        '/',
        name: 'user_index',
        methods: 'GET'
    )]
    #[isGranted('ROLE_ADMIN')]
    public function index(Request $request): Response
    {
        $pagination = $this->userService->createPaginationList(
            $request->query->getInt('page', 1)
        );

        return $this->render('user/index.html.twig', ['pagination' => $pagination]);
    }// end index()

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
            ['user' => $user]
        );
    }// end show()

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
            $formUserId = $form->getData()->getId();
            $currentUserId = $user->getId();
            if ($currentUserId === $formUserId) {
                $session = new Session();
                $session->invalidate();
            }

            $this->userService->delete($user);
            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully'));

            return $this->redirectToRoute('homepage');
        }

        return $this->render(
            'user/delete.html.twig',
            [
                'form' => $form->createView(),
                'user' => $user,
            ]
        );
    }// end delete()

    /**
     * Edit password action.
     *
     * @param Request $request HTTP request
     * @param User    $user    User entity
     *
     * @return Response HTTP response
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

            $this->addFlash(
                'success',
                $this->translator->trans('message.updated_successfully'));

            return $this->redirectToRoute('homepage');
        }

        return $this->render(
            'user/edit_password.html.twig',
            [
                'form' => $form->createView(),
                'user' => $user,
            ]
        );
    }// end editPassword()

    /**
     * Edit role action.
     *
     * @param Request $request HTTP request
     * @param User    $user    User entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/edit-role',
        name: 'user_edit_role',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|PUT',
    )]
    #[isGranted('ROLE_ADMIN')]
    public function editRole(Request $request, User $user): Response
    {
        $form = $this->createForm(
            UserRoleType::class,
            $user,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl(
                    'user_edit_role',
                    ['id' => $user->getId()],
                ),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // check how many admins are in database
            $admins = $this->userService->countAdmins();
            // if there is only one admin in database, do not allow change role to user
            if ($admins < 2 && !in_array('ROLE_ADMIN', $form->get('roles')->getData(), true)) {
                $this->addFlash('danger', 'message.only_one_admin');

                return $this->redirectToRoute('user_index');
            }

            $this->userService->editRole($user, $form->get('roles')->getData());
            $this->addFlash(
                'success',
                $this->translator->trans('message.updated_successfully'));

            return $this->redirectToRoute('user_index');
        }

        return $this->render(
            'user/edit_role.html.twig',
            [
                'form' => $form->createView(),
                'user' => $user,
            ]
        );
    }// end editRole()

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param User    $user    User entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/edit',
        name: 'user_edit',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|PUT',
    )]
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(
            UserDataType::class,
            $user,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl(
                    'user_edit',
                    ['id' => $user->getId()],
                ),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->editData($user);
            $this->addFlash(
                'success',
                $this->translator->trans('message.updated_successfully'));

            return $this->redirectToRoute('homepage');
        }

        return $this->render(
            'user/edit.html.twig',
            [
                'form' => $form->createView(),
                'user' => $user,
            ]
        );
    }// end edit()

    /**
     * Block action.
     *
     * @param User $user User entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/block',
        name: 'user_block',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|PUT',
    )]
    #[isGranted('ROLE_ADMIN')]
    public function blockUser(User $user): Response
    {
        $this->userService->blockUser($user, true);
        $this->addFlash(
            'success',
            $this->translator->trans('message.blocked_successfully'));

        return $this->redirectToRoute('user_index');
    }// end blockUser()

    /**
     * Unblock action.
     *
     * @param User $user User entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/unblock',
        name: 'user_unblock',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|PUT',
    )]
    #[isGranted('ROLE_ADMIN')]
    public function unblockUser(User $user): Response
    {
        $this->userService->blockUser($user, false);
        $this->addFlash(
            'success',
            $this->translator->trans('message.unblocked_successfully'));

        return $this->redirectToRoute('user_index');
    }// end unblockUser()
}// end class
