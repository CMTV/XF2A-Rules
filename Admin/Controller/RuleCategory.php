<?php
/**
 * Rules xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\Rules\Admin\Controller;

use CMTV\Rules\Constants as C;
use XF\Admin\Controller\AbstractController;
use XF\ControllerPlugin\Delete;
use XF\Mvc\FormAction;
use XF\Mvc\ParameterBag;

class RuleCategory extends AbstractController
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
        return $this->redirectPermanently($this->buildLink('rules'));
    }

    public function actionAdd()
    {
        $ruleCategory = $this->em()->create(C::__('RuleCategory'));
        return $this->ruleCategoryAddEdit($ruleCategory);
    }

    public function actionEdit(ParameterBag $params)
    {
        $ruleCategory = $this->assertRuleCategoryExists($params['rule_category_id']);
        return $this->ruleCategoryAddEdit($ruleCategory);
    }

    public function actionSave(ParameterBag $params)
    {
        $this->assertPostOnly();

        if ($params['rule_category_id'])
        {
            $ruleCategory = $this->assertRuleCategoryExists($params['rule_category_id']);
        }
        else
        {
            $ruleCategory = $this->em()->create(C::__('RuleCategory'));
        }

        $this->ruleSaveProcess($ruleCategory)->run();

        return $this->redirect($this->buildLink('rules'));
    }

    public function actionDelete(ParameterBag $params)
    {
        $ruleCategory = $this->assertRuleCategoryExists($params['rule_category_id']);

        /** @var Delete $plugin */
        $plugin = $this->plugin('XF:Delete');

        return $plugin->actionDelete(
            $ruleCategory,
            $this->buildLink('rule-categories/delete', $ruleCategory),
            $this->buildLink('rule-categories/edit', $ruleCategory),
            $this->buildLink('rules'),
            $ruleCategory->title
        );
    }

    public function actionDeleteRules(ParameterBag $params)
    {
        if ($this->isPost())
        {
            $ruleCategoryId = $params['rule_category_id'];

            $this->app->jobManager()->enqueue(
                C::__('DeleteRules'),
                ['category_id' => $ruleCategoryId],
                true
            );

            return $this->redirectPermanently($this->buildLink('rules'));
        }
        else
        {
            $ruleCategory = $this->assertRuleCategoryExists($params['rule_category_id']);

            $totalRules = $this->finder(C::__('Rule'))
                ->where('rule_category_id', $ruleCategory->rule_category_id)
                ->total();

            $viewParams = [
                'totalRules' => $totalRules,
                'ruleCategory' => $ruleCategory
            ];

            return $this->view(
                C::__('RuleCategory\DeleteRules'),
                C::_('delete_rules'),
                $viewParams
            );
        }
    }

    //
    // UTIL
    //

    protected function ruleCategoryAddEdit(\CMTV\Rules\Entity\RuleCategory $ruleCategory)
    {
        $viewParams = [
            'ruleCategory' => $ruleCategory
        ];

        return $this->view(
            C::__('RuleCategory\Edit'),
            C::_('rule_category_edit'),
            $viewParams
        );
    }

    protected function ruleSaveProcess(\CMTV\Rules\Entity\RuleCategory $ruleCategory)
    {
        $entityInput = $this->filter([
            'icon' => 'str',
            'display_order' => 'uint'
        ]);

        $form = $this->formAction();
        $form->basicEntitySave($ruleCategory, $entityInput);

        $titlePhrase = $this->filter('title', 'str');

        $form->validate(function (FormAction $form) use ($titlePhrase)
        {
            if ($titlePhrase === '')
            {
                $form->logError(\XF::phrase('please_enter_valid_title'), 'title');
            }
        });

        $form->apply(function () use ($titlePhrase, $ruleCategory)
        {
            $masterTitle = $ruleCategory->getMasterTitlePhrase();
            $masterTitle->phrase_text = $titlePhrase;
            $masterTitle->save();
        });

        return $form;
    }

    protected function assertRuleCategoryExists($id, $with = null, $phraseKey = null): \CMTV\Rules\Entity\RuleCategory
    {
        if ($id == 0)
        {
            return $this->getRuleCategoryRepo()->getDefaultCategory();
        }

        return $this->assertRecordExists(C::__('RuleCategory'), $id, $with, $phraseKey);
    }

    protected function getRuleCategoryRepo(): \CMTV\Rules\Repository\RuleCategory
    {
        return $this->repository(C::__('RuleCategory'));
    }
}