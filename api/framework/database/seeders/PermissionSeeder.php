<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$mapKeys = [
			'list'                => 'Listar',
			'show'                => 'Visualizar',
			'create'              => 'Cadastrar',
			'edit'                => 'Editar',
			'delete'              => 'Deletar',
			'export'              => 'Exportar',
			'activate-deactivate' => 'Ativar/Desativar',
			'dashboard'           => 'Dashboard',
			'answer'              => 'Responder',
			'approve'             => 'Aprovar',
			'disapprove'          => 'Reprovar',
			'cancel'              => 'Cancelar',
			'mark-received'       => 'Marcar como recebido',
			'mark-receivable'     => 'Marcar como a receber',
			'mark-paid'           => 'Marcar como pago',
			'mark-unpaid'         => 'Marcar como a pagar',
			'likes'               => 'Curtidas',
			'change-status'       => 'Alterar situação',
		];

		$items = [];

		// General
		$items['log']['Log de alterações']     = ['list', 'show', 'export'];
		$items['roles']['Papéis e permissões'] = ['list', 'show', 'create', 'edit', 'delete', 'export'];
		$items['system-log']['Log do sistema'] = ['list', 'show', 'export'];
		// $items['websockets']['Websockets']     = ['dashboard'];

		// Users
		$items['customers']['Clientes']             = ['list', 'show', 'create', 'edit', 'delete', 'export'];
		$items['users']['Usuários administradores'] = ['list', 'show', 'create', 'edit', 'delete', 'export'];
        $items['profissionais']['Profissionais']    = ['list', 'show', 'create', 'edit', 'delete', 'export'];

		// Settings
		$items['settings-general']['Configurações gerais']                = ['list', 'edit'];
		$items['settings-notifications']['Configurações de notificações'] = ['list', 'edit'];

		// Push's
		$items['push-city']['Push por cidade']  = ['list', 'show', 'edit', 'create', 'delete', 'export'];
		$items['push-general']['Push geral']    = ['list', 'show', 'edit', 'create', 'delete', 'export'];
		$items['push-user']['Push por usuário'] = ['list', 'show', 'edit', 'create', 'delete', 'export'];
		$items['push-state']['Push por estado'] = ['list', 'show', 'edit', 'create', 'delete', 'export'];

		$items['notifications']['Notificações'] = ['list', 'show', 'edit', 'create', 'delete', 'export'];

		// Institucional Contents
		$items['privacy-policy']['Institucional - Política de Privacidade'] = ['edit'];
		$items['terms-of-use']['Institucional - Termos de uso']             = ['edit'];
		$items['grupo-aiz']['Institucional - Grupo AIZ'] = ['edit'];

		// Contents
		$items['faq']['Perguntas frequentes']        = ['list', 'show', 'create', 'edit', 'delete', 'export'];
        $items['motivosrecusa']['Motivos de recusa']        = ['list', 'show', 'create', 'edit', 'delete', 'export'];
		$items['report-problem']['Relatar problema'] = ['list', 'show', 'delete', 'export', 'answer'];
		$items['banners']['Banners']        = ['list', 'show', 'create', 'edit', 'delete', 'export'];
		$items['news']['Noticias']        = ['list', 'show', 'create', 'edit', 'delete', 'export'];
		$items['ver_mais']['Contéudo Ver Mais']        = ['list', 'show', 'create', 'edit', 'delete', 'export'];
		$items['categorias_treinamento']['Categorias de treinamento']        = ['list', 'show', 'create', 'edit', 'delete', 'export'];
		$items['treinamento']['Treinamento']        = ['list', 'show', 'create', 'edit', 'delete', 'export'];
		$items['manuais']['Manuais']        = ['list', 'show', 'create', 'edit', 'delete', 'export'];
		$items['protetores_tela']['Protetores de tela']        = ['list', 'show', 'create', 'edit', 'delete', 'export'];
		$items['vale_rio_doce']['Vale do rio doce']        = ['list', 'show', 'create', 'edit', 'delete', 'export'];
		$items['perfil_acesso']['Perfis de acesso']        = ['list', 'show', 'create', 'edit', 'delete', 'export'];
		$items['preferencias']['Preferências']        = ['list', 'show', 'create', 'edit', 'delete', 'export'];

		// onboarding
		$items['onboarding']['Onboarding']        = ['list', 'show', 'create', 'edit', 'delete', 'export'];

		// Notifications
		$items['notifications']['Notificações']        = ['list', 'show', 'create', 'edit', 'delete', 'export'];

		// customers deleted
		$items['customers-deleted']['Clientes excluídos']  = ['list', 'show', 'export'];

        // profissionais deletados
        $items['profissionais-deleted']['Profissionais excluídos']  = ['list', 'show', 'export'];

		// marcas
		$items['marcas']['Marcas']        = ['list', 'show', 'create', 'edit', 'delete', 'export'];

		// modelos
		$items['modelos']['Modelos']        = ['list', 'show', 'create', 'edit', 'delete', 'export'];

		// marcas veiculos
		$items['marcasveiculos']['Marcas de veículos']        = ['list', 'show', 'create', 'edit', 'delete', 'export'];

		// modelos veiculos
		$items['modelosveiculos']['Modelos de veículos']        = ['list', 'show', 'create', 'edit', 'delete', 'export'];

		// motorcycles
		$items['motorcycles']['Motos']        = ['list', 'show', 'create', 'edit', 'delete', 'export'];

		// Pedido Socorro
		$items['pedido-socorro']['Pedidos Socorro']        = ['list', 'show', 'export'];

		// veiculos
		$items['vehicles']['Veículos']        = ['list', 'show', 'create', 'edit', 'delete', 'export'];

		// If run only this seeder
		$is_only_this_seeder = $this->command->option('class') === 'PermissionSeeder';

		if( $is_only_this_seeder )
		{
			if (DB::getDriverName() === 'mysql') {
				DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
			} elseif (DB::getDriverName() === 'sqlite') {
				DB::statement('PRAGMA foreign_keys = OFF;');
			}

			Permission::truncate();

			if (DB::getDriverName() === 'mysql') {
				DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
			} elseif (DB::getDriverName() === 'sqlite') {
				DB::statement('PRAGMA foreign_keys = ON;');
			}
		}

		foreach( $items as $name => $data )
		{
			foreach( $data as $group => $keys )
			{
				$index = 0;

				foreach( $keys as $key )
				{
					Permission::create([
						'uuid'  => uuid(),
						'key'   => $name . '.' . $key,
						'name'  => $mapKeys[$key],
						'group' => $group,
						'order' => $index,
					]);

					$index++;
				}
			}
		}

		if( $is_only_this_seeder )
		{
			Role::find(1)->permissions()->sync(Permission::pluck('id'));
		}
	}
}
