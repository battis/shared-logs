<?php
/** URLsBindingTrait */

namespace Battis\SharedLogs\Database\Bindings\Traits;

use Battis\SharedLogs\Database\Bindings\UrlsBinding;

/**
 * Provide an as-needed instance of UrlsBinding
 *
 * @author Seth Battis <seth@battis.net>
 */
trait UrlsBindingTrait
{
    /** @var UrlsBinding Binding between Url objects and `urls` database table */
    private $urls;

    /**
     * Provide an instance of UrlsBinding
     *
     * @uses AbstractBinding::$database()
     *
     * @return UrlsBinding
     */
    protected function urls()
    {
        if (empty($this->urls)) {
            $this->urls = new UrlsBinding($this->database());
        }
        return $this->urls;
    }
}