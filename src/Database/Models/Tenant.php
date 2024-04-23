<?php

declare(strict_types=1);

namespace Stancl\Tenancy\Database\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Contracts;
use Stancl\Tenancy\Database\Concerns;
use Stancl\Tenancy\Database\TenantCollection;
use Stancl\Tenancy\Events;

/**
 * @property string|int $id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property array $data
 *
 * @method static TenantCollection all($columns = ['*'])
 */
class Tenant extends Model implements Contracts\Tenant
{
    use Concerns\CentralConnection,
        Concerns\GeneratesIds,
        Concerns\HasDataColumn,
        Concerns\HasInternalKeys,
        Concerns\TenantRun,
        Concerns\InvalidatesResolverCache;

    protected static $modelsShouldPreventAccessingMissingAttributes = false;

    protected $table = 'tenants';
    protected $primaryKey = 'id';
    protected $guarded = [];

    protected $cleanTenantKey = true;
    
    public function getTenantKeyName(): string
    {
        return 'id';
    }

    public function getTenantKey()
    {
        $tenantKey = $this->getAttribute($this->getTenantKeyName());

        if (! $this->cleanTenantKey) {
            return $tenantKey;
        }
        
        return preg_replace('![^'.preg_quote('-').'\pL\pN\s]+!u', '', $tenantKey);
    }

    public function newCollection(array $models = []): TenantCollection
    {
        return new TenantCollection($models);
    }

    protected $dispatchesEvents = [
        'saving' => Events\SavingTenant::class,
        'saved' => Events\TenantSaved::class,
        'creating' => Events\CreatingTenant::class,
        'created' => Events\TenantCreated::class,
        'updating' => Events\UpdatingTenant::class,
        'updated' => Events\TenantUpdated::class,
        'deleting' => Events\DeletingTenant::class,
        'deleted' => Events\TenantDeleted::class,
    ];
}
