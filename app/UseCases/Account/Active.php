<?php

namespace App\UseCases\Account;

use Throwable;
use App\UseCases\BaseUseCase;
use App\Repositories\Account\UpdateStatus as RepositoryUpdateStatus;
use App\Integrations\Banking\Account\UpdateStatus as IntegrationUpdateStatus;

/* PONTO DE ATENÇÃO
    Para manter a consistência dos dados do BD com o BaaS, seria interessante alterar a ordem das chamadas 
    das funções de atualização do status e realizar o controle da transação no banco de dados.

    1- Realizar a tentativa de atualizar a conta no BaaS. Em caso de falha gerar log e abortar.
    2- Em caso de sucesso de ativar no BaaS realiza a atualização no BD.
    3- Em caso de falha na atualização no BD gerar logs apontando a necessidade de retentativa.
    4- É possível implementar ainda uma política de retentativa para as duas chamadas de forma síncrôna ou assíncrona usando uma tabela
    ou fila.
 */

class Active extends BaseUseCase
{
    /**
     * Id do usuário
     *
     * @var string
     */
    protected string $userId;

    /**
     * Conta
     *
     * @var array
     */
    protected array $account;

    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Atualiza no banco de dados
     *
     * @return void
     */
    protected function updateDatabase(): void
    {
        (new RepositoryUpdateStatus($this->userId, 'active'))->handle();
    }

    /**
     * Atualiza a conta
     *
     * @return void
     */
    protected function updateStatus(): void
    {
        $this->account = (new IntegrationUpdateStatus($this->userId, 'active'))->handle();
    }

    /**
     * Ativa a conta
     */
    public function handle(): void
    {
        try {
            $this->updateDatabase();
            $this->updateStatus();
        } catch (Throwable $th) {
            $this->defaultErrorHandling(
                $th,
                [
                    'userId' => $this->userId,
                ]
            );
        }
    }
}
