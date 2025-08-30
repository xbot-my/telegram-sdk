<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class RefundStarPayment extends BaseEndpoint
{
    public function __invoke(int $userId, string $telegramPaymentChargeId): bool
    {
        $this->validateUserId($userId);
        $this->validateRequired(['telegram_payment_charge_id' => $telegramPaymentChargeId], ['telegram_payment_charge_id']);

        $parameters = $this->prepareParameters([
            'user_id' => $userId,
            'telegram_payment_charge_id' => $telegramPaymentChargeId,
        ]);

        $response = $this->call('refundStarPayment', $parameters);
        return (bool)$response->getResult();
    }
}

