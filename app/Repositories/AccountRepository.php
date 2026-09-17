<?php

namespace App\Repositories;

use App\Interfaces\DefaultRepositoryInterface;
use App\Models\Account;

class AccountRepository extends BaseRepository implements DefaultRepositoryInterface
{
    public function __construct()
    {
        $this->prepare();
    }

    public function prepare()
    {
        $this->query = Account::query();
    }

    public function filteredByEstablishment($establishmentId)
    {
        $this->query->where('establishment_id', $establishmentId);

        return $this;
    }
}
