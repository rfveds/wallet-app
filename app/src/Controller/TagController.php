<?php
/**
 * wallet-app.
 *
 * (c) Karol Kijowski , 2023
 */

namespace App\Controller;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TagController.
 */
#[Route('/tag')]
class TagController extends AbstractController
{
    /**
     * Index action.
     *
     * @param Request            $request       HTTP request
     * @param TagRepository      $tagRepository Tag repository
     * @param PaginatorInterface $paginator     Paginator
     *
     * @return Response HTTP response
     */
    #[Route(
        '/',
        name: 'tag_index',
        methods: 'GET'
    )]
    public function index(Request $request, TagRepository $tagRepository, PaginatorInterface $paginator): Response
    {
        $pagination = $paginator->paginate(
            $tagRepository->queryAll(),
            $request->query->getInt('page', 1),
            TagRepository::PAGINATOR_ITEMS_PER_PAGE
        );

        return $this->render(
            'tag/index.html.twig',
            ['pagination' => $pagination]
        );
    }


    /**
     * Show action.
     *
     * @param Tag $tag Tag entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'tag_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET',
    )]
    public function show(Tag $tag): Response
    {
        return $this->render(
            'tag/show.html.twig',
            ['tag' => $tag]
        );
    }
}
