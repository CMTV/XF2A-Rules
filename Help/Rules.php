<?php
/**
 * Rules xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\Rules\Help;

use CMTV\Rules\Constants as C;
use CMTV\Rules\Repository\Rule;
use XF\Mvc\Controller;
use XF\Mvc\Reply\View;

class Rules
{
    static function renderRules(Controller $controller, View &$reply)
    {
        /** @var Rule $rulesRepo */
        $rulesRepo = $controller->repository(C::__('Rule'));

        $ruleData = $rulesRepo->getRuleListData();

        foreach (array_keys($ruleData['ruleCategories']) as $catId)
        {
            if (!array_key_exists($catId, $ruleData['rules']))
            {
                unset($ruleData['ruleCategories'][$catId]);
            }
        }

        $reply->setParam('ruleData', $ruleData);
    }
}