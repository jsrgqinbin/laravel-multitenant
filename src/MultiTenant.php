<?php


namespace Solutosoft\MultiTenant;

use RuntimeException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

trait MultiTenant
{

    /**
     * @inheritdoc
     */
    public static function bootMultiTenant()
    {
        if (!App::runningInConsole()) {

            static::addGlobalScope(new TenantScope());

            static::creating(function(Model $model)
            {
                $model->applyTenant();
            });
        }
    }

    /**
     * Sets tenant id column with current tenant
     *
     * @throws \Solutosoft\MultiTenant\TenantException
     */
    public function applyTenant()
    {
        /** @var TenantInterface $user */
        $user = Auth::user();
        $valid =  (!Auth::guest() && $user instanceof Tenant);
        $tenantId = $this->getAttribute(Tenant::ATTRIBUTE_NAME);

        if (!$tenantId) {
            if ($valid) {
                $this->setAttribute(Tenant::ATTRIBUTE_NAME, $user->getTenantId());
            } else {
                throw new RuntimeException("Current user must implement Tenant interface");
            }
        }
    }

    /**
     * Remove a registered Tenant global scope.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function withoutTenant()
    {
       return static::withoutGlobalScope(TenantScope::class);
    }

}
