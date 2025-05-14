<?php

namespace App\Repositories\User;

use App\Models\User;
use App\Repositories\BaseRepository;

/* SUGESTÃO DE MELHORIA
    Alteraria o nome da classe para DocumentNumberIsValid ou IsDocumentNumberValid.
*/
class CanUseDocumentNumber extends BaseRepository
{
    /**
     * CPF
     *
     * @var string
     */
    protected string $document_number;

    /**
     * Setar a model do usuário
     *
     * @return void
     */
    public function setModel(): void
    {
        $this->model = User::class;
    }

    public function __construct(string $document_number)
    {
        $this->document_number = $document_number;

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
            ->where('document_number', $this->document_number)
            ->first();

        return is_null($user);
    }
}
