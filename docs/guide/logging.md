---
title: 日志配置
---

# 日志配置

良好的日志记录有助于诊断问题并监控机器人运行状况。XBot Telegram SDK 通过环境变量控制日志功能，并提供丰富的事件名称以区分不同的情况。以下信息摘自项目 README【736136284671642†L222-L233】。

## 环境变量

| 变量 | 描述 |
|-----|-----|
| `TELEGRAM_LOG_ENABLED` | 开启或关闭日志 (`true`/`false`) |
| `TELEGRAM_LOG_SUPPRESS_INFO` | 当设为 `true` 时，只记录警告和错误，屏蔽正常请求/响应日志 |
| `TELEGRAM_LOG_CHANNEL` | 指定 Laravel 的日志通道，例如 `stack` |

示例配置：

```ini
TELEGRAM_LOG_ENABLED=true
TELEGRAM_LOG_SUPPRESS_INFO=false
TELEGRAM_LOG_CHANNEL=stack
```

## 事件命名

SDK 在执行请求、解析响应、重试以及出现异常时，会触发以下事件（日志频道）：

* `telegram.request` — 请求发出时的事件。
* `telegram.response` — 正常返回的响应，附带 `elapsed_ms` 表示耗时。
* `telegram.client_exception` — 客户端请求异常（如无效参数）。
* `telegram.server_exception` — Telegram 端返回错误码。
* `telegram.connect_exception` — 网络连接错误。
* `telegram.redirect_exception` — HTTP 重定向错误。
* `telegram.request_exception` — Guzzle 请求异常。
* `telegram.unexpected_exception` — 其他未知异常。
* `telegram.retry` — 发生重试时触发，包含 `attempt` 和 `reason` 字段。
* `telegram.retry.delay` — 每次重试延迟时触发，包含 `attempt` 和 `delay_ms` 字段。

通过监听或查看这些日志，你可以快速定位问题并评估接口性能。如果你在非 Laravel 环境下使用 SDK，可以通过注入自定义 Logger 实现这些事件的记录。