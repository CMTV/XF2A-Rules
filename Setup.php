<?php
/**
 * Rules xF2 addon by CMTV
 * Enjoy!
 */

namespace CMTV\Rules;

use CMTV\Rules\Constants as C;
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
}