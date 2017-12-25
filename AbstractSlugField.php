<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm\Fields;

abstract class AbstractSlugField extends CharField
{
    use SlugifyTrait;

    /**
     * @var string
     */
    public $source = 'name';

    /**
     * @var bool
     */
    public $unique = true;
}
