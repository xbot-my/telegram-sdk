---
title: Stars 示例
---

# Stars 示例

Telegram 在 2024 年引入了 **Telegram Stars**，用于在应用内购买和转账。这些接口在 Bot API 9.x 中被支持，XBot SDK 对其提供了一致的调用方式。以下示例摘自项目 README【736136284671642†L171-L175】。

## 查询 Stars 余额

```php
// 查询当前 Bot 的 Stars 余额
$balance = $bot->getMyStarBalance()->toArray()['balance'] ?? 0;

// 输出余额
echo "当前 Stars 余额: {$balance}";
```

## 退款 Stars 付款

```php
// 执行 Stars 退款
$bot->refundStarPayment($userId, $chargeId);
```

> 注意：退款操作需要在用户购买后的一定时间内进行，并遵循 Telegram Stars 的退款政策。请确保在调用前校验权限和时间。

## 小结

Stars API 让机器人能够轻松处理付费和退款。SDK 将参数和返回值封装为对象，你可以通过 `->toArray()` 等方法访问更多字段。若需要处理订单列表或 WebApp 支付流程，请参考 Telegram 官方文档。