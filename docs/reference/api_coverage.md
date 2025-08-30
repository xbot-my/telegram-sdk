---
title: API 覆盖
---

# Telegram Bot API → SDK Endpoint Mapping

此文档来自项目的 `docs/API_COVERAGE.md` 文件，用于列出官方 Bot API 的方法及其对应的 SDK 类。目标版本为 Bot API 9.2（2025‑08‑15），列举的所有端点均已在 `src/API` 中实现【247764572816424†L13-L15】。

## 映射规则

* SDK 类放在命名空间 `XBot\Telegram\API` 下。
* 类名使用 StudlyCase 格式，去掉下划线，例如 `GetMe` 对应 `getMe`【247764572816424†L4-L11】。
* 在 Bot 实例上同时提供蛇形别名，例如 `$bot->get_webhook_info()` 等【247764572816424†L16-L23】。

## 实现的端点

以下表格列出了官方方法与 SDK 类的对应关系。若需比较最新 Bot API 文档，请访问 [Telegram 官方文档](https://core.telegram.org/bots/api#available-methods)。此列表改编自项目文档【247764572816424†L17-L139】。

| Bot API 方法 | SDK 类 |
|-------------|-------|
| addStickerToSet | AddStickerToSet |
| answerCallbackQuery | AnswerCallbackQuery |
| answerInlineQuery | AnswerInlineQuery |
| answerPreCheckoutQuery | AnswerPreCheckoutQuery |
| answerShippingQuery | AnswerShippingQuery |
| answerWebAppQuery | AnswerWebAppQuery |
| approveChatJoinRequest | ApproveChatJoinRequest |
| approveSuggestedPost | ApproveSuggestedPost |
| declineSuggestedPost | DeclineSuggestedPost |
| banChatMember | BanChatMember |
| banChatSenderChat | BanChatSenderChat |
| close | Close |
| closeForumTopic | CloseForumTopic |
| closeGeneralForumTopic | CloseGeneralForumTopic |
| copyMessage | CopyMessage |
| copyMessages | CopyMessages |
| createChatInviteLink | CreateChatInviteLink |
| createForumTopic | CreateForumTopic |
| createInvoiceLink | CreateInvoiceLink |
| createNewStickerSet | CreateNewStickerSet |
| deleteBusinessMessages | DeleteBusinessMessages |
| deleteChatPhoto | DeleteChatPhoto |
| deleteChatStickerSet | DeleteChatStickerSet |
| deleteForumTopic | DeleteForumTopic |
| deleteMessage | DeleteMessage |
| deleteMessages | DeleteMessages |
| deleteMyCommands | DeleteMyCommands |
| deleteStickerFromSet | DeleteStickerFromSet |
| deleteWebhook | DeleteWebhook |
| declineChatJoinRequest | DeclineChatJoinRequest |
| editChatInviteLink | EditChatInviteLink |
| editForumTopic | EditForumTopic |
| editGeneralForumTopic | EditGeneralForumTopic |
| editMessageCaption | EditMessageCaption |
| editMessageMedia | EditMessageMedia |
| editMessageReplyMarkup | EditMessageReplyMarkup |
| editMessageText | EditMessageText |
| editMessageLiveLocation | EditMessageLiveLocation |
| exportChatInviteLink | ExportChatInviteLink |
| forwardMessage | ForwardMessage |
| forwardMessages | ForwardMessages |
| getChat | GetChat |
| getChatAdministrators | GetChatAdministrators |
| getChatBoosts | GetChatBoosts |
| getChatMember | GetChatMember |
| getChatMemberCount | GetChatMemberCount |
| getChatMenuButton | GetChatMenuButton |
| getCustomEmojiStickers | GetCustomEmojiStickers |
| getFile | GetFile |
| getForumTopicIconStickers | GetForumTopicIconStickers |
| getGameHighScores | GetGameHighScores |
| getMe | GetMe |
| getMyCommands | GetMyCommands |
| getMyDefaultAdministratorRights | GetMyDefaultAdministratorRights |
| getMyDescription | GetMyDescription |
| getMyName | GetMyName |
| getMyShortDescription | GetMyShortDescription |
| getMyStarBalance | GetMyStarBalance |
| getStickerSet | GetStickerSet |
| getUpdates | GetUpdates |
| getUserChatBoosts | GetUserChatBoosts |
| getUserProfilePhotos | GetUserProfilePhotos |
| getWebhookInfo | GetWebhookInfo |
| hideGeneralForumTopic | HideGeneralForumTopic |
| leaveChat | LeaveChat |
| logOut | LogOut |
| pinChatMessage | PinChatMessage |
| promoteChatMember | PromoteChatMember |
| readBusinessMessage | ReadBusinessMessage |
| refundStarPayment | RefundStarPayment |
| reopenForumTopic | ReopenForumTopic |
| reopenGeneralForumTopic | ReopenGeneralForumTopic |
| replaceStickerInSet | ReplaceStickerInSet |
| restrictChatMember | RestrictChatMember |
| revokeChatInviteLink | RevokeChatInviteLink |
| sendAnimation | SendAnimation |
| sendAudio | SendAudio |
| sendChatAction | SendChatAction |
| sendContact | SendContact |
| sendDice | SendDice |
| sendDocument | SendDocument |
| sendGame | SendGame |
| sendInvoice | SendInvoice |
| sendLocation | SendLocation |
| sendMediaGroup | SendMediaGroup |
| sendMessage | SendMessage |
| sendPhoto | SendPhoto |
| sendPaidMedia | SendPaidMedia |
| sendPoll | SendPoll |
| sendSticker | SendSticker |
| sendVenue | SendVenue |
| sendVideo | SendVideo |
| sendVideoNote | SendVideoNote |
| sendVoice | SendVoice |
| setChatAdministratorCustomTitle | SetChatAdministratorCustomTitle |
| setChatDescription | SetChatDescription |
| setChatMenuButton | SetChatMenuButton |
| setChatPermissions | SetChatPermissions |
| setChatPhoto | SetChatPhoto |
| setChatStickerSet | SetChatStickerSet |
| setChatTitle | SetChatTitle |
| setMessageReaction | SetMessageReaction |
| setMyCommands | SetMyCommands |
| setMyDefaultAdministratorRights | SetMyDefaultAdministratorRights |
| setMyDescription | SetMyDescription |
| setMyName | SetMyName |
| setMyShortDescription | SetMyShortDescription |
| setStickerEmojiList | SetStickerEmojiList |
| setStickerKeywords | SetStickerKeywords |
| setStickerMaskPosition | SetStickerMaskPosition |
| setStickerPositionInSet | SetStickerPositionInSet |
| setStickerSetTitle | SetStickerSetTitle |
| setWebhook | SetWebhook |
| stopMessageLiveLocation | StopMessageLiveLocation |
| stopPoll | StopPoll |
| unbanChatMember | UnbanChatMember |
| unbanChatSenderChat | UnbanChatSenderChat |
| unhideGeneralForumTopic | UnhideGeneralForumTopic |
| unpinAllChatMessages | UnpinAllChatMessages |
| unpinAllForumTopicMessages | UnpinAllForumTopicMessages |
| unpinAllGeneralForumTopicMessages | UnpinAllGeneralForumTopicMessages |
| unpinChatMessage | UnpinChatMessage |
| uploadStickerFile | UploadStickerFile |

> **状态说明：** 以上列出的所有方法在 SDK 的 `src/API` 目录中均已实现【247764572816424†L143-L147】。如果新的官方方法未在此列表中，请在仓库中创建相应类即可获得支持。