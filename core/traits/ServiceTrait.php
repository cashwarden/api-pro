<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://github.com/cashwarden
 * @copyright Copyright (c) 2019 - 2022 forecho
 * @license https://github.com/cashwarden/api-pro/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\core\traits;

use app\core\services\AccountService;
use app\core\services\AnalysisService;
use app\core\services\BudgetService;
use app\core\services\CategoryService;
use app\core\services\LedgerService;
use app\core\services\MailerService;
use app\core\services\PayService;
use app\core\services\RecurrenceService;
use app\core\services\RuleService;
use app\core\services\TagService;
use app\core\services\TelegramService;
use app\core\services\TransactionService;
use app\core\services\UploadService;
use app\core\services\UserProService;
use app\core\services\UserService;
use app\core\services\WechatService;
use app\core\services\WishListService;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Trait ServiceTrait.
 * @property UserService $userService
 * @property UserProService $userProService
 * @property AccountService $accountService
 * @property TransactionService $transactionService
 * @property CategoryService $categoryService
 * @property RuleService $ruleService
 * @property TelegramService $telegramService
 * @property AnalysisService $analysisService
 * @property TagService $tagService
 * @property RecurrenceService $recurrenceService
 * @property UploadService $uploadService
 * @property MailerService $mailerService
 * @property BudgetService $budgetService
 * @property LedgerService $ledgerService
 * @property PayService $payService
 * @property WishListService $wishListService
 * @property WechatService $wechatService
 */
trait ServiceTrait
{
    /**
     * @return UserService|object
     */
    public function getUserService()
    {
        try {
            return Yii::createObject(UserService::class);
        } catch (InvalidConfigException $e) {
            return new UserService();
        }
    }

    /**
     * @return UserProService|object
     */
    public function getUserProService()
    {
        try {
            return Yii::createObject(UserProService::class);
        } catch (InvalidConfigException $e) {
            return new UserProService();
        }
    }

    /**
     * @return AccountService|object
     */
    public function getAccountService()
    {
        try {
            return Yii::createObject(AccountService::class);
        } catch (InvalidConfigException $e) {
            return new AccountService();
        }
    }

    /**
     * @return TransactionService|object
     */
    public function getTransactionService()
    {
        try {
            return Yii::createObject(TransactionService::class);
        } catch (InvalidConfigException $e) {
            return new TransactionService();
        }
    }

    /**
     * @return CategoryService|object
     */
    public function getCategoryService()
    {
        try {
            return Yii::createObject(CategoryService::class);
        } catch (InvalidConfigException $e) {
            return new CategoryService();
        }
    }

    /**
     * @return RuleService|object
     */
    public function getRuleService()
    {
        try {
            return Yii::createObject(RuleService::class);
        } catch (InvalidConfigException $e) {
            return new RuleService();
        }
    }


    /**
     * @return TelegramService|object
     */
    public function getTelegramService()
    {
        try {
            return Yii::createObject(TelegramService::class);
        } catch (InvalidConfigException $e) {
            return new TelegramService();
        }
    }

    /**
     * @return AnalysisService|object
     */
    public function getAnalysisService()
    {
        try {
            return Yii::createObject(AnalysisService::class);
        } catch (InvalidConfigException $e) {
            return new AnalysisService();
        }
    }

    /**
     * @return TagService|object
     */
    public function getTagService()
    {
        try {
            return Yii::createObject(TagService::class);
        } catch (InvalidConfigException $e) {
            return new TagService();
        }
    }

    /**
     * @return RecurrenceService|object
     */
    public function getRecurrenceService()
    {
        try {
            return Yii::createObject(RecurrenceService::class);
        } catch (InvalidConfigException $e) {
            return new RecurrenceService();
        }
    }

    /**
     * @return UploadService|object
     */
    public function getUploadService()
    {
        try {
            return Yii::createObject(UploadService::class);
        } catch (InvalidConfigException $e) {
            return new UploadService();
        }
    }

    /**
     * @return MailerService|object
     */
    public function getMailerService()
    {
        try {
            return Yii::createObject(MailerService::class);
        } catch (InvalidConfigException $e) {
            return new MailerService();
        }
    }


    /**
     * @return BudgetService|object
     */
    public function getBudgetService()
    {
        try {
            return Yii::createObject(BudgetService::class);
        } catch (InvalidConfigException $e) {
            return new BudgetService();
        }
    }

    /**
     * @return LedgerService|object
     */
    public function getLedgerService()
    {
        try {
            return Yii::createObject(LedgerService::class);
        } catch (InvalidConfigException $e) {
            return new LedgerService();
        }
    }

    /**
     * @return PayService|object
     */
    public function getPayService(): PayService
    {
        try {
            return Yii::createObject(PayService::class);
        } catch (InvalidConfigException $e) {
            return new PayService();
        }
    }


    /**
     * @return WishListService|object
     */
    public function getWishListService(): WishListService
    {
        try {
            return Yii::createObject(WishListService::class);
        } catch (InvalidConfigException $e) {
            return new WishListService();
        }
    }

    /**
     * @return WechatService|object
     */
    public function getWechatService(): WechatService
    {
        try {
            return Yii::createObject(WechatService::class);
        } catch (InvalidConfigException $e) {
            return new WechatService();
        }
    }
}
