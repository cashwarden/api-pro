<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

return [
    'appURL' => env('APP_URL'),
    'adminEmail' => env('ADMIN_EMAIL'),
    'senderEmail' => env('SENDER_EMAIL'),
    'senderName' => env('SENDER_NAME', env('APP_NAME')),
    'telegramToken' => env('TELEGRAM_TOKEN'),
    'telegramBotName' => env('TELEGRAM_BOT_NAME'),
    'userPasswordResetTokenExpire' => env('USER_RESET_TOKEN_EXPIRE', 3600),
    'seoKeywords' => env('SEO_KEYWORDS'),
    'seoDescription' => env('SEO_DESCRIPTION'),
    'googleAnalyticsAU' => env('GOOGLE_ANALYTICS_AU'),
    // 不记录 header 指定 key 的值到日志，默认值为 authorization，配置自定义会覆盖默认值
    'logFilterIgnoredHeaderKeys' => env('LOG_FILTER_IGNORED_HEADER_KEYS', 'authorization,token,cookie'),
    'logFilterIgnoredKeys' => env('LOG_FILTER_IGNORED_KEYS', 'password'), // 不记录日志
    'logFilterHideKeys' => env('LOG_FILTER_HIDE_KEYS'), // 用*代替所有数据
    'logFilterHalfHideKeys' => env('LOG_FILTER_HALF_HIDE_KEYS', 'email'), // 部分数据隐藏，只显示头部 20% 和尾部 20% 数据，剩下的用*代替
    'uploadSavePath' => '@webroot/uploads',
    'uploadWebPath' => '@web/uploads',
    'frontendURL' => env('FRONTEND_URL'),
    'verificationEmail' => env('VERIFICATION_EMAIL'),
    'emailHost' => env('EMAIL_HOST'),
    'emailUsername' => env('EMAIL_USERNAME'),
    'emailPassword' => env('EMAIL_PASSWORD'),
    'emailPort' => env('EMAIL_PORT', '465'),
    'emailEncryption' => env('EMAIL_ENCRYPTION', 'ssl'),
    'proUserPriceCent' => env('USER_PRO_PRICE_CENT', 3900),
    'userLedgerTotal' => env('USER_LEDGER_TOTAL', 1),
    'userAccountTotal' => env('USER_ACCOUNT_TOTAL', 5),
    'userRecurrenceTotal' => env('USER_RECURRENCE_TOTAL', 1),
    'userRuleTotal' => env('USER_RULE_TOTAL', 5),
    'useXunSearch' => env('USE_XUN_SEARCH', 0),
    'rapidapiKey' => env('RAPIDAPI_KEY'),
    'wechatAppId' => env('WECHAT_APP_ID'),
    'wechatAppSecret' => env('WECHAT_APP_SECRET'),
    'wechatToken' => env('WECHAT_TOKEN'),
    'superAdminUserId' => env('SUPER_ADMIN_USER_ID'),
    'userChildCount' => env('USER_CHILD_COUNT', 2),
    'memberIds' => [],
];
