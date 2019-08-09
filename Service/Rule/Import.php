<?php
/**
 * Rules xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\Rules\Service\Rule;

use CMTV\Rules\Constants as C;
use CMTV\Rules\Entity\Rule;
use CMTV\Rules\Repository\RuleCategory;
use XF\Service\AbstractService;

class Import extends AbstractService
{
    public function importRules($data, &$errors = [])
    {
        $results = [
            'categories' => [],
            'rules' => [],
            'skips' => $data['skips']
        ];

        $this->db()->beginTransaction();

        foreach ($data['categories'] as $category)
        {
            /** @var \CMTV\Rules\Entity\RuleCategory $catEntity */
            $catEntity = $this->em()->create(C::__('RuleCategory'));
            $catEntity->icon = $category['icon'];
            $catEntity->display_order = $category['display_order'];

            $masterTitle = $catEntity->getMasterTitlePhrase();
            $masterTitle->phrase_text = $category['title'];
            $catEntity->addCascadedSave($masterTitle);

            if (!$catEntity->preSave())
            {
                foreach ($catEntity->getErrors() as $field => $error)
                {
                    $errors[$field . '__' . $category['rule_category_id']] = $error;
                }

                $this->db()->rollback();
                return false;
            }

            $catEntity->save(true, false);

            $data['catIdMap'][$category['rule_category_id']] = $catEntity->rule_category_id;

            $results['categories'][] = [
                'id' => $catEntity->rule_category_id,
                'title' => $catEntity->title
            ];
        }

        foreach ($data['rules'] as $rule)
        {
            /** @var Rule $ruleEntity */
            $ruleEntity = $this->em()->create(C::__('Rule'));
            $ruleEntity->rule_id = $rule['rule_id'];
            $ruleEntity->display_order = $rule['display_order'];
            $ruleEntity->rule_category_id = ($rule['rule_category_id'] === 0) ? 0 : $data['catIdMap'][$rule['rule_category_id']];

            $masterTitle = $ruleEntity->getMasterTitlePhrase();
            $masterTitle->phrase_text = $rule['title'];

            $masterDesc = $ruleEntity->getMasterDescriptionPhrase();
            $masterDesc->phrase_text = $rule['description'];

            $ruleEntity->addCascadedSave($masterTitle);
            $ruleEntity->addCascadedSave($masterDesc);

            if (!$ruleEntity->preSave())
            {
                foreach ($ruleEntity->getErrors() as $field => $error)
                {
                    $errors[$field . '__' . $rule['rule_id']] = $error;
                }

                $this->db()->rollback();
                return false;
            }

            $ruleEntity->save(true, false);

            $results['rules'][] = [
                'id' => $ruleEntity->rule_id,
                'title' => $ruleEntity->title
            ];
        }

        if (!$errors)
        {
            $this->db()->commit();
            return $results;
        }
        else
        {
            $this->db()->rollback();
            return false;
        }
    }

    public function getDataFromXml(\SimpleXMLElement $xml)
    {
        return $this->getCategoryDataFromXml($xml) + $this->getRuleDataFromXml($xml);
    }

    public function getCategoryDataFromXml(\SimpleXMLElement $xml)
    {
        $catIdMap = [];
        $newCategories = [];

        foreach ($xml->rule_categories->rule_category AS $newCategory)
        {
            $importId = (int)$newCategory['id'];

            if (array_key_exists($importId, $catIdMap))
            {
                continue; // Skip all categories with the same ID in XML
            }

            $catIdMap[$importId] = 0;

            $newCategories[] = [
                'rule_category_id' => $importId,
                'title' => (string)$newCategory['title'],
                'icon' => (string)$newCategory['icon'],
                'display_order' => (int)$newCategory['display_order']
            ];
        }

        return [
            'catIdMap' => $catIdMap,
            'categories' => $newCategories
        ];
    }

    public function getRuleDataFromXml(\SimpleXMLElement $xml)
    {
        $skips = [];

        $existingIds = $this->db()->fetchAllColumn(
            "SELECT rule_id FROM " . C::_db('rule')
        );

        $importedIds = [];
        $newRules = [];

        foreach ($xml->rules->rule as $newRule)
        {
            $importId = (string)$newRule['rule_id'];

            if (in_array($importId, $importedIds))
            {
                continue; // Skip all rules with the same ID in XML
            }

            $importedIds[] = $importId;

            if (in_array($importId, $existingIds))
            {
                $skips[] = [
                    'id' => $importId,
                    'title' => (string)$newRule['title']
                ];

                continue;
            }

            $newRules[] = [
                'rule_id' => $importId,
                'rule_category_id' => (isset($newRule['rule_category_id']) ? (int)$newRule['rule_category_id'] : 0),
                'title' => (string)$newRule['title'],
                'description' => \XF\Util\Xml::processSimpleXmlCdata($newRule),
                'display_order' => (int)$newRule['display_order']
            ];
        }

        return [
            'rules' => $newRules,
            'skips' => $skips
        ];
    }

    protected function getRuleCategoryRepo(): RuleCategory
    {
        return $this->repository(C::__('RuleCategory'));
    }
}