<?php

namespace App\Api\Foundation\Repository\Eloquent;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Api\Foundation\Repository\EloquentRepositoryInterface;

abstract class BaseRepository implements EloquentRepositoryInterface
{
    /**
     * Execute the query as a "select" statement.
     *
     * @param array $options
     * @return Builder[]|Collection
     */
    public function all(array $options = []): Collection|array
    {
        return $this->optionsQuery($options)->get();
    }

    /**
     * Determine the optional query parameter.
     *
     * @param array $options
     * @return Builder
     */
    protected function optionsQuery(array $options): Builder
    {
        $query = $this->connection()->query();

        if (isset($options['limit'])) {
            $query = $query->limit($options['limit']);
        }

        if (isset($options['offset'])) {
            $query = $query->offset($options['offset']);
        }

        if (isset($options['order_by'])) {
            if (is_array($options['order_by'])) {
                foreach ($options['order_by'] as $column => $orderBy) {
                    $query = $query->orderBy($column, $orderBy);
                }
            } else {
                $query = $query->orderBy('created_at', $options['order_by']);
            }
        } else {
            $query = $query->orderBy('created_at', 'desc');
        }

        if (isset($options['with'])) {
            $query = $query->with($options['with']);
        }

        if (isset($options['withDefault'])) {
            $query = $query->with(['createAdmin', 'updateAdmin', 'deleteAdmin']);
        }

        if (isset($options['only'])) {
            $query = $query->select($options['only']);
        }

        if (isset($options['id'])) {
            $query = $query->where('id', '=', $options['id']);
        }

        if (!empty($options['user_id'])) {
            $query = $query->where('user_id', '=', $options['user_id']);
        }

        return $query;
    }

    /**
     * Execute the query and get the first result with option.
     *
     * @param array $options
     * @return Builder[]|Collection
     */
    public function getDataByOptions(array $options = []): array|Collection
    {
        return $this->optionsQuery($options)->get();
    }

    /**
     * Execute the query and get the first result with id.
     *
     * @param int $id
     * @param array $relations
     * @return Builder|Model|object|null
     */
    public function getDataById(int $id, array $relations = [])
    {
        return $this->connection()->query()->with($relations)->where('id', $id)->first();
    }

    /**
     * Execute the query and get the first result with first().
     *
     * @param array $relations
     * @return Builder|Model|object|null
     */
    public function getFirstOnly(array $relations = [])
    {
        return $this->connection()->query()->with($relations)->first();
    }

    /**
     * Execute the query and get the first result with first().
     *
     * @param array $options
     * @param array $relations
     * @return Builder|Model|object|null
     */
    public function getLatest(array $options = [], array $relations = [])
    {
        return $this->optionsQuery($options)->with($relations)->latest()->first();
    }

    /**
     * Save a new model and return the instance.
     *
     * @param array $data
     * @return Builder|Model
     * @throws Exception
     */
    public function insert(array $data): Model|Builder
    {
        $record = $this->connection()->query()->create($data);

        if (!$record) {
            throw new Exception("Failed to insert record into the database.");
        }

        return $record;
    }

    /**
     * Update single record in the database with id.
     *
     * @param array $data
     * @param int $id
     * @return int
     */
    public function update(array $data, int $id): int
    {
        return $this->connection()->query()->find($id)->update($data);
    }

    public function updateByOptions(array $data, array $options = []): int
    {
        return $this->optionsQuery($options)->update($data);
    }

    public function increment($amount, $column, int $id): int
    {
        return $this->connection()->query()->where('id', $id)
            ->increment($column, $amount);
    }

    public function decrement($amount, $column, int $id): int
    {
        return $this->connection()->query()->where('id', $id)
            ->decrement($column, $amount);
    }

    /**
     * Update records in the database with ids.
     *
     * @param array $data
     * @param array $ids
     * @return int
     */
    public function updateWithIds(array $data, array $ids): int
    {
        return $this->connection()->query()->whereIn('id', $ids)->update($data);
    }

    /**
     * Delete the record from the database with id.
     *
     * @param int $id
     * @return mixed
     */
    public function destroy(int $id)
    {
        return $this->connection()->query()->find($id)->delete();
    }

    public function forceDestroy(int $id)
    {
        return $this->connection()->query()->find($id)->forceDelete();
    }

    /**
     * Delete the models from the database with ids.
     *
     * @param array $ids
     * @return mixed
     */
    public function destroyWithIds(array $ids): mixed
    {
        return $this->connection()->query()->whereIn('id', $ids)->delete();
    }

    /**
     * Delete the models from the database with options
     * @param array $options
     * @return mixed
     */
    public function destroyByOptions(array $options): mixed
    {
        return $this->optionsQuery($options)->delete();
    }

    /**
     * Get the total row's count.
     *
     * @param array $options
     * @return int
     */
    public function totalCount(array $options = []): int
    {
        return $this->optionsQuery($options)->count();
    }

    public function sum(string $column, array $options = [])
    {
        return $this->optionsQuery($options)->sum($column);
    }

    /**
     * @param array $options
     * @return bool
     */
    public function checkExistsByOptions(array $options = []): bool
    {
        return $this->optionsQuery($options)->exists();
    }

    public function checkExistsWithDeletedAt(array $options = []): bool
    {
        return $this->optionsQuery($options)->withTrashed()->exists();
    }

    /**
     * Model Connection.
     *
     * @return Model
     */
    abstract public function connection(): Model;
}
