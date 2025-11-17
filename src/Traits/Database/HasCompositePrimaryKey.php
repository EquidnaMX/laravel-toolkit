<?php

/**
 * HasCompositePrimaryKey Trait
 *
 * @author Gabriel Ruelas
 * @license MIT
 * @version 1.0.0
 *
 * Enables Eloquent models to support composite primary keys.
 */

namespace Equidna\Toolkit\Traits\Database;

use Illuminate\Database\Eloquent\Builder;

trait HasCompositePrimaryKey
{
    /**
     * Set the keys for a save update query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery(mixed $query): Builder
    {
        $keys = $this->getKeyName();
        if (!is_array($keys)) {
            return parent::setKeysForSaveQuery($query);
        }

        foreach ($keys as $keyName) {
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }

        return $query;
    }

    /**
     * Get the primary key value for a save query.
     *
     * @param string|null $keyName
     * @return mixed
     */
    protected function getKeyForSaveQuery(mixed $keyName = null): mixed
    {
        if (is_null($keyName)) {
            $keyName = $this->getKeyName();
        }

        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }

        return $this->getAttribute($keyName);
    }
}

