<?php

namespace statikbe\molliesubscriptions\services;

use craft\base\Component;
use statikbe\molliepayments\models\PaymentFormModel;
use statikbe\molliepayments\records\PaymentFormRecord;
use statikbe\molliepayments\elements\Payment as PaymentElement;

class Currency extends Component
{
    const defaultCurrencies = [
        "USD" => ["name" => "United States dollar", "short" => "USD", "symbol" => "$"],
        "EUR" => ["name" => "Euro", "short" => "EUR", "symbol" => "€"],
        "GBP" => ["name" => "Pound sterling", "short" => "GBP", "symbol" => "£"],
        "JPY" => ["name" => "Japanese yen", "short" => "JPY", "symbol" => "¥"],
        "AUD" => ["name" => "Australian dollar", "short" => "AUD", "symbol" => "A$"],
        "CAD" => ["name" => "Canadian dollar", "short" => "CAD", "symbol" => "C$"],
        "CHF" => ["name" => "Swiss franc", "short" => "CHF", "symbol" => "Fr"],
        "CNY" => ["name" => "Renminbi", "short" => "CNY", "symbol" => "元"],
        "SEK" => ["name" => "Swedish krona", "short" => "SEK", "symbol" => "kr"],
        "NZD" => ["name" => "New Zealand dollar", "short" => "NZD", "symbol" => "NZ$"],
        "MXN" => ["name" => "Mexican peso", "short" => "MXN", "symbol" => "$"],
        "SGD" => ["name" => "Singapore dollar", "short" => "SGD", "symbol" => "S$"],
        "HKD" => ["name" => "Hong Kong dollar", "short" => "HKD", "symbol" => "HK$"],
        "NOK" => ["name" => "Norwegian krone", "short" => "NOK", "symbol" => "kr"],
        "KRW" => ["name" => "South Korean won", "short" => "KRW", "symbol" => "₩"],
        "TRY" => ["name" => "Turkish lira", "short" => "TRY", "symbol" => "₺"],
        "RUB" => ["name" => "Russian ruble", "short" => "RUB", "symbol" => "₽"],
        "INR" => ["name" => "Indian rupee", "short" => "INR", "symbol" => "₹"],
        "BRL" => ["name" => "Brazilian real", "short" => "BRL", "symbol" => "R$"],
        "ZAR" => ["name" => "South African rand", "short" => "ZAR", "symbol" => "R"],
    ];

    public function getCurrencies() {
        return self::defaultCurrencies;
    }
}
