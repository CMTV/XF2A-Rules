<?php
/**
 * Rules xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\Rules\Service\Rule;

use CMTV\Rules\Constants as C;
use XF\Mvc\Entity\Finder;
use XF\Service\AbstractXmlExport;

class Export extends AbstractXmlExport
{
    public function getRootName()
    {
        return 'rules_export';
    }

    public function export(Finder $rules)
    {
        $rules = $rules->fetch();

        $ruleCategories = $this->finder(C::__('RuleCategory'))
            ->with('MasterTitle')
            ->order(['display_order']);

        return $this->exportFromArray($rules->toArray(), $ruleCategories->fetch()->toArray());
    }

    public function exportFromArray(array $rules, array $ruleCategories)
    {
        $document = $this->createXml();
        $rootNode = $document->createElement($this->getRootName());
        $document->appendChild($rootNode);

        //
        // Exporting rules
        //

        $rulesNode = $document->createElement('rules');

        foreach ($rules as $rule)
        {
            $ruleNode = $document->createElement('rule');

            $ruleNode->setAttribute('rule_id', $rule['rule_id']);

            $ruleNode->setAttribute('title', $rule['title']);
            $this->exportCdata($ruleNode, $rule['description']);

            if ($rule['rule_category_id'])
            {
                $ruleNode->setAttribute('rule_category_id', $rule['rule_category_id']);
            }

            $ruleNode->setAttribute('display_order', $rule['display_order']);

            //

            $rulesNode->appendChild($ruleNode);
        }

        $rootNode->appendChild($rulesNode);

        //
        // Exporting rule categories
        //

        $ruleCategoriesNode = $document->createElement('rule_categories');

        foreach ($ruleCategories as $ruleCategory)
        {
            $ruleCategoryNode = $document->createElement('rule_category');
            $ruleCategoryNode->setAttribute('id', $ruleCategory['rule_category_id']);
            $ruleCategoryNode->setAttribute('title', $ruleCategory['title']);
            $ruleCategoryNode->setAttribute('icon', $ruleCategory['icon']);
            $ruleCategoryNode->setAttribute('display_order', $ruleCategory['display_order']);

            $ruleCategoriesNode->appendChild($ruleCategoryNode);
        }

        $rootNode->appendChild($ruleCategoriesNode);

        //

        return $document;
    }
}