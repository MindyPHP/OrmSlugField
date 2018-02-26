<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Fields;

use Cocur\Slugify\Slugify;
use Mindy\Orm\QuerySetInterface;

/**
 * Class UniqueUrl.
 */
trait SlugifyTrait
{
    /**
     * @var Slugify
     */
    protected $slugify;

    /**
     * @param $source
     *
     * @return string
     */
    protected function createSlug($source): string
    {
        if (null === $this->slugify) {
            $this->slugify = new Slugify();
        }

        return $this->slugify->slugify($source);
    }

    /**
     * @param string $url
     * @param int  $count
     * @param null $pk
     *
     * @return string
     */
    public function generateUniqueUrl(string $url, int $count = 0, $pk = null): string
    {
        $url = ltrim($url, '/');

        /* @var $model \Mindy\Orm\Model */
        $model = $this->getModel();
        $newUrl = $url;
        if ($count) {
            $newUrl .= '-'.$count;
        }

        /** @var QuerySetInterface $qs */
        $qs = call_user_func([$model, 'objects'])
            ->filter([$this->getName() => $newUrl]);
        if ($pk) {
            $qs = $qs->exclude(['pk' => $pk]);
        }
        if ($qs->count() > 0) {
            ++$count;

            return $this->generateUniqueUrl($url, $count, $pk);
        }

        return $newUrl;
    }
}
