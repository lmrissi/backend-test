<?php

namespace App\Integrations\Banking\Card;

use App\Integrations\Banking\Gateway;
use App\Repositories\Account\FindByUser;
use App\Exceptions\InternalErrorException;

/* PONTO DE ATENÇÃO
    A camada de integração não deve acessar a camada de repository, o correto seria que o respectivo
    useCase buscasse os dados no repository e preparasse os dados para essa camada utilizar.
        1- Quebra a separação de responsabilidades
        2- Gera acoplamento com a camada de infraestrutura da aplicação
        3- Dificulta a realização de testes unitários e manutenção do código.
*/

class Find extends Gateway
{
    /**
     * Id externo da conta
     *
     * @var string
     */
    protected string $externalAccountId;

    /**
     * Id do usuário
     *
     * @var string
     */
    protected string $userId;

    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Busca os dados de conta
     *
     * @return void
     */
    protected function findAccountData(): void
    {
        $account = (new FindByUser($this->userId))->handle();

        if (is_null($account)) {
            throw new InternalErrorException(
                'ACCOUNT_NOT_FOUND',
                161001001
            );
        }

        $this->externalAccountId = $account['external_id'];
    }

    /**
     * Constroi a url da request
     *
     * @return string
     */
    protected function requestUrl(): string
    {
        return "account/$this->externalAccountId/card";
    }

    /**
     * Cria de uma conta
     *
     * @return array
     */
    public function handle(): array
    {
        $this->findAccountData();

        $url = $this->requestUrl();

        $request = $this->sendRequest(
            method: 'get',
            url:    $url,
            action: 'FIND_CARD',
            params: []
        );

        return $this->formatDetailsResponse($request);
    }
}
