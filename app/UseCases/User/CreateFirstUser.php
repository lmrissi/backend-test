<?php

namespace App\UseCases\User;

use Throwable;
use App\UseCases\BaseUseCase;
use App\Domains\User\Create as CreateUserDomain;
use App\Repositories\Token\Create as CreateToken;
use App\UseCases\Params\User\CreateFirstUserParams;
use App\Domains\Company\Create as CreateCompanyDomain;
use App\Repositories\User\Create as CreateUserRepository;
use App\Repositories\Company\Create as CreateCompanyRepository;

/* PONTO DE ATENÇÃO
    Sugiro alterar o fluxo das chamadas para que primeiro fossem feitas as validações da companhia e do usuário,
    para depois fazer a criação do usuário. Da forma como está no código seria possível criar uma companhia com um usuário inválido.
 */

/* SUGESTÃO DE MELHORIA
    Adicionar a tipagem de retorno de array na função handle
 */

class CreateFirstUser extends BaseUseCase
{
    /**
     * @var CreateFirstUserParams
     */
    protected CreateFirstUserParams $params;

    /**
     * Token de acesso
     *
     * @var string
     */
    protected string $token;

    /**
     * Empresa
     *
     * @var array
     */
    protected array $company;

    /**
     * Usuário
     *
     * @var array
     */
    protected array $user;

    public function __construct(
        CreateFirstUserParams $params
    ) {
        $this->params = $params;
    }

    /**
     * Valida a empresa
     *
     * @return CreateCompanyDomain
     */
    protected function validateCompany(): CreateCompanyDomain
    {
        return (new CreateCompanyDomain(
            $this->params->companyName,
            $this->params->companyDocumentNumber
        ))->handle();
    }

    /**
     * Cria a empresa
     *
     * @param CreateCompanyDomain $domain
     *
     * @return void
     */
    protected function createCompany(CreateCompanyDomain $domain): void
    {
        $this->company = (new CreateCompanyRepository($domain))->handle();
    }

    /**
     * Valida o usuário
     *
     * @return CreateUserDomain
     */
    protected function validateUser(): CreateUserDomain
    {
        return (new CreateUserDomain(
            $this->company['id'],
            $this->params->userName,
            $this->params->userDocumentNumber,
            $this->params->email,
            $this->params->password,
            'MANAGER'
        ))->handle();
    }

    /**
     * Cria o usuário
     *
     * @param CreateUserDomain $domain
     *
     * @return void
     */
    protected function createUser(CreateUserDomain $domain): void
    {
        $this->user = (new CreateUserRepository($domain))->handle();
    }

    /**
     * Criação de token de acesso
     *
     * @return void
     */
    protected function createToken(): void
    {
        $this->token = (new CreateToken($this->user['id']))->handle();
    }

    /**
     * Cria um usuário MANAGER e a empresa
     */
    public function handle()
    {
        try {
            $companyDomain = $this->validateCompany();
            $this->createCompany($companyDomain);
            $userDomain = $this->validateUser();
            $this->createUser($userDomain);
            $this->createToken();
        } catch (Throwable $th) {
            $this->defaultErrorHandling(
                $th,
                [
                    'params' => $this->params->toArray(),
                ]
            );
        }

        return [
            'user'    => $this->user,
            'company' => $this->company,
            'token'   => $this->token,
        ];
    }
}
