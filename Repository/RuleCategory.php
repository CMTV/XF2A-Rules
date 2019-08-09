<?php
/**
 * Rules xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\Rules\Repository;

use CMTV\Rules\Constants as C;
use XF\Mvc\Entity\Repository;

class RuleCategory extends Repository
{
    public function getDefaultCategory()
    {
        $category = $this->em->create(C::__('RuleCategory'));
        $category->setTrusted('rule_category_id', 0);
        $category->setTrusted('display_order', 0);
        $category->setReadOnly(true);

        return $category;
    }

    public function getRuleCategoriesForList($getDefault = false)
    {
        $categories = $this->finder(C::__('RuleCategory'))
            ->with('MasterTitle')
            ->order(['display_order'])
            ->fetch();

        if ($getDefault)
        {
            $defaultCategory = $this->getDefaultCategory();
            $categories = [$defaultCategory] + $categories->toArray();
        }

        return $categories;
    }

    public function getRuleCategoryTitlePairs()
    {
        $ruleCategories = $this->finder(C::__('RuleCategory'))->order('display_order');
        return $ruleCategories->fetch()->pluckNamed('title', 'rule_category_id');
    }
}