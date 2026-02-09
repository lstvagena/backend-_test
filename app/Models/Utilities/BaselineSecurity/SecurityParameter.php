<?php

namespace App\Models\Utilities\BaselineSecurity;

use Illuminate\Database\Eloquent\Model;

class SecurityParameter extends Model
{
    protected $fillable = [
        'is_minimum_password_length_enabled',
        'minimum_password_length',
        'is_maximum_password_length_enabled',
        'maximum_password_length',
        'is_minimum_lowercase_required_enabled',
        'minimum_lowercase_required',
        'is_minimum_uppercase_required_enabled',
        'minimum_uppercase_required',
        'is_minimum_numeric_required_enabled',
        'minimum_numeric_required',
        'is_minimum_special_char_required_enabled',
        'minimum_special_char_required',
        'is_maximum_login_attempts_enabled',
        'maximum_login_attempts',
        'is_idle_logout_time_enabled',
        'idle_logout_time',
        'is_admin_password_expire_after_enabled',
        'admin_password_expire_after',
        'is_user_password_expire_after_enabled',
        'user_password_expire_after',
        'is_remind_password_expiration_enabled',
        'remind_password_expiration_after',
        'is_disable_unused_accounts_after_enabled',
        'disable_unused_accounts_after',
        'is_restrict_username_as_password',
        'is_restrict_sequential_char_as_password',
        'is_restrict_repetitive_char_as_password',
        'is_force_login_allowed',
        'do_lock_account_on_dual_login',
    ];
}
