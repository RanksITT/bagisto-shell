<?php

namespace Local\SSLCommerz\Enums;

/**
 * Transaction statuses SSLCommerz posts back and answers the validation API with.
 */
enum SSLCommerzPaymentStatus: string
{
    /**
     * The payment was taken, and this is the first time it has been validated.
     */
    case VALID = 'VALID';

    /**
     * The payment was taken and had already been validated once before.
     *
     * The customer's return and the IPN each validate the same payment, so whichever arrives
     * second is answered with this rather than with VALID.
     */
    case VALIDATED = 'VALIDATED';

    /**
     * The customer has not finished paying yet.
     */
    case PENDING = 'PENDING';

    /**
     * The payment was declined or failed.
     */
    case FAILED = 'FAILED';

    /**
     * The customer cancelled on the SSLCommerz page.
     */
    case CANCELLED = 'CANCELLED';

    /**
     * The session ran out before the customer paid.
     */
    case EXPIRED = 'EXPIRED';

    /**
     * The customer never chose a way to pay.
     */
    case UNATTEMPTED = 'UNATTEMPTED';

    /**
     * The validation API does not recognise the validation id.
     */
    case INVALID_TRANSACTION = 'INVALID_TRANSACTION';

    /**
     * Determine whether the status represents a payment that was taken.
     */
    public function isSuccessful(): bool
    {
        return in_array($this, [self::VALID, self::VALIDATED], true);
    }

    /**
     * Determine whether the customer cancelled the payment.
     */
    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
    }
}
