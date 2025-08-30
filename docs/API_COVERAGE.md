# Telegram Bot API → SDK Endpoint Mapping

This document maps official Bot API method names to SDK endpoint classes. The SDK uses a deterministic mapping:

- SDK class: `XBot\Telegram\API\{StudlyMethod}`
- Bot API method: `lcfirst({StudlyMethod})`

Example: `XBot\Telegram\API\GetMe` ↔ `getMe`

Snake case aliases are supported on the Bot instance (e.g., `get_webhook_info` calls `GetWebhookInfo`).

Target version: Bot API 9.2 (2025‑08‑15)

Current implemented endpoints (method ↔ class)

- addStickerToSet ↔ AddStickerToSet
- answerCallbackQuery ↔ AnswerCallbackQuery
- answerInlineQuery ↔ AnswerInlineQuery
- answerPreCheckoutQuery ↔ AnswerPreCheckoutQuery
- answerShippingQuery ↔ AnswerShippingQuery
- answerWebAppQuery ↔ AnswerWebAppQuery
- approveChatJoinRequest ↔ ApproveChatJoinRequest
- approveSuggestedPost ↔ ApproveSuggestedPost
- declineSuggestedPost ↔ DeclineSuggestedPost
- banChatMember ↔ BanChatMember
- banChatSenderChat ↔ BanChatSenderChat
- close ↔ Close
- closeForumTopic ↔ CloseForumTopic
- closeGeneralForumTopic ↔ CloseGeneralForumTopic
- copyMessage ↔ CopyMessage
- copyMessages ↔ CopyMessages
- createChatInviteLink ↔ CreateChatInviteLink
- createForumTopic ↔ CreateForumTopic
- createInvoiceLink ↔ CreateInvoiceLink
- createNewStickerSet ↔ CreateNewStickerSet
- deleteBusinessMessages ↔ DeleteBusinessMessages
- deleteChatPhoto ↔ DeleteChatPhoto
- deleteChatStickerSet ↔ DeleteChatStickerSet
- deleteForumTopic ↔ DeleteForumTopic
- deleteMessage ↔ DeleteMessage
- deleteMessages ↔ DeleteMessages
- deleteMyCommands ↔ DeleteMyCommands
- deleteStickerFromSet ↔ DeleteStickerFromSet
- deleteWebhook ↔ DeleteWebhook
- declineChatJoinRequest ↔ DeclineChatJoinRequest
- editChatInviteLink ↔ EditChatInviteLink
- editForumTopic ↔ EditForumTopic
- editGeneralForumTopic ↔ EditGeneralForumTopic
- editMessageCaption ↔ EditMessageCaption
- editMessageMedia ↔ EditMessageMedia
- editMessageReplyMarkup ↔ EditMessageReplyMarkup
- editMessageText ↔ EditMessageText
- editMessageLiveLocation ↔ EditMessageLiveLocation
- exportChatInviteLink ↔ ExportChatInviteLink
- forwardMessage ↔ ForwardMessage
- forwardMessages ↔ ForwardMessages
- getChat ↔ GetChat
- getChatAdministrators ↔ GetChatAdministrators
- getChatBoosts ↔ GetChatBoosts
- getChatMember ↔ GetChatMember
- getChatMemberCount ↔ GetChatMemberCount
- getChatMenuButton ↔ GetChatMenuButton
- getCustomEmojiStickers ↔ GetCustomEmojiStickers
- getFile ↔ GetFile
- getForumTopicIconStickers ↔ GetForumTopicIconStickers
- getGameHighScores ↔ GetGameHighScores
- getMe ↔ GetMe
- getMyCommands ↔ GetMyCommands
- getMyDefaultAdministratorRights ↔ GetMyDefaultAdministratorRights
- getMyDescription ↔ GetMyDescription
- getMyName ↔ GetMyName
- getMyShortDescription ↔ GetMyShortDescription
- getMyStarBalance ↔ GetMyStarBalance
- getStickerSet ↔ GetStickerSet
- getUpdates ↔ GetUpdates
- getUserChatBoosts ↔ GetUserChatBoosts
- getUserProfilePhotos ↔ GetUserProfilePhotos
- getWebhookInfo ↔ GetWebhookInfo
- hideGeneralForumTopic ↔ HideGeneralForumTopic
- leaveChat ↔ LeaveChat
- logOut ↔ LogOut
- pinChatMessage ↔ PinChatMessage
- promoteChatMember ↔ PromoteChatMember
- readBusinessMessage ↔ ReadBusinessMessage
- refundStarPayment ↔ RefundStarPayment
- reopenForumTopic ↔ ReopenForumTopic
- reopenGeneralForumTopic ↔ ReopenGeneralForumTopic
- replaceStickerInSet ↔ ReplaceStickerInSet
- restrictChatMember ↔ RestrictChatMember
- revokeChatInviteLink ↔ RevokeChatInviteLink
- sendAnimation ↔ SendAnimation
- sendAudio ↔ SendAudio
- sendChatAction ↔ SendChatAction
- sendContact ↔ SendContact
- sendDice ↔ SendDice
- sendDocument ↔ SendDocument
- sendGame ↔ SendGame
- sendInvoice ↔ SendInvoice
- sendLocation ↔ SendLocation
- sendMediaGroup ↔ SendMediaGroup
- sendMessage ↔ SendMessage
- sendPhoto ↔ SendPhoto
- sendPaidMedia ↔ SendPaidMedia
- sendPoll ↔ SendPoll
- sendSticker ↔ SendSticker
- sendVenue ↔ SendVenue
- sendVideo ↔ SendVideo
- sendVideoNote ↔ SendVideoNote
- sendVoice ↔ SendVoice
- setChatAdministratorCustomTitle ↔ SetChatAdministratorCustomTitle
- setChatDescription ↔ SetChatDescription
- setChatMenuButton ↔ SetChatMenuButton
- setChatPermissions ↔ SetChatPermissions
- setChatPhoto ↔ SetChatPhoto
- setChatStickerSet ↔ SetChatStickerSet
- setChatTitle ↔ SetChatTitle
- setMessageReaction ↔ SetMessageReaction
- setMyCommands ↔ SetMyCommands
- setMyDefaultAdministratorRights ↔ SetMyDefaultAdministratorRights
- setMyDescription ↔ SetMyDescription
- setMyName ↔ SetMyName
- setMyShortDescription ↔ SetMyShortDescription
- setStickerEmojiList ↔ SetStickerEmojiList
- setStickerKeywords ↔ SetStickerKeywords
- setStickerMaskPosition ↔ SetStickerMaskPosition
- setStickerPositionInSet ↔ SetStickerPositionInSet
- setStickerSetTitle ↔ SetStickerSetTitle
- setWebhook ↔ SetWebhook
- stopMessageLiveLocation ↔ StopMessageLiveLocation
- stopPoll ↔ StopPoll
- unbanChatMember ↔ UnbanChatMember
- unbanChatSenderChat ↔ UnbanChatSenderChat
- unhideGeneralForumTopic ↔ UnhideGeneralForumTopic
- unpinAllChatMessages ↔ UnpinAllChatMessages
- unpinAllForumTopicMessages ↔ UnpinAllForumTopicMessages
- unpinAllGeneralForumTopicMessages ↔ UnpinAllGeneralForumTopicMessages
- unpinChatMessage ↔ UnpinChatMessage
- uploadStickerFile ↔ UploadStickerFile

Status

- Implemented endpoints: all listed methods present in `src/API`.
- To audit against the latest official list, compare this mapping with the Bot API documentation for your target version. If a method is missing, add `XBot\Telegram\API\{StudlyMethod}` and it will be callable as `$bot->{methodName}()`.

Notes for Bot API 9.2
- Direct messages topics and suggested posts are supported as options passthrough (e.g., `direct_messages_topic_id`, `suggested_post_parameters`).
- Checklists supported via `reply_parameters.checklist_task_id` in send methods.
