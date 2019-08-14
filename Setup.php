<?php
/**
 * Rules xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\Rules;

use CMTV\Rules\Constants as C;
use CMTV\Rules\Entity\Rule;
use CMTV\Rules\Entity\RuleCategory;
use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Create;

class Setup extends AbstractSetup
{
	use StepRunnerInstallTrait;
	use StepRunnerUpgradeTrait;
	use StepRunnerUninstallTrait;

	//
    // INSTALLATION
    //

    /* Table for rules */
	public function installStep1()
    {
        $this->schemaManager()->createTable(C::_db('rule'), function (Create $table)
        {
            $table->addColumn('rule_id', 'varchar', 50)->primaryKey();
            $table->addColumn('rule_category_id', 'int')->setDefault(0);
            $table->addColumn('display_order', 'int')->setDefault(0);
        });
    }

    /* Table for rule categories */
    public function installStep2()
    {
        $this->schemaManager()->createTable(C::_db('rule_category'), function (Create $table)
        {
            $table->addColumn('rule_category_id', 'int')->autoIncrement();
            $table->addColumn('icon', 'varchar', 50)->setDefault('');
            $table->addColumn('display_order', 'int');
        });
    }

    //
    // UNINSTALLATION
    //

    /* Removing tables */
    public function uninstallStep1()
    {
        $this->schemaManager()->dropTable(C::_db('rule'));
        $this->schemaManager()->dropTable(C::_db('category'));
    }

    /* Removing phrases */
    public function uninstallStep2()
    {
        $phrases = $this->app->finder('XF:Phrase')->where(['title', 'LIKE', 'CMTV_Rules_%'])->fetch();

        foreach ($phrases as $phrase)
        {
            $phrase->delete(false);
        }
    }

    //
    // UPGRADE
    //

    // Removing 'addon_id' from all dynamically created phrases (rules and rule categories)
    public function upgrade1000270Step1(array $stepParams = [])
    {
        $rules = \XF::finder(C::__('Rule'))->fetch();

        /** @var Rule $rule */
        foreach ($rules as $rule)
        {
            if ($rule->MasterTitle)
            {
                $rule->MasterTitle->fastUpdate('addon_id', '');
            }

            if ($rule->MasterDescription)
            {
                $rule->MasterDescription->fastUpdate('addon_id', '');
            }
        }

        unset($rules);

        $categories = \XF::finder(C::__('RuleCategory'))->fetch();

        /** @var RuleCategory $category */
        foreach ($categories as $category)
        {
            if ($category->MasterTitle)
            {
                $category->MasterTitle->fastUpdate('addon_id', '');
            }
        }
    }
}