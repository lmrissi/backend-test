<?php

namespace App\Repositories\Company;

use App\Models\Company;
use App\Repositories\BaseRepository;

/* SUGESTÃO DE MELHORIA
    O nome da variável user não condiz com o retorno da query, o correto
    deveria ser company.
*/

class CanUseDocumentNumber extends BaseRepository
{
    /**
     * CNPJ
     *
     * @var string
     */
    protected string $documentNumber;

    /**
     * Setar a model da empresa
     *
     * @return void
     */
    public function setModel(): void
    {
        $this->model = Company::class;
    }

    public function __construct(string $documentNumber)
    {
        $this->documentNumber = $documentNumber;

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
            ->where('document_number', $this->documentNumber)
            ->first();

        return is_null($user);
    }
}
