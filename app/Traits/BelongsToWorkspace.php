<?php

namespace App\Traits;

use App\Models\Workspace;
use App\Scopes\WorkspaceScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToWorkspace
{
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new WorkspaceScope);
    }

    /**
     * The Model's Workspace.
     *
     * @return BelongsTo
     */
    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }
}
