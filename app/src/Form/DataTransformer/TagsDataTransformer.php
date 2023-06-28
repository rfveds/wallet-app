<?php
/**
 * Tags data transformer.
 */

namespace App\Form\DataTransformer;

use App\Entity\Tag;
use App\Service\TagServiceInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Class TagsDataTransformer.
 *
 * @implements DataTransformerInterface<mixed, mixed>
 */
class TagsDataTransformer implements DataTransformerInterface
{
    /**
     * Tag service.
     */
    private TagServiceInterface $tagService;

    /**
     * Security.
     *
     * @var Security Security helper
     */
    private Security $security;

    /**
     * Constructor.
     *
     * @param TagServiceInterface $tagService Tag service
     * @param Security            $security   Security helper
     */
    public function __construct(TagServiceInterface $tagService, Security $security)
    {
        $this->tagService = $tagService;
        $this->security = $security;
    }// end __construct()

    /**
     * Transform array of tags to string of tag titles.
     *
     * @param Collection<int, Tag> $value Tags entity collection
     *
     * @return string Result
     */
    public function transform($value): string
    {
        if ($value->isEmpty()) {
            return '';
        }

        $tagTitles = [];

        foreach ($value as $tag) {
            $tagTitles[] = $tag->getTitle();
        }

        return implode(', ', $tagTitles);
    }

    /**
     * Transform string of tag names into array of Tag entities.
     *
     * @param string $value String of tag names
     *
     * @return array<int, Tag> Result
     */
    public function reverseTransform($value): array
    {
        $tagTitles = explode(',', $value);

        $tags = [];

        foreach ($tagTitles as $tagTitle) {
            if ('' !== trim($tagTitle)) {
                $tag = $this->tagService->findOneByTitle(strtolower($tagTitle));
                if (null === $tag) {
                    $tag = new Tag();
                    $tag->setTitle($tagTitle);
                    $tag->setAuthor($this->security->getUser());
                    if ($this->security->isGranted('ROLE_ADMIN')) {
                        $tag->setUserOrAdmin('admin');
                    } else {
                        $tag->setUserOrAdmin('user');
                    }

                    $this->tagService->save($tag);
                }
                $tags[] = $tag;
            }
        }

        return $tags;
    }
}
