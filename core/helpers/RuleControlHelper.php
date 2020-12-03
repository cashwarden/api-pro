<?php

namespace app\core\helpers;

use app\core\exceptions\InvalidArgumentException;
use app\core\types\LedgerMemberRule;

class RuleControlHelper
{
    /**
     * @var int 查看
     */
    public const VIEW = 0x1;

    /**
     * @var int 编辑
     */
    public const EDIT = 0x2;

    /**
     * @var int 管理
     */
    public const MANAGE = 0x4;

    public static function names(): array
    {
        return [
            self::VIEW => 'view',
            self::EDIT => 'edit',
            self::MANAGE => 'manage',
        ];
    }

    /**
     * 计算总权限值
     * @param array $permissions
     * @return int
     */
    public static function computePermission(array $permissions): int
    {
        $permissions = is_array($permissions) ? $permissions : func_get_args();
        $totalPermission = 0;
        foreach ($permissions as $permission) {
            $totalPermission |= $permission;
        }
        return $totalPermission;
    }

    /**
     * 判断是否有权限
     * @param int $totalPermission
     * @param int $permission
     * @return bool
     */
    public static function can(int $totalPermission, int $permission): bool
    {
        return (($totalPermission & $permission) === $permission);
    }

    /**
     * 根据角色获取对应的权限值
     * @param string $rule
     * @return int
     * @throws InvalidArgumentException
     */
    public static function getPermissionByRule(string $rule): int
    {
        $permissions = [];
        switch (LedgerMemberRule::toEnumValue($rule)) {
            case LedgerMemberRule::VIEWER:
                $permissions = [self::VIEW];
                break;
            case LedgerMemberRule::EDITOR:
                $permissions = [self::VIEW, self::EDIT];
                break;
            case LedgerMemberRule::OWNER:
                $permissions = [self::VIEW, self::EDIT, self::MANAGE];
                break;
        }
        return self::computePermission($permissions);
    }

    /**
     * 获取所有觉得的权限值
     * @return array
     * @throws InvalidArgumentException
     */
    public static function getRulesPermission(): array
    {
        $items = [];
        foreach (LedgerMemberRule::names() as $key => $name) {
            $items[self::getPermissionByRule($name)] = $key;
        }
        return $items;
    }

    /**
     * 根据权限值获取对应的角色
     * @param int $permission
     * @return string
     * @throws InvalidArgumentException
     */
    public static function getRuleByPermission(int $permission): string
    {
        $items = self::getRulesPermission();
        if (!isset($items[$permission])) {
            throw new InvalidArgumentException();
        }
        return $items[$permission];
    }
}
