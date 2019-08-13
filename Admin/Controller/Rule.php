<?php
/**
 * Rules xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\Rules\Admin\Controller;

use CMTV\Rules\Constants as C;
use CMTV\Rules\Service\Rule\Import;
use XF\Admin\Controller\AbstractController;
use XF\ControllerPlugin\Delete;
use XF\Mvc\FormAction;
use XF\Mvc\ParameterBag;

class Rule extends AbstractController
{
    protected function preDispatchController($action, ParameterBag $params)
    {
        $this->assertAdminPermission('CMTV_Rules');
    }

    //
    // ACTIONS
    //

    public function actionIndex()
    {
        $ruleRepo = $this->getRuleRepo();

        $viewParams = [
            'ruleData' => $ruleRepo->getRuleListData()
        ];

        return $this->view(
            C::__('Rule\List'),
            C::_('rule_list'),
            $viewParams
        );
    }

    public function actionAdd()
    {
        $rule = $this->em()->create(C::__('Rule'));
        return $this->ruleAddEdit($rule);
    }

    public function actionEdit(ParameterBag $params)
    {
        $rule = $this->assertRuleExists($params->rule_id);
        return $this->ruleAddEdit($rule);
    }

    public function actionSave(ParameterBag $params)
    {
        $this->assertPostOnly();

        if ($params->rule_id)
        {
            $rule = $this->assertRuleExists($params->rule_id);
        }
        else
        {
            $rule = $this->em()->create(C::__('Rule'));
        }

        $this->ruleSaveProcess($rule)->run();

        return $this->redirect($this->buildLink('rules'));
    }

    public function actionDelete(ParameterBag $params)
    {
        $rule = $this->assertRuleExists($params->rule_id);

        /** @var Delete $plugin */
        $plugin = $this->plugin('XF:Delete');

        return $plugin->actionDelete(
            $rule,
            $this->buildLink('rules/delete', $rule),
            $this->buildLink('rules/edit', $rule),
            $this->buildLink('rules'),
            $rule->title
        );
    }

    public function actionSort(ParameterBag $params)
    {
        if ($this->isPost())
        {
            $rules = $this->finder(C::__('Rule'))->fetch();

            foreach ($this->filter('rules', 'array-json-array') AS $rulesInCategory)
            {
                $lastOrder = 0;

                foreach ($rulesInCategory as $key => $ruleValue)
                {
                    if (!isset($ruleValue['id']) || !isset($rules[$ruleValue['id']]))
                    {
                        continue;
                    }

                    $lastOrder += 10;

                    /** @var \CMTV\Rules\Entity\Rule $rule */
                    $rule = $rules[$ruleValue['id']];
                    $rule->rule_category_id = $ruleValue['parent_id'];
                    $rule->display_order = $lastOrder;
                    $rule->saveIfChanged();
                }
            }

            return $this->redirect($this->buildLink('rules'));
        }
        else
        {
            $ruleData = $this->getRuleRepo()->getRuleListData();

            $viewParams = [
                'ruleData' => $ruleData
            ];

            return $this->view(
                C::__('Rule\Sort'),
                C::_('rule_sort'),
                $viewParams
            );
        }
    }

    public function actionExport()
    {
        if ($this->isPost())
        {
            $rules = $this->finder(C::__('Rule'))->order(['Category.display_order', 'display_order']);
            return $this->plugin('XF:Xml')->actionExport($rules, C::__('Rule\Export'));
        }
        else
        {
            $viewParams = [
                'totalRules' => $this->finder(C::__('Rule'))->total(),
                'totalCategories' => $this->finder(C::__('RuleCategory'))->total()
            ];

            return $this->view(
                C::__('Rule\Export'),
                C::_('rules_export'),
                $viewParams
            );
        }
    }

    public function actionImport()
    {
        return $this->view(
            C::__('Rule\Import'),
            C::_('rule_import')
        );
    }

    public function actionImportDo()
    {
        $this->assertPostOnly();

        /** @var Import $ruleImporter */
        $ruleImporter = $this->service(C::__('Rule\Import'));

        $upload = $this->request->getFile('upload', false);

        if (!$upload)
        {
            return $this->error(\XF::phrase(C::_('please_upload_valid_rules_xml_file')));
        }

        try
        {
            $xml = \XF\Util\Xml::openFile($upload->getTempFile());
        }
        catch (\Exception $e)
        {
            $xml = null;
        }

        if (!$xml || $xml->getName() != 'rules_export')
        {
            return $this->error(\XF::phrase(C::_('please_upload_valid_rules_xml_file')));
        }

        $data = $ruleImporter->getDataFromXml($xml);
        $results = $ruleImporter->importRules($data, $errors);

        if (!empty($errors))
        {
            return $this->error($errors);
        }

        $viewParams = [
            'skipped' => $results['skips'],
            'categories' => $results['categories'],
            'rules' => $results['rules'],
            'totalSkipped' => count($results['skips']),
            'totalCategories' => count($results['categories']),
            'totalRules' => count($results['rules'])
        ];

        return $this->view(
            C::__('Rule\ImportResults'),
            C::_('rule_import_results'),
            $viewParams
        );
    }

    //
    // UTIL
    //

    protected function ruleAddEdit(\CMTV\Rules\Entity\Rule $rule)
    {
        $viewParams = [
            'rule' => $rule,
            'ruleCategories' => $this->getRuleCategoryRepo()->getRuleCategoryTitlePairs()
        ];

        return $this->view(
            C::__('Rule\Edit'),
            C::_('rule_edit'),
            $viewParams
        );
    }

    protected function ruleSaveProcess(\CMTV\Rules\Entity\Rule $rule)
    {
        $form = $this->formAction();

        $ruleInput = $this->filter([
            'rule_category_id' => 'uint',
            'display_order' => 'uint'
        ]);

        if ($rule->isInsert())
        {
            $ruleInput['rule_id'] = $this->filter('rule_id', 'str');
        }

        $form->basicEntitySave($rule, $ruleInput);

        $phraseInput = $this->filter([
            'title' => 'str',
            'description' => 'str'
        ]);

        $form->validate(function (FormAction $form) use ($phraseInput)
        {
            if ($phraseInput['title'] === '')
            {
                $form->logError(\XF::phrase('please_enter_valid_title'), 'title');
            }
        });

        $form->apply(function () use ($phraseInput, $rule)
        {
            $masterTitle = $rule->getMasterTitlePhrase();
            $masterTitle->phrase_text = $phraseInput['title'];
            $masterTitle->save();

            $masterDescription = $rule->getMasterDescriptionPhrase();
            $masterDescription->phrase_text = $phraseInput['description'];
            $masterDescription->save();
        });

        return $form;
    }

    protected function assertRuleExists($id, $with = null, $phraseKey = null): \CMTV\Rules\Entity\Rule
    {
        return $this->assertRecordExists(C::__('Rule'), $id, $with, $phraseKey);
    }

    protected function getRuleRepo(): \CMTV\Rules\Repository\Rule
    {
        return $this->repository(C::__('Rule'));
    }

    protected function getRuleCategoryRepo(): \CMTV\Rules\Repository\RuleCategory
    {
        return $this->repository(C::__('RuleCategory'));
    }
}