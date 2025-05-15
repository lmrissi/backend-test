<?php

namespace App\Domains\Company;

use App\Domains\BaseDomain;
use App\Exceptions\InternalErrorException;
use App\Repositories\Company\CanUseDocumentNumber;

/* PONTO DE ATENÇÃO
    As chamadas nos repositories devem ser feitas pelos respectivos UseCases e não pelo Domain,
    evitando acoplamento desta camada com infraestrutura e APIs.
    
        1- Facilita testar as regras de negócio sem precisar de mocks de BD ou retorno de API.
        2- Domain deve conter as entidades e regras de negócio, UseCase orquestra o fluxo de ações da aplicação.

    Utilizar injeção de dependência CanUseDocumentNumber no método construtor ou no handle
        1- No caso injetar a dependência do UseCase
    
    Utilizar nomes para o método handle que representam melhor a ação realizada
*/

class Create extends BaseDomain
{
    /**
     * Nome
     *
     * @var string
     */
    protected string $name;

    /**
     * CNPJ
     *
     * @var string
     */
    protected string $documentNumber;

    public function __construct(string $name, string $documentNumber)
    {
        $this->name           = $name;
        $this->documentNumber = $documentNumber;
    }

    /**
     * Documento de empresa deve ser único no sistema
     */
    protected function checkDocumentNumber()
    {
        if (!(new CanUseDocumentNumber($this->documentNumber))->handle()) {
            throw new InternalErrorException(
                'Não é possível adicionar o CNPJ informado',
                0
            );
        }
    }

    /**
     * Checa se é possível criar a empresa
     *
     * @return self
     */
    public function handle(): self
    {
        $this->checkDocumentNumber();

        return $this;
    }
}
