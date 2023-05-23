<?php
/**
 * Tags data transformer.
 */

namespace App\Form\DataTransformer;

use App\Entity\Tag;
use App\Service\TagServiceInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\DataTransformerInterface;

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
     * Constructor.
     *
     * @param TagServiceInterface $tagService Tag service
     */
    public function __construct(TagServiceInterface $tagService)
    {
        $this->tagService = $tagService;
    }

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

                    $this->tagService->save($tag);
                }
                $tags[] = $tag;
            }
        }

        return $tags;
    }
}
