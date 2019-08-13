<?php
/**
 * Rules xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\Rules\Admin\View\Rule;

use XF\Mvc\View;

class Export extends View
{
    public function renderXml()
    {
        /** @var \DOMDocument $document */
        $document = $this->params['xml'];
        $this->response->setDownloadFileName('rules.xml');
        return $document->saveXML();
    }
}