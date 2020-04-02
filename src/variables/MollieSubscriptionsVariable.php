<?php
/**
 * Mollie Subscriptions plugin for Craft CMS 3.x
 *
 * Subscriptions through Mollie
 *
 * @link      https://www.statik.be
 * @copyright Copyright (c) 2020 Statik
 */

namespace statikbe\molliesubscriptions\variables;

use statikbe\molliesubscriptions\MollieSubscriptions;

use Craft;

/**
 * Mollie Subscriptions Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.mollieSubscriptions }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    Statik
 * @package   MollieSubscriptions
 * @since     1.0.0
 */
class MollieSubscriptionsVariable
{
    // Public Methods
    // =========================================================================

    public function getPlanByid($id)
    {
        return MollieSubscriptions::$plugin->plans->getPlanById($id);
    }
}
