<?php
/**
 * Rules xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\Rules\Repository;

use CMTV\Rules\Constants as C;
use XF\Mvc\Entity\Finder;
use XF\Mvc\Entity\Repository;

class Rule extends Repository
{
    /**
     * @return Finder
     */
    public function findRulesForList()
    {
        return $this->finder(C::__('Rule'))->order('display_order');
    }

    public function getRuleListData()
    {
        $rules = $this->findRulesForList()->fetch();
        $ruleCategories = $this->getRuleCategoryRepo()->getRuleCategoriesForList(true);

        return [
            'ruleCategories' => $ruleCategories,
            'totalRules' => $rules->count(),
            'rules' => $rules->groupBy('rule_category_id')
        ];
    }

    //
    // UTIL
    //

    protected function getRuleCategoryRepo(): RuleCategory
    {
        return $this->repository(C::__('RuleCategory'));
    }
}