<?php

namespace App\Repositories\Card;

use App\Models\Card;
use App\Repositories\BaseRepository;

/* SUGESTÃO DE MELHORIAS
    Alteraria o nome da classe para ExternalIdIsValid ou IsExternalIdValid.
*/

class CanUseExternalId extends BaseRepository
{
    /**
     * Id externo
     *
     * @var string
     */
    protected string $externalId;

    /**
     * Setar a model do cartão
     *
     * @return void
     */
    public function setModel(): void
    {
        $this->model = Card::class;
    }

    public function __construct(string $externalId)
    {
        $this->externalId = $externalId;

        parent::__construct();
    }

    /**
     * Valida se o documento é único
     *
     * @return bool
     */
    public function handle(): bool
    {
        $user = $this->builder
            ->where('external_id', $this->externalId)
            ->first();

        return is_null($user);
    }
}
