<?php

namespace App\Domains\User;

use App\Domains\BaseDomain;
use Illuminate\Support\Facades\Hash;
use App\Repositories\User\CanUseEmail;
use App\Exceptions\InternalErrorException;

/* PONTO DE ATENÇÃO
    As chamadas nos repositories devem ser feitas pelos respectivos UseCases e não pelo Domain,
    evitando acoplamento desta camada com infraestrutura e APIs.
    
        1- Facilita testar as regras de negócio sem precisar de mocks de BD ou retorno de API.
        2- Domain deve conter as entidades e regras de negócio, UseCase orquestra o fluxo de ações da aplicação.

    Utilizar injeção de dependência CanUseDocumentNumber e CanUseEmail no método construtor ou no handle
        1- No caso injetar a dependência do UseCase
    
    Utilizar nomes para o método handle que representam melhor a ação realizada
*/

class Update extends BaseDomain
{
    /**
     * Id do usuário
     *
     * @var string
     */
    protected string $id;

    /**
     * Empresa
     *
     * @var string
     */
    protected string $companyId;

    /**
     * Nome
     *
     * @var string|null
     */
    protected ?string $name;

    /**
     * Email
     *
     * @var string|null
     */
    protected ?string $email;

    /**
     * Senha
     *
     * @var string|null
     */
    protected ?string $password;

    /**
     * Tipo
     *
     * @var string|null
     */
    protected ?string $type;

    public function __construct(
        string $id,
        string $companyId,
        ?string $name,
        ?string $email,
        ?string $password,
        ?string $type
    ) {
        $this->id        = $id;
        $this->companyId = $companyId;
        $this->name      = $name;
        $this->email     = $email;
        $this->type      = $type;

        $this->cryptPassword($password);
    }

    /**
     * Encripta a senha
     *
     * @param string|null $password
     *
     * @return void
     */
    protected function cryptPassword(?string $password): void
    {
        $this->password = !is_null($password) ? Hash::make($password) : null;
    }

    /**
     * Email deve ser únicos no sistema
     *
     * @return void
     */
    protected function checkEmail(): void
    {
        if (is_null($this->email)) {
            return;
        }
        if (!(new CanUseEmail($this->email))->handle()) {
            throw new InternalErrorException(
                'Não é possível adicionar o E-mail informado',
                0
            );
        }
    }

    /**
     * Valida o tipo
     *
     * @return void
     */
    protected function checkType(): void
    {
        if (is_null($this->type)) {
            return;
        }
        if (!in_array($this->type, ['USER', 'VIRTUAL', 'MANAGER'])) {
            throw new InternalErrorException(
                'Não é possível adicionar o tipo informado',
                0
            );
        }
    }

    /**
     * Checa se é possível realizar a criação do usuário
     *
     * @return self
     */
    public function handle(): self
    {
        $this->checkEmail();
        $this->checkType();

        return $this;
    }
}
