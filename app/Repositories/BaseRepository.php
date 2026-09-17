<?php

namespace App\Repositories;

abstract class BaseRepository
{
    protected $query;

    abstract public function prepare();

    public function byParams(array $params)
    {
        $this->query->where($params);

        return $this;
    }

    public function sortBy($column, $direction = 'ASC')
    {
        $this->query->orderBy($column, $direction);

        return $this;
    }

    public function first()
    {
        $result = $this->query->first();

        $this->prepare();

        return $result;
    }

    public function get()
    {
        $result = $this->query->get();

        $this->prepare();

        return $result;
    }

    public function add(array $content)
    {
        $result = $this->query->create($content);

        $this->prepare();

        return $result;
    }

    public function update(array $content)
    {
        $result = $this->query->update($content);

        $this->prepare();

        return $result;
    }
}
