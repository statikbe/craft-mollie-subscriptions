<?php

namespace statikbe\molliesubscriptions\behaviors;

use Craft;
use craft\elements\db\EntryQuery;
use statikbe\molliesubscriptions\elements\Subscription;
use yii\base\Behavior;

/**
 * Class EntryQueryBehavior
 *
 * @property EntryQuery $owner
 */
class CraftVariableBehavior extends Behavior
{
    public function subscriptions($criteria = null): \craft\elements\db\ElementQueryInterface
    {
        $query = Subscription::find();
        if ($criteria) {
            Craft::configure($query, $criteria);
        }
        return $query;
    }
}