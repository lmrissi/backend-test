<?php

namespace App\Repositories\Card;

use App\Models\Card;
use App\Repositories\BaseRepository;

/* SUGESTÃO DE MELHORIA
    O método joinAccount nunca é utilizado no código não deveria ser chamado no handle?
*/

class FindByUser extends BaseRepository
{
    /**
     * Id do usuário
     *
     * @var string
     */
    protected string $userId;

    /**
     * Setar a model do cartão
     *
     * @return void
     */
    public function setModel(): void
    {
        $this->model = Card::class;
    }

    public function __construct(string $userId)
    {
        $this->userId = $userId;

        parent::__construct();
    }

    /**
     * Join com accounts
     *
     * @return void
     */
    protected function joinAccount(): void
    {
        $this->builder->leftJoin(
            'accounts',
            'accounts.id',
            '=',
            'cards.account_id'
        );
    }

    /**
     * Enconta a conta
     *
     * @return array|null
     */
    public function handle(): ?array
    {
        $this->builder->where('accounts.user_id', $this->userId);

        return $this->first(['cards.*']);
    }
}
