<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Event;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class TechnicalErrorEvent
{
    /** @var \Throwable $error */
    private $error;

    public function __construct(\Throwable $error)
    {
        $this->error = $error;
    }

    public function getError(): \Throwable
    {
        return $this->error;
    }
}
