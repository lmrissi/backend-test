<?php

namespace App\Domains\Card;

use App\Domains\BaseDomain;
use App\Repositories\Account\FindByUser;
use App\Exceptions\InternalErrorException;
use App\Repositories\Card\CanUseExternalId;

/* SUGESTÃO DE MELHORIA
    Utilizar injeção de dependências FindByUser e CanUseExternalId no método construtor ou no handle

    Utilizar nomes para o método handle que representam melhor a ação realizada
    
    As chamadas nos repositories devem ser feitas pelos respectivos UseCases e não pelo Domain,
    evitando acoplamento desta camada com infraestrutura e APIs.
    
    Facilita testar as regras de negócio sem precisar de mocks de BD ou retorno de API.
    
    Domain deve conter as entidades e regras de negócio, UseCase orquestra o fluxo de ações da aplicação.

    *Vale para todas as classes de domain.
*/

class Register extends BaseDomain
{
    /**
     * Id da conta
     *
     * @var string
     */
    protected string $accountId;

    /**
     * Id do usuário
     *
     * @var string
     */
    protected string $userId;

    /**
     * Id do cartão
     *
     * @var string
     */
    protected string $cardId;

    /**
     * PIN do cartão
     *
     * @var string
     */
    protected string $pin;

    public function __construct(string $userId, string $pin, string $cardId)
    {
        $this->userId = $userId;
        $this->pin    = $pin;
        $this->cardId = $cardId;
    }

    /**
     * Busca o id de conta
     *
     * @return void
     */
    protected function findAccountId(): void
    {
        $account = (new FindByUser($this->userId))->handle();

        if (is_null($account)) {
            throw new InternalErrorException(
                'ACCOUNT_NOT_FOUND',
                161001001
            );
        }

        $this->accountId = $account['id'];
    }

    /**
     * Cartão não pode já estar vinculado
     */
    protected function checkExternalId()
    {
        if (!(new CanUseExternalId($this->cardId))->handle()) {
            throw new InternalErrorException(
                'Não é possível vincular esse cartão',
                0
            );
        }
    }

    /**
     * Checa se é possível vincular o cartão
     *
     * @return self
     */
    public function handle(): self
    {
        $this->findAccountId();
        $this->checkExternalId();

        return $this;
    }
}
