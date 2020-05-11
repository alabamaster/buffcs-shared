<?php 
namespace app\models;

use app\core\Model;
use app\core\Config;

use app\lib\DB;
use PDO;

class Privileges extends Model
{
	public function validateAdd($post)
	{
		if ( mb_strlen($post['pri_name']) < 3 || mb_strlen($post['pri_name']) > 30 ) 
		{
			$this->error = 'Название должно быть от 3 до 30 символов';
			return false;
		}
		if ( !isset($post['server']) ) {
			$this->error = 'Выберите сервер';
			return false;
		}
		if ( mb_strlen($post['access']) < 1 ) {
			$this->error = 'Укажите флаги доступа';
			return false;
		}
		if ( mb_strlen($post['time']) < 1 ) {
			$this->error = 'Укажите время';
			return false;
		}
		if ( mb_strlen($post['price']) < 1 ) {
			$this->error = 'Укажите цену';
			return false;
		}
		return true;
	}

	public function addprivilege($post, $file)
	{
		$arr_time = explode(',', $post['time']);
		$arr_cost = explode(',', $post['price']);
		$n = count( $arr_cost );

		$active = ( !isset($post['active']) ) ? $active = 0 : $active = $post['active'];

		try {
			DB::beginTransaction();
			DB::run('INSERT INTO `ez_privileges` (`sid`, `name`, `access`, `active`) VALUES (?, ?, ?, ?)', [ $post['server'], $post['pri_name'], $post['access'], $active ]);

			$lastInsertId = DB::lastInsertId();

			for( $i = 0; $i < $n; $i++ )
			{
				DB::run('INSERT INTO `ez_privileges_times` (`pid`, `sid`, `price`, `time`) VALUES (?, ?, ?, ?)', [$lastInsertId, $post['server'], $arr_cost[$i], $arr_time[$i] ]);
			}

			if ( !self::uploadPrivilegeIcon($file, $lastInsertId) ) {
				DB::rollBack();
				return false;
			}
		} catch (Exception $e) {
			DB::rollBack();
			$this->error = 'Ошибка добавления в БД';
			echo 'Error: ' . $e->getMessage();
			return false;
		}
		return true;
	}

	public function uploadPrivilegeIcon($file, $pid)
	{
		if ( Config::get('ICONS') == 0 ) {
			$defaultFileName = 'unknown.png';
			DB::run('UPDATE `ez_privileges` SET `icon_img` = ? WHERE `id` = ?', [ $defaultFileName, $pid ]);
			return true;
		}
		if ( $file['privilegeIcon']['size'] == 0 || empty($file['privilegeIcon']) || !isset($file['privilegeIcon']) || $file['privilegeIcon']['error'] != 0 ) {
			$defaultFileName = 'unknown.png';
			DB::run('UPDATE `ez_privileges` SET `icon_img` = ? WHERE `id` = ?', [ $defaultFileName, $pid ]);
			return true;
		}

		$file["privilegeIcon"]["name"] = 'pid-' . $pid . '.png';

		$target_dir = "icons/";
		$target_file = $target_dir . basename($file["privilegeIcon"]["name"]);
		$uploadOk = 1;
		$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
		// является ли файл изображения или поддельным изображением
		if( isset($_POST["submit"]) ) {
			$check = getimagesize($file["privilegeIcon"]["tmp_name"]);
			if( $check !== false ) {
				// echo "File is an image - " . $check["mime"] . ".";
				$uploadOk = 1;
			} else {
				// echo "File is not an image.";
				$uploadOk = 0;
				$this->deletePrivilegeById($pid);;
				$this->error = 'Файл является поддельным изображением';
				return false;
			}
		}
		// существует ли файл
		if ( file_exists($target_file) ) {
			// echo "Sorry, file already exists.";
			$uploadOk = 0;
			$this->deletePrivilegeById($pid);
			$this->error = 'Файл с таким названием уже существует на сервере';
			return false;
		}
		// размер файла// max 500kb
		if ( $file["privilegeIcon"]["size"] > 500000 ) { 
			// echo "Sorry, your file is too large.";
			$uploadOk = 0;
			$this->deletePrivilegeById($pid);
			$this->error = 'Максимальный размер файла 500КБ';
			return false;
		}
		// определенные форматы файлов
		if( $imageFileType != "png" ) {
			// echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
			$uploadOk = 0;
			$this->deletePrivilegeById($pid);
			$this->error = 'Можно загружать файлы только в формате PNG';
			return false;
		}
		// если что то не так
		if ( $uploadOk == 0 ) {
			// echo "Sorry, your file was not uploaded.";
			$this->deletePrivilegeById($pid);
			$this->error = 'К сожалению, при загрузке файла произошла ошибка.';
			return false;
		//  если все окей
		} else {
			if (move_uploaded_file($file["privilegeIcon"]["tmp_name"], $target_file)) 
			{
				$fileName = basename($file['privilegeIcon']['name']);
				DB::run('UPDATE `ez_privileges` SET `icon_img` = ? WHERE `id` = ?', [ $fileName, $pid ]);
				// echo "The file ". basename( $file["privilegeIcon"]["name"]). " has been uploaded.";
				return true;
			} else {
				// echo "Sorry, there was an error uploading your file.";
				$this->deletePrivilegeById($pid);
				$this->error = 'К сожалению, при перемещении файла произошла ошибка.';
				return false;
			}
		}
		return false;
	}

	public function editPrivilege($post)
	{
		try {
			DB::run('UPDATE `ez_privileges` SET `name` = ?, `access` = ?, `active` = ? WHERE `id` = ?', [ $post['p_name'], $post['p_access'], $post['p_active'], $post['privilege_id'] ]);
			return true;
		} catch (Exception $e) {
			echo 'Error: ' . $e->getMessage();
		}
		$this->error = 'Ошибка редактирования: model->Admin->deletePrivilegeById';
		return false;
	}

	public function deletePrivilegeById($pid)
	{
		if(Config::get('ICONS') == 1) 
		{
			$query = DB::run('SELECT `icon_img` FROM `ez_privileges` WHERE `id` = ?', [ $pid ])->fetch(PDO::FETCH_ASSOC);
			if ( $query['icon_img'] != null || $query['icon_img'] != '' ) {
				if ( @!unlink('icons/' . $query['icon_img']) ) {
					$this->error = 'Ошибка при удалении иконки, возможно она не найдена';
					return false;
				}
			}
		}

		try {
			DB::run('DELETE FROM `ez_privileges` WHERE `id` = ?', [ $pid ]);
			DB::run('DELETE FROM `ez_privileges_times` WHERE `pid` = ?', [ $pid ] );
			return true;
		} catch (Exception $e) {
			echo 'Error: ' . $e->getMessage();
		}
		$this->error = 'Ошибка удаления: model->Admin->deletePrivilegeById';
		return false;
	}

	public function deleteAllPrivileges()
	{
		if (file_exists('icons/')) {
			foreach (glob('icons/*') as $file) {
				if ( $file == 'unknown.png' ) {
					continue;
				}
				unlink($file);
			}
		}
		try {
			DB::beginTransaction();
			DB::run('TRUNCATE TABLE `ez_privileges`');
			DB::run('TRUNCATE TABLE `ez_privileges_times`');
			DB::commit();
		} catch (Exception $e) {
			DB::rollBack();
			echo 'Error: ' . $e->getMessage();
			$this->error = 'Ошибка удаления всех привилегий!';
			return false;
		}
		return true;
	}

	// infoprivileges Action
	public function saveAboutPrivilege($post)
	{
		if( !isset($post['privilege']) ) {
			$this->error = 'Выберите привилегию';
			return false;
		}

		$sql = DB::run('SELECT COUNT(*) FROM `ez_editor` WHERE `pid` = ?', [ (int)$post['privilege'] ])->fetchColumn();
		
		if ( $sql > 0 ) 
		{
			try {
				DB::run('UPDATE `ez_editor` SET `content` = ?, `created` = ? WHERE `pid` = ?', [ $post['editor'], $this->time, (int)$post['privilege'] ]);
				return true;
			} catch (Exception $e) {
				echo 'Error: ' . $e->getMessage();
				$this->erro = 'Ошибка сохранения';
				return false;
			}
		} else {
			try {
				DB::run('INSERT INTO `ez_editor`(`content`, `created`, `pid`) VALUES (?, ?, ?)', [ $post['editor'], $this->time, (int)$post['privilege'] ]);
				return true;
			} catch (Exception $e) {
				echo 'Error: ' . $e->getMessage();
				$this->erro = 'Ошибка сохранения';
				return false;
			}
		}
	}
}