<?php

namespace studioespresso\molliesubscriptions\migrations;

use Craft;
use craft\db\Migration;
use studioespresso\molliesubscriptions\records\SubscriberRecord;
use studioespresso\molliesubscriptions\records\SubscriptionPaymentRecord;
use studioespresso\molliesubscriptions\records\SubscriptionPlanRecord;
use studioespresso\molliesubscriptions\records\SubscriptionRecord;

/***
 * @author    Studio Espresso
 * @package   MollieSubscriptions
 * @since     1.0.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================
    public $driver;


    // Public Methods
    // =========================================================================
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->addForeignKeys();
            Craft::$app->db->schema->refresh();
        }

        return true;
    }

    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();
        return true;
    }

    // Protected Methods
    // =========================================================================

    protected function createTables()
    {
        $tablesCreated = false;
        $tableSchema = Craft::$app->db->schema->getTableSchema(SubscriptionPlanRecord::tableName());
        if ($tableSchema === null) {
            $tablesCreated = true;

            $this->createTable(
                SubscriptionRecord::tableName(),
                [
                    'id' => $this->integer()->notNull(),
                    'email' => $this->string()->notNull(),
                    'subscriber' => $this->string(30),
                    'subscriptionStatus' => $this->string()->notNull(),
                    'amount' => $this->decimal("10,2")->notNull(),
                    'plan' => $this->integer()->notNull(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    'PRIMARY KEY(id)',
                ]
            );


            $this->createTable(
                SubscriberRecord::tableName(),
                [
                    'id' => $this->string(30),
                    'name' => $this->string(),
                    'email' => $this->string()->notNull(),
                    'userId' => $this->integer(),
                    'locale' => $this->string(5),
                    'metadata' => $this->text(),
                    'links' => $this->text(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                ]
            );

            $this->createTable(
                SubscriptionPlanRecord::tableName(),
                [
                    'id' => $this->primaryKey(),
                    'title' => $this->string(255)->notNull()->defaultValue(''),
                    'handle' => $this->string(255)->notNull()->defaultValue(''),
                    'currency' => $this->string(3)->defaultValue('EUR'),
                    'amount' => $this->decimal("10,2"),
                    'times' => $this->integer(3),
                    'interval' => $this->integer(3),
                    'intervalType' => $this->string(6),
                    'description' => $this->text()->notNull(),
                    'fieldLayout' => $this->integer(10),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                ]
            );

            $this->createTable(SubscriptionPaymentRecord::tableName(), [
                'id' => $this->string()->notNull(),
                'subscription' => $this->integer()->notNull(),
                'amount' => $this->decimal("10,2")->notNull(),
                'currency' => $this->string(3)->defaultValue('EUR'),
                'status' => $this->string()->notNull(),
                'method' => $this->string(),
                'paidAt' => $this->dateTime(),
                'canceledAt' => $this->dateTime(),
                'expiresAt' => $this->dateTime(),
                'failedAt' => $this->dateTime(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
                'PRIMARY KEY(id)',
            ]);
        }
        return $tablesCreated;
    }

    protected function addForeignKeys()
    {

    }

    protected function removeTables()
    {
        $this->dropTableIfExists(SubscriptionPlanRecord::tableName());
        $this->dropTableIfExists(SubscriptionPaymentRecord::tableName());
        $this->dropTableIfExists(SubscriptionRecord::tableName());
        $this->dropTableIfExists(SubscriberRecord::tableName());
    }
}
