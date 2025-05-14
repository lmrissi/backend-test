<?php

namespace App\Repositories\Account;

use App\Models\Account;
use App\Repositories\BaseRepository;

/* SUGESTÃO DE MELHORIA
    Alterar o nome da função handle para createAccount, por exemplo

    Passar os dados para a função handle como parâmetros.
    1- Reutilização sem precisar criar várias instâncias,
    2- Facilita os testes, diminui o número de setups,
    3- Não cria acoplamento com o estado da classe.

    *Essa sugestão vale para todas as classes dentro de Reposotories

    Armazenar o valor do status 'BLOCK' em um enum, facilitando possíveis alterações nesse valor.
*/

class Create extends BaseRepository
{
    /**
     * Id de usuário
     *
     * @var string
     */
    protected string $userId;

    /**
     * Id externo
     *
     * @var string
     */
    protected string $externalId;

    /**
     * Setar a model do usuário
     *
     * @return void
     */
    public function setModel(): void
    {
        $this->model = Account::class;
    }

    public function __construct(string $userId, string $externalId)
    {
        $this->userId     = $userId;
        $this->externalId = $externalId;

        parent::__construct();
    }

    /**
     * Criação de conta
     *
     * @return array
     */
    public function handle(): array
    {
        return $this->create(
            [
                'user_id'     => $this->userId,
                'external_id' => $this->externalId,
                'status'      => 'BLOCK',
            ]
        );
    }
}
