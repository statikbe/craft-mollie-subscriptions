<?php
namespace statikbe\molliesubscriptions\events;

use Craft;
use statikbe\molliesubscriptions\elements\Subscription;
use statikbe\molliesubscriptions\records\SubscriptionPaymentRecord;
use yii\base\Event;

/**
 * @author    Statik
 * @package   MollieSubscriptions
 * @since     1.0.0
 */
class PaymentUpdateEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var SubscriptionPaymentRecord the transaction associated being updates.
     */
    public $payment;

    /**
     * @var Subscription the payment element associated with the transations.
     */
    public $subscription;

    /**
     * @string the updated status of the transaction
     */
    public $status;
}