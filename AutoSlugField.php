<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Fields;

use Mindy\Orm\ModelInterface;
use Mindy\QueryBuilder\Expression;

/**
 * Class AutoSlugField.
 */
class AutoSlugField extends AbstractSlugField
{
    /**
     * @var string|null
     */
    protected $oldValue;

    /**
     * Internal event.
     *
     * @param \Mindy\Orm\TreeModel|ModelInterface $model
     * @param $value
     */
    public function beforeInsert(ModelInterface $model, $value)
    {
        if (empty($value)) {
            $slug = $this->createSlug($model->getAttribute($this->source));
        } else {
            $slug = $this->getLastSegment($value);
        }

        if ($model->parent) {
            $slug = $model->parent->getAttribute($this->getAttributeName()).'/'.ltrim($slug, '/');
        }

        $model->setAttribute($this->getAttributeName(), $this->uniqueUrl(ltrim($slug, '/')));
    }

    /**
     * @param $slug
     *
     * @return string
     */
    protected function getLastSegment($slug)
    {
        if (false === strpos($slug, '/')) {
            return $slug;
        }

        return substr($slug, strrpos($slug, '/', -1) + 1);
    }

    /**
     * @param $slug
     *
     * @return string
     */
    protected function getParentSegment($slug)
    {
        if (false === strpos($slug, '/')) {
            return $slug;
        }

        return substr($slug, 0, strrpos($slug, '/', -1));
    }

    /**
     * Internal event.
     *
     * @param \Mindy\Orm\TreeModel|ModelInterface $model
     * @param $value
     */
    public function beforeUpdate(ModelInterface $model, $value)
    {
        if (empty($value)) {
            $slug = $this->createSlug($model->getAttribute($this->source));
        } else {
            $slug = $this->getLastSegment($value);
        }

        if ($model->parent) {
            $slug = implode('/', [
                $this->getParentSegment($model->parent->getAttribute($this->getAttributeName())),
                $slug,
            ]);
        }

        $slug = $this->uniqueUrl(ltrim($slug, '/'), 0, $model->pk);

        $conditions = [
            'lft__gte' => $model->lft,
            'rgt__lte' => $model->rgt,
            'root' => $model->root,
        ];

        $attributeValue = $model->getOldAttribute($this->getAttributeName());
        if (empty($attributeValue)) {
            $attributeValue = $model->getAttribute($this->getAttributeName());
        }

        if ($attributeValue === $slug) {
            return;
        }

        $expr = sprintf(
            'REPLACE(%s, %s, %s)',
            $model->getConnection()->quoteIdentifier($this->getAttributeName()),
            $model->getConnection()->quote($attributeValue),
            $model->getConnection()->quote($slug)
        );

        $qs = $model->objects()->filter($conditions);
        $qs->update([
            $this->getAttributeName() => new Expression($expr),
        ]);

        $model->setAttribute($this->getAttributeName(), $slug);
    }
}
