---
title: XBot Telegram SDK
---

# XBot Telegram SDK

一个功能强大且易于使用的 PHP Telegram Bot SDK，支持多 Token、多实例和 Laravel 集成。SDK 基于 Guzzle HTTP 客户端实现高性能请求，并提供链式调用、语义化端点和完善的异常处理。

## ✨ 功能特性

* **多 Bot 支持** — 可以同时管理多个 Bot，互不干扰。
* **实例隔离** — 每个 Bot 实例完全独立，状态和配置互相隔离。
* **高性能** — 使用 Guzzle 连接池和异步请求提升性能，适合高并发场景。
* **语义端点** — 按 Telegram API 的语义拆分 Endpoint，避免复杂的 DTO 设计。
* **异常与重试机制** — 内置智能重试和完善的异常处理体系，调用更稳定。
* **统计监控** — 提供详细的调用统计和性能指标，方便你接入监控系统。
* **Laravel 集成** — 开箱即用的 Laravel ServiceProvider 和中间件，支持自动路由和依赖注入。
* **链式调用** — 统一的返回对象支持 `→toArray()`、`→toObject()`、`→toJson()`，使用灵活。

> 若要详细了解所有端点与类的对应关系，请参阅[API 覆盖](reference/api_coverage.md)文档。

## 🚀 快速入口

阅读以下章节即可快速上手：

* [快速开始](guide/quickstart.md) — 安装 SDK 并发送第一条消息。
* [Laravel 集成](guide/laravel.md) — 在 Laravel 应用中注册和配置 Telegram 服务。
* [Webhook 与更新处理](guide/webhook.md) — 配置 Webhook、编写 Update 处理器和命令路由。
* [选项透传](guide/options.md) — Bot API 9.2 新增参数的用法。
* [日志配置](guide/logging.md) — 自定义日志输出和采集策略。

SDK 的初衷在于让开发者只需关注业务逻辑，底层的网络请求和异常处理均由库内部完成。若您在使用过程中发现任何问题或有改进建议，欢迎在 [GitHub 项目页](https://github.com/xbot-my/telegram-sdk) 提出 Issue。