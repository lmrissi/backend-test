<?php

namespace App\Integrations\Banking\Card;

use App\Integrations\Banking\Gateway;
use App\Exceptions\InternalErrorException;
use App\Domains\Card\Register as RegisterDomain;
use App\Repositories\Card\FindByUser as FindCardByUser;
use App\Repositories\Account\FindByUser as FindAccountByUser;

/* PONTO DE ATENÇÃO
    A camada de integração não deve acessar a camada de repository, o correto seria que o respectivo
    useCase buscasse os dados no repository e preparasse os dados para essa camada utilizar.
        1- Quebra a separação de responsabilidades
        2- Gera acoplamento com a camada de infraestrutura da aplicação
        3- Dificulta a realização de testes unitários e manutenção do código.
*/

class Register extends Gateway
{
    /**
     * Id externo do cartão
     *
     * @var string
     */
    protected string $externalCardId;

    /**
     * Id externo da conta
     *
     * @var string
     */
    protected string $externalAccountId;

    /**
     * Dados necessários para o registro do cartão
     *
     * @var RegisterDomain
     */
    protected RegisterDomain $domain;

    public function __construct(RegisterDomain $domain)
    {
        $this->domain = $domain;
    }

    /**
     * Busca os dados de conta
     *
     * @return void
     */
    protected function findAccountData(): void
    {
        $account = (new FindAccountByUser($this->domain->userId))->handle();

        if (is_null($account)) {
            throw new InternalErrorException(
                'ACCOUNT_NOT_FOUND',
                161001001
            );
        }

        $this->externalAccountId = $account['external_id'];
    }

    /**
     * Busca os dados de conta
     *
     * @return void
     */
    protected function findCardData(): void
    {
        $account = (new FindCardByUser($this->domain->userId))->handle();

        if (is_null($account)) {
            throw new InternalErrorException(
                'CARD_NOT_FOUND',
                149001001
            );
        }

        $this->externalCardId = $account['external_id'];
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
            method: 'post',
            url:    $url,
            action: 'REGISTER_CARD',
            params: [
                'pin' => $this->domain->pin,
                'id'  => $this->domain->cardId,
            ]
        );

        return $this->formatDetailsResponse($request);
    }
}
